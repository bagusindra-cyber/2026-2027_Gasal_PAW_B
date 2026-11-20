<?php
if (!defined('APP_ENTRY')) {
    http_response_code(404);
    exit();
}

$config = $masterConfig[$page];
$editId = $_GET['edit'] ?? '';
$editId = is_scalar($editId) ? (string) $editId : '';
$editRecord = null;
$editNotFound = false;

if ($editId !== '') {
    $lookupId = $config['key_type'] === 'i' ? (int) $editId : $editId;
    $statement = runStatement(
        $connect,
        'SELECT * FROM `' . $config['table'] . '` WHERE `' . $config['key'] . '` = ?',
        $config['key_type'],
        [$lookupId]
    );
    $editRecord = mysqli_fetch_assoc(mysqli_stmt_get_result($statement));
    $editNotFound = !$editRecord;
}

$masterQueries = [
    'barang' => [
        'sql' => 'SELECT b.id, b.kode_barang, b.nama_barang, b.harga, b.stok, s.nama AS supplier
                 FROM barang b LEFT JOIN supplier s ON s.id = b.supplier_id ORDER BY b.id',
        'columns' => ['id' => 'ID', 'kode_barang' => 'Kode Barang', 'nama_barang' => 'Nama Barang', 'harga' => 'Harga', 'stok' => 'Stok', 'supplier' => 'Supplier'],
    ],
    'supplier' => [
        'sql' => 'SELECT id, nama, telp, alamat FROM supplier ORDER BY id',
        'columns' => ['id' => 'ID', 'nama' => 'Nama Supplier', 'telp' => 'Telepon', 'alamat' => 'Alamat'],
    ],
    'pelanggan' => [
        'sql' => 'SELECT id, nama, jenis_kelamin, telp, alamat FROM pelanggan ORDER BY id',
        'columns' => ['id' => 'Kode', 'nama' => 'Nama Pelanggan', 'jenis_kelamin' => 'Jenis Kelamin', 'telp' => 'Telepon', 'alamat' => 'Alamat'],
    ],
    'user' => [
        'sql' => 'SELECT id_user, username, nama, alamat, hp, level FROM user ORDER BY id_user',
        'columns' => ['id_user' => 'ID', 'username' => 'Username', 'nama' => 'Nama', 'alamat' => 'Alamat', 'hp' => 'Nomor HP', 'level' => 'Level'],
    ],
];

$listResult = mysqli_query($connect, $masterQueries[$page]['sql']);
if (!$listResult) {
    die('Gagal memuat ' . h($config['title']) . ': ' . h(mysqli_error($connect)));
}
$supplierResult = $page === 'barang' ? mysqli_query($connect, 'SELECT id, nama FROM supplier ORDER BY nama') : null;
?>
<h2><?php echo h($config['title']); ?></h2>

<?php if ($editNotFound) { ?>
    <p class="flash error">Data yang akan diedit tidak ditemukan.</p>
<?php } ?>

<section class="section">
    <h3><?php echo $editRecord ? 'Edit Data' : 'Tambah Data'; ?></h3>
    <form method="post" action="index.php?page=<?php echo h($page); ?>">
        <input type="hidden" name="action" value="save_master">
        <?php if ($editRecord) { ?>
            <input type="hidden" name="record_id" value="<?php echo h($editRecord[$config['key']]); ?>">
        <?php } ?>
        <div class="form-grid">
            <?php foreach ($config['fields'] as $name => $field) {
                if ($name === 'id' && $config['auto_key']) {
                    continue;
                }
                $fieldValue = $editRecord[$name] ?? '';
                $isRequired = $field['required'] && !($name === 'password' && $editRecord);
                ?>
                <label>
                    <?php echo h($field['label']); ?>
                    <?php if ($field['type'] === 'textarea') { ?>
                        <textarea name="<?php echo h($name); ?>" <?php echo $isRequired ? 'required' : ''; ?>><?php echo h($fieldValue); ?></textarea>
                    <?php } elseif ($field['type'] === 'select') { ?>
                        <select name="<?php echo h($name); ?>" <?php echo $isRequired ? 'required' : ''; ?>>
                            <?php foreach ($field['options'] as $optionValue => $optionLabel) { ?>
                                <option value="<?php echo h($optionValue); ?>" <?php echo (string) $fieldValue === (string) $optionValue ? 'selected' : ''; ?>>
                                    <?php echo h($optionLabel); ?>
                                </option>
                            <?php } ?>
                        </select>
                    <?php } elseif ($field['type'] === 'supplier') { ?>
                        <select name="<?php echo h($name); ?>">
                            <option value="">-- Tanpa Supplier --</option>
                            <?php while ($supplier = mysqli_fetch_assoc($supplierResult)) { ?>
                                <option value="<?php echo h($supplier['id']); ?>" <?php echo (string) $fieldValue === (string) $supplier['id'] ? 'selected' : ''; ?>>
                                    <?php echo h($supplier['nama']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    <?php } else { ?>
                        <input
                            type="<?php echo h($field['type']); ?>"
                            name="<?php echo h($name); ?>"
                            value="<?php echo $field['type'] === 'password' ? '' : h($fieldValue); ?>"
                            <?php echo isset($field['min']) ? 'min="' . h($field['min']) . '"' : ''; ?>
                            <?php echo $isRequired ? 'required' : ''; ?>
                            <?php echo $field['type'] === 'password' && $editRecord ? 'autocomplete="new-password"' : ''; ?>
                        >
                    <?php } ?>
                    <?php if ($name === 'password' && $editRecord) { ?>
                        <small>Kosongkan jika password tidak ingin diubah.</small>
                    <?php } ?>
                </label>
            <?php } ?>
        </div>
        <button type="submit"><?php echo $editRecord ? 'Simpan Perubahan' : 'Tambah Data'; ?></button>
        <?php if ($editRecord) { ?>
            <a class="button" href="index.php?page=<?php echo h($page); ?>">Batal</a>
        <?php } ?>
    </form>
</section>

<h3>Daftar <?php echo h($config['title']); ?></h3>
<table>
    <thead>
        <tr>
            <?php foreach ($masterQueries[$page]['columns'] as $label) { ?>
                <th><?php echo h($label); ?></th>
            <?php } ?>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php if (mysqli_num_rows($listResult) === 0) { ?>
            <tr><td colspan="<?php echo count($masterQueries[$page]['columns']) + 1; ?>">Belum ada data.</td></tr>
        <?php } ?>
        <?php while ($row = mysqli_fetch_assoc($listResult)) { ?>
            <tr>
                <?php foreach ($masterQueries[$page]['columns'] as $column => $label) { ?>
                    <td>
                        <?php
                        if ($page === 'barang' && $column === 'harga') {
                            echo 'Rp ' . number_format((float) $row[$column], 0, ',', '.');
                        } elseif ($page === 'user' && $column === 'level') {
                            echo (int) $row[$column] === 1 ? 'Owner' : 'Kasir';
                        } else {
                            echo h($row[$column]);
                        }
                        ?>
                    </td>
                <?php } ?>
                <td>
                    <a class="button" href="index.php?page=<?php echo h($page); ?>&amp;edit=<?php echo rawurlencode((string) $row[$config['key']]); ?>">Edit</a>
                    <form method="post" action="index.php?page=<?php echo h($page); ?>" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                        <input type="hidden" name="action" value="delete_master">
                        <input type="hidden" name="record_id" value="<?php echo h($row[$config['key']]); ?>">
                        <button class="danger" type="submit">Hapus</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>
