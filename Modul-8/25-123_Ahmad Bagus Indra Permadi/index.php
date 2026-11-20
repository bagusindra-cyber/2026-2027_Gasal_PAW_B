<?php
include 'cek_session.php';
include 'koneksi.php';

define('APP_ENTRY', true);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function h($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function runStatement(mysqli $connect, string $sql, string $types = '', array $values = []): mysqli_stmt
{
    $statement = mysqli_prepare($connect, $sql);
    if ($types !== '') {
        $parameters = [$statement, &$types];
        foreach ($values as $index => $value) {
            $values[$index] = $value;
            $parameters[] = &$values[$index];
        }
        call_user_func_array('mysqli_stmt_bind_param', $parameters);
    }
    mysqli_stmt_execute($statement);
    return $statement;
}

function showFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function redirectToPage(string $page, array $parameters = []): void
{
    $parameters = ['page' => $page] + $parameters;
    header('Location: index.php?' . http_build_query($parameters));
    exit();
}

function validDate(string $value): bool
{
    $date = DateTime::createFromFormat('!Y-m-d', $value);
    return $date !== false && $date->format('Y-m-d') === $value;
}

function recalculateTransactionTotal(mysqli $connect, int $transactionId): void
{
    $statement = runStatement(
        $connect,
        'SELECT COALESCE(SUM(harga * qty), 0) AS total FROM transaksi_detail WHERE transaksi_id = ?',
        'i',
        [$transactionId]
    );
    $total = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($statement))['total'];
    runStatement($connect, 'UPDATE transaksi SET total = ? WHERE id = ?', 'ii', [$total, $transactionId]);
}

$masterConfig = [
    'barang' => [
        'title' => 'Data Barang',
        'table' => 'barang',
        'key' => 'id',
        'key_type' => 'i',
        'auto_key' => true,
        'fields' => [
            'kode_barang' => ['label' => 'Kode Barang', 'type' => 'text', 'required' => true, 'bind' => 's'],
            'nama_barang' => ['label' => 'Nama Barang', 'type' => 'text', 'required' => true, 'bind' => 's'],
            'harga' => ['label' => 'Harga', 'type' => 'number', 'required' => true, 'min' => 0, 'bind' => 'i'],
            'stok' => ['label' => 'Stok', 'type' => 'number', 'required' => true, 'min' => 0, 'bind' => 'i'],
            'supplier_id' => ['label' => 'Supplier', 'type' => 'supplier', 'required' => false, 'bind' => 'i'],
        ],
    ],
    'supplier' => [
        'title' => 'Data Supplier',
        'table' => 'supplier',
        'key' => 'id',
        'key_type' => 'i',
        'auto_key' => true,
        'fields' => [
            'nama' => ['label' => 'Nama Supplier', 'type' => 'text', 'required' => true, 'bind' => 's'],
            'telp' => ['label' => 'Telepon', 'type' => 'text', 'required' => false, 'bind' => 's'],
            'alamat' => ['label' => 'Alamat', 'type' => 'textarea', 'required' => false, 'bind' => 's'],
        ],
    ],
    'pelanggan' => [
        'title' => 'Data Pelanggan',
        'table' => 'pelanggan',
        'key' => 'id',
        'key_type' => 's',
        'auto_key' => false,
        'fields' => [
            'id' => ['label' => 'Kode Pelanggan', 'type' => 'text', 'required' => true, 'bind' => 's'],
            'nama' => ['label' => 'Nama Pelanggan', 'type' => 'text', 'required' => true, 'bind' => 's'],
            'jenis_kelamin' => ['label' => 'Jenis Kelamin', 'type' => 'select', 'required' => true, 'bind' => 's', 'options' => ['L' => 'Laki-laki', 'P' => 'Perempuan']],
            'telp' => ['label' => 'Telepon', 'type' => 'text', 'required' => false, 'bind' => 's'],
            'alamat' => ['label' => 'Alamat', 'type' => 'textarea', 'required' => false, 'bind' => 's'],
        ],
    ],
    'user' => [
        'title' => 'Data User',
        'table' => 'user',
        'key' => 'id_user',
        'key_type' => 'i',
        'auto_key' => true,
        'fields' => [
            'username' => ['label' => 'Username', 'type' => 'text', 'required' => true, 'bind' => 's'],
            'nama' => ['label' => 'Nama', 'type' => 'text', 'required' => true, 'bind' => 's'],
            'password' => ['label' => 'Password', 'type' => 'password', 'required' => true, 'bind' => 's'],
            'alamat' => ['label' => 'Alamat', 'type' => 'textarea', 'required' => false, 'bind' => 's'],
            'hp' => ['label' => 'Nomor HP', 'type' => 'text', 'required' => false, 'bind' => 's'],
            'level' => ['label' => 'Level', 'type' => 'select', 'required' => true, 'bind' => 'i', 'options' => ['1' => 'Owner', '2' => 'Kasir']],
        ],
    ],
];

