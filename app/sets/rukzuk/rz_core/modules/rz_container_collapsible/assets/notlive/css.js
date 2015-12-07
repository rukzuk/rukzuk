DynCSS.defineModule('rz_container_collapsible', function (api, v, ctx) {

    var cssHandleDisplay = {};
    var cssContentDisplay = {};

    if (v.cssEnableContainer) {
        cssHandleDisplay.display = 'block';
        cssContentDisplay.display = 'none';
    } else {
        cssHandleDisplay.display = 'none';
        cssContentDisplay.display = 'block';
    }

    var cssHandlePosition = {};
    if (v.cssHorizontalHandlePosition == 'left') {
        cssHandlePosition.float = 'left';
    } else {
        cssHandlePosition.float = 'right';
    }

    return {
        '& > .collapsibleHandle': cssHandleDisplay,
        '& > .collapsibleContent': cssContentDisplay,
        '&:not(.collapsed) > .collapsibleContent': {
            display: 'block'
        },
        '& > .collapsibleHandle span': cssHandlePosition
    };
});