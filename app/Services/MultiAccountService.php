<?php

namespace App\Services;

use App\Http\Controllers\TagihanController;
use App\Models\MultiAccountGroup;
use App\Models\MultiAccountMember;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MultiAccountService
{
    private static function wsUrl(?string $path = null): string
    {
        $url = rtrim(config('services.tagihan_ws.url'), '?&');

        if ($path) {
            $url .= (str_contains($url, '?') ? '&' : '?').'path='.$path;
        }

        return $url;
    }

    private static function postWs(string $path, array $payload, int $timeout = 12): array
    {
        try {
            $url = self::wsUrl($path);
            $response = Http::timeout($timeout)
                ->withoutVerifying()
                ->acceptJson()
                ->asJson()
                ->post($url, $payload);

            $json = $response->json();
            if (is_array($json) && !empty($json['status'])) {
                return $json;
            }

            $formResponse = Http::timeout($timeout)
                ->withoutVerifying()
                ->acceptJson()
                ->asForm()
                ->post($url, $payload);
            $formJson = $formResponse->json();
            if (is_array($formJson) && !empty($formJson['status'])) {
                return $formJson;
            }

            if (is_array($json)) {
                return $json;
            }
            if (is_array($formJson)) {
                return $formJson;
            }

            $body = (string) $response->body();
            Log::warning('multi-akun WS non-json', [
                'path' => $path,
                'http' => $response->status(),
                'body' => mb_substr($body, 0, 500),
            ]);

            $message = 'Response WS tidak valid';
            if (stripos($body, 'Fatal error') !== false || stripos($body, 'Call to undefined method') !== false) {
                $message = 'File WS di server belum lengkap. Upload ulang ws/controllers/TagihanController.php dan ws/models/MultiAkun.php';
            } elseif (stripos($body, 'Endpoint tidak ditemukan') !== false) {
                $message = 'Endpoint multi akun belum tersedia di WS server. Upload ulang ws/index.php';
            } elseif (trim($body) === '') {
                $message = 'WS tidak mengembalikan data. Periksa koneksi WS_TAGIHAN_URL';
            }

            return [
                'status' => false,
                'message' => $message,
            ];
        } catch (\Throwable $e) {
            Log::error('multi-akun WS call failed', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => false,
                'message' => 'Gagal menghubungi web service multi akun',
            ];
        }
    }

    public static function listForNoCust(string $noCust, ?string $activeNoCust = null): Collection
    {
        $normalized = TagihanController::normalizeVa($noCust);
        $active = TagihanController::normalizeVa($activeNoCust ?: $noCust);
        $local = self::localAccounts($normalized, $active);
        $sessionAccounts = collect(session('tagihan.linked_accounts', []));
        $merged = $local
            ->concat($sessionAccounts)
            ->unique(fn ($acc) => TagihanController::normalizeVa($acc['no_cust'] ?? ''))
            ->values();

        if ($merged->isNotEmpty()) {
            return $merged;
        }

        $result = self::postWs('multi-akun-list', [
            'va' => $normalized,
            'active_va' => $active,
        ], 5);

        $accounts = [];
        if (!empty($result['status'])) {
            $accounts = $result['data']['accounts'] ?? [];
        }

        return collect(is_array($accounts) ? $accounts : [])
            ->unique(fn ($acc) => TagihanController::normalizeVa($acc['no_cust'] ?? ''))
            ->values();
    }

    /**
     * @return array{status:bool,message?:string,group_id?:int,members?:Collection,active_no_cust?:string}
     */
    public static function linkAccountsViaWs(
        string $activeVa,
        string $activeAcademicYear,
        string $newVa,
        string $password,
        string $newAcademicYear
    ): array {
        $password = trim($password);
        $newVa = trim($newVa);

        $cek = TagihanController::postWsWithVaCandidates('cek-tagihan-pw', [
            'password' => $password,
            'tahun_akademik' => 'all',
        ], $newVa);

        if (empty($cek['status']) || empty($cek['data'])) {
            $cek = TagihanController::postWsWithVaCandidates('cek-tagihan', [
                'password' => $password,
                'tahun_akademik' => 'all',
            ], $newVa);
        }

        if (empty($cek['status']) || empty($cek['data'])) {
            Log::info('multi-akun cek akun failed', [
                'va' => TagihanController::normalizeVa($newVa),
                'message' => $cek['message'] ?? null,
            ]);

            return [
                'status' => false,
                'message' => $cek['message'] ?? 'VA atau password salah, atau data tidak ditemukan',
            ];
        }

        $resolvedNis = TagihanController::normalizeVa(
            $cek['data']['no_cust'] ?? $cek['data']['num2nd'] ?? $newVa
        );

        $local = self::linkLocally($activeVa, $cek['data']);
        if (!empty($local['status'])) {
            return $local;
        }

        $session = self::linkInSession($activeVa, $cek['data']);
        if (!empty($session['status'])) {
            return $session;
        }

        $remote = self::postWs('multi-akun-tambah', [
            'active_va' => TagihanController::normalizeVa($activeVa),
            'active_tahun_akademik' => $activeAcademicYear,
            'va' => $resolvedNis,
            'password' => $password,
            'tahun_akademik' => $newAcademicYear,
        ], 8);

        if (!empty($remote['status'])) {
            $data = is_array($remote['data'] ?? null) ? $remote['data'] : [];

            return [
                'status' => true,
                'message' => $remote['message'] ?? 'Akun berhasil ditambahkan',
                'group_id' => $data['group_id'] ?? null,
                'members' => collect($data['accounts'] ?? []),
                'active_no_cust' => $data['active_no_cust'] ?? TagihanController::normalizeVa($activeVa),
            ];
        }

        return [
            'status' => false,
            'message' => $remote['message'] ?? 'Akun terverifikasi, tetapi gagal disimpan ke multi akun',
        ];
    }

    /**
     * @return array{status:bool,message?:string,result?:array,accounts?:Collection,va_display?:string}
     */
    public static function switchViaWs(
        string $activeVa,
        string $targetVa,
        string $academicYear
    ): array {
        $local = self::switchLocally($activeVa, $targetVa, $academicYear);
        if (!empty($local['status'])) {
            return $local;
        }

        $result = self::postWs('multi-akun-switch', [
            'active_va' => TagihanController::normalizeVa($activeVa),
            'target_va' => TagihanController::normalizeVa($targetVa),
            'tahun_akademik' => $academicYear,
        ], 8);

        if (empty($result['status']) || empty($result['data'])) {
            return [
                'status' => false,
                'message' => $local['message'] ?? $result['message'] ?? 'Gagal beralih akun',
            ];
        }

        $data = $result['data'];
        $accounts = collect($data['accounts'] ?? []);
        $groupId = $data['group_id'] ?? null;
        unset($data['accounts'], $data['active_no_cust'], $data['group_id']);

        return [
            'status' => true,
            'message' => $result['message'] ?? 'Berhasil beralih akun',
            'result' => [
                'status' => true,
                'message' => $result['message'] ?? 'Berhasil beralih akun',
                'data' => $data,
            ],
            'accounts' => $accounts,
            'va_display' => $data['va_number'] ?? $data['no_cust'] ?? $targetVa,
            'group_id' => $groupId,
        ];
    }

    /**
     * @return array{status:bool,message?:string,group_id?:int|null,members?:Collection,active_no_cust?:string}
     */
    public static function hapusViaWs(string $activeVa, string $targetVa): array
    {
        $local = self::removeLocally($activeVa, $targetVa);
        if (!empty($local['status'])) {
            return $local;
        }

        $result = self::postWs('multi-akun-hapus', [
            'active_va' => TagihanController::normalizeVa($activeVa),
            'target_va' => TagihanController::normalizeVa($targetVa),
        ], 8);

        if (empty($result['status'])) {
            return [
                'status' => false,
                'message' => $result['message'] ?? 'Gagal menghapus akun dari multi akun',
            ];
        }

        $data = is_array($result['data'] ?? null) ? $result['data'] : [];

        return [
            'status' => true,
            'message' => $result['message'] ?? 'Akun berhasil dihapus dari multi akun',
            'group_id' => $data['group_id'] ?? null,
            'members' => collect($data['accounts'] ?? []),
            'active_no_cust' => $data['active_no_cust'] ?? TagihanController::normalizeVa($activeVa),
            'removed_no_cust' => $data['removed_no_cust'] ?? TagihanController::normalizeVa($targetVa),
        ];
    }

    public static function putSession(
        string $noCust,
        string $vaDisplay,
        string $academicYear,
        $groupId = null
    ): void {
        $existing = session('tagihan');
        $existing = is_array($existing) ? $existing : [];

        session([
            'tagihan' => array_merge($existing, [
                'active_no_cust' => TagihanController::normalizeVa($noCust),
                'va_display' => $vaDisplay,
                'academic_year' => $academicYear,
                'group_id' => $groupId,
            ]),
        ]);
    }

    private static function localAccounts(string $noCust, string $activeNoCust): Collection
    {
        try {
            $member = MultiAccountMember::query()
                ->where('no_cust', $noCust)
                ->orWhere('no_cust', $activeNoCust)
                ->first();

            if (!$member) {
                return collect();
            }

            return $member->group
                ? $member->group->members->map(fn (MultiAccountMember $item) => [
                    'group_id' => $item->group_id,
                    'no_cust' => $item->no_cust,
                    'va_display' => $item->va_display ?: TagihanController::formatNova($item->no_cust),
                    'nama' => $item->nama,
                    'kelas' => $item->kelas,
                    'jenjang' => $item->jenjang,
                    'is_active' => TagihanController::normalizeVa($item->no_cust) === $activeNoCust,
                ])
                : collect();
        } catch (\Throwable $e) {
            Log::warning('multi-akun local list failed', ['error' => $e->getMessage()]);

            return collect();
        }
    }

    private static function linkLocally(string $activeVa, array $cekData): array
    {
        try {
            $activeNis = TagihanController::normalizeVa($activeVa);
            $newNis = TagihanController::normalizeVa($cekData['no_cust'] ?? '');
            if ($activeNis === '' || $newNis === '') {
                return ['status' => false];
            }

            $activeMember = MultiAccountMember::query()->where('no_cust', $activeNis)->first();
            $siswa = session('tagihan.siswa', []);
            $siswa = is_array($siswa) ? $siswa : [];
            $linked = collect(session('tagihan.linked_accounts', []))->first(
                fn ($acc) => TagihanController::normalizeVa($acc['no_cust'] ?? '') === $activeNis
            );

            if ($activeMember) {
                $group = $activeMember->group;
            } else {
                $group = MultiAccountGroup::create();
                MultiAccountMember::create([
                    'group_id' => $group->id,
                    'no_cust' => $activeNis,
                    'va_display' => TagihanController::formatNova($activeNis),
                    'nama' => $siswa['nama'] ?? ($linked['nama'] ?? null),
                    'kelas' => $siswa['kelas'] ?? ($linked['kelas'] ?? null),
                    'jenjang' => $siswa['jenjang'] ?? ($linked['jenjang'] ?? null),
                ]);
            }

            MultiAccountMember::query()->updateOrCreate(
                ['no_cust' => $newNis],
                [
                    'group_id' => $group->id,
                    'va_display' => TagihanController::formatNova($cekData['no_cust'] ?? $newNis),
                    'nama' => $cekData['nama'] ?? null,
                    'kelas' => $cekData['kelas'] ?? null,
                    'jenjang' => $cekData['jenjang'] ?? null,
                ]
            );

            $accounts = self::localAccounts($activeNis, $activeNis);

            return [
                'status' => true,
                'message' => 'Akun berhasil ditambahkan',
                'group_id' => $group->id,
                'members' => $accounts,
                'active_no_cust' => $activeNis,
            ];
        } catch (\Throwable $e) {
            Log::warning('multi-akun local fallback failed', ['error' => $e->getMessage()]);

            return ['status' => false];
        }
    }

    private static function switchLocally(string $activeVa, string $targetVa, string $academicYear): array
    {
        $activeNis = TagihanController::normalizeVa($activeVa);
        $targetNis = TagihanController::normalizeVa($targetVa);
        $accounts = self::listForNoCust($activeNis, $activeNis);
        $known = $accounts->contains(
            fn ($acc) => TagihanController::normalizeVa($acc['no_cust'] ?? '') === $targetNis
        );

        if (!$known) {
            return [
                'status' => false,
                'message' => 'Akun tidak ada di daftar multi akun',
            ];
        }

        $json = TagihanController::postWsWithVaCandidates('cek-tagihan', [
            'tahun_akademik' => $academicYear ?: 'all',
        ], $targetNis);

        if (empty($json['status']) || empty($json['data'])) {
            return [
                'status' => false,
                'message' => $json['message'] ?? 'Gagal beralih akun',
            ];
        }

        $vaDisplay = TagihanController::formatNova($json['data']['no_cust'] ?? $targetNis);

        return [
            'status' => true,
            'message' => 'Berhasil beralih akun',
            'result' => $json,
            'accounts' => $accounts->map(fn ($acc) => array_merge($acc, [
                'is_active' => TagihanController::normalizeVa($acc['no_cust'] ?? '') === $targetNis,
            ])),
            'va_display' => $vaDisplay,
            'group_id' => $accounts->first()['group_id'] ?? null,
        ];
    }

    private static function removeLocally(string $activeVa, string $targetVa): array
    {
        $activeNis = TagihanController::normalizeVa($activeVa);
        $targetNis = TagihanController::normalizeVa($targetVa);

        try {
            MultiAccountMember::query()->where('no_cust', $targetNis)->delete();
        } catch (\Throwable $e) {
            Log::warning('multi-akun local delete failed', ['error' => $e->getMessage()]);
        }

        $linked = session('tagihan.linked_accounts', []);
        if (is_array($linked)) {
            $linked = array_values(array_filter(
                $linked,
                fn ($acc) => TagihanController::normalizeVa($acc['no_cust'] ?? '') !== $targetNis
            ));
            session(['tagihan.linked_accounts' => $linked]);
        }

        $accounts = self::listForNoCust($activeNis, $activeNis);

        return [
            'status' => true,
            'message' => 'Akun berhasil dihapus dari multi akun',
            'group_id' => $accounts->first()['group_id'] ?? null,
            'members' => $accounts,
            'active_no_cust' => $activeNis,
            'removed_no_cust' => $targetNis,
        ];
    }

    private static function linkInSession(string $activeVa, array $cekData): array
    {
        $activeNis = TagihanController::normalizeVa($activeVa);
        $newNis = TagihanController::normalizeVa($cekData['no_cust'] ?? '');
        if ($activeNis === '' || $newNis === '') {
            return [
                'status' => false,
                'message' => 'VA atau password salah, atau data tidak ditemukan',
            ];
        }

        $current = session('tagihan.linked_accounts', []);
        $current = is_array($current) ? $current : [];
        $byNis = [];
        foreach ($current as $acc) {
            $key = TagihanController::normalizeVa($acc['no_cust'] ?? '');
            if ($key !== '') {
                $byNis[$key] = $acc;
            }
        }

        $siswa = session('tagihan.siswa', []);
        $siswa = is_array($siswa) ? $siswa : [];
        if (!isset($byNis[$activeNis])) {
            $byNis[$activeNis] = [
                'group_id' => null,
                'no_cust' => $activeNis,
                'va_display' => TagihanController::formatNova($activeNis),
                'nama' => $siswa['nama'] ?? null,
                'kelas' => $siswa['kelas'] ?? null,
                'jenjang' => $siswa['jenjang'] ?? null,
            ];
        }

        $byNis[$newNis] = [
            'group_id' => $byNis[$activeNis]['group_id'] ?? null,
            'no_cust' => $newNis,
            'va_display' => TagihanController::formatNova($cekData['no_cust'] ?? $newNis),
            'nama' => $cekData['nama'] ?? null,
            'kelas' => $cekData['kelas'] ?? null,
            'jenjang' => $cekData['jenjang'] ?? null,
        ];

        $accounts = collect(array_values($byNis))->map(fn ($acc) => array_merge($acc, [
            'is_active' => TagihanController::normalizeVa($acc['no_cust'] ?? '') === $activeNis,
        ]));

        session(['tagihan.linked_accounts' => $accounts->all()]);

        return [
            'status' => true,
            'message' => 'Akun berhasil ditambahkan',
            'group_id' => $byNis[$activeNis]['group_id'] ?? null,
            'members' => $accounts,
            'active_no_cust' => $activeNis,
        ];
    }

    public static function syncMemberAfterLogin(array $data, string $vaDisplay, string $academicYear): void
    {
        $noCust = TagihanController::normalizeVa($data['no_cust'] ?? $vaDisplay);
        self::linkInSession($noCust, $data);
        $accounts = self::listForNoCust($noCust, $noCust);
        $groupId = $accounts->first()['group_id'] ?? null;

        self::putSession($noCust, $vaDisplay, $academicYear, $groupId);
    }
}
