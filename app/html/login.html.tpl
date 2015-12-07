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

</head>
<body class="animate">
<div class="wrapper">
    <div class="shadowBox">
        <div class="login_logo">
        </div>

        <!-- Forms -->
        <div id="form">
            <div class="messagebar">
                <span class="message error msgServerUnreachable"><%= i18n('login.msgServerUnreachable') %></span>
                <span class="message error msgEmailOrPwIncorrect"><%= i18n('login.msgEmailOrPwIncorrect') %></span>
                <span class="message msgCredentialsRequested"><%= i18n('login.msgCredentialsRequested') %></span>
                <span class="message error msgEmailNotFound"><%= i18n('login.msgEmailNotFound') %></span>
                <span class="message msgPwInvalid"><%= i18n('login.msgPwInvalid') %></span>
                <span class="message msgExpired"><%= i18n('login.msgExpired') %></span>
                <span class="message msgPwMismatch"><%= i18n('login.msgPwMismatch') %></span>
                <span class="message msgPasswordChanged"><%= i18n('login.msgPasswordChanged') %></span>
                <span class="message error fromServer"></span>
            </div>
            <!-- Login Form -->
            <div class="panel" id="login-panel">
                <form method="POST" action="blank.htm" id="login_form" name="login_form" autocomplete="on">
                    <input type="text" name="username" id="username" placeholder="<%= i18n('login.emailPlaceholder') %>">

                    <div class="pwsubmit twocol topspace">
                        <div class="pwwarp"><input type="password" name="password" id="password" placeholder="<%= i18n('login.passwordPlaceholder') %>"></div>
                        <div class="btnwrap"><input type="submit" name="login" id="login" value="<%= i18n('login.loginButton') %>" class="loginButton"></div>
                    </div>
                    <div class="tools twocol topspace">
                        <div class="fullscreen"><input type="checkbox" id="fullscreen"><label for="fullscreen"><span></span><%= i18n('login.fullscreen') %></label></div>
                        <div class="lostpw"><a href="#" id="lostpwlink"><%= i18n('login.lostPw') %></a></div>
                    </div>
                </form>
            </div>

            <!-- Lost PW -->
            <div class="panel" id="lostpw-panel">
                <input type="text" name="lostpw_email" id="lostpw_email" placeholder="<%= i18n('login.emailPlaceholder') %>">
                <input type="button" name="lostpwBtn" id="lostpwBtn" value="<%= i18n('login.lostPwButton') %>" class="loginButton topspace">
                <div class="lostpwback topspace"><a href="#" id="lostpwback"><%= i18n('login.backButton') %></a></div>
            </div>

            <div class="panel" id="optin-panel">
                <input type="password" name="optin_pw" id="optin_pw" placeholder="<%= i18n('login.newPasswordPlaceholder') %>">
                <div class="twocol topspace">
                    <div class="pwwarp"><input type="password" name="optin_pw2" id="optin_pw2" placeholder="<%= i18n('login.newPasswordRepeatPlaceholder') %>"></div>
                    <div class="btnwrap"><input type="button" id="optinBtn" value="<%= i18n('login.newPasswordSave') %>" class="loginButton"></div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Scripts -->
<script src="/<%= cacheBuster('app/login/jquery-2.1.4.min.js') %>" type="text/javascript"></script>
<% _.forEach(loginSources, function (src) { %><script src="/<%= cacheBuster(src) %>" type="text/javascript"></script><% }); %>

</body>
</html>
