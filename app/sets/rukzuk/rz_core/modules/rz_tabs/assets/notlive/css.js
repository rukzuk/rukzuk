DynCSS.defineModule('rz_tabs', function (api, v) {
    var css = {};

    var numberOfTabs = v['tabTitles'].split('\n').length;

    switch (v['cssMode']) {
        case 'tabs':
            css['& > label'] = {
                display: 'inline-block'
            };
            css['& > div.tabsWrapper > section'] = {
                display: 'none'
            };
            css['& > div.tabsWrapper > section > label'] = {
                display: 'none'
            };
            css['& > div.tabsWrapper > section > div'] = {
                display: 'block'
            };

            for (var i = 1; i <= numberOfTabs; i++) {
                css['& > input:nth-of-type(' + i + '):checked ~ .tabsWrapper > section:nth-of-type(' + i + ')'] = {
                    display: 'block'
                };
            }

            break;

        case 'accordion':
            css['& > label'] = {
                display: 'none'
            };
            css['& > div.tabsWrapper > section'] = {
                display: 'block'
            };
            css['& > div.tabsWrapper > section > label'] = {
                display: 'block'
            };

            css['& > div.tabsWrapper > section > div'] = {
                display: 'none'
            };

            for (var i = 1; i <= numberOfTabs; i++) {
                css['& > input:nth-of-type(' + i + '):checked ~ .tabsWrapper > section:nth-of-type(' + i + ') > div'] = {
                    display: 'block'
                };
            }

            break;

        default:
            css['& > label'] = {
                display: 'none'
            };
            css['& > div.tabsWrapper > section'] = {
                display: 'block'
            };
            css['& > div.tabsWrapper > section > label'] = {
                display: 'block',
                cursor: 'text'
            };
            css['& > div.tabsWrapper > section > div'] = {
                display: 'block'
            };
            break;
    }

    return css;
});
