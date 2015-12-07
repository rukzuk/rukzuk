<!DOCTYPE html>
<html lang="<%= i18nLangCode %>">
    <!-- timestamp: <%= timestamp %> -->
    <!-- build: <%= build %> -->
    <!-- date: <%= date %> -->

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="author" content="rukzuk AG">
    <meta name="audience" content="all">
    <meta name="robots" content="noindex,nofollow">
    <meta name="google" content="notranslate">
    <title><%= i18n('html.pageTitle') %></title>

    <link href="/<%= cacheBuster('app/css/login.css') %>" rel="stylesheet">
    <link href="/<%= cacheBuster('cms/data/theme/login-theme.css') %>" rel="stylesheet">

    <style>
        .logo { display: block; }
        #oldbrowser { display: block; }
        body.background:after {
            display: none;
        }
    </style>
</head>
<body class="background">
<div class="wrapper">
    <div class="logo">
    </div>

    <div id="oldbrowser" class="bigwarning">
        <p class="bigwarning-text"><i class="bigico warning"></i><%= i18n('unsupportedBrowser.notSupported') %><br><br>
            <%= i18n('unsupportedBrowser.useDesktopVersion') %></p>
        <div class="browser-list notamac">
            <div>
                <a href="http://www.google.com/chrome/" title="Google Chrome" target="_top"><i class="bigico chrome"></i>Chrome</a>
                <a href="http://www.mozilla.org/firefox/" title="Mozilla Firefox" target="_top"><i class="bigico firefox"></i>Firefox</a>
                <a href="http://www.apple.com/safari/" title="Apple Safari" class="safari_link" target="_top"><i class="bigico safari"></i>Safari</a>
            </div>
        </div>
    </div>

</div>

<!-- Scripts -->
<script src="/<%= cacheBuster('app/login/jquery-2.1.4.min.js') %>" type="text/javascript"></script>
<script>
$(function () {
    var isMac = navigator.userAgent.indexOf('Macintosh') !== -1;
    if (isMac) {
        $('.safari_link').show();
        $('.browser-list').removeClass('notamac');
    }
});
</script>

</body>
</html>
