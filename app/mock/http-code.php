<?php header('HTTP/1.0 ' . (isset($_GET['code']) ? $_GET['code'] : 500) . ' ' . (isset($_GET['text']) ? $_GET['text'] : ''));
