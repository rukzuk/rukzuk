define(['jquery'], function ($) {
    var moduleSelector = '.rz_container_collapsible';
    var handleSelector = '.collapsibleHandle';

    /**
     * initialize a collapsible
     * @param unitId
     */
    var initCollapsible = function (unitId) {
        $('#' + unitId + ' > ' + handleSelector + ' span').on('click', collapseHandler);

        // check if collapsible should close when clicking on a link inside the content
        if ($('#' + unitId + ' > ' + handleSelector).data('closeonlinkclick') == 1) {
            $('#' + unitId + ' > .collapsibleContent a').on('click', collapseHandler);
        }
    };

    var initAllCollapsiblesInDom = function () {
        $(moduleSelector).each(function () {
            initCollapsible($(this).attr('id'));
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
        initAllCollapsiblesInDom: initAllCollapsiblesInDom
    };
});