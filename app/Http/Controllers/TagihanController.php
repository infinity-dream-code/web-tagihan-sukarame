<?php

namespace App\Http\Controllers;

use App\Services\MultiAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TagihanController extends Controller
{
    private function wsUrl(?string $path = null): string
    {
        $url = rtrim(config('services.tagihan_ws.url'), '?&');

        if ($path) {
            $url .= (str_contains($url, '?') ? '&' : '?').'path='.$path;
        }

        return $url;
    }

    public static function vaPrefix(): string
    {
        return '777726';
    }

    public static function vaPrefixes(): array
    {
        $prefixes = ['797777', '96240', '797783', '757777', '797766', '751000', '777726', '77726'];
        usort($prefixes, fn ($a, $b) => strlen($b) <=> strlen($a));

        return $prefixes;
    }

    public static function vaSuffixLength(): int
    {
        return max(1, 16 - strlen(self::vaPrefix()));
    }

    public static function stripVaPrefix(?string $va): string
    {
        $va = preg_replace('/\D+/', '', (string) $va);
        if ($va === '') {
            return '';
        }

        $prefixes = self::vaPrefixes();
        for ($i = 0; $i < 4; $i++) {
            $hit = false;
            foreach ($prefixes as $prefix) {
                if (str_starts_with($va, $prefix) && strlen($va) > strlen($prefix)) {
                    $va = substr($va, strlen($prefix));
                    $hit = true;
                    break;
                }
            }
            if (!$hit) {
                break;
            }
        }

        return $va;
    }

    public static function vaCandidates(?string $va): array
    {
        $raw = preg_replace('/\D+/', '', (string) $va);
        if ($raw === '') {
            return [];
        }

        $afterPrefix = self::stripVaPrefix($raw);
        $trimmed = ltrim($afterPrefix, '0');
        if ($trimmed === '') {
            $trimmed = $afterPrefix;
        }

        $padded = str_pad($trimmed, self::vaSuffixLength(), '0', STR_PAD_LEFT);
        $formatted = self::vaPrefix().$padded;

        return array_values(array_unique(array_filter([
            $formatted,
            $padded,
            $trimmed,
            $afterPrefix,
            $raw,
            self::vaPrefix().$trimmed,
        ], fn ($v) => $v !== '' && $v !== '-')));
    }

    public static function normalizeVa(?string $va): string
    {
        $afterPrefix = self::stripVaPrefix($va);
        if ($afterPrefix === '') {
            return '';
        }

        $trimmed = ltrim($afterPrefix, '0');

        return $trimmed !== '' ? $trimmed : $afterPrefix;
    }

    public static function formatNova(?string $nocust): string
    {
        $n = self::normalizeVa($nocust);

        if ($n === '' || $n === '-') {
            return '-';
        }

        return self::vaPrefix().str_pad($n, self::vaSuffixLength(), '0', STR_PAD_LEFT);
    }

    public static function postWsWithVaCandidates(string $path, array $payload, ?string $vaInput = null): array
    {
        $url = rtrim(config('services.tagihan_ws.url'), '?&');
        $url .= (str_contains($url, '?') ? '&' : '?').'path='.$path;

        $candidates = self::vaCandidates($vaInput ?? ($payload['va'] ?? ''));
        if (!$candidates) {
            $candidates = [preg_replace('/\s+/', '', (string) ($payload['va'] ?? ''))];
        }

        $last = ['status' => false, 'message' => 'VA atau password salah, atau data tidak ditemukan'];

        $local = \App\Services\BillingTagihanService::cek($path, $payload, $vaInput);
        if (is_array($local) && !empty($local['status'])) {
            return $local;
        }

        foreach ($candidates as $candidate) {
            $try = $payload;
            $try['va'] = $candidate;

            try {
                $response = Http::timeout(30)
                    ->withoutVerifying()
                    ->acceptJson()
                    ->asJson()
                    ->post($url, $try);

                $json = $response->json();
                if (is_array($json) && !empty($json['status'])) {
                    return $json;
                }

                $formResponse = Http::timeout(30)
                    ->withoutVerifying()
                    ->acceptJson()
                    ->asForm()
                    ->post($url, $try);
                $formJson = $formResponse->json();
                if (is_array($formJson) && !empty($formJson['status'])) {
                    return $formJson;
                }

                if (is_array($json)) {
                    $last = $json;
                } elseif (is_array($formJson)) {
                    $last = $formJson;
                }
            } catch (\Throwable $e) {
                Log::warning('WS VA candidate failed', [
                    'path' => $path,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if (is_array($local)) {
            return $local;
        }

        return $last;
    }

    public function cek(Request $request)
    {
        $request->validate([
            'no_cust' => 'required|string',
            'academic_year' => 'required|string'
        ]);

        $response = Http::timeout(30)
            ->withoutVerifying()
            ->post($this->wsUrl('cek-tagihan'), [
                'va' => self::normalizeVa($request->no_cust),
                'tahun_akademik' => $request->academic_year
            ]);

        $result = $response->json();
        $result = $this->withNova($result, $request->no_cust);

        return view('index', compact('result'))
            ->with([
                'va' => $request->no_cust,
                'academic_year' => $request->academic_year
            ]);
    }

    public function cek2(Request $request)
    {
        $request->validate([
            'no_cust' => 'required|string',
            'password' => 'required|string',
            'academic_year' => 'nullable|string'
        ]);

        $academicYear = $request->input('academic_year', 'all');
        if (!is_string($academicYear) || $academicYear === '' || stripos($academicYear, 'Semua') !== false) {
            $academicYear = 'all';
        }

        $payload = [
            'password' => $request->password,
            'tahun_akademik' => 'all'
        ];

        $json = self::postWsWithVaCandidates('cek-tagihan-pw', $payload, $request->no_cust);
        if (empty($json['status'])) {
            $json = self::postWsWithVaCandidates('cek-tagihan', $payload, $request->no_cust);
        }

        if (empty($json['status'])) {
            Log::info('WS cek-tagihan-pw failed', [
                'va' => self::normalizeVa($request->no_cust),
                'status' => $json['status'] ?? null,
                'message' => $json['message'] ?? null,
            ]);
        }

        $result = $this->withNova($json, $request->no_cust);

        if (empty($result['status'])) {
            return back()->with([
                'error' => $result['message'] ?? 'VA atau password salah, atau data tidak ditemukan',
                'va' => $request->no_cust,
                'academic_year' => $academicYear
            ])->withInput($request->except('password'));
        }

        return $this->renderIndex3AfterLogin($result, $request->no_cust, $academicYear);
    }

    public function loginByToken(string $token)
    {
        $token = strtolower(trim($token));

        $response = Http::timeout(30)
            ->withoutVerifying()
            ->acceptJson()
            ->asJson()
            ->post($this->wsUrl('token-login'), [
                'token' => $token,
            ]);

        $json = $response->json();
        $result = $this->withNova(is_array($json) ? $json : null, null);

        if (empty($result['status']) || empty($result['data'])) {
            $message = $result['message'] ?? 'Link login tidak valid, sudah kadaluarsa, atau sudah dipakai';
            if ($response->failed() && empty($json)) {
                $message = 'Gagal menghubungi web service login token';
            }

            return redirect('/')
                ->with('error', $message);
        }

        $noCust = $result['data']['no_cust'] ?? '';
        $academicYear = $result['data']['tahun_dipilih'] ?? 'all';
        if (!is_string($academicYear) || $academicYear === '' || stripos($academicYear, 'Semua') !== false) {
            $academicYear = 'all';
        }

        $va = self::formatNova($noCust);

        return $this->renderIndex3AfterLogin($result, $va, $academicYear);
    }

    private function renderIndex3AfterLogin(array $result, string $vaDisplay, string $academicYear)
    {
        $multiAccounts = collect();
        try {
            session([
                'tagihan.siswa' => [
                    'no_cust' => self::normalizeVa($result['data']['no_cust'] ?? $vaDisplay),
                    'nama' => $result['data']['nama'] ?? null,
                    'kelas' => $result['data']['kelas'] ?? null,
                    'jenjang' => $result['data']['jenjang'] ?? null,
                ],
            ]);
            MultiAccountService::syncMemberAfterLogin(
                $result['data'],
                $vaDisplay,
                $academicYear
            );

            $multiAccounts = MultiAccountService::listForNoCust(
                $result['data']['no_cust'] ?? $vaDisplay,
                self::normalizeVa($vaDisplay)
            );
        } catch (\Throwable $e) {
            Log::warning('multi-akun sync after login failed', [
                'error' => $e->getMessage(),
            ]);
            session([
                'tagihan' => [
                    'active_no_cust' => self::normalizeVa($vaDisplay),
                    'va_display' => $vaDisplay,
                    'academic_year' => $academicYear,
                    'group_id' => null,
                ],
            ]);
        }

        return view('index3', compact('result', 'multiAccounts'))
            ->with([
                'va' => $vaDisplay,
                'academic_year' => $academicYear
            ]);
    }

    private function withNova($result, ?string $fallback = null): array
    {
        if (!is_array($result)) {
            return ['status' => false, 'message' => 'Terjadi kesalahan'];
        }

        if (!empty($result['data'])) {
            $nocust = $result['data']['no_cust']
                ?? $result['data']['num2nd']
                ?? $result['data']['va_number']
                ?? $fallback;
            $result['data']['va_number'] = self::formatNova($nocust);
            $result['data']['kelompok'] = $result['data']['kelompok']
                ?? $result['data']['jurusan']
                ?? $result['data']['DESC03']
                ?? '-';
            $result['data']['angkatan'] = $result['data']['angkatan']
                ?? $result['data']['tahun_akademik']
                ?? $result['data']['tahun_masuk']
                ?? $result['data']['DESC04']
                ?? '-';
            $result = self::normalizeBillAmounts($result);
        }

        return $result;
    }

    private function extractVa($result, $fallback = null)
    {
        if (!is_array($result)) {
            return $fallback;
        }

        $data = $result['data'] ?? null;
        if (is_array($data)) {
            foreach (['va_number', 'NOVA', 'nova', 'NOCUST', 'nocust'] as $key) {
                if (array_key_exists($key, $data)) {
                    return $data[$key];
                }
            }
        }

        if (is_string($data) || is_numeric($data)) {
            return $data;
        }

        foreach (['va_number', 'nova', 'NOVA'] as $key) {
            if (array_key_exists($key, $result)) {
                return $result[$key];
            }
        }

        return $fallback;
    }

    public static function normalizeBillAmounts(array $result): array
    {
        foreach (['tagihan', 'tagihan_lunas'] as $key) {
            if (empty($result['data'][$key]) || !is_array($result['data'][$key])) {
                continue;
            }

            foreach ($result['data'][$key] as &$item) {
                if (!is_array($item)) {
                    continue;
                }

                $total = (int) ($item['total_tagihan'] ?? 0);
                $hasPaymentLeft = array_key_exists('paymentleft', $item) || array_key_exists('PAYMENTLEFT', $item);
                $hasBillPaid = array_key_exists('billpaid', $item) || array_key_exists('BILLPAID', $item);

                if ($hasPaymentLeft) {
                    $sisa = max(0, (int) ($item['paymentleft'] ?? $item['PAYMENTLEFT']));
                    $paid = $hasBillPaid
                        ? max(0, (int) ($item['billpaid'] ?? $item['BILLPAID']))
                        : max(0, $total - $sisa);
                } elseif ($hasBillPaid) {
                    $paid = max(0, (int) ($item['billpaid'] ?? $item['BILLPAID']));
                    $sisa = max(0, $total - $paid);
                } else {
                    $paid = max(0, (int) ($item['sisa_tagihan'] ?? 0));
                    $sisa = max(0, (int) ($item['sudah_dibayar'] ?? max(0, $total - $paid)));
                }

                $item['sudah_dibayar'] = $paid;
                $item['sisa_tagihan'] = $sisa;
            }
            unset($item);
        }

        return $result;
    }

    public function tagihanView()
    {
        return view('tagihan');
    }

    public function buatVA(Request $request)
    {
        return response()->json([
            'status' => false,
            'message' => 'Pembayaran tidak tersedia. Halaman ini hanya untuk melihat tagihan.',
        ], 403);

        $request->validate([
            'custid' => 'required',
            'nocust' => 'required|string',
            'namacust' => 'required|string',
        ]);

        $pairs = [];
        $items = $request->input('items');
        if (is_array($items) && count($items)) {
            foreach ($items as $item) {
                $aa = (int) ($item['AA'] ?? $item['aa'] ?? 0);
                $amount = (int) ($item['amount'] ?? $item['billam'] ?? 0);
                if ($aa > 0 && $amount > 0) {
                    $pairs[] = ['aa' => $aa, 'amount' => $amount];
                }
            }
        } else {
            $arrayTagihan = $request->input('array_tagihan', $request->input('arrayTagihan', ''));
            if (is_array($arrayTagihan)) {
                $arrayTagihan = implode(',', $arrayTagihan);
            }
            $ids = collect(explode(',', (string) $arrayTagihan))
                ->map(fn ($id) => (int) trim($id))
                ->filter(fn ($id) => $id > 0)
                ->values();

            $billamRaw = $request->input('billam', $request->input('total', ''));
            if (is_array($billamRaw)) {
                $amounts = array_map('intval', $billamRaw);
            } else {
                $amounts = collect(explode(',', (string) $billamRaw))
                    ->map(fn ($n) => (int) trim($n))
                    ->values()
                    ->all();
            }

            foreach ($ids as $i => $aa) {
                $amount = (int) ($amounts[$i] ?? 0);
                if ($aa > 0 && $amount > 0) {
                    $pairs[] = ['aa' => $aa, 'amount' => $amount];
                }
            }
        }

        if (empty($pairs)) {
            return response()->json([
                'status' => false,
                'message' => 'Tagihan yang dipilih tidak valid',
            ], 422);
        }

        $idsCsv = collect($pairs)->pluck('aa')->implode(',');
        $billamCsv = collect($pairs)->pluck('amount')->implode(',');
        $total = (int) collect($pairs)->sum('amount');
        $nocust = self::normalizeVa($request->nocust);

        $payload = [
            'custid' => $request->custid,
            'nocust' => $nocust,
            'namacust' => $request->namacust,
            'array_tagihan' => $idsCsv,
            'arrayTagihan' => $idsCsv,
            'billam' => $billamCsv,
            'total' => $total,
            'billtot' => $total,
            'bank' => $request->input('bank', 'Muamalat'),
            'items' => $pairs,
        ];

        try {
            $wsUrl = $this->wsUrl('generate-va');
            Log::info('WS generate-va incoming', [
                'url' => $wsUrl,
                'request' => $request->all(),
                'payload' => $payload,
                'payload_types' => collect($payload)->map(fn ($v) => gettype($v) . ':' . json_encode($v))->all(),
                'empty_fields' => collect($payload)->filter(fn ($v) => $v === null || $v === '' || $v === false)->keys()->values()->all(),
            ]);

            $response = Http::timeout(30)
                ->withoutVerifying()
                ->acceptJson()
                ->asJson()
                ->post($wsUrl, $payload);

            Log::info('WS generate-va json', [
                'http' => $response->status(),
                'body' => $response->body(),
                'json' => $response->json(),
            ]);

            if ($response->failed() || empty($response->json()['status'])) {
                $formResponse = Http::timeout(30)
                    ->withoutVerifying()
                    ->asForm()
                    ->post($wsUrl, $payload);
                Log::info('WS generate-va form', [
                    'http' => $formResponse->status(),
                    'body' => $formResponse->body(),
                    'json' => $formResponse->json(),
                ]);
                if ($formResponse->successful()) {
                    $response = $formResponse;
                }
            }

            Log::info('WS generate-va response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            $result = $response->json();
            if (!is_array($result)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal membuat nomor VA',
                ], 500);
            }

            $va = $this->extractVa($result, null);
            $vaOk = $va !== false && $va !== null && $va !== '' && $va !== 'false';

            if (!empty($result['status']) && $vaOk) {
                $result['data'] = array_merge(
                    is_array($result['data'] ?? null) ? $result['data'] : [],
                    ['va_number' => $nocust]
                );

                return response()->json($result);
            }

            $wsMessage = (string) ($result['message'] ?? '');
            $looksSuccess = stripos($wsMessage, 'berhasil') !== false;
            $message = ($wsMessage !== '' && !$looksSuccess)
                ? $wsMessage
                : 'Gagal menyimpan ke scctva. insertVA return false. Kalau NIS '.$nocust.' sudah ada di kolom NOVA, lepas UNIQUE di NOVA supaya tiap bayar bisa baris baru dengan nomor yang sama.';

            Log::warning('WS generate-va insert failed', [
                'ws_status' => $result['status'] ?? null,
                'ws_message' => $wsMessage,
                'va' => $va,
                'nocust' => $nocust,
            ]);

            return response()->json([
                'status' => false,
                'message' => $message,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error generate-va', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat membuat nomor VA',
            ], 500);
        }
    }

    public function filterTahun(Request $request)
    {
        $session = session('tagihan');
        if (!is_array($session) || empty($session['active_no_cust'])) {
            return redirect('/')->with('error', 'Sesi akun tidak ditemukan. Silakan login ulang.');
        }

        $academicYear = $request->input('academic_year', 'all');
        if (!is_string($academicYear) || $academicYear === '' || stripos($academicYear, 'Semua') !== false) {
            $academicYear = 'all';
        }

        $va = $session['va_display'] ?? $session['active_no_cust'];
        $json = self::postWsWithVaCandidates('cek-tagihan', [
            'tahun_akademik' => $academicYear,
        ], $va);

        $result = $this->withNova($json, $va);
        if (empty($result['status'])) {
            return back()->with('error', $result['message'] ?? 'Gagal memfilter tagihan');
        }

        return $this->renderIndex3AfterLogin($result, self::formatNova($result['data']['no_cust'] ?? $va), $academicYear);
    }

    public function listTahunAkademik()
    {
        try {
            $response = Http::timeout(30)
                ->withoutVerifying()
                ->get(config('services.tagihan_ws.url'), [
                    'path' => 'list-tahun-aka'
                ]);

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'status' => false,
                'message' => 'API tidak memberikan response yang valid'
            ], 500);
        } catch (\Exception $e) {
            \Log::error('Error fetching tahun akademik', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data tahun akademik',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function ubahPassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:3|confirmed',
        ], [
            'current_password.required' => 'Password saat ini wajib diisi',
            'password.required' => 'Password baru wajib diisi',
            'password.min' => 'Password baru minimal 3 karakter',
            'password.confirmed' => 'Konfirmasi password tidak sama',
        ]);

        $session = session('tagihan');
        if (!is_array($session) || empty($session['active_no_cust'])) {
            return response()->json([
                'status' => false,
                'message' => 'Sesi akun tidak ditemukan. Silakan cek tagihan ulang.',
            ], 401);
        }

        $json = self::postWsWithVaCandidates('ubah-password', [
            'password' => $request->current_password,
            'password_baru' => $request->password,
        ], $session['active_no_cust']);

        if (empty($json['status'])) {
            return response()->json([
                'status' => false,
                'message' => $json['message'] ?? 'Gagal mengubah password',
            ], 422);
        }

        return response()->json([
            'status' => true,
            'message' => $json['message'] ?? 'Password berhasil diubah',
        ]);
    }

    public function logout(Request $request)
    {
        $request->session()->forget('tagihan');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
