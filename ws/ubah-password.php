<?php

header('Content-Type: application/json; charset=utf-8');

if (!class_exists('Database')) {
    foreach ([
        __DIR__ . '/config/database.php',
        __DIR__ . '/../config/database.php',
        dirname(__DIR__) . '/ws/config/database.php',
    ] as $databaseFile) {
        if (is_file($databaseFile)) {
            require_once $databaseFile;
            break;
        }
    }
}

$raw = file_get_contents('php://input');
$input = json_decode((string) $raw, true);
if (!is_array($input) || !$input) {
    $input = $_POST;
}
if (!is_array($input)) {
    $input = [];
}

$va = preg_replace('/\D+/', '', (string) ($input['va'] ?? $input['no_cust'] ?? $input['va_number'] ?? ''));
$oldPassword = trim((string) ($input['password'] ?? $input['current_password'] ?? ''));
$newPassword = trim((string) ($input['password_baru'] ?? $input['new_password'] ?? ''));

if ($newPassword === '' || strlen($newPassword) < 3) {
    echo json_encode(['status' => false, 'message' => 'Password baru minimal 3 karakter', 'data' => null]);
    return;
}

$prefixes = ['777726', '797777', '757777', '797766', '751000', '797783', '77726', '96240'];
for ($i = 0; $i < 4; $i++) {
    $hit = false;
    foreach ($prefixes as $prefix) {
        if (strpos($va, $prefix) === 0 && strlen($va) > strlen($prefix)) {
            $va = substr($va, strlen($prefix));
            $hit = true;
            break;
        }
    }
    if (!$hit) {
        break;
    }
}
$nis = ltrim($va, '0');
$nis = $nis !== '' ? $nis : $va;
if ($nis === '') {
    echo json_encode(['status' => false, 'message' => 'Nomor VA / NIS tidak valid', 'data' => null]);
    return;
}

try {
    $db = (new Database())->getConnection();
    $stmt = $db->prepare("SELECT userlogin, kunci FROM sm_user
        WHERE TRIM(userlogin) = ?
           OR TRIM(LEADING '0' FROM TRIM(userlogin)) = ?
           OR CAST(TRIM(userlogin) AS UNSIGNED) = ?
        LIMIT 1");
    $stmt->execute([$nis, $nis, (int) $nis]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $hash = sha1($newPassword);

    if ($user) {
        $stored = (string) ($user['kunci'] ?? '');
        if ($stored !== '' && $stored !== sha1($oldPassword)) {
            echo json_encode(['status' => false, 'message' => 'Password saat ini salah', 'data' => null]);
            return;
        }
        $upd = $db->prepare('UPDATE sm_user SET kunci = ? WHERE userlogin = ?');
        $upd->execute([$hash, $user['userlogin']]);
        echo json_encode(['status' => true, 'message' => 'Password berhasil diubah', 'data' => null]);
        return;
    }

    $ins = $db->prepare('INSERT INTO sm_user (userlogin, kunci) VALUES (?, ?)');
    $ins->execute([$nis, $hash]);
    echo json_encode(['status' => true, 'message' => 'Password berhasil disimpan', 'data' => null]);
} catch (Throwable $e) {
    echo json_encode(['status' => false, 'message' => 'Gagal mengubah password', 'data' => null]);
}
