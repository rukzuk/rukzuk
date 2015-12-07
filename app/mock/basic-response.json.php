<?php if (!isset($_GET['fail'])) { ?>
{
    "success": true,
    "data": {}
}
<?php } else { ?>
{
    "success": false,
    "error": [{
        "code": "-1"
    }]
}
<?php } ?>
