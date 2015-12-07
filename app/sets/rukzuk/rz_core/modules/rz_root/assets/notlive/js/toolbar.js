/*
 * Toolbar which is shown after selecting a unit.
 * Provides editing actions for the selected unit.
 */

/* global $:false, CMS:false */

$(function() {

    var RUKZUK = {};

    $(document.body).append('<div id="RUKZUKtoolbar" class="cssReset" style="display: none;"><div class="arrow"><div></div></div>' +
		'<div class="header">' + 
			'<div class="name active"></div>' +
			'<div class="moduleIcon"><span><img src="" alt=""></span></div>' +
			'<div class="buttons">' +
                '<div class="action actionInsertStyle" title="' + CMS.i18n({'de':'Style einfügen', 'en': 'Insert Style'}) + '"><span></span></div>' +
			'</div>' +
		'</div>' + 
        '<div class="extensionUnitButtons"></div>' +
    '</div>');

    var $toolbar = $('#RUKZUKtoolbar');
    var $toolbarName = $toolbar.find('.name');
    var $toolbarActionInsertStyle = $toolbar.find('.actionInsertStyle');
    var $toolbarModuleIcon = $toolbar.find('.moduleIcon img').first();
    var $toolbarExtensionUnitButtons = $toolbar.find('.extensionUnitButtons');
    var offset = 10;

    $toolbarActionInsertStyle.click(function () {
        CMS.openInsertExtensionMenu();
        return false;
    });

    //listen on clicks on extension unit buttons
    $toolbarExtensionUnitButtons.on('click', 'div', function (event) {
        var unitId = $(event.currentTarget).data('unitId');
        if (unitId) {
            CMS.select(unitId);
            CMS.openFormPanel();
        }
        return false;
    });

    var selectUnitOnClick = function () {
        var unitId = $toolbar.data('unitId');
        if (unitId) {
            CMS.select(unitId);
            CMS.openFormPanel();
        }
        return false;
    };

    $toolbarName.click(selectUnitOnClick);
    $toolbarModuleIcon.click(selectUnitOnClick);

	

    var resetAdditionalButtons = function () {
        $toolbarExtensionUnitButtons.empty();
    };

    var addAdditionalButton = function (unitId, name, icon, isActive) {
        var active = isActive ? ' class="active"' : '';
        $toolbarExtensionUnitButtons.append('<div title="' + name + '" data-unit-id="' + unitId + '"' + active + '>' +
            '<img src="' + icon + '" alt="">' +
        '</div>');
    };

    // iterates over the parentUnits until a default unit is found
    var getNextParentDefaultUnit = function (unit) {
        var parentUnit = CMS.get(unit.parentUnitId, false);
        if (CMS.getModule(parentUnit.moduleId).extensionModule) {
            return getNextParentDefaultUnit(parentUnit);
        } else {
            return parentUnit;
        }
    };

    // iterates over the parentUnits until a default unit is found
    var getLastParentExtensionUnit = function (unit, defaultUnit) {
        if (unit.parentUnitId == defaultUnit.id) {
            return unit;
        } else {
            var parentUnit = CMS.get(unit.parentUnitId, false);
            return getLastParentExtensionUnit(parentUnit, defaultUnit);
        }
    };

    // Finds the right unit to show the toolbar for; if a unit is not editable, the next useful unit will be used.
    // If the unit is based on an extension module, the next parent default unit will be used.
    var findRightUnitAndShowToolbar = function (unit) {
        var showToolbar = false;
        if (unit.editable) {
            showToolbar = true;
        } else {
            if (unit.ghostContainer) {
                showToolbar = true;
            } else {
                var parentUnit = unit.parentUnitId && CMS.get(unit.parentUnitId, false);
                if (parentUnit && parentUnit.ghostContainer) {
                    showToolbar = true;
                }
            }
        }

        var extensionUnitId;
        // If the unit is based on an extension module, the next parent default unit will be used.
        if (CMS.getModule(unit.moduleId).extensionModule) {
            var parentDefaultUnit = getNextParentDefaultUnit(unit);
            extensionUnitId = getLastParentExtensionUnit(unit, parentDefaultUnit).id;
            unit = parentDefaultUnit;
        }

        if (showToolbar) {
            RUKZUK.toolbar.show(unit, extensionUnitId);
        } else {
            RUKZUK.toolbar.hide();
        }
    };



    RUKZUK.toolbar = RUKZUK.toolbar || (function () {
        var delayedFixPositionTimeout;

        var addExtensionUnitActions = function (unit, activeUnitId) {
            resetAdditionalButtons();

            $.each(unit.children, function (index, childUnitId) {
                var childUnit = CMS.get(childUnitId, false);
                if (childUnit.editable) {
                    var mData = CMS.getModule(childUnit.moduleId);
                    if (mData.extensionModule && mData.allowedChildModuleTypes === '') {
                        addAdditionalButton(childUnitId, childUnit.name, mData.icon, (childUnitId == activeUnitId));
                    }
                }
            });
        };

        return {
            show: function (unit, extensionUnitId) {
                //abort if toolbar is disabled in visualHelper state
                var visualHelpersState = CMS.getVisualHelpersState();
                if (visualHelpersState.toolbar === false) {
                    return;
                }

                if (typeof unit === 'string') {
                    unit = CMS.get(unit, false);
                }
                var unitId = unit.id;

                //abort if unit has no corresponding DOM element
                if (!document.getElementById(unitId)) {
                    return;
                }

                var unitData = CMS.get(unitId, false);
                var moduleIcon = CMS.getModule(unitData.moduleId).icon;
                $toolbarModuleIcon.attr('src', moduleIcon);
                $toolbar.data('unitId', unitId);

                $toolbarName.html(unitData.name);

				if (extensionUnitId) {
					$toolbarName.removeClass('active');
				} else {
					$toolbarName.addClass('active');
				}
				
                //add the icons of editable extension units
                addExtensionUnitActions(unitData, extensionUnitId);

                this.fixPosition();
                $toolbar.show();
            },

            hide: function () {
                $toolbar.hide();
            },

            fixPosition: function () {
                var $unit = $('#' + $toolbar.data('unitId'));

                if (!$unit.length) {
                    return;
                }

                //calculate the right toolbar position
                var unitOffset = $unit.offset();

                $toolbar.removeClass('left right top bottom');

                var posTop = unitOffset.top - ($toolbar.outerHeight() + offset);
                var posLeft = unitOffset.left + ($unit.outerWidth() / 2) - ($toolbar.outerWidth() / 2);

                //decide where the toolbar should be positioned so it's visible in the viewport
                if (posTop < $(window).scrollTop()) {
                    posTop = unitOffset.top + $unit.outerHeight() + offset;

                    if (posTop > window.innerHeight + $(window).scrollTop()) {
                        posTop = $(window).scrollTop() + (window.innerHeight / 2);

                        posLeft = unitOffset.left + $unit.outerWidth() + offset;

                        $toolbar.addClass('left');

                        /*
                        if (posLeft + $toolbar.outerWidth() > $(window).scrollLeft() + window.innerWidth) {
                            posLeft = unitOffset.left - $toolbar.outerWidth() - offset;
                            $toolbar.addClass('right');
                        } else {
                            $toolbar.addClass('left');
                        }
                        */

                    } else {
                        $toolbar.addClass('bottom');
                    }
                } else {
                    $toolbar.addClass('top');
                }

                $toolbar.css({
                    top: posTop,
                    left: posLeft
                });
            },

            delayedFixPosition: function (delay) {
                delay = delay || 200;
                if ($toolbar.is(':visible')) {
                    $toolbar.hide();

                    window.clearTimeout(delayedFixPositionTimeout);
                    delayedFixPositionTimeout = window.setTimeout(function () {
                        RUKZUK.toolbar.fixPosition();
                        $toolbar.show();
                    }, delay);
                }
            }

        };
    })();


    // enable toolbar only in template mode
    if ($('body').hasClass('isTemplate')) {
        CMS.on('unitSelect', function (config) {
            findRightUnitAndShowToolbar(CMS.get(config.unitId, false));
        });

        CMS.on('unitDeselect', function () {
            RUKZUK.toolbar.hide();
        });

        //show toolbar after reload if toolbar was visible before
        CMS.on('afterRenderPage', function () {
            var unit = CMS.getSelected(false);

            findRightUnitAndShowToolbar(unit);

            // fix the position of the toolbar again after some time, e.g. reflow caused by long loading images
            window.setTimeout(function () {
                RUKZUK.toolbar.fixPosition();
            }, 500);
        });

        //fix toolbar position after unit rendering
        CMS.on('afterRenderUnit', function () {
            RUKZUK.toolbar.delayedFixPosition();
        });

        //fix the position after window.resize
        $(window).on('resize', function () {
            RUKZUK.toolbar.delayedFixPosition();
        });

        //fix position after CSS changes, e.g. resize in rz_grid, rz_width_height, ...
        $('.generatedStyles').on('DOMSubtreeModified', function () {
            RUKZUK.toolbar.delayedFixPosition(1000);
        });

        //show/hide the toolbar on changeVisualHelpers
        CMS.on('visualHelpersStateChange', function (config) {
            if (config.toolbar === true) {
                var unit = CMS.getSelected(false);
                if (unit) {
                    findRightUnitAndShowToolbar(unit);
                }
            } else {
                RUKZUK.toolbar.hide();
            }
        });
    }

});
