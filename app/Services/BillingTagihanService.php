<?php

namespace App\Services;

use App\Http\Controllers\TagihanController;
use PDO;
use Throwable;

class BillingTagihanService
{
    public static function enabled(): bool
    {
        return (string) env('WS_DB_DATABASE', env('DB_DATABASE_DATA_MYSQL', '')) !== '';
    }

    public static function cek(string $path, array $payload, ?string $vaInput = null): ?array
    {
        if (!self::enabled()) {
            return null;
        }

        $candidates = TagihanController::vaCandidates($vaInput ?: (string) ($payload['va'] ?? ''));
        if (!$candidates) {
            return null;
        }

        try {
            require_once base_path('ws/config/database.php');
            require_once base_path('ws.php');

            $tagihan = new \Tagihan(self::pdo());

            if ($path === 'ubah-password') {
                $last = ['status' => false, 'message' => 'Gagal mengubah password'];
                foreach ($candidates as $va) {
                    $result = $tagihan->ubahPassword(
                        $va,
                        (string) ($payload['password'] ?? ''),
                        (string) ($payload['password_baru'] ?? '')
                    );
                    if (is_array($result) && !empty($result['status'])) {
                        return $result;
                    }
                    if (is_array($result)) {
                        $last = $result;
                    }
                }

                return $last;
            }

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
