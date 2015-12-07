Ext.ns('CMS.home');

/**
 * Provides ability to change the template of an existing page
 * @class CMS.home.PageTemplateComboBox
 * @extends CMS.form.AddRecordComboBox
 */
CMS.home.PageTemplateComboBox = Ext.extend(CMS.form.AddRecordComboBox, {

    /**
     * The id of the template on which
     * the current page is based upon
     * @property templateId
     * @type String
     */
    templateId: null,

    /**
     * @property storeLoaded
     * @type Boolean
     * @private
     */
    storeLoaded: false,

    forceSelection: true,
    editable: false,

    initComponent: function () {
        var config = {
            emptyText: CMS.i18n('Template wählen'),
            storeType: 'template',
            store: []
        };
        Ext.apply(this, config);
        CMS.home.PageTemplateComboBox.superclass.initComponent.apply(this, arguments);
        // These can't be set in the above defined config object because
        // the store of this combo box is set to an empty array in the
        // initComponent of  CMS.form.AddRecordComboBox which overrides
        // these fields (See initComponent of Ext.form.ComboBox line 467
        // for details).
        this.valueField = 'id';
        this.displayField = 'name';
    },

    /**
     * Overwrites the original setValue method. This is necessary because the
     * store has to be loaded before setValue can be called. But since setValue
     * is automatically called by the BasicForm.setValues method before the store
     * is ready, we have to check the state of the store which unfortunately is
     * rather ugly.
     */
    setValue: function (value) {
        CMS.home.PageTemplateComboBox.superclass.setValue.call(this, value);
        if (!this.storeLoaded) {
            this.templateId = value;
            if (value) {
                this.initStore();
            }
        }
    },

    /**
     * @private
     * Calls the setValue method of the combo box after the store has been loaded
     */
    storeLoadedHandler: function (store, records) {
        this.storeLoaded = true;

        if (this.templateId) {
            this.setValue(this.templateId);
        } else if (records && records.length > 0) {
            this.setValue(records[0].id);
        }
    },

    /**
     * Stores the given website id and initializes the store
     * @param {String} websiteId The id of the current website
     */
    setWebsiteId: function (websiteId) {
        this.websiteId = websiteId;
        this.storeLoaded = false;
        this.initStore();
    }
});

Ext.reg('CMSpagetemplatecombobox', CMS.home.PageTemplateComboBox);
