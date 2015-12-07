<!-- quirks mode FTW !-->
<html style="height: 100%;">
    <!-- timestamp: <%= timestamp %> -->
    <!-- build: <%= build %> -->
    <!-- branch: <%= branch %> -->
    <!-- date: <%= date %> -->

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="author" content="rukzuk AG">
        <meta name="audience" content="all">
        <meta name="robots" content="noindex,nofollow">
        <meta name="google" content="notranslate">
        <link rel="apple-touch-icon" href="/<%= cacheBuster('app/images/webclipicon.png') %>">
        <link rel="icon" href="/<%= cacheBuster('app/images/webclipicon.png') %>">
        <title><%= i18n('html.pageTitle') %></title>

        <!-- apply the following styles immediately -->
        <style>
            body {
                background: #454444 url('/<%= cacheBuster('app/images/background.png') %>');
                height: 100%;
            }
            body.CMSloading:before {
                content: '';
                position: fixed;
                top: calc(50% - 19px);
                left: calc(50% - 19px);
                height: 38px;
                width: 38px;
                display: block;
                background: transparent url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzgiIGhlaWdodD0iMzgiIHZpZXdCb3g9IjAgMCAzOCAzOCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiBzdHJva2U9IiNmZmYiPgogICAgPGcgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIj4KICAgICAgICA8ZyB0cmFuc2Zvcm09InRyYW5zbGF0ZSgxIDEpIiBzdHJva2Utd2lkdGg9IjIiPgogICAgICAgICAgICA8Y2lyY2xlIHN0cm9rZS1vcGFjaXR5PSIuMyIgY3g9IjE4IiBjeT0iMTgiIHI9IjE4Ii8+CiAgICAgICAgICAgIDxwYXRoIGQ9Ik0zNiAxOGMwLTkuOTQtOC4wNi0xOC0xOC0xOCI+CiAgICAgICAgICAgICAgICA8YW5pbWF0ZVRyYW5zZm9ybQogICAgICAgICAgICAgICAgICAgIGF0dHJpYnV0ZU5hbWU9InRyYW5zZm9ybSIKICAgICAgICAgICAgICAgICAgICB0eXBlPSJyb3RhdGUiCiAgICAgICAgICAgICAgICAgICAgZnJvbT0iMCAxOCAxOCIKICAgICAgICAgICAgICAgICAgICB0bz0iMzYwIDE4IDE4IgogICAgICAgICAgICAgICAgICAgIGR1cj0iMXMiCiAgICAgICAgICAgICAgICAgICAgcmVwZWF0Q291bnQ9ImluZGVmaW5pdGUiLz4KICAgICAgICAgICAgPC9wYXRoPgogICAgICAgIDwvZz4KICAgIDwvZz4KPC9zdmc+Cg==') center center no-repeat;
                z-index: -1;
            }
        </style>
    </head>

    <body class="CMSloading" data-build="<%= build %>" data-branch="<%= branch %>"  data-channel="<%= channel %>">

        <link rel="stylesheet" type="text/css" href="/<%= cacheBuster('app/css/fileuploadfield.css') %>">
        <link rel="stylesheet" type="text/css" href="/<%= cacheBuster('app/css/tricheckbox.css') %>">
        <link rel="stylesheet" type="text/css" href="/<%= cacheBuster('app/css/fonts/ubuntu/stylesheet.css') %>">
        <link rel="stylesheet" type="text/css" href="/<%= cacheBuster('app/css/cms-all.css') %>">
        <link rel="stylesheet" type="text/css" href="/<%= cacheBuster('cms/data/theme/cms-theme.css') %>">

        <!-- CMSSERVER object with static server information -->
        <script src="/<%= cacheBuster('app/service/index/info') %>" type="text/javascript"></script>

        <!-- 3rd party scripts -->
        <script src="/<%= cacheBuster('app/js/bowser/bowser.js') %>" type="text/javascript"></script>
        <script src="/<%= cacheBuster('app/js/zipjs/zip.js') %>" type="text/javascript"></script>
        <!-- worker script must stay separately and will get loaded automatically by webworker -->
        <script src="/<%= cacheBuster('app/js/zipjs/deflate.js') %>" type="text/javascript"></script>
        <% _.forEach(pluploadSources, function (src) { %><script src="/<%= cacheBuster(src) %>" type="text/javascript"></script><% }); %>
        <script src="/<%= cacheBuster('app/js/tiny_mce/tiny_mce.js') %>" type="text/javascript"></script>
        <script src="/<%= cacheBuster('app/js/qrcodejs/qrcode.js') %>" type="text/javascript"></script>

        <!-- Ext -->
        <% _.forEach(extSources, function (src) { %><script src="/<%= cacheBuster(src) %>" type="text/javascript"></script><% }); %>
        <% if (mode === 'dev') { %><script src="/app/js/Ext/ext-3.2.1/ext-lint.js" type="text/javascript"></script><% } %>

        <!-- SB ext extensions-->
        <% _.forEach(extFixes, function (src) { %><script src="/<%= cacheBuster(src) %>" type="text/javascript"></script><% }); %>

        <!-- 3rd party ext extensions -->
        <script src="/<%= cacheBuster('app/js/Ext/ux/Spinner.js') %>" type="text/javascript"></script>
        <script src="/<%= cacheBuster('app/js/Ext/ux/TinyMCE.js') %>" type="text/javascript"></script>

        <!-- SB libs and components -->
        <% if (mode === 'dev') { %><script src="/app/js/SB/debug.js" type="text/javascript"></script><% } %>
        <% _.forEach(sbSources, function (src) { %><script src="/<%= cacheBuster(src) %>" type="text/javascript"></script><% }); %>

        <% if (mode === 'dev') { %>
        <!-- A script to support GUI testing with casperjs; include between config and component to inject mocks -->
        <script src="/app/js/testing.js" type="text/javascript"></script>
        <% } %>

        <!-- CMS -->
        <!-- CMS lang and config -->
        <script src="/<%= cacheBuster('app/lang/lang.js') %>" type="text/javascript"></script>
        <% _.forEach(cmsConfigs, function (src) { %><script src="/<%= cacheBuster(src) %>" type="text/javascript"></script><% }); %>

        <% if (mode === 'dev') { %>
        <!-- A script that notes that it is a dev version in the application title -->
        <script src="/app/js/uncompressed.js" type="text/javascript"></script>
        <% } %>

        <!-- CMS source files -->
        <% _.forEach(cmsSources, function (src) { %><script src="/<%= cacheBuster(src) %>" type="text/javascript"></script><% }); %>

        <!-- Fallback for non-js browsers -->
        <noscript>
            <iframe src="/<%= cacheBuster('app/nojs.html') %>" frameborder="0" style="width: 100%; height: 100%;"></iframe>
        </noscript>

    </body>
</html>
