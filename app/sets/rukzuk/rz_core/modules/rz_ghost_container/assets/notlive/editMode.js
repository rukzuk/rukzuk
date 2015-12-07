define(['rz_root/notlive/js/baseJsModule', 'CMS', 'jquery'], function (JsModule, CMS, $) {
    var toolbarHtml;

    var toolbarClick = function (event) {
        var $eventTarget = $(event.target);

        // abort if target is disabled
        if ($eventTarget.hasClass('disabled')) {
            return false;
        }

        var targetUnitId = $eventTarget.parents('.isModule').first().attr('id');
        if ($eventTarget.hasClass('actionMoveDown')) {
            CMS.moveDown(targetUnitId);
            return false;
        }
        if ($eventTarget.hasClass('actionMoveUp')) {
            CMS.moveUp(targetUnitId);
            return false;
        }
        if ($eventTarget.hasClass('actionRemove')) {
            CMS.remove(targetUnitId, true);
            return false;
        }
        if ($eventTarget.hasClass('actionInsert')) {
            CMS.openInsertWindow(targetUnitId, 1);
            return false;
        }
        if ($eventTarget.hasClass('actionDuplicate')) {
            CMS.duplicate(targetUnitId);
            return false;
        }
    };

    var initToolbar = function (unitId) {
        var unitData = CMS.get(unitId, false);
        var $unit = $('#' + unitId);
        var $toolbar = $(toolbarHtml);

        if (!unitData) {
            return;
        }

        $unit.children('.gcToolbar').remove();
        $toolbar.appendTo($unit);

        $toolbar.children('.unitName').html(unitData.name);
        if (!unitData.downmovable) {
            $toolbar.children('.actionMoveDown').addClass('disabled');
        }
        if (!unitData.upmovable) {
            $toolbar.children('.actionMoveUp').addClass('disabled');
        }

        $toolbar.click(toolbarClick);
    };

    var setToolbar = function (targetId, ghostContainerUnitId) {
        var $unit = $('#' + targetId);
        var nextIsModuleUnitId = $unit.parents('.isModule').first().attr('id');
        if (nextIsModuleUnitId == ghostContainerUnitId) {
            initToolbar(targetId);
        } else {
            targetId = nextIsModuleUnitId;
            if (targetId) {
                setToolbar(targetId, ghostContainerUnitId);
            }
        }
    };

    /**
     * show AddModuleButton in page mode when unit is a ghost container
     */
    var showAddModuleButton = function (unitId) {
        if ($('#' + unitId + ' > div > .isModule').length === 0) {
            var $emptyBox = $('<div><div class="RUKZUKemptyBox"><div class="RUKZUKmissingInputHint"></div></div></div>');
            $('#' + unitId).append($emptyBox);

            $('<button class="add" style="pointer-events: auto"></button>').click(function () {
                CMS.openInsertWindow(unitId, 0);
            }).appendTo($emptyBox.find('.RUKZUKmissingInputHint'));
        }
    };


    return JsModule.extend({
        initModule: function () {
            toolbarHtml = [
                '<div class="gcToolbar">',
                    '<div class="unitName"></div>',
                    '<div class="actionMoveUp" title="' + CMS.i18n(this.i18n('toolbar.actionMoveUp')) + '"></div>',
                    '<div class="actionMoveDown" title="' + CMS.i18n(this.i18n('toolbar.actionMoveDown')) + '"></div>',
                    '<div class="actionInsert" title="' + CMS.i18n(this.i18n('toolbar.actionInsert')) + '"></div>',
                    '<div class="actionDuplicate" title="' + CMS.i18n(this.i18n('toolbar.actionDuplicate')) + '"></div>',
                    '<div class="actionRemove" title="' + CMS.i18n(this.i18n('toolbar.actionRemove')) + '"></div>',
                '</div>'].join('');
        },

        initUnit: function (ghostContainerUnitId) {

            var $unit = $('#' + ghostContainerUnitId);

            // only in page mode
            if (CMS.get(ghostContainerUnitId, false).templateUnitId) {
                $unit.on('mouseover', function (e) {
                    var $eventTargetEl = $(e.target);
                    var targetId = $eventTargetEl.closest('.isModule').attr('id');

                    // end event handler if we are inside of the toolbar
                    if ($eventTargetEl.parent().hasClass('gcToolbar')
                        || $eventTargetEl.hasClass('gcToolbar')) {
                        return;
                    }

                    if (targetId) {
                        setToolbar(targetId, ghostContainerUnitId);
                    }
                });
                $unit.on('mouseout', function (e) {
                    // do not remove when mouseout into toolbar
                    if ($(e.relatedTarget).closest('.gcToolbar').length > 0) {
                        return false;
                    }
                    $('.gcToolbar').remove();
                });
                showAddModuleButton(ghostContainerUnitId);
            }
        }
    });
});