$requestedPage = $_GET['page'] ?? 'home';
$page = is_string($requestedPage) && $requestedPage !== '' ? $requestedPage : 'home';
$allowedPages = ['home', 'barang', 'supplier', 'pelanggan', 'user', 'transaksi', 'laporan'];
$pageError = '';

if (!in_array($page, $allowedPages, true)) {
    http_response_code(404);
    $pageError = 'Halaman yang dipilih tidak tersedia.';
}

if (isset($masterConfig[$page]) && (int) $_SESSION['level'] !== 1) {
    http_response_code(403);
    $pageError = 'Anda tidak memiliki akses ke menu Data Master.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pageError === '') {
    $action = $_POST['action'] ?? '';

    try {
        if (isset($masterConfig[$page]) && in_array($action, ['save_master', 'delete_master'], true)) {
            $config = $masterConfig[$page];
            $keyColumn = $config['key'];

            if ($action === 'delete_master') {
                $recordId = $_POST['record_id'] ?? '';
                if (!is_scalar($recordId) || (string) $recordId === '') {
                    throw new InvalidArgumentException('ID data tidak valid.');
                }
                if ($page === 'user' && (int) $recordId === (int) $_SESSION['id_user']) {
                    throw new InvalidArgumentException('Akun yang sedang digunakan tidak dapat dihapus.');
                }
                $recordId = $config['key_type'] === 'i' ? (int) $recordId : (string) $recordId;
                runStatement(
                    $connect,
                    'DELETE FROM `' . $config['table'] . '` WHERE `' . $keyColumn . '` = ?',
                    $config['key_type'],
                    [$recordId]
                );
                showFlash('success', $config['title'] . ' berhasil dihapus.');
            } else {
                $rawRecordId = $_POST['record_id'] ?? '';
                if (!is_scalar($rawRecordId)) {
                    throw new InvalidArgumentException('ID data tidak valid.');
                }
                $isEdit = (string) $rawRecordId !== '';
                $values = [];
                $types = '';

                foreach ($config['fields'] as $name => $field) {
                    $rawValue = $_POST[$name] ?? '';
                    if (!is_scalar($rawValue)) {
                        throw new InvalidArgumentException('Input ' . $field['label'] . ' tidak valid.');
                    }
                    $value = trim((string) $rawValue);

                    if ($name === 'password' && $isEdit && $value === '') {
                        continue;
                    }
                    if ($field['required'] && $value === '') {
                        throw new InvalidArgumentException($field['label'] . ' wajib diisi.');
                    }
                    if ($field['type'] === 'number' && $value !== '') {
                        $number = filter_var($value, FILTER_VALIDATE_INT);
                        if ($number === false || $number < $field['min']) {
                            throw new InvalidArgumentException($field['label'] . ' harus berupa bilangan bulat yang valid.');
                        }
                        $value = (int) $number;
                    } elseif ($name === 'supplier_id' && $value === '') {
                        $value = null;
                    } elseif ($field['type'] === 'select' && !array_key_exists($value, $field['options'])) {
                        throw new InvalidArgumentException('Pilihan ' . $field['label'] . ' tidak valid.');
                    } elseif ($name === 'password') {
                        $value = md5($value);
                    }

                    $values[] = $value;
                    $types .= $field['bind'];
                }

                if (!$config['auto_key'] && !$isEdit && !in_array($keyColumn, array_keys($config['fields']), true)) {
                    throw new InvalidArgumentException('Kode data wajib diisi.');
                }

                if ($isEdit) {
                    $recordId = $_POST['record_id'];
                    $recordId = $config['key_type'] === 'i' ? (int) $recordId : (string) $recordId;
                    $setParts = [];
                    foreach (array_keys($config['fields']) as $fieldName) {
                        if ($fieldName === 'password' && trim((string) ($_POST['password'] ?? '')) === '') {
                            continue;
                        }
                        $setParts[] = '`' . $fieldName . '` = ?';
                    }
                    $values[] = $recordId;
                    $types .= $config['key_type'];
                    runStatement(
                        $connect,
                        'UPDATE `' . $config['table'] . '` SET ' . implode(', ', $setParts) . ' WHERE `' . $keyColumn . '` = ?',
                        $types,
                        $values
                    );
                    if ($page === 'user' && (int) $recordId === (int) $_SESSION['id_user']) {
                        $_SESSION['nama'] = (string) ($_POST['nama'] ?? $_SESSION['nama']);
                        $_SESSION['username'] = (string) ($_POST['username'] ?? $_SESSION['username']);
                        $_SESSION['level'] = (int) ($_POST['level'] ?? $_SESSION['level']);
                    }
                    showFlash('success', $config['title'] . ' berhasil diperbarui.');
                } else {
                    $fieldNames = array_keys($config['fields']);
                    $insertColumns = $fieldNames;
                    if (!$config['auto_key'] && !in_array($keyColumn, $insertColumns, true)) {
                        array_unshift($insertColumns, $keyColumn);
                        array_unshift($values, trim((string) $_POST[$keyColumn]));
                        $types = $config['key_type'] . $types;
                    }
                    $placeholders = implode(', ', array_fill(0, count($insertColumns), '?'));
                    $quotedColumns = array_map(function ($column) {
                        return '`' . $column . '`';
                    }, $insertColumns);
                    runStatement(
                        $connect,
                        'INSERT INTO `' . $config['table'] . '` (' . implode(', ', $quotedColumns) . ') VALUES (' . $placeholders . ')',
                        $types,
                        $values
                    );
                    showFlash('success', $config['title'] . ' berhasil ditambahkan.');
                }
            }
            redirectToPage($page);
        }

        if ($page === 'transaksi') {
            if ($action === 'create_transaction') {
                $date = $_POST['waktu_transaksi'] ?? '';
                $customerId = $_POST['pelanggan_id'] ?? '';
                $description = trim((string) ($_POST['keterangan'] ?? ''));
                if (!is_string($date) || !validDate($date) || !is_string($customerId) || $customerId === '') {
                    throw new InvalidArgumentException('Tanggal transaksi dan pelanggan wajib diisi dengan benar.');
                }
                $statement = runStatement(
                    $connect,
                    'INSERT INTO transaksi (waktu_transaksi, keterangan, total, pelanggan_id) VALUES (?, ?, 0, ?)',
                    'sss',
                    [$date, $description, $customerId]
                );
                $transactionId = mysqli_insert_id($connect);
                showFlash('success', 'Transaksi berhasil dibuat. Silakan tambahkan item barang.');
                redirectToPage('transaksi', ['id' => $transactionId]);
            }

            if ($action === 'add_detail') {
                $transactionId = filter_var($_POST['transaksi_id'] ?? null, FILTER_VALIDATE_INT);
                $productId = filter_var($_POST['barang_id'] ?? null, FILTER_VALIDATE_INT);
                $quantity = filter_var($_POST['qty'] ?? null, FILTER_VALIDATE_INT);
                if (!$transactionId || !$productId || !$quantity || $quantity < 1) {
                    throw new InvalidArgumentException('Pilih transaksi, barang, dan jumlah yang valid.');
                }

                $productStatement = runStatement($connect, 'SELECT harga FROM barang WHERE id = ?', 'i', [$productId]);
                $product = mysqli_fetch_assoc(mysqli_stmt_get_result($productStatement));
                if (!$product) {
                    throw new InvalidArgumentException('Barang tidak ditemukan.');
                }
                $transactionStatement = runStatement($connect, 'SELECT id FROM transaksi WHERE id = ?', 'i', [$transactionId]);
                if (!mysqli_fetch_assoc(mysqli_stmt_get_result($transactionStatement))) {
                    throw new InvalidArgumentException('Transaksi tidak ditemukan.');
                }

                runStatement(
                    $connect,
                    'INSERT INTO transaksi_detail (transaksi_id, barang_id, harga, qty) VALUES (?, ?, ?, ?)',
                    'iiii',
                    [$transactionId, $productId, (int) $product['harga'], $quantity]
                );
                recalculateTransactionTotal($connect, $transactionId);
                showFlash('success', 'Item berhasil ditambahkan dan total transaksi diperbarui.');
                redirectToPage('transaksi', ['id' => $transactionId]);
            }

            if ($action === 'delete_detail') {
                $transactionId = filter_var($_POST['transaksi_id'] ?? null, FILTER_VALIDATE_INT);
                $productId = filter_var($_POST['barang_id'] ?? null, FILTER_VALIDATE_INT);
                if (!$transactionId || !$productId) {
                    throw new InvalidArgumentException('Data item transaksi tidak valid.');
                }
                runStatement(
                    $connect,
                    'DELETE FROM transaksi_detail WHERE transaksi_id = ? AND barang_id = ?',
                    'ii',
                    [$transactionId, $productId]
                );
                recalculateTransactionTotal($connect, $transactionId);
                showFlash('success', 'Item berhasil dihapus dan total transaksi diperbarui.');
                redirectToPage('transaksi', ['id' => $transactionId]);
            }
        }
    } catch (InvalidArgumentException $exception) {
        showFlash('error', $exception->getMessage());
        redirectToPage(isset($masterConfig[$page]) ? $page : 'transaksi');
    } catch (mysqli_sql_exception $exception) {
        $message = 'Data gagal disimpan. Periksa kembali isian dan keterkaitan data.';
        if ($page === 'transaksi') {
            $message = 'Transaksi gagal diproses. Pastikan data pelanggan/barang valid dan item belum pernah ditambahkan.';
        }
        showFlash('error', $message);
        redirectToPage(isset($masterConfig[$page]) ? $page : 'transaksi');
    }
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistem Penjualan</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; margin: 0; color: #222; }
        .navbar { align-items: center; background: #084b8a; color: #fff; display: flex; min-height: 48px; padding: 0 16px; }
        .brand { color: #fff; font-weight: bold; margin-right: 12px; text-decoration: none; white-space: nowrap; }
        .nav-links { align-items: center; display: flex; gap: 2px; }
        .nav-item { position: relative; }
        .nav-link { color: #fff; display: block; padding: 15px 12px; text-decoration: none; white-space: nowrap; }
        .nav-link:hover, .nav-item:focus-within > .nav-link { background: #063b6c; }
        .dropdown-menu { background: #fff; box-shadow: 0 4px 10px rgba(0,0,0,.2); display: none; left: 0; min-width: 180px; position: absolute; top: 100%; z-index: 10; }
        .dropdown-menu a { color: #222; display: block; padding: 10px 14px; text-decoration: none; }
        .dropdown-menu a:hover, .dropdown-menu a:focus { background: #edf3f8; }
        .nav-item:hover .dropdown-menu, .nav-item:focus-within .dropdown-menu { display: block; }
        .account { margin-left: auto; }
        .account .dropdown-menu { left: auto; right: 0; min-width: 125px; }
        main { margin: 24px auto; max-width: 1100px; padding: 0 18px; }
        .flash { border-radius: 4px; margin: 16px 0; padding: 10px 12px; }
        .flash.success { background: #e8f5e9; color: #1b5e20; }
        .flash.error { background: #ffebee; color: #b71c1c; }
        .form-grid { display: grid; gap: 12px; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin: 14px 0; max-width: 760px; }
        label { display: block; font-weight: bold; }
        input, select, textarea { border: 1px solid #aaa; border-radius: 3px; margin-top: 5px; padding: 8px; width: 100%; }
        textarea { min-height: 70px; resize: vertical; }
        button, .button { background: #084b8a; border: 0; border-radius: 3px; color: #fff; cursor: pointer; display: inline-block; padding: 8px 12px; text-decoration: none; }
        button.danger { background: #b3261e; }
        table { border-collapse: collapse; margin: 14px 0 24px; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; vertical-align: top; }
        th { background: #f2f5f8; }
        td form { display: inline-block; margin: 0; }
        .section { border: 1px solid #ddd; border-radius: 4px; margin: 18px 0; padding: 16px; }
        @media (max-width: 680px) {
            .navbar { align-items: stretch; flex-wrap: wrap; padding: 0 8px; }
            .nav-links { flex-wrap: wrap; }
            .nav-link { padding: 12px 8px; }
            .account { margin-left: 0; }
        }
        @media print {
            .no-print, .navbar { display: none !important; }
            main { margin: 0; max-width: none; padding: 0; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a class="brand" href="index.php">▣ Sistem Penjualan</a>
        <div class="nav-links">
            <div class="nav-item"><a class="nav-link" href="index.php">Home</a></div>
            <?php if ((int) $_SESSION['level'] === 1) { ?>
                <div class="nav-item">
                    <a class="nav-link" href="#" aria-haspopup="true">Data Master &#9662;</a>
                    <div class="dropdown-menu">
                        <a href="index.php?page=barang">Data Barang</a>
                        <a href="index.php?page=supplier">Data Supplier</a>
                        <a href="index.php?page=pelanggan">Data Pelanggan</a>
                        <a href="index.php?page=user">Data User</a>
                    </div>
                </div>
            <?php } ?>
            <div class="nav-item"><a class="nav-link" href="index.php?page=transaksi">Transaksi</a></div>
            <div class="nav-item"><a class="nav-link" href="index.php?page=laporan">Laporan</a></div>
        </div>
        <div class="nav-item account">
            <a class="nav-link" href="#" aria-haspopup="true"><?php echo h($_SESSION['nama']); ?> &#9662;</a>
            <div class="dropdown-menu">
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <main>
        <?php if ($flash) { ?>
            <div class="flash <?php echo h($flash['type']); ?>"><?php echo h($flash['message']); ?></div>
        <?php } ?>
        <?php if ($pageError !== '') { ?>
            <h2><?php echo h($pageError); ?></h2>
        <?php } elseif (isset($masterConfig[$page])) { ?>
            <?php include 'data_master_view.php'; ?>
        <?php } elseif ($page === 'transaksi') { ?>
            <?php include 'transaksi_view.php'; ?>
        <?php } elseif ($page === 'laporan') { ?>
            <?php include 'laporan_view.php'; ?>
        <?php } else { ?>
            <h2>Selamat datang, <?php echo h($_SESSION['nama']); ?>!</h2>
            <p>Anda login sebagai <?php echo (int) $_SESSION['level'] === 1 ? 'Owner' : 'Kasir'; ?>.</p>
        <?php } ?>
    </main>
</body>
</html>
