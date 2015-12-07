<?php
    //fake upload time
    sleep(3);
?>
{
    "success": <?php echo $_REQUEST["name"] == "Selection.js" ? "false" : "true"; ?>,
    "data": {}
}