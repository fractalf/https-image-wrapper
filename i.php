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
 *   $imgHttps = 'https://mysite.com/i.php?' . $crc . base64_encode($imgHttp);
 *   echo '<img src="$imgHttp" title="Original image served with http">';
 *   echo '<img src="$imgHttps" title="Image served with https">';
 * ?>
 */

$fileTypes = [
    "jpg" => "image/jpeg",
    "jpeg" => "image/jpeg",
    "gif" => "image/gif",
    "png" => "image/png"
];

$secret = 'mysecret';

$arg = key($_GET);
$crc = substr($arg, 0, 8);
$url = base64_decode(substr($arg, 8));
$fileType = pathinfo($url, PATHINFO_EXTENSION);

if (hash("crc32b", $secret . $url) === $crc && isset($fileTypes[$fileType])) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    $result = curl_exec($ch);

    header("HTTP/1.1 200 OK");
    header("Content-Type: " . $fileTypes[$fileType]);
    header("Cache-Control: s-maxage=31536000"); // one year
    echo $result;
} else {
    header("HTTP/1.1 404 Not Found");
}
