Ext.ns('CMS.form');

/**
 * @class CMS.form.TemplateChooser
 * @extends Ext.form.Chooser
 * A form element for selecting a template of the current website
 */
CMS.form.TemplateChooser = Ext.extend(CMS.form.Chooser, {

    /**
     * @cfg {Boolean} showCurrentTemplate
     * <tt>true</tt> to make this component show the current template
     * Defaults to <tt>false</tt>
     */
    showCurrentTemplate: false,

    initComponent: function () {
        try {
            this.originalStore = CMS.data.StoreManager.get('template', this.websiteId);
        } catch (e) {
            console.warn('[TemplateChooser] could not get template data', e);
            this.originalStore = new Ext.data.JsonStore({
                id: 0,
                fields: ['id', 'name'],
                data: []
            });
        }

        CMS.form.TemplateChooser.superclass.initComponent.apply(this, arguments);
    },

    /**
     * @private
     * Will get called after initialization and every time the originalStore fires the datachanged event.
     * Overwritten for i18n
     */
    datachangedHandler: function () {
        var records = this.originalStore.getRange();
        var currentTemplateId = this.getCurrentTemplateId();
        var showCurrentTemplate = this.showCurrentTemplate;

        var data = [];
        Ext.each(records, function (record) {
            if (showCurrentTemplate == true || record.get('id') != currentTemplateId) {
                data.push([record.get('id'), CMS.translateInput(record.get('name'))]);
            }
        });

        this.syncComboBoxStore(data);
    },

    /**
     * @private
     * Returns the current template id
     */
    getCurrentTemplateId: function () {
        var iframeWorkbenchPanel = this.findParentByType('CMSiframeworkbenchpanel');
        if (iframeWorkbenchPanel) {
            if (iframeWorkbenchPanel.mode == 'template' || iframeWorkbenchPanel.mode == 'page') {
                return iframeWorkbenchPanel.getTemplateId();
            }
        }
        return null;
    }
});

Ext.reg('CMStemplatechooser', CMS.form.TemplateChooser);
