<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/controllers/TagihanController.php';

$path = $_GET['path'] ?? '';

switch ($path) {
    case 'cek-tagihan':
        $controller = new TagihanController();
        $controller->cek();
        break;

    case 'cek-tagihan-pw':
        $controller = new TagihanController();
        $controller->cek2();
        break;

    case 'generate-va':
        $controller = new TagihanController();
        $controller->generateVA();
        break;

    case 'list-tahun-aka':
        $controller = new TagihanController();
        $controller->getTahunAkademik();
        break;

    case 'multi-akun-list':
        $controller = new TagihanController();
        $controller->multiAkunList();
        break;

    case 'multi-akun-tambah':
        $controller = new TagihanController();
        $controller->multiAkunTambah();
        break;

    case 'multi-akun-switch':
        $controller = new TagihanController();
        $controller->multiAkunSwitch();
        break;

    case 'multi-akun-hapus':
        $controller = new TagihanController();
        $controller->multiAkunHapus();
        break;

    case 'token-buat':
        $controller = new TagihanController();
        $controller->tokenBuat();
        break;

    case 'token-login':
        $controller = new TagihanController();
        $controller->tokenLogin();
        break;

    case 'ubah-password':
        $controller = new TagihanController();
        if (method_exists($controller, 'ubahPassword')) {
            $controller->ubahPassword();
        } else {
            require __DIR__ . '/ubah-password.php';
        }
        break;

    default:
        header('Content-Type: application/json');
        echo json_encode(['status' => false, 'message' => 'Endpoint tidak ditemukan']);
        break;
}
