DynCSS.defineModule('rz_anchor_navigation', function (api, v, context) {
    var space = v.cssSpace || 0;
    var listCss = {
        textAlign: v.cssAlign || 'left'
    };
    var listItemCss;

    if (v.cssDistribution == 'horizontal') {
        listItemCss = {
            display: 'inline-block',
            paddingLeft: space,
            paddingTop: 0
        };
    } else if (v.cssDistribution  == 'horizontal_full') {
        listItemCss = {
            display: 'table-cell',
            paddingLeft: space,
            paddingTop: 0
        };
        listCss.width = '100%';
        listCss.display = 'table';
    } else {
        listItemCss = {
            display: 'block',
            paddingLeft: 0,
            paddingTop: space
        };
    }

	var offSet = {
        content: "'" + parseInt(v.offSet) + "'"
    };

    return {
        '> ul': listCss,
        '> ul > li': listItemCss,
        '&': offSet
    };
});
