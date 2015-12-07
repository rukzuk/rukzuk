Ext.ns('CMS');

/**
* @class CMS.TemplateSnippetGrid
* @extends CMS.ModuleGrid
*/
CMS.TemplateSnippetGrid = Ext.extend(CMS.ModuleGrid, {

    recordType: CMS.data.TemplateSnippetRecord,

    initComponent: function () {
        CMS.TemplateSnippetGrid.superclass.initComponent.apply(this, arguments);

        this.on('afterrender', function () {
            if (this.enableDragDrop || this.enableDrag) {

                // overwrite empty onBeforeDrag method to abort drag if modules are not available
                this.getView().dragZone.onBeforeDrag = function (e) {
                    if (!e.selections[0] || e.selections[0].data.content.length != 1) {
                        // Can't handle more than one units on first level in templateSnippet
                        return;
                    }

                    var firstUnit = e.selections[0].data.content[0];

                    // check if the modules of all units in the templateSnippet are available
                    if (!e.grid.validateModules(firstUnit)) {
                        CMS.Message.info(CMS.i18n('Im Snippet befinden sich Module welche in dieser Website nicht vorhanden sind. Das Snippet kann daher nicht eingefügt werden.'));
                        return false;
                    }
                };
            }
        }, this);
    },

    /**
     * @private
     * Iterates over all units in the templateSnippet and checks if the
     * corresponding modules are available in this website.
     * @param {Object} unit
     * @return {Boolean} Whether all modules are available or not
     */
    validateModules: function (unit) {
        var moduleStore = CMS.data.StoreManager.get('module', this.websiteId);

        if (!moduleStore.getById(unit.moduleId)) {
            return false;
        } else {
            var modulesAvailable = true;
            Ext.each(unit.children, function (childUnit) {
                if (!this.validateModules(childUnit)) {
                    modulesAvailable = false;
                    return false;
                }
            }, this);

            return modulesAvailable;
        }
    }

});

Ext.reg('CMStemplatesnippetgrid', CMS.TemplateSnippetGrid);
