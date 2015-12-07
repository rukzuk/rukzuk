<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="author" content="rukzuk AG">
    <meta name="audience" content="all">
    <meta name="robots" content="noindex,nofollow">
    <base href="<?php echo !empty($base_path) ? $base_path : '/'; ?>">
    <title>Web Design Platform - <?php echo $title; ?></title>
    <link href="css/login.css" rel="stylesheet">
    <link href="cms/data/theme/login-theme.css" rel="stylesheet">
    <style>
        .bigwarning, #form, #login-panel, .logo, .message { display: block; }
    </style>
</head>
<body class="background">

<div class="wrapper">
    <div class="shadowBox">
        <div class="login_logo"></div>

        <?php if(empty($error)) { ?>
            <!-- Forms -->
            <div id="form">
                <div class="messagebar"><span class="message error"><?php echo $login_result; ?></span></div>
                <!-- Login Form -->
                <div class="panel" id="login-panel">
                    <form method="POST" action="<?php echo $login_action; ?>" id="login_form" name="login_form" autocomplete="on">
                        <input type="text" name="username" id="username" placeholder="<?php echo $lang['emailPlaceholder']; ?>">
                        <?php echo $hidden_fields; ?>

                        <div class="pwsubmit twocol topspace">
                            <div class="pwwarp"><input type="password" name="password" id="password" placeholder="<?php echo $lang['passwordPlaceholder']; ?>"></div>
                            <div class="btnwrap"><input type="submit" name="login" id="login" value="<?php echo $lang['loginButton']; ?>" class="loginButton"></div>
                        </div>
                    </form>
                </div>
            </div>
            <script>
                try { document.forms[0].elements[0].focus(); } catch(e) { }
            </script>
        <?php } else { ?>
            <!-- Error -->
            <div class="bigwarning">
                <p class="bigwarning-text"><i class="bigico warning"></i><?php echo $error?></p>
            </div>
        <?php } ?>
    </div>
</div>
</body>
</html>