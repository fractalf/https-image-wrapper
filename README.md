# Https Image Wrapper

Get rid of that annoying yellow warning triangle in firefox when showing images from other sites served over http on your own site.
https://support.mozilla.org/en-US/kb/how-do-i-tell-if-my-connection-is-secure

![](https://support.cdn.mozilla.net/media/uploads/gallery/images/2015-10-21-19-03-53-88bff4.png)

Usage:

Put i.php on your webserver root, then use it like this:

````
<?php
  $secret = 'mysecret';
  $imgHttp = 'http://somesite.com/image.jpg';
  $crc = hash('crc32b', $secret . $imgHttp);
  $imgHttps = 'https://mysite.com/i.php?' . $crc . base64_encode($imgHttp);
  echo '<img src="$imgHttp" title="Original image served with http">';
  echo '<img src="$imgHttps" title="Image served with https">';
?>
````
