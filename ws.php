
<?php
require_once __DIR__ . '/../config/database.php';

class Tagihan
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getAllTahunAkademik()
    {
        $sql = "SELECT urut, thn_aka 
            FROM mst_thn_aka 
            ORDER BY urut DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function logVa($message, $data = [])
    {
        $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . ' ' . json_encode($data, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        @file_put_contents(__DIR__ . '/va_debug.log', $line, FILE_APPEND);
    }

    /**
     * Pola insert sama seperti WS Deliserdang (bukan generate VA unik).
     * Untuk klien ini: NOVA = NOCUST, setiap bayar = baris baru.
     *
     * index.php generate-va — tambahkan log ini sebelum insert:
     *   file_put_contents(__DIR__.'/va_debug.log', date('c').' INPUT '.json_encode($in).PHP_EOL, FILE_APPEND);
     */
    public function insertVA($custid, $nocust, $namacust, $arrayTagihan, $billam)
    {
        $this->logVa('insertVA start', [
            'custid' => $custid,
            'nocust' => $nocust,
            'namacust' => $namacust,
            'arrayTagihan' => $arrayTagihan,
            'billam' => $billam,
            'billam_type' => gettype($billam),
        ]);

        if ($custid === '' || $custid === null || $nocust === '' || $nocust === null) {
            $this->logVa('insertVA abort empty custid/nocust');
            return false;
        }

        $ids = array_values(array_filter(array_map('intval', explode(',', str_replace(' ', '', (string) $arrayTagihan))), function ($id) {
            return $id > 0;
        }));
        if (empty($ids)) {
            $this->logVa('insertVA abort empty ids', ['arrayTagihan' => $arrayTagihan]);
            return false;
        }

        $amounts = $this->resolveBillam($ids, $billam);
        $billtot = array_sum($amounts);
        if ($billtot <= 0) {
            $this->logVa('insertVA abort billtot', ['billam' => $billam, 'amounts' => $amounts]);
            return false;
        }

        $tagihanAA = implode(',', $ids);
        $billamStore = implode(',', $amounts);

        $inserted = $this->doInsertVa($custid, $nocust, $namacust, $nocust, $tagihanAA, $billamStore, $billtot);
        if ($inserted) {
            $this->logVa('insertVA ok', [
                'nova' => $nocust,
                'arrayTagihan' => $tagihanAA,
                'billam' => $billamStore,
                'billtot' => $billtot,
            ]);
            return $nocust;
        }

        $this->logVa('insertVA failed, NOVA harus sama dengan NOCUST', ['nocust' => $nocust]);
        return false;
    }

    private function resolveBillam(array $ids, $billam)
    {
        $fromArg = $this->parseAmounts($billam);
        if (count($fromArg) === count($ids)) {
            return $fromArg;
        }

        $raw = $this->readIncoming();
        $fromField = $this->parseAmounts($raw['billam'] ?? '');
        if (count($fromField) === count($ids)) {
            return $fromField;
        }

        $items = $raw['items'] ?? [];
        if (is_array($items) && count($items)) {
            $byAa = [];
            foreach ($items as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $aa = (int) ($item['AA'] ?? $item['aa'] ?? 0);
                $amt = (int) ($item['amount'] ?? $item['billam'] ?? 0);
                if ($aa > 0 && $amt > 0) {
                    $byAa[$aa] = $amt;
                }
            }
            $ordered = [];
            foreach ($ids as $aa) {
                $ordered[] = (int) ($byAa[$aa] ?? 0);
            }
            if (count(array_filter($ordered)) === count($ids)) {
                return $ordered;
            }
        }

        return $fromArg;
    }

    private function parseAmounts($billam)
    {
        if (is_array($billam)) {
            return array_values(array_filter(array_map('intval', $billam), function ($n) {
                return $n > 0;
            }));
        }

        return array_values(array_filter(array_map('intval', explode(',', str_replace(' ', '', (string) $billam))), function ($n) {
            return $n > 0;
        }));
    }

    private function readIncoming()
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        $json = json_decode((string) @file_get_contents('php://input'), true);
        if (is_array($json) && count($json)) {
            $cached = $json;
            return $cached;
        }

        $cached = is_array($_POST) ? $_POST : [];
        return $cached;
    }

    private function doInsertVa($custid, $nocust, $namacust, $nova, $arrayTagihan, $billam, $billtot)
    {
        $params = [
            ':custid' => $custid,
            ':nocust' => $nocust,
            ':namacust' => $namacust,
            ':nova' => $nova,
            ':arrayTagihan' => $arrayTagihan,
            ':billam' => $billam,
        ];

        $attempts = [
            [
                'sql' => 'INSERT INTO scctva (CUSTID, NOCUST, NMCUST, NOVA, ArrayTagihan, BILLAM, BILLTOT, STATUS, CREATED_AT)
                    VALUES (:custid, :nocust, :namacust, :nova, :arrayTagihan, :billam, :billtot, 1, NOW())',
                'params' => $params + [':billtot' => $billtot],
                'label' => 'BILLTOT',
            ],
            [
                'sql' => 'INSERT INTO scctva (CUSTID, NOCUST, NMCUST, NOVA, ArrayTagihan, BILLAM, STATUS, CREATED_AT)
                    VALUES (:custid, :nocust, :namacust, :nova, :arrayTagihan, :billam, 1, NOW())',
                'params' => $params,
                'label' => 'basic',
            ],
        ];

        foreach ($attempts as $attempt) {
            try {
                $stmt = $this->db->prepare($attempt['sql']);
                $ok = $stmt->execute($attempt['params']);
                if ($ok) {
                    return true;
                }
                $this->logVa('insert '.$attempt['label'].' returned false', ['error' => $stmt->errorInfo()]);
            } catch (Exception $e) {
                $this->logVa('insert '.$attempt['label'].' exception', ['error' => $e->getMessage()]);
            }
        }

        return false;
    }

    public function cekTagihanByVA($va_number, $tahun_akademik = null)
    {
        $siswa = $this->getSiswaByVa($va_number);
        if (!$siswa) {
            return null;
        }

        return $this->attachTagihan($siswa, $va_number, $tahun_akademik);
    }

    public function cekTagihanByVAPw($va_number, $password, $tahun_akademik = null)
    {
        $user = $this->findUserByVa($va_number);
        if (!$user || $user['kunci'] !== sha1($password)) {
            return null;
        }

        $siswa = $this->getSiswaByVa($va_number);
        if (!$siswa) {
            return null;
        }

        return $this->attachTagihan($siswa, $va_number, $tahun_akademik);
    }

    private function vaCandidates($va_number)
    {
        $raw = preg_replace('/\s+/', '', (string) $va_number);
        if ($raw === '') {
            return [];
        }

        $prefixes = ['777726', '797777', '757777', '797766', '751000', '797783', '77726', '96240'];
        $afterPrefix = $raw;
        foreach ($prefixes as $prefix) {
            if (strpos($raw, $prefix) === 0 && strlen($raw) > strlen($prefix)) {
                $afterPrefix = substr($raw, strlen($prefix));
                break;
            }
        }

        $trimmed = ltrim($afterPrefix, '0');
        if ($trimmed === '') {
            $trimmed = $afterPrefix;
        }

        $padded = str_pad($trimmed, 10, '0', STR_PAD_LEFT);

        return array_values(array_unique(array_filter([
            $raw,
            $afterPrefix,
            $trimmed,
            $padded,
            '777726'.$padded,
            '777726'.$trimmed,
        ])));
    }

    private function stripVa($va_number)
    {
        $va = preg_replace('/\D+/', '', (string) $va_number);
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
        $trimmed = ltrim($va, '0');

        return $trimmed !== '' ? $trimmed : $va;
    }

    private function findUserByVa($va_number)
    {
        $candidates = $this->vaCandidates($va_number);
        if (!$candidates) {
            return null;
        }

        $placeholders = implode(',', array_fill(0, count($candidates), '?'));
        $sql = "SELECT userlogin, kunci FROM sm_user
            WHERE userlogin IN ($placeholders)
               OR TRIM(LEADING '0' FROM userlogin) IN ($placeholders)
            LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge($candidates, $candidates));

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function getSiswaByVa($va_number)
    {
        $candidates = $this->vaCandidates($va_number);
        if (!$candidates) {
            return null;
        }

        $placeholders = implode(',', array_fill(0, count($candidates), '?'));
        $sql = "
        SELECT 
            c.CUSTID AS id,
            c.NOCUST AS no_cust,
            c.NUM2ND AS num2nd,
            c.NMCUST AS nama,
            c.CODE02 AS jenjang,
            c.DESC02 AS kelas,
            c.CODE03 AS kode_jurusan,
            c.DESC03 AS jurusan,
            c.DESC03 AS kelompok,
            c.DESC04 AS tahun_akademik,
            c.DESC04 AS angkatan,
            c.DESC05 AS alamat,
            c.GENUS AS orangtua,
            c.LastUpdate AS updated_at
        FROM scctcust c
        WHERE c.NOCUST IN ($placeholders)
           OR c.NUM2ND IN ($placeholders)
           OR TRIM(LEADING '0' FROM c.NOCUST) IN ($placeholders)
           OR TRIM(LEADING '0' FROM c.NUM2ND) IN ($placeholders)
        LIMIT 1
    ";
        $params = array_merge($candidates, $candidates, $candidates, $candidates);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function attachTagihan(array $siswa, $va_number, $tahun_akademik = null)
    {
        $num2nd = $siswa['no_cust'];

        $sqlSaldo = "SELECT SALDO FROM v_saldo_va WHERE NOCUST = :nocust LIMIT 1";
        $stmtSaldo = $this->db->prepare($sqlSaldo);
        $stmtSaldo->execute([':nocust' => $num2nd]);
        $saldoRow = $stmtSaldo->fetch(PDO::FETCH_ASSOC);
        $saldo = $saldoRow['SALDO'] ?? 0;

        $siswa['va_number'] = $va_number;
        $siswa['saldo'] = $saldo;

        if (!$tahun_akademik || strtolower($tahun_akademik) === 'all') {
            $tahun_akademik = null;
        }

        $baseFilter = "b.CUSTID = :custid";
        if ($tahun_akademik) {
            $baseFilter .= " AND b.BTA = :tahun_akademik";
        }

        $selectCols = "
            b.AA, b.CUSTID, b.BILLCD, b.BILLNM AS nama_tagihan, b.BILLAM AS total_tagihan,
            b.BILLPAID AS billpaid, b.PAYMENTLEFT AS paymentleft,
            b.BILLAC AS periode, b.BTA AS tahun_akademik_tagihan,
            b.FTGLTagihan, b.FURUTAN, b.isINSTALLABLE AS isINSTALLABLE,
            b.PAIDST, b.PAIDDT
        ";

        $sqlBelum = "
        SELECT $selectCols
        FROM scctbill b
        WHERE $baseFilter AND b.PAIDST = '0' AND b.FSTSBolehBayar = '1'
        ORDER BY b.FURUTAN ASC
    ";
        $stmtBelum = $this->db->prepare($sqlBelum);
        $params = [':custid' => $siswa['id']];
        if ($tahun_akademik) {
            $params[':tahun_akademik'] = $tahun_akademik;
        }
        $stmtBelum->execute($params);
        $belumLunas = $stmtBelum->fetchAll(PDO::FETCH_ASSOC);

        $sqlLunas = "
        SELECT $selectCols
        FROM scctbill b
        WHERE $baseFilter AND b.PAIDST = '1'
        ORDER BY b.PAIDDT DESC, b.FURUTAN ASC
    ";
        $stmtLunas = $this->db->prepare($sqlLunas);
        $stmtLunas->execute($params);
        $lunas = $stmtLunas->fetchAll(PDO::FETCH_ASSOC);

        $aktifItems = $this->groupData($belumLunas);
        $lunasItems = $this->groupData($lunas);

        $tagihanAktif = [];
        foreach ($aktifItems as $item) {
            if ((int) $item['sisa_tagihan'] <= 0) {
                $lunasItems[] = $item;
            } else {
                $tagihanAktif[] = $item;
            }
        }

        $custid = $siswa['id'];
        $lunasItems = $this->attachLunasDetails($custid, $lunasItems, $num2nd);

        $siswa['tahun_dipilih'] = $tahun_akademik ?: 'Semua Tahun Akademik';
        $siswa['tagihan'] = $tagihanAktif;
        $siswa['tagihan_lunas'] = $lunasItems;

        return $siswa;
    }

    private function groupData(array $rows)
    {
        $grouped = [];
        foreach ($rows as $row) {
            $key = $row['AA'];
            if (!isset($grouped[$key])) {
                $total = (int) $row['total_tagihan'];
                $sudah = max(0, (int) ($row['billpaid'] ?? $row['BILLPAID'] ?? 0));
                $paymentLeft = $row['paymentleft'] ?? $row['PAYMENTLEFT'] ?? null;
                $sisa = ($paymentLeft !== null && $paymentLeft !== '')
                    ? max(0, (int) $paymentLeft)
                    : max(0, $total - $sudah);
                $grouped[$key] = [
                    'AA' => $row['AA'],
                    'BILLCD' => $row['BILLCD'],
                    'nama_tagihan' => $row['nama_tagihan'],
                    'total_tagihan' => $row['total_tagihan'],
                    'periode' => $row['periode'],
                    'tahun_akademik_tagihan' => $row['tahun_akademik_tagihan'],
                    'FTGLTagihan' => $row['FTGLTagihan'] ?? null,
                    'FURUTAN' => $row['FURUTAN'] ?? null,
                    'isINSTALLABLE' => $this->flagInstallable($row),
                    'is_installment' => $this->flagInstallable($row),
                    'sudah_dibayar' => $sudah,
                    'sisa_tagihan' => $sisa,
                    'billpaid' => $sudah,
                    'paymentleft' => $sisa,
                    'PAIDST' => $row['PAIDST'],
                    'PAIDDT' => $row['PAIDDT'],
                    'CUSTID' => $row['CUSTID'] ?? null,
                    'TRANSNO' => $row['TRANSNO'] ?? null,
                    'detail' => []
                ];
            }
        }

        return array_values($grouped);
    }

    private function attachAktifDetails($custid, array $items)
    {
        foreach ($items as &$item) {
            $item['detail'] = $this->fetchAktifDetails(
                $item['CUSTID'] ?? $custid,
                $item['BILLCD'] ?? null,
                $item['AA'] ?? null,
                $item['periode'] ?? null
            );
        }
        unset($item);

        return $items;
    }

    private function attachLunasDetails($custid, array $items, $nocust = null)
    {
        foreach ($items as &$item) {
            $item['detail'] = $this->fetchLunasDetails(
                $item['CUSTID'] ?? $custid,
                $item['AA'] ?? null,
                $item['nama_tagihan'] ?? null,
                $item['TRANSNO'] ?? null,
                $item['BILLCD'] ?? null,
                $item['periode'] ?? null,
                $item,
                $nocust
            );
        }
        unset($item);

        return $items;
    }

    private function fetchAktifDetails($custid, $billcd, $aa, $periode)
    {
        $attempts = [];

        if ($aa !== null && $aa !== '') {
            $attempts[] = [
                'sql' => "SELECT d.BILLAM AS nominal_detail, COALESCE(u.NamaAkun, d.KodePost, '-') AS akun_detail
                    FROM scctbill_detail d
                    LEFT JOIN u_akun u ON u.KodeAkun = d.KodePost
                    WHERE d.AA = ?
                    ORDER BY d.tahun ASC, d.periode ASC",
                'params' => [$aa],
            ];
        }

        if ($custid && $billcd && $periode && preg_match('/^\d{6}/', (string) $periode)) {
            $tahun = substr((string) $periode, 0, 4);
            $bulan = substr((string) $periode, 4, 2);
            $attempts[] = [
                'sql' => "SELECT d.BILLAM AS nominal_detail, COALESCE(u.NamaAkun, d.KodePost, '-') AS akun_detail
                    FROM scctbill_detail d
                    LEFT JOIN u_akun u ON u.KodeAkun = d.KodePost
                    WHERE d.CUSTID = ? AND d.BILLCD = ? AND CAST(d.tahun AS CHAR) = ? AND CAST(d.periode AS CHAR) = ?
                    ORDER BY d.tahun ASC, d.periode ASC",
                'params' => [$custid, $billcd, $tahun, $bulan],
            ];
        }

        if ($custid && $billcd) {
            $attempts[] = [
                'sql' => "SELECT d.BILLAM AS nominal_detail, COALESCE(u.NamaAkun, d.KodePost, '-') AS akun_detail
                    FROM scctbill_detail d
                    LEFT JOIN u_akun u ON u.KodeAkun = d.KodePost
                    WHERE d.CUSTID = ? AND d.BILLCD = ?
                    ORDER BY d.tahun ASC, d.periode ASC",
                'params' => [$custid, $billcd],
            ];
        }

        foreach ($attempts as $attempt) {
            $rows = $this->safeFetch($attempt['sql'], $attempt['params']);
            $details = [];
            foreach ($rows as $row) {
                $details[] = [
                    'sumber' => 'bill_detail',
                    'akun_detail' => $row['akun_detail'] ?: '-',
                    'nominal_detail' => (int) ($row['nominal_detail'] ?? 0),
                ];
            }
            if ($details) {
                return $details;
            }
        }

        return [];
    }

    private function fetchLunasDetails($custid, $aa, $billnm, $transno, $billcd = null, $periode = null, array $item = [], $nocust = null)
    {
        $billDetails = $this->fetchAktifDetails($custid, $billcd, $aa, $periode);
        if ($billDetails) {
            return $billDetails;
        }

        $tranDetails = $this->fetchLunasTranDetails($custid, $aa, $billnm, $transno, $billcd);
        if ($tranDetails) {
            return $tranDetails;
        }

        $vaDetails = $this->fetchLunasVaDetails($custid, $nocust, $aa);
        if ($vaDetails) {
            return $vaDetails;
        }

        $nominal = (int) ($item['total_tagihan'] ?? 0);
        if ($nominal <= 0 && !empty($item['PAIDDT'])) {
            $nominal = (int) ($item['sudah_dibayar'] ?? 0);
        }

        if ($nominal > 0 || !empty($item['PAIDDT'])) {
            return [[
                'sumber' => 'tran',
                'akun_detail' => $item['nama_tagihan'] ?: 'Pembayaran',
                'nominal_detail' => $nominal,
                'trxdate' => $item['PAIDDT'] ?? null,
                'metode' => 'Lunas',
                'noreff' => $item['periode'] ?? null,
                'transno' => $item['TRANSNO'] ?? null,
            ]];
        }

        return [];
    }

    private function fetchLunasTranDetails($custid, $aa, $billnm, $transno, $billcd = null)
    {
        $attempts = [];
        $select = "SELECT t.TRXDATE, t.METODE, t.DEBET, t.KREDIT, t.NOREFF, t.TRANSNO, t.BILLTARGET, t.INSTALLMENT FROM sccttran t";

        if ($aa !== null && $aa !== '') {
            $attempts[] = ['sql' => "$select WHERE t.BILLID = ? ORDER BY t.TRXDATE DESC", 'params' => [$aa]];
            $attempts[] = ['sql' => "$select WHERE t.AA = ? ORDER BY t.TRXDATE DESC", 'params' => [$aa]];
        }

        if ($custid && $transno && (string) $transno !== '-') {
            $attempts[] = ['sql' => "$select WHERE t.CUSTID = ? AND t.TRANSNO = ? ORDER BY t.TRXDATE DESC", 'params' => [$custid, $transno]];
        }

        if ($custid && $billcd) {
            $attempts[] = ['sql' => "$select WHERE t.CUSTID = ? AND t.BILLCD = ? ORDER BY t.TRXDATE DESC", 'params' => [$custid, $billcd]];
        }

        if ($custid && $billnm) {
            $attempts[] = ['sql' => "$select WHERE t.CUSTID = ? AND UPPER(TRIM(t.BILLTARGET)) = UPPER(TRIM(?)) ORDER BY t.TRXDATE DESC", 'params' => [$custid, $billnm]];
        }

        foreach ($attempts as $attempt) {
            $rows = $this->safeFetch($attempt['sql'], $attempt['params']);
            $details = [];
            foreach ($rows as $row) {
                $row = array_change_key_case($row, CASE_UPPER);
                $nominal = (int) ($row['DEBET'] ?? 0);
                if ($nominal <= 0) {
                    $nominal = (int) ($row['KREDIT'] ?? 0);
                }
                $details[] = [
                    'sumber' => 'tran',
                    'akun_detail' => ($row['BILLTARGET'] ?? '') ?: ($row['METODE'] ?? '-'),
                    'nominal_detail' => $nominal,
                    'trxdate' => $row['TRXDATE'] ?? null,
                    'metode' => $row['METODE'] ?? null,
                    'noreff' => $row['NOREFF'] ?? null,
                    'transno' => $row['TRANSNO'] ?? null,
                    'installment' => $row['INSTALLMENT'] ?? null,
                ];
            }
            if ($details) {
                return $details;
            }
        }

        return [];
    }

    private function fetchLunasVaDetails($custid, $nocust, $aa)
    {
        if ($aa === null || $aa === '') {
            return [];
        }

        $attempts = [];
        if ($custid) {
            $attempts[] = ['sql' => "SELECT CREATED_AT, BILLAM, BILLTOT, NOVA, ArrayTagihan FROM scctva WHERE CUSTID = ? ORDER BY CREATED_AT DESC", 'params' => [$custid]];
        }
        if ($nocust) {
            $attempts[] = ['sql' => "SELECT CREATED_AT, BILLAM, BILLTOT, NOVA, ArrayTagihan FROM scctva WHERE NOCUST = ? ORDER BY CREATED_AT DESC", 'params' => [$nocust]];
        }

        $aa = (string) $aa;
        foreach ($attempts as $attempt) {
            $rows = $this->safeFetch($attempt['sql'], $attempt['params']);
            $details = [];
            foreach ($rows as $row) {
                $row = array_change_key_case($row, CASE_UPPER);
                $ids = array_map('trim', explode(',', (string) ($row['ARRAYTAGIHAN'] ?? '')));
                $ams = array_map('trim', explode(',', (string) ($row['BILLAM'] ?? '')));
                $idx = array_search($aa, $ids, true);
                if ($idx === false) {
                    continue;
                }
                $nominal = isset($ams[$idx]) ? (int) $ams[$idx] : (int) ($row['BILLTOT'] ?? 0);
                $details[] = [
                    'sumber' => 'tran',
                    'akun_detail' => 'Virtual Account',
                    'nominal_detail' => $nominal,
                    'trxdate' => $row['CREATED_AT'] ?? null,
                    'metode' => 'VA',
                    'noreff' => $row['NOVA'] ?? null,
                    'transno' => $row['NOVA'] ?? null,
                ];
            }
            if ($details) {
                return $details;
            }
        }

        return [];
    }

    private function safeFetch($sql, array $params)
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(array_values($params));
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            $this->logVa('detail fetch failed', ['error' => $e->getMessage(), 'sql' => $sql]);
            return [];
        }
    }

    private function flagInstallable(array $row)
    {
        $value = $row['isINSTALLABLE']
            ?? $row['isinstallable']
            ?? $row['ISINSTALLABLE']
            ?? $row['is_installment']
            ?? 0;

        return ((int) $value === 1) ? 1 : 0;
    }
}
