define([
    'jquery',
    'CMS',
    'rz_root/notlive/js/baseJsModule',
    'rz_root/notlive/js/cssHelper',
    'rz_root/notlive/js/breakpointHelper'
], function ($, CMS, JsModule, cssHelper, bpHelper) {

	// save current child widths to unit field
	function saveChildWidths(unitId, syncOnly) {
		if ($('#' + unitId + ' > div >.isModule').length > 0) {
			var activeResolutionId = CMS.getCurrentResolution();
			var childWidth = CMS.get(unitId).formValues.cssChildWidth.value;
			if (!childWidth[activeResolutionId] && syncOnly) {
				return;
			}
			
			var childWidths = [];
			var i=0;
			$('#' + unitId + ' > div >.isModule').each(function() {
				childWidths[i] = $(this).css('content').replace(/[\"\']/g, '');
				i++;
			});

			childWidth[activeResolutionId] = childWidths.join(" ");
			CMS.set(unitId, 'cssChildWidth', childWidth);
			cssHelper.refreshCSS(unitId);   
		}
	}	
	
	// inject or update the width display
	function updateShowModuleWidth(unitId) {
        if (CMS.get(unitId).formValues.cssChildWidth.editable) {

            $('#' + unitId + ' > div > .isModule').each(function() {
                var currentUnit = $(this);
                var currentWidth = currentUnit.css('content').replace(/[\"\']/g, "");

                if (currentUnit.children('.showModuleWidth').length > 0) {
                    currentUnit.children('.showModuleWidth').children('.display').html(currentWidth);
                } else {

                    var showModuleWidth = $('<div class="showModuleWidth"><div class="display">' + currentWidth + '</div><div class="widthOptionDropdown"><div class="percent">%</div><div class="pixel">px</div><div class="auto">auto</div></div></div>').appendTo(currentUnit);

                    var widthOptionDropdown = showModuleWidth.children('.widthOptionDropdown');

                    showModuleWidth.on('mousedown', function(ev) {
                        ev.stopImmediatePropagation();
                        adjustDropDown(currentUnit.attr('id'));
                        widthOptionDropdown.toggle();
                    });

                    showModuleWidth.on('mouseleave', function(ev) {
                        widthOptionDropdown.hide();
                    });

                    showModuleWidth.children().children().on('mousedown', function(ev) {
                        ev.stopImmediatePropagation();
                        widthOptionDropdown.hide();

                        var currentWidth = currentUnit.css('content').replace(/[\"\']/g, "");
                        var currentWidthPx = currentUnit.width();
                        var currentWidthPercent = Math.round(currentUnit.width()/currentUnit.parent().width()*100);

                        if ($(this).hasClass('percent')) {
                            currentWidth = '"' + currentWidthPercent + '%"';
                        }
                        if ($(this).hasClass('pixel')) {
                            currentWidth = '"' + currentWidthPx + 'px"';
                        }
                        if ($(this).hasClass('auto')) {
                            currentWidth = '"auto"';
                        }

                        currentUnit.css('content', currentWidth);
                        saveChildWidths(currentUnit.parent().parent().attr('id'));
                        currentUnit.css('content', '');
                        updateShowModuleWidth(unitId);

                    });
                }
            });
        }
	}
	
	function adjustDropDown(unitId) {
		var currentUnit = $('#' + unitId);
		var currentUnitData = CMS.get(unitId, false);
		
		var showModuleWidth = currentUnit.find('.showModuleWidth').first();
		showModuleWidth.children().children().css('display', 'block');
		var currentWidth = currentUnit.css('content').replace(/[\"\']/g, "");

		if (currentWidth.match(/px/)) {
			showModuleWidth.find('.pixel').first().css('display', 'none');
		}
		if (currentWidth.match(/\%/)) {
			showModuleWidth.find('.percent').first().css('display', 'none');
		}	
		if (currentWidth.match(/auto/)) {
			showModuleWidth.find('.auto').first().css('display', 'none');
		}
		if ((currentUnitData.moduleId != 'rz_textfield') && (currentUnitData.moduleId != 'rz_headline') && (currentUnitData.moduleId != 'rz_link')) {
			showModuleWidth.find('.auto').first().css('display', 'none');
		}
	}
	
	
	// inject and initialize the drag handler
	function initializeDragHandlers(unitId) {

        if (CMS.get(unitId).formValues.cssChildWidth.editable) {

            var $uiBlocker = $('<div class="uiBlocker"></div>');
            $('#' + unitId + ' > div > .isModule').each(function() {

                // check if resize handler exists
                if ($(this).children('.resizer').length === 0) {
                    var dragHandler = $('<div class="resizer"></div>').appendTo($(this));
                    var currentUnit = $(this);


                    dragHandler.on('mousedown', function() {
                        // show resize handler
                        $(this).css('display', 'block');
                        $(this).next().css('display', 'block');
                    });

                    dragHandler.drag('start', function (ev, dd) {
                        var startWidth = currentUnit.css('content').replace(/[\"\']/g, "");
                        if (startWidth == 'auto') {
                            $(this).css('display', '');
                            $(this).next().css('display', '');
                            return false;
                        } else if (startWidth.match(/\%/)) {
                            dd.unit = '%';
                        } else {
                            dd.unit = 'px';
                        }

                        // calculate factors (used to convert to percent values)
                        dd.factor = (200 / currentUnit.parent().width());
                        dd.leftFactor = ((dd.unit == '%') ? dd.factor : 1);
                        dd.startChildWidth = parseFloat(startWidth);

                        // inject uiblocker
                        $('body').append($uiBlocker);


                    }).drag(function (ev, dd) {
                        var currentChildWidth = Math.round(dd.startChildWidth + (dd.deltaX * dd.leftFactor));

                        if (currentChildWidth < 0) {
                            currentChildWidth = 0;
                        }
                        if ((dd.unit == '%') && (currentChildWidth > 100)) {
                            currentChildWidth = 100;
                        }

                        var newWidth = currentChildWidth + dd.unit;
                        currentUnit.css('content', '"' + newWidth + '"');

                        updateShowModuleWidth(unitId);

                        saveChildWidths(unitId);
                        currentUnit.css('content', '');

                    }).drag('end', function (ev, dd) {

                        $uiBlocker.detach();
                        $(this).css('display', '');
                        $(this).next().css('display', '');
                    });
                }
            });
        }
	}

    return JsModule.extend({

        /** @protected */
        onFormValueChange: function (config) {

			
        },

        /** @protected */
        onUnitSelect: function (config) {

        },

        /** @protected */
        onUnitDeselect: function (config) {
			
        },

		/** @protected */
        initModule: function (config) {
			CMS.on('beforeRemoveUnit', function(unitId) {
				var unitData = CMS.get(unitId);
				if (unitData.parentModuleId == 'rz_flexbox') {
					var parentUnitData = CMS.get(unitData.parentUnitId, true);
					var cssChildWidth = parentUnitData.formValues.cssChildWidth.value;
					var unitPosition = $('#' + unitData.parentUnitId + ' > div > .isModule').index($('#' + unitId));
					for (var bp in cssChildWidth) {
						if (bp != "type") {
							var childWidthString = cssChildWidth[bp].split(" ");
							delete childWidthString[unitPosition];
							cssChildWidth[bp] = childWidthString.join(" ");
						}
					}
					CMS.set(parentUnitData.id, 'cssChildWidth', cssChildWidth);
					cssHelper.refreshCSS(parentUnitData.id);
					updateShowModuleWidth(parentUnitData.id);					
				}
				
			});
			CMS.on('beforeMoveUnit', function(config) {
				var parentUnitData = CMS.get(config.parentUnitId, true);
				if (parentUnitData.moduleId == 'rz_flexbox') {
					var unitId = config.unitId;
					var oldPosition = $('#' + config.parentUnitId + ' > div > .isModule').index($('#' + unitId));

					// move in between flexbox
					if (oldPosition >  -1) {
						var cssChildWidth = parentUnitData.formValues.cssChildWidth.value;
						for (var bp in cssChildWidth) {
							if (bp != "type") {
								var childWidthString = cssChildWidth[bp].split(" ");
                                var newPosition = config.index;
                                var temp = childWidthString.splice(oldPosition, 1);
                                childWidthString.splice(newPosition, 0, temp[0]);
								cssChildWidth[bp] = childWidthString.join(" ");
							}
						}
						CMS.set(parentUnitData.id, 'cssChildWidth', cssChildWidth);
						cssHelper.refreshCSS(parentUnitData.id);
						updateShowModuleWidth(parentUnitData.id);					
					}
				}
				
			});
			CMS.on('beforeInsertUnit', function(config) {
				var parentUnitData = CMS.get(config.parentUnitId, true);
				if (parentUnitData.moduleId == 'rz_flexbox') {
					var index = parentUnitData.children.indexOf(config.unitId);
					if (index >  -1) {
						var cssChildWidth = parentUnitData.formValues.cssChildWidth.value;
						for (var bp in cssChildWidth) {
							if (bp != "type") {
								var childWidthString = cssChildWidth[bp].split(" ");
								childWidthString.splice(index, 0, childWidthString[Math.max(index-1, 0)]);
								cssChildWidth[bp] = childWidthString.join(" ");
							}
						}
						CMS.set(parentUnitData.id, 'cssChildWidth', cssChildWidth);
						cssHelper.refreshCSS(parentUnitData.id);
						updateShowModuleWidth(parentUnitData.id);					
					}
				}
			});
        },

        /** @protected */
        initUnit: function (unitId) {

            saveChildWidths(unitId, true);
			initializeDragHandlers(unitId);
			updateShowModuleWidth(unitId);
			CMS.on('resolutionChange', function() {
				updateShowModuleWidth(unitId);
			});
			
			CMS.on('formValueChange', function(config) {
				if (config.key == 'cssChildWidth') {
					saveChildWidths(unitId, true);
					updateShowModuleWidth(unitId);
				}
			});
			CMS.on('afterRenderUnit', function(childUnitId) {
				if ($('#'+childUnitId).parent().parent().attr('id') == unitId) {
					initializeDragHandlers(unitId);
					updateShowModuleWidth(unitId);
				}
			});
        },

        /** @protected */
        onResolutionChange: function (unitId) {

        },

        /** @protected */
        onVisualHelpersChange: function (cfg) {

        }
    });
});
