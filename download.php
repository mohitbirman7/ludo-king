<?php
/**
 * Ludo King - Official App Download Endpoint
 * Sends proper Android APK headers to force immediate download across all browsers.
 */
$apkPath = __DIR__ . '/LudoKing.apk';

if (!file_exists($apkPath)) {
    http_response_code(404);
    die('App package not found.');
}

// Clear any output buffer
if (ob_get_level()) {
    ob_end_clean();
}

header('Content-Description: File Transfer');
header('Content-Type: application/vnd.android.package-archive');
header('Content-Disposition: attachment; filename="LudoKing.apk"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . filesize($apkPath));

readfile($apkPath);
exit;
