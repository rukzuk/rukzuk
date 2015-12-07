{
    "success": true,
    "data":
<?php if ($_POST['format'] == 'json') { ?>
    [{
        "id": "PAGE-451212-FOO-PAGE",
        "name": "Page Foo",
        "dateTime": "28.07.2011 09:16:24",
        "userlogin": "sbcms@seitenbau.com",
        "action": "Page delete"
    }, {
        "id": "PAGE-451212-BAR-PAGE",
        "name": "Page Bar",
        "dateTime": "28.07.2011 09:16:24",
        "userlogin": "johndoes@seitenbau.com",
        "action": "Page move"
    }]
<?php } else { ?>
    [
      "PAGE-451212-FOO-PAGE|Page Foo|28.07.2011 16:34:42|sbcms@seitenbau.com|Page delete",
      "PAGE-451212-BAR-PAGE|Page Bar|28.07.2011 16:34:42|johndoes@seitenbau.com|Page move"
    ]
<?php } ?>
}
