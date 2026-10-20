<?php
ini_set('memory_limit', '512M');
set_time_limit(0);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: *');
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') exit;

$url = 'https://opencode.ai/zen/' . ltrim(str_replace('/index.php', '', $_SERVER['REQUEST_URI']), '/');

$headers = [];
foreach (getallheaders() as $k => $v) {
    if (!in_array(strtolower($k), ['host', 'content-length', 'accept-encoding'])) {
        $headers[] = "$k: $v";
    }
}

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_TIMEOUT => 120,
    CURLOPT_ENCODING => '',
    CURLOPT_FOLLOWLOCATION => true,
]);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents('php://input'));
}

header('X-Accel-Buffering: no');

curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($ch, $header) {
    $len = strlen($header);
    if (stripos($header, 'HTTP/') === 0) {
        $parts = explode(' ', $header);
        if (isset($parts[1])) {
            $code = (int)$parts[1];
            if ($code !== 100) {
                http_response_code($code);
            }
        }
    } elseif (stripos($header, 'Content-Type:') === 0) {
        header($header);
    }
    return $len;
});

curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch, $data) {
    echo $data;
    if (ob_get_level() > 0) ob_flush();
    flush();
    return strlen($data);
});

curl_exec($ch);
curl_close($ch);