define(['jquery'], function ($) {
    var moduleSelector = '.rz_container_collapsible';
    var handleSelector = '.collapsibleHandle';

    /**
     * initialize a collapsible
     * @param el
     */
    var initCollapsible = function (el) {
        el.find(handleSelector).first().find('span').on('click', collapseHandler);

        // check if collapsible should close when clicking on a link inside the content

        if (el.find(handleSelector).first().data('closeonlinkclick') == 1) {
            el.find('.collapsibleContent').first().find('a').on('click', collapseHandler);
        }
    };

    var initAllCollapsiblesInDom = function () {
        $(moduleSelector).each(function () {
            initCollapsible($(this));
        });
    };

    var initAllCollapsiblesInDomInLayoutInclude = function () {
        $(moduleSelector).each(function () {
            if($(this).parents('.rz_include').length > 0) {
                initCollapsible($(this));
            }
        });
    };

    var collapseHandler = function () {
        var $collapsible = $(this).parents(moduleSelector).first();
        changeState($collapsible);
    };

    var changeState = function ($collapsible) {
        var $handle = $collapsible.children(handleSelector);
        var animationDuration = $handle.data('duration');
        var $collapsibleContent = $collapsible.children('.collapsibleContent');

        // show/hide content
        if ($handle.css('display') != 'none') {
            $collapsibleContent.animate({
                height: 'toggle'
            }, animationDuration, 'swing', function () {
                $handle.toggleClass('collapsed');
                $collapsible.toggleClass('collapsed');
                $collapsibleContent.css('display', '');
            });
        }
    };

    return {
        initCollapsible: initCollapsible,
        initAllCollapsiblesInDom: initAllCollapsiblesInDom,
        initAllCollapsiblesInDomInLayoutInclude: initAllCollapsiblesInDomInLayoutInclude
    };
});