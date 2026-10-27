<?php

namespace App\Services;

use App\Http\Controllers\TagihanController;
use Illuminate\Support\Facades\Log;
use PDO;
use Throwable;

class BillingTagihanService
{
    public static function enabled(): bool
    {
        return (string) env('WS_DB_DATABASE', env('DB_DATABASE_DATA_MYSQL', '')) !== '';
    }

    public static function ubahPassword(string $vaInput, string $oldPassword, string $newPassword): ?array
    {
        $tagihan = self::makeTagihan();
        if (!$tagihan) {
            return null;
        }

        $candidates = TagihanController::vaCandidates($vaInput);
        if (!$candidates) {
            $candidates = [preg_replace('/\D+/', '', $vaInput)];
        }

        $last = ['status' => false, 'message' => 'Gagal mengubah password'];
        foreach ($candidates as $va) {
            $result = $tagihan->ubahPassword($va, $oldPassword, $newPassword);
            if (is_array($result) && !empty($result['status'])) {
                return $result;
            }
            if (is_array($result)) {
                $last = $result;
            }
        }

        return $last;
    }

    public static function cek(string $path, array $payload, ?string $vaInput = null): ?array
    {
        if ($path === 'ubah-password') {
            return self::ubahPassword(
                (string) ($vaInput ?: ($payload['va'] ?? '')),
                (string) ($payload['password'] ?? ''),
                (string) ($payload['password_baru'] ?? '')
            );
        }

        $tagihan = self::makeTagihan();
        if (!$tagihan) {
            return null;
        }

        $candidates = TagihanController::vaCandidates($vaInput ?: (string) ($payload['va'] ?? ''));
        if (!$candidates) {
            return null;
        }

        try {
            $data = null;

            foreach ($candidates as $va) {
                if ($path === 'cek-tagihan-pw') {
                    $data = $tagihan->cekTagihanByVAPw(
                        $va,
                        (string) ($payload['password'] ?? ''),
                        $payload['tahun_akademik'] ?? 'all'
                    );
                } elseif ($path === 'cek-tagihan') {
                    $data = $tagihan->cekTagihanByVA(
                        $va,
                        $payload['tahun_akademik'] ?? 'all'
                    );
                } else {
                    return null;
                }

                if ($data) {
                    break;
                }
            }

            if (!$data) {
                return [
                    'status' => false,
                    'message' => 'VA atau password salah, atau data tidak ditemukan',
                ];
            }

            return [
                'status' => true,
                'message' => 'Data ditemukan',
                'data' => $data,
            ];
        } catch (Throwable $e) {
            Log::warning('BillingTagihanService cek failed', ['error' => $e->getMessage()]);

            return null;
        }
    }

    private static function makeTagihan(): ?\Tagihan
    {
        $file = base_path('ws.php');
        if (!is_file($file)) {
            return null;
        }

        try {
            $config = base_path('ws/config/database.php');
            if (is_file($config)) {
                require_once $config;
            }
            require_once $file;

            if (self::enabled()) {
                return new \Tagihan(self::pdo());
            }

            return new \Tagihan();
        } catch (Throwable $e) {
            Log::warning('BillingTagihanService koneksi gagal', ['error' => $e->getMessage()]);

            return null;
        }
    }

    private static function pdo(): PDO
    {
        $host = env('WS_DB_HOST', env('DB_HOST_DATA_MYSQL', '127.0.0.1'));
        $port = env('WS_DB_PORT', env('DB_PORT_DATA_MYSQL', '3306'));
        $name = env('WS_DB_DATABASE', env('DB_DATABASE_DATA_MYSQL'));
        $user = env('WS_DB_USERNAME', env('DB_USERNAME_DATA_MYSQL', 'root'));
        $pass = env('WS_DB_PASSWORD', env('DB_PASSWORD_DATA_MYSQL', ''));

        return new PDO(
            "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
            $user,
            $pass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }
}
