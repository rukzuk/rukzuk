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
    <base href="/">
    <meta name="robots" content="noindex,nofollow">
    <meta name="google" content="notranslate">
    <title><%= i18n('html.pageTitle') %> - <%= i18n('error500.serverError') %></title>

    <link href="/<%= cacheBuster('app/css/login.css') %>" rel="stylesheet">
    <link href="/<%= cacheBuster('cms/data/theme/login-theme.css') %>" rel="stylesheet">
    <style>
        .logo { display: block !important; }
        #error404 { display: block !important; }
        body.animate { opacity: 1 !important; }
        body { background: #454444 url('/<%= cacheBuster('app/login/images/rkzk-bg.png') %>') repeat; }
    </style>

</head>
<body class="animate">

<div class="wrapper">
    <div class="logo">
    </div>
    <div id="error404" class="bigwarning">
        <p class="bigwarning-text"><i class="bigico warning"></i><%= i18n('error500.serverError') %><br><br>
            <%= i18n('error500.serverErrorText') %></p>
    </div>
</div>

</body>
</html>
