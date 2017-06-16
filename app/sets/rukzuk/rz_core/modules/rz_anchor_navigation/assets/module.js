/* global define */
define(['jquery'], function ($) {
    'user strict';

    function getAnchorSelectorFromHref(href) {
        return '.anchor[id="' + href.replace(/#/g, '') + '"]';
    }

    function initScrollToHandler(unitId, scrollSpeed, scrollEasing, updateLocationHash) {
		var anchorListHtml = '';
		$('body').find('.anchor').each(function(){
			anchorListHtml += '<li class="anchorItem"><a class="anchorLink" href="#' + $(this).attr('id') + '">' + $(this).attr('data-anchorname') + '</a></li>';
		});
		$('#' + unitId + ' .anchorList').html(anchorListHtml);
		
        $('#' + unitId + ' .anchorLink').each(function () {
            var $anchorLink = $(this);
            var anchorSel = getAnchorSelectorFromHref($anchorLink.attr('href'));
			var offSet = parseInt($('#' + unitId).css('content').substr(1));
			
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
            }, { offset: (offSet + 1) + 'px' });

			
			
            // scroll to anchor on click
            $anchorLink.click(function () {
                var anchorHref = $(this).attr('href');
                var $anchor = $(getAnchorSelectorFromHref(anchorHref));

                $.scrollTo($anchor, scrollSpeed, {
                    easing: scrollEasing,
                    axis: 'y',
                    offset: (offSet * -1),
                    onAfter: function () {
                        if (updateLocationHash) {
                            //window.location.hash = anchorHref;
                        }
                    }
                });
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
