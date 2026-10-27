<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TagihanController;
use App\Http\Controllers\MultiAccountController;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

Route::get('/', function () {
    return response()
        ->view('index3')
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->header('Pragma', 'no-cache');
});

Route::post('/logout', [TagihanController::class, 'logout'])->name('logout');
Route::post('/password/ubah', [TagihanController::class, 'ubahPassword'])->name('password.ubah');

Route::get('/dua', function () {
    return view('index');
});


Route::get('/list-tahun-akademik', [TagihanController::class, 'listTahunAkademik']);
Route::any('/ws-tagihan', function () {
    require base_path('ws/index.php');
});

Route::post('/cek-tagihan', function (Request $request) {
    try {
        $payload = [
            'va' => TagihanController::normalizeVa($request->input('va')),
            'tahun_akademik' => $request->input('tahun_akademik')
        ];

        $path = 'cek-tagihan';
        if ($request->filled('password')) {
            $payload['password'] = $request->input('password');
            $path = 'cek-tagihan-pw';
        }

        $response = Http::timeout(30)
            ->withoutVerifying()
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])
            ->post(config('services.tagihan_ws.url').'?path='.$path, $payload);

        \Log::info('WS cek-tagihan response', [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json([
            'status' => false,
            'message' => 'Gagal mengecek tagihan'
        ], 500);
    } catch (\Exception $e) {
        \Log::error('Error cek-tagihan', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'status' => false,
            'message' => 'Terjadi kesalahan',
            'error' => $e->getMessage()
        ], 500);
    }
})->name('cek-tagihan');

Route::post('/generate-va', [TagihanController::class, 'buatVA'])->name('generate-va');

Route::post('/dua', [TagihanController::class, 'cek'])->name('tagihan.cek');

Route::post('/', [TagihanController::class, 'cek2'])->name('tagihan.cek2');
Route::post('/filter-tahun', [TagihanController::class, 'filterTahun'])->name('tagihan.filter');

Route::post('/multi-akun/tambah', [MultiAccountController::class, 'tambah'])->name('multi-akun.tambah');
Route::post('/multi-akun/switch', [MultiAccountController::class, 'switch'])->name('multi-akun.switch');
Route::post('/multi-akun/hapus', [MultiAccountController::class, 'hapus'])->name('multi-akun.hapus');

Route::get('/tagihan/view', [TagihanController::class, 'tagihanView'])->name('tagihan.view');

Route::post('/pembayaran/buat-va', [TagihanController::class, 'buatVA'])->name('pembayaran.buatva');

Route::get('/{token}', [TagihanController::class, 'loginByToken'])
    ->where('token', '[A-Fa-f0-9]{64}')
    ->name('tagihan.token-login');
