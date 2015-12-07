<!-- quirks mode FTW !-->
<html style="height: 100%;">
    <!-- rukzuk form editor -->
    <!-- timestamp: <%= timestamp %> -->
    <!-- build: <%= build %> -->
    <!-- date: <%= date %> -->

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="author" content="rukzuk AG">
        <meta name="audience" content="all">
        <meta name="robots" content="noindex,nofollow">
        <meta name="google" content="notranslate">
        <title><%= i18n('html.pageTitle') %> - Form Editor</title>

        <!-- apply the following styles immediately -->
        <style>
            body {
                background: #454444 url('images/background.png');
                height: 100%;
                overflow: hidden;
            }
        </style>
    </head>

    <body>

        <link rel="stylesheet" type="text/css" href="/<%= cacheBuster('app/css/fileuploadfield.css') %>">
        <link rel="stylesheet" type="text/css" href="/<%= cacheBuster('app/css/tricheckbox.css') %>">
        <link rel="stylesheet" type="text/css" href="/<%= cacheBuster('app/css/fonts/ubuntu/stylesheet.css') %>">
        <link rel="stylesheet" type="text/css" href="/<%= cacheBuster('app/css/cms-all.css') %>">
        <link rel="stylesheet" type="text/css" href="/<%= cacheBuster('cms/data/theme/cms-theme.css') %>">

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
        <script type="text/javascript">
            /* Fake CMS Stuff */
            var CMSSERVER = CMSSERVER || {};
            var CMS = CMS || {};
            CMS.config = CMS.config || {};
            CMS.app = CMS.app || {};
            CMS.app.initialized = true;
            CMS.app.lang = 'en';
            CMS.config.debugMode = true;
        </script>

        <!-- CMS FormEdit Sources -->
        <% _.forEach(cmsFormEditSources, function (src) { %><script src="/<%= cacheBuster(src) %>" type="text/javascript"></script><% }); %>

        <!-- Fallback for non-js browsers -->
        <noscript>
            Error: JavaScript is required for this app to work.
        </noscript>

        <div id="form-editor" style="width: 100%; height: 100%;"></div>
        <script type="application/javascript">
            Ext.onReady(function () {
                //CMS.app.ErrorManager.init();
                Ext.QuickTips.init();
                var formEditor = new CMS.moduleEditor.FormEditor({
                    width: '100%',
                    height: '100%'
                });
                formEditor.render('form-editor');

                window.onbeforeunload = function(e) {
                    return 'Any unsaved changes will be lost!';
                };

            });
        </script>
    </body>
</html>
