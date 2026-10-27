<?php

header('Content-Type: application/json; charset=utf-8');

$tagihanFile = is_file(__DIR__ . '/../ws.php') ? __DIR__ . '/../ws.php' : __DIR__ . '/ws.php';
if (!is_file(__DIR__ . '/config/database.php')) {
    // continue; ws.php will look up Database itself
} else {
    require_once __DIR__ . '/config/database.php';
}
require_once $tagihanFile;

$raw = file_get_contents('php://input');
$input = json_decode((string) $raw, true);
if (!is_array($input) || !$input) {
    $input = $_POST;
}
if (!is_array($input)) {
    $input = [];
}

$path = (string) ($_GET['path'] ?? $input['path'] ?? '');
$va = $input['va'] ?? $input['no_cust'] ?? $input['va_number'] ?? '';
$password = $input['password'] ?? '';
$passwordBaru = $input['password_baru'] ?? $input['new_password'] ?? '';
$tahun = $input['tahun_akademik'] ?? 'all';

try {
    $tagihan = new Tagihan();
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => false,
        'message' => 'Gagal koneksi database billing',
        'data' => null,
    ]);
    exit;
}

switch ($path) {
    case 'cek-tagihan-pw':
        $data = $tagihan->cekTagihanByVAPw($va, $password, $tahun);
        echo json_encode([
            'status' => (bool) $data,
            'message' => $data ? 'Data ditemukan' : 'VA atau Password salah, atau data tidak ditemukan',
            'data' => $data ?: null,
        ]);
        break;

    case 'cek-tagihan':
        $data = $tagihan->cekTagihanByVA($va, $tahun);
        echo json_encode([
            'status' => (bool) $data,
            'message' => $data ? 'Data ditemukan' : 'Data tidak ditemukan untuk VA dan Tahun Akademik tersebut',
            'data' => $data ?: null,
        ]);
        break;

    case 'ubah-password':
        $result = $tagihan->ubahPassword($va, $password, $passwordBaru);
        if (!is_array($result)) {
            $result = ['status' => false, 'message' => 'Gagal mengubah password'];
        }
        $result['data'] = $result['data'] ?? null;
        echo json_encode($result);
        break;

    case 'list-tahun-aka':
        $data = $tagihan->getAllTahunAkademik();
        echo json_encode([
            'status' => true,
            'message' => 'OK',
            'data' => $data,
        ]);
        break;

    case 'generate-va':
        $ok = $tagihan->insertVA(
            $input['custid'] ?? $input['CUSTID'] ?? '',
            $input['nocust'] ?? $input['NOCUST'] ?? $va,
            $input['namacust'] ?? $input['NMCUST'] ?? '',
            $input['arrayTagihan'] ?? $input['ArrayTagihan'] ?? '',
            $input['billam'] ?? $input['BILLAM'] ?? ''
        );
        echo json_encode([
            'status' => (bool) $ok,
            'message' => $ok ? 'VA berhasil dibuat' : 'Gagal membuat VA',
            'data' => $ok ? ['va_number' => $input['nocust'] ?? $va] : null,
        ]);
        break;

    default:
        echo json_encode([
            'status' => false,
            'message' => 'Endpoint tidak ditemukan',
            'data' => null,
        ]);
}
