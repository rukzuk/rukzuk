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
        .logo { display: block !important; }
        #nojs { display: block !important; }
        body.animate { opacity: 1 !important; }
        body { background: #454444 url('/<%= cacheBuster('app/login/images/rkzk-bg.png') %>') repeat; }
    </style>

</head>
<body class="animate">

<div class="wrapper">
    <div class="logo">
    </div>
    <!-- nojs -->
    <div id="nojs" class="bigwarning">
        <p class="bigwarning-text"><i class="bigico warning"></i><%= i18n('nojs.jsDisabled') %><br><br>
            <%= i18n('nojs.tutorial').replace('{0}', '<a href="http://www.enable-javascript.com" target="_blank">').replace('{1}', '</a>') %>
        </p>

    </div>
</div>

</body>
</html>
