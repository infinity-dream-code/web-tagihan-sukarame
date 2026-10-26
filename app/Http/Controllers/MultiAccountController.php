<?php

namespace App\Http\Controllers;

use App\Services\MultiAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MultiAccountController extends Controller
{
    private function wsUrl(?string $path = null): string
    {
        $url = rtrim(config('services.tagihan_ws.url'), '?&');

        if ($path) {
            $url .= (str_contains($url, '?') ? '&' : '?').'path='.$path;
        }

        return $url;
    }

    private function withNova($result, ?string $fallback = null): array
    {
        if (!is_array($result)) {
            return ['status' => false, 'message' => 'Terjadi kesalahan'];
        }

        if (!empty($result['data'])) {
            $nocust = $result['data']['no_cust'] ?? $result['data']['va_number'] ?? $fallback;
            $result['data']['va_number'] = TagihanController::formatNova($nocust);
            $result['data']['kelompok'] = $result['data']['kelompok']
                ?? $result['data']['jurusan']
                ?? '-';
            $result['data']['angkatan'] = $result['data']['angkatan']
                ?? $result['data']['tahun_akademik']
                ?? '-';
            $result = TagihanController::normalizeBillAmounts($result);
        }

        return $result;
    }

    private function fetchTagihan(string $va, string $academicYear): array
    {
        $json = TagihanController::postWsWithVaCandidates('cek-tagihan', [
            'tahun_akademik' => $academicYear,
        ], $va);

        return $this->withNova($json, $va);
    }

    private function requireActiveSession(): ?array
    {
        $session = session('tagihan');
        if (!is_array($session) || empty($session['active_no_cust'])) {
            return null;
        }

        return $session;
    }

    private function renderIndex3(array $result, string $va, string $academicYear, $multiAccounts = null)
    {
        if ($multiAccounts === null) {
            $multiAccounts = MultiAccountService::listForNoCust(
                $result['data']['no_cust'] ?? $va,
                TagihanController::normalizeVa($va)
            );
        }

        return view('index3', [
            'result' => $result,
            'multiAccounts' => collect($multiAccounts),
        ])->with([
            'va' => $va,
            'academic_year' => $academicYear,
        ]);
    }

    public function tambah(Request $request)
    {
        $request->validate([
            'no_cust' => 'required|string',
            'password' => 'required|string',
            'academic_year' => 'nullable|string',
        ]);

        $session = $this->requireActiveSession();
        if (!$session) {
            return response()->json([
                'status' => false,
                'message' => 'Sesi akun aktif tidak ditemukan. Silakan cek tagihan ulang.',
            ], 401);
        }

        $activeNoCust = $session['active_no_cust'];
        $activeYear = 'all';
        $activeVaDisplay = $session['va_display'] ?? $activeNoCust;

        $linked = MultiAccountService::linkAccountsViaWs(
            $activeNoCust,
            $activeYear,
            $request->no_cust,
            $request->password,
            'all'
        );

        if (empty($linked['status'])) {
            return response()->json([
                'status' => false,
                'message' => $linked['message'] ?? 'Gagal menambahkan multi akun',
            ], 422);
        }

        MultiAccountService::putSession(
            $activeNoCust,
            $activeVaDisplay,
            $activeYear,
            $linked['group_id'] ?? null
        );

        return response()->json([
            'status' => true,
            'message' => $linked['message'] ?? 'Akun berhasil ditambahkan',
            'data' => [
                'group_id' => $linked['group_id'] ?? null,
                'accounts' => ($linked['members'] ?? collect())->values(),
                'active_no_cust' => $linked['active_no_cust'] ?? $activeNoCust,
            ],
        ]);
    }

    public function hapus(Request $request)
    {
        $request->validate([
            'no_cust' => 'required|string',
        ]);

        $session = $this->requireActiveSession();
        if (!$session) {
            return response()->json([
                'status' => false,
                'message' => 'Sesi akun aktif tidak ditemukan. Silakan cek tagihan ulang.',
            ], 401);
        }

        $activeNoCust = $session['active_no_cust'];
        $activeVaDisplay = $session['va_display'] ?? $activeNoCust;
        $activeYear = 'all';

        $removed = MultiAccountService::hapusViaWs($activeNoCust, $request->no_cust);

        if (empty($removed['status'])) {
            return response()->json([
                'status' => false,
                'message' => $removed['message'] ?? 'Gagal menghapus akun',
            ], 422);
        }

        MultiAccountService::putSession(
            $activeNoCust,
            $activeVaDisplay,
            $activeYear,
            $removed['group_id'] ?? null
        );

        return response()->json([
            'status' => true,
            'message' => $removed['message'] ?? 'Akun berhasil dihapus dari multi akun',
            'data' => [
                'group_id' => $removed['group_id'] ?? null,
                'accounts' => ($removed['members'] ?? collect())->values(),
                'active_no_cust' => $removed['active_no_cust'] ?? $activeNoCust,
                'removed_no_cust' => $removed['removed_no_cust'] ?? null,
            ],
        ]);
    }

    public function switch(Request $request)
    {
        $request->validate([
            'no_cust' => 'required|string',
            'academic_year' => 'nullable|string',
        ]);

        $session = $this->requireActiveSession();
        if (!$session) {
            return redirect('/')->with('error', 'Sesi akun aktif tidak ditemukan. Silakan cek tagihan ulang.');
        }

        $activeNoCust = $session['active_no_cust'];
        $targetNoCust = TagihanController::normalizeVa($request->no_cust);
        $academicYear = 'all';

        $switched = MultiAccountService::switchViaWs($activeNoCust, $targetNoCust, $academicYear);

        if (empty($switched['status']) || empty($switched['result'])) {
            return $this->switchFailed(
                $session,
                $switched['message'] ?? 'Gagal beralih akun. Silakan coba lagi.'
            );
        }

        $result = $this->withNova($switched['result'], $targetNoCust);
        $vaDisplay = $switched['va_display'] ?? ($result['data']['va_number'] ?? $targetNoCust);

        MultiAccountService::putSession(
            $result['data']['no_cust'] ?? $targetNoCust,
            $vaDisplay,
            $academicYear,
            $switched['group_id'] ?? null
        );

        return $this->renderIndex3(
            $result,
            $vaDisplay,
            $academicYear,
            $switched['accounts'] ?? null
        );
    }

    private function switchFailed(array $session, string $message)
    {
        $activeNoCust = $session['active_no_cust'] ?? null;
        $academicYear = 'all';
        $vaDisplay = $session['va_display'] ?? $activeNoCust;

        if (!$activeNoCust) {
            return redirect('/')->with('error', $message);
        }

        $result = $this->fetchTagihan($activeNoCust, $academicYear);
        if (empty($result['status']) || empty($result['data'])) {
            return redirect('/')->with('error', $message);
        }

        return $this->renderIndex3($result, $vaDisplay, $academicYear)
            ->with('error', $message);
    }
}
