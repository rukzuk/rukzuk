/*globals URLify */
define(['jquery', 'CMS', 'rz_root/notlive/js/baseJsModule'], function ($, CMS, JsModule) {

    /**
     * Updates the name of the unit with the current anchor name
     * @param {String} unitId The id of the unit to update
     * @private
     */
    var updateUnitName = function (unitId) {
        var unit = CMS.get(unitId);
        var anchorName = unit.formValues.anchorName.value;
        CMS.setInfo(unitId, 'anchorName', anchorName);
    };

    /**
     * Updates the anchorId field of the unit with the urlify anchor name
     * @param {String} unitId The id of the unit to update
     * @private
     */
    var updateAnchorId = function (unitId) {
        var unit = CMS.get(unitId);
        var anchorName = unit.formValues.anchorName.value;
        /*jshint newcap: false */
        var anchorId = URLify(anchorName, 256);
        if (!anchorId) {
            anchorId = 'to-'+unitId;
        }
        CMS.set(unitId, 'anchorId', '#' + anchorId);
        $('#' + unitId + ' .anchor').attr('id', anchorId);
        $('#' + unitId + ' .anchor > div').text('#' + anchorId);
    };

    /**
     * Click handler for the anchor name marker; Tells the rukzuk client to
     * open the form panel
     * @private
     */
    var openFormPanel = function () {
        CMS.openFormPanel('anchorName', true);
    };

    return JsModule.extend({
        initUnit: function (unitId) {
            updateUnitName(unitId);

            // add click handler on anchorName marker
            // This is a workaround to make it easy to change the anchor name.
            // The better way would be to use inline-editing. Unfortunately the RTE
            // does not trigger a "formValuesChanged" event.
            $('#' + unitId + ' .anchor > div').on('click', function () {
                window.setTimeout(openFormPanel, 0);
            });
        },

        initModule: function () {
            // initial insert
            var eventFilter = {moduleId: this.moduleId};
            CMS.processInsertedUnits(eventFilter, function (cfg) {
                updateAnchorId(cfg.unitId);
            });
        },

        onFormValueChange: function (cfg) {
            if (cfg.key === 'anchorName') {
                updateUnitName(cfg.unitId);
                updateAnchorId(cfg.unitId);
            }
        }
    });
});
