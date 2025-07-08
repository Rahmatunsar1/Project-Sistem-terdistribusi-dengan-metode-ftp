<?php
// Daftar IP server FTP
$servers = [
    "1" => "192.168.194.109",
    "2" => "192.168.194.102",
    "3" => "192.168.194.100",
    "4" => "192.168.194.99"
];

// Daftar user dan password per server
$ftp_credentials = [
    "1" => ["user" => "server",  "pass" => "admin"],
    "2" => ["user" => "client1", "pass" => "admin"],
    "3" => ["user" => "client1", "pass" => "admin"],
    "4" => ["user" => "client2", "pass" => "admin"],
];

// Fungsi cek status server
function check_status($ip) {
    exec("ping -c 1 -W 1 $ip", $out, $status);
    return $status === 0 ? "Aktif" : "Mati";
}

// Fungsi upload file
function upload_file($server_id) {
    global $servers, $ftp_credentials;

    $ip   = $servers[$server_id];
    $user = $ftp_credentials[$server_id]["user"];
    $pass = $ftp_credentials[$server_id]["pass"];

    if ($_FILES["file"]["error"] === UPLOAD_ERR_OK) {
        $tmp_file = $_FILES["file"]["tmp_name"];
        $file_name = $_FILES["file"]["name"];

        $ftp_conn = ftp_connect($ip);
        if ($ftp_conn && ftp_login($ftp_conn, $user, $pass)) {
            ftp_put($ftp_conn, $file_name, $tmp_file, FTP_BINARY);
            ftp_close($ftp_conn);
            return "✅ File berhasil diupload ke server $ip.";
        } else {
            return "❌ Gagal koneksi ke server FTP $ip.";
        }
    } else {
        return "⚠️ Terjadi error saat upload file.";
    }
}

// Handle jika form di-submit
$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["server_id"])) {
    $server_id = $_POST["server_id"];
    $msg = upload_file($server_id);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>FTP Web Terdistribusi</title>
    <style>
        body { font-family: sans-serif; margin: 30px; background: #f8f8f8; }
        h1 { color: #0066cc; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #aaa; padding: 8px; text-align: center; }
        th { background-color: #eee; }
        .status-aktif { color: green; font-weight: bold; }
        .status-mati { color: red; font-weight: bold; }
        .message { margin: 10px 0; color: blue; }
    </style>
</head>
<body>
    <h1>🌐 FTP Web Terdistribusi</h1>

    <!-- Status Server -->
    <h2>Status Server</h2>
    <table>
        <tr><th>Server</th><th>IP</th><th>Status</th></tr>
        <?php foreach ($servers as $id => $ip): ?>
        <tr>
            <td>Server <?= $id ?></td>
            <td><?= $ip ?></td>
            <td class="<?= check_status($ip) == 'Aktif' ? 'status-aktif' : 'status-mati' ?>">
                <?= check_status($ip) ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <!-- Upload File -->
    <h2>Upload File ke Server</h2>
    <?php if ($msg): ?>
        <div class="message"><?= $msg ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <label for="file">Pilih file:</label>
        <input type="file" name="file" required><br><br>
        <label>Pilih server tujuan:</label>
        <select name="server_id">
            <?php foreach ($servers as $id => $ip): ?>
                <option value="<?= $id ?>">Server <?= $id ?> (<?= $ip ?>)</option>
            <?php endforeach; ?>
        </select>
        <br><br>
        <button type="submit">Upload</button>
    </form>

    <!-- Download File -->
    <h2>Link Download Manual</h2>
    <ul>
        <?php foreach ($servers as $id => $ip): ?>
            <li><a href="ftp://<?= $ftp_credentials[$id]["user"] ?>:<?= $ftp_credentials[$id]["pass"] ?>@<?= $ip ?>/"
                   target="_blank">Browse Server <?= $id ?> (<?= $ip ?>)</a></li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
