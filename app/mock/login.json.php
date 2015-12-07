<?php if (isset($_POST['params'])) {
    $json = json_decode($_POST['params'], TRUE);
    $username = $json['username'];
    $password = $json['password'];
}
if (isset($username) && isset($password)) {
    $expires = time() + 60 * 60 * 24; // seconds
    setcookie('username', $username, $expires);
    setcookie('token', "dummy-token", $expires);
?>
{
    "success": true,
    "data": {
        "userInfo": {
            "isAdmin": true,
            "username": "<?php echo $username; ?>"
        }
    }
}
<?php } else { ?>
{
    "success": false,
    "error": [{
        "code": "-2"
    }]
}
<?php } ?>
