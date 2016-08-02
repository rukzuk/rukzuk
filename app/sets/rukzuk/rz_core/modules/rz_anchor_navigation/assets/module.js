/* global define */
define(['jquery'], function ($) {
    'user strict';

    function getAnchorSelectorFromHref(href) {
        return '.anchor[id="' + href.replace(/#/g, '') + '"]';
    }

    function initScrollToHandler(unitId, scrollSpeed, scrollEasing, updateLocationHash) {
        $('#' + unitId + ' .anchorLink').each(function () {
            var $anchorLink = $(this);
            var anchorSel = getAnchorSelectorFromHref($anchorLink.attr('href'));

            // mark current anchor when scrolling
            $(anchorSel).parent().waypoint(function (direction) {
                $('body').find('.anchorItemCurrent').removeClass('anchorItemCurrent');
                $('body').find('.anchorLinkCurrent').removeClass('anchorLinkCurrent');

                var active_section;
                active_section = $(this);
                if (direction == 'up') {
                    active_section = $(this).waypoint('prev');
                }
                var anchorId = active_section.find('.anchor').first().attr('id');
                var anchorLinkCurrent = $('body').find('a[href="#' + anchorId + '"].anchorLink');
                anchorLinkCurrent.addClass('anchorLinkCurrent');
                anchorLinkCurrent.parent().addClass('anchorItemCurrent');
            }, { offset: '25%' });

            // scroll to anchor on click
            $anchorLink.click(function () {
                var anchorHref = $(this).attr('href');
                var $anchor = $(getAnchorSelectorFromHref(anchorHref));

                $.scrollTo($anchor, parseInt(scrollSpeed), {
                    easing: scrollEasing,
                    axis: 'y',
                    onAfter: function () {
                        if (updateLocationHash) {
                            window.location.hash = anchorHref;
                        }
                    }
                });
                return false;
            });
        });
    }

    $(function () {
        $('.rz_anchor_navigation').each(function () {
            var $el = $(this);
            var data = $el.data();
            var unitId = $el.attr('id');

            initScrollToHandler(unitId, data.scrollSpeed, data.scrollEasing, data.updateLocationHash);
        });
    });

    return {};
});
