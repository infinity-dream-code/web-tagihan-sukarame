<?php

namespace App\Services;

use App\Http\Controllers\TagihanController;
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

    private static function postWs(string $path, array $payload): array
    {
        try {
            $response = Http::timeout(30)
                ->withoutVerifying()
                ->acceptJson()
                ->asJson()
                ->post(self::wsUrl($path), $payload);

            $json = $response->json();
            if (is_array($json)) {
                return $json;
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
        $result = self::postWs('multi-akun-list', [
            'va' => TagihanController::normalizeVa($noCust),
            'active_va' => TagihanController::normalizeVa($activeNoCust ?: $noCust),
        ]);

        if (empty($result['status'])) {
            return collect();
        }

        $accounts = $result['data']['accounts'] ?? [];

        return collect(is_array($accounts) ? $accounts : []);
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
        $result = ['status' => false, 'message' => 'Gagal menambahkan multi akun'];
        foreach (TagihanController::vaCandidates($newVa) as $candidate) {
            $result = self::postWs('multi-akun-tambah', [
                'active_va' => TagihanController::normalizeVa($activeVa),
                'active_tahun_akademik' => $activeAcademicYear,
                'va' => $candidate,
                'password' => $password,
                'tahun_akademik' => $newAcademicYear,
            ]);

            if (!empty($result['status'])) {
                break;
            }
        }

        if (empty($result['status'])) {
            return [
                'status' => false,
                'message' => $result['message'] ?? 'Gagal menambahkan multi akun. Pastikan nomor VA dan password benar.',
            ];
        }

        $data = is_array($result['data'] ?? null) ? $result['data'] : [];

        return [
            'status' => true,
            'message' => $result['message'] ?? 'Akun berhasil ditambahkan',
            'group_id' => $data['group_id'] ?? null,
            'members' => collect($data['accounts'] ?? []),
            'active_no_cust' => $data['active_no_cust'] ?? TagihanController::normalizeVa($activeVa),
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
        $result = self::postWs('multi-akun-switch', [
            'active_va' => TagihanController::normalizeVa($activeVa),
            'target_va' => TagihanController::normalizeVa($targetVa),
            'tahun_akademik' => $academicYear,
        ]);

        if (empty($result['status']) || empty($result['data'])) {
            return [
                'status' => false,
                'message' => $result['message'] ?? 'Gagal beralih akun',
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
        $result = self::postWs('multi-akun-hapus', [
            'active_va' => TagihanController::normalizeVa($activeVa),
            'target_va' => TagihanController::normalizeVa($targetVa),
        ]);

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
        session([
            'tagihan' => [
                'active_no_cust' => TagihanController::normalizeVa($noCust),
                'va_display' => $vaDisplay,
                'academic_year' => $academicYear,
                'group_id' => $groupId,
            ],
        ]);
    }

    public static function syncMemberAfterLogin(array $data, string $vaDisplay, string $academicYear): void
    {
        $noCust = TagihanController::normalizeVa($data['no_cust'] ?? $vaDisplay);
        $accounts = self::listForNoCust($noCust, $noCust);
        $groupId = $accounts->first()['group_id'] ?? null;

        self::putSession($noCust, $vaDisplay, $academicYear, $groupId);
    }
}
