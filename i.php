<?php
/**
 * Https Image Wrapper (i.php)
 *
 * Get rid of that annoying yellow warning triangle in firefox when showing
 * images for other sites served over http.
 * https://support.mozilla.org/en-US/kb/how-do-i-tell-if-my-connection-is-secure
 *
 * Usage:
 * Put this file on your webserver root, then use it like this:
 *
 * <?php
 *   $secret = 'mysecret';
 *   $imgHttp = 'http://somesite.com/image.jpg';
 *   $crc = hash('crc32b', $secret . $imgHttp);
 *   $cache = '=300'; // 5min
 *   $imgHttps = 'https://mysite.com/i.php?' . $crc . urlencode(base64_encode($imgHttp)) . $cache;
 *   echo '<img src="$imgHttp" title="Original image served with http">';
 *   echo '<img src="$imgHttps" title="Image served with https">';
 * ?>
 */

$contentTypes = [
    'image/jpeg' => true,
    'image/gif' => true,
    'image/png' => true,
    'image/tiff' => true
];
$fileTypes = [
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif' => 'image/gif',
    'png' => 'image/png',
    'tif' => 'image/tiff',
    'tiff' => 'image/tiff'
];

$secret = 'mysecret';

$arg = key($_GET);
$crc = substr($arg, 0, 8);
$url = urldecode(base64_decode(substr($arg, 8)));

if (hash('crc32b', $secret . $url) === $crc) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    $result = curl_exec($ch);
    $info = curl_getinfo($ch);
    
    $valid = false;
    $type = strtolower($info['content_type']);
    if ($type && $contentTypes[$type]) {
        $valid = true;
    } else {
        $url = explode('?', $url)[0]; // strip away ?foo=bar
        $fileType = strtolower(pathinfo($url, PATHINFO_EXTENSION));
        if (isset($fileTypes[$fileType])) {
            $valid = true;
            $type = $fileTypes[$fileType];
        }
    }
    
    if ($valid) {
        $ttl = current($_GET);
        if (!$ttl) {
            $ttl = 2678400; // 1 month
            // $ttl = 31536000; // 1 year
        }
        header('HTTP/1.1 200 OK');
        header('Content-Type: ' . $type);
        header('Cache-Control: s-maxage=' . $ttl);
        echo $result;
    } else {
        header('HTTP/1.1 415 Unsupported Media Type');
    }
    
} else {
    header('HTTP/1.1 404 Not Found');
}
