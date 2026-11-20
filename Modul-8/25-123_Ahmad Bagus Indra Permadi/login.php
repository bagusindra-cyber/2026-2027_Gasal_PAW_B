<?php
session_start();
include 'koneksi.php';

if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    header('Location: index.php');
    exit();
}

$loginMessage = '';
if (isset($_POST['tombol_login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!is_string($username) || !is_string($password)) {
        $loginMessage = 'Username atau password tidak valid.';
    } else {
        $statement = mysqli_prepare($connect, 'SELECT id_user, username, password, nama, level FROM user WHERE username = ?');
        mysqli_stmt_bind_param($statement, 's', $username);
        mysqli_stmt_execute($statement);
        $result = mysqli_stmt_get_result($statement);
        $row = mysqli_fetch_assoc($result);

        if ($row && hash_equals($row['password'], md5($password))) {
            session_regenerate_id(true);
            $_SESSION['login']    = true;
            $_SESSION['id_user']  = $row['id_user'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['nama']     = $row['nama'];
            $_SESSION['level']    = $row['level'];

            header('Location: index.php');
            exit();
        } elseif ($row) {
            $loginMessage = 'Password salah.';
        } else {
            $loginMessage = 'Username tidak ditemukan.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Halaman Login</title>
</head>
<body>
    <h3>Silakan Login</h3>
    <?php if ($loginMessage !== '') { ?>
        <p role="alert"><?php echo htmlspecialchars($loginMessage, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php } ?>
    <form action="" method="post">
        <input type="text" name="username" placeholder="Username" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <input type="submit" name="tombol_login" value="Login Masuk">
    </form>
</body>
</html>
