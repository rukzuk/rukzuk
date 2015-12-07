<?php if (isset($_COOKIE['username']) && isset($_COOKIE['token'])) { ?>
{
    "success": true,
    "data": {
        "userInfo": {
            "isAdmin": true,
            "username": "<?php echo $_COOKIE['username']; ?>"
        }
    }
}
<?php } else { ?>
{
    "success": false,
    "error": [{
        "code": "-1"
    }]
}
<?php } ?>
