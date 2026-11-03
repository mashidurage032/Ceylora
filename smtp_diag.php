<?php
/**
 * SMTP Diagnostics helper
 * - Open in browser: http://localhost/ceylora/smtp_diag.php
 * - This page runs nslookup, TCP connect test, and an SMTP handshake.
 * - If you provide SMTP username/password they are used only for this test
 *   and are NOT stored.
 */

function runCmd($cmd) {
    $out = [];
    $rc = null;
    exec($cmd . ' 2>&1', $out, $rc);
    return [implode("\n", $out), $rc];
}

$host = $_POST['host'] ?? 'smtp.gmail.com';
$port = (int)($_POST['port'] ?? 587);
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$doAuth = !empty($username) && !empty($password) && ($_POST['test_auth'] ?? '') === '1';

$results = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // DNS check
    list($out, $rc) = runCmd("nslookup " . escapeshellarg($host));
    $results['nslookup'] = $out ?: "(no output)";

    // TCP connect check using fsockopen
    $timeout = 10;
    $socketHost = $host;
    $sock = @fsockopen($socketHost, $port, $errno, $errstr, $timeout);
    if (!$sock) {
        $results['tcp'] = "Connection failed: $errstr ($errno)";
    } else {
        stream_set_timeout($sock, $timeout);
        $line = fgets($sock, 512);
        $results['tcp'] = "Connected. Server greeting: " . trim($line);

        // SMTP handshake
        fwrite($sock, "EHLO localhost\r\n");
        $res = '';
        while ($l = fgets($sock, 512)) {
            $res .= $l;
            if (substr($l,3,1) === ' ') break;
        }
        $results['ehlo'] = trim($res);

        // Try STARTTLS if available and requested port 587
        if (stripos($res, 'STARTTLS') !== false) {
            fwrite($sock, "STARTTLS\r\n");
            $r = fgets($sock, 512);
            $results['starttls_resp'] = trim($r);
            if (strpos($r, '220') === 0) {
                if (!stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    $results['starttls_enable'] = 'Failed to enable TLS';
                } else {
                    // EHLO again after TLS
                    fwrite($sock, "EHLO localhost\r\n");
                    $res2 = '';
                    while ($l = fgets($sock, 512)) {
                        $res2 .= $l;
                        if (substr($l,3,1) === ' ') break;
                    }
                    $results['ehlo_after_tls'] = trim($res2);
                }
            }
        }

        // If credentials provided and user asked to test auth
        if ($doAuth) {
            // Send AUTH LOGIN
            fwrite($sock, "AUTH LOGIN\r\n");
            $a1 = fgets($sock, 512);
            fwrite($sock, base64_encode($username) . "\r\n");
            $a2 = fgets($sock, 512);
            fwrite($sock, base64_encode($password) . "\r\n");
            $a3 = fgets($sock, 512);
            $results['auth_steps'] = trim($a1) . "\n" . trim($a2) . "\n" . trim($a3);
        }

        fwrite($sock, "QUIT\r\n");
        fclose($sock);
    }
}

function h($s){ return htmlspecialchars($s, ENT_QUOTES); }
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>SMTP Diagnostics</title>
  <style>body{font-family:Arial,Helvetica,sans-serif;margin:20px}textarea{width:100%;height:160px}</style>
  </head>
<body>
  <h2>SMTP Diagnostics</h2>
  <p>Use this page to test DNS, TCP and (optionally) AUTH against an SMTP server. Passwords entered here are used only for this test and not stored.</p>

  <form method="post">
    <label>Host: <input name="host" value="<?= h($host) ?>"></label><br><br>
    <label>Port: <input name="port" value="<?= h($port) ?>"></label><br><br>
    <label>SMTP Username: <input name="username" value="<?= h($username) ?>"></label><br><br>
    <label>SMTP Password: <input type="password" name="password" value=""></label><br><br>
    <label><input type="checkbox" name="test_auth" value="1" <?= $doAuth ? 'checked' : '' ?>> Test AUTH LOGIN with provided credentials</label><br><br>
    <button type="submit">Run Diagnostics</button>
  </form>

  <?php if (!empty($results)): ?>
    <h3>Results</h3>
    <h4>nslookup</h4>
    <textarea readonly><?= h($results['nslookup'] ?? '') ?></textarea>

    <h4>TCP / SMTP</h4>
    <textarea readonly><?= h($results['tcp'] ?? '') . "\n\n" . h($results['ehlo'] ?? '') . "\n\n" . h($results['ehlo_after_tls'] ?? '') ?></textarea>

    <?php if (!empty($results['starttls_resp'])): ?>
      <h4>STARTTLS response</h4>
      <pre><?= h($results['starttls_resp']) ?></pre>
    <?php endif; ?>

    <?php if (!empty($results['auth_steps'])): ?>
      <h4>AUTH exchange</h4>
      <pre><?= h($results['auth_steps']) ?></pre>
    <?php endif; ?>
  <?php endif; ?>

</body>
</html>
