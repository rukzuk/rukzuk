Ext.ns('CMS.home');

/**
 * Form for entering metadata of a page
 *
 * @class CMS.home.PagePropertyForm
 * @extends Ext.FormPanel
 */
CMS.home.PagePropertyForm = Ext.extend(Ext.Panel, {
    /** @lends CMS.home.PagePropertyForm.prototype */

    bodyStyle: 'overflow-y: auto;',

    height: 550,

    layout: 'fit',

    /**
     * The currenty opened website's id
     * @property websiteId
     * @type String
     */
    websiteId: null,

    /**
     * The currently saved page meta data
     * @private
     * @property currentData
     * @type Object
     */
    pageAttributes: undefined,

    /** @protected */
    initComponent: function () {

        this.defaults = {
            anchor: '-' + Ext.getPreciseScrollBarWidth()
        };

        CMS.home.PagePropertyForm.superclass.initComponent.apply(this, arguments);

        this.on('afterrender', function () {
            this.fireEvent('clientvalidation', {}, false);
        }, this);
    },

    valueChangeHandler: function(cmp, changes) {
        this.pageAttributes[changes.key] = changes.newValue;
    },

    /**
     * Set Page Meta Data
     *
     * @param {Object} data The form data to set
     */
    setPageMetaData: function (data) {
        // load pagetype and website settings store first
        var multiStoreLoader = new CMS.data.MultiStoreLoader({
            storeTypes: ['pageType', 'websiteSettings'],
            websiteId: this.websiteId,
            scope: this,
            callback: function () {
                var store = CMS.data.StoreManager.get('pageType', this.websiteId);
                this.buildFormPanel(data, store);
            }
        });
        multiStoreLoader.load(true);
    },

    /**
     * Build a GeneratedForm panel with the supplied data. Uses a fake record to keep pageType store clean.
     * @param pageMetaData
     * @param pageTypeStore
     */
    buildFormPanel: function (pageMetaData, pageTypeStore) {

        this.removeAll();

        var pageTypeRecord = pageTypeStore.getById(pageMetaData.pageType);

        // fallback page type
        if (!pageTypeRecord) {
            pageTypeRecord = pageTypeStore.getById(CMS.config.fallbackPageType);
        }

        // build pageAttributes
        this.pageAttributes = {};
        Ext.apply(this.pageAttributes, this.getAttributesFromPageMeta(pageMetaData), pageTypeRecord.data.formValues);

        // create fake record with formValues
        var fakeRecord = new CMS.data.PageTypeRecord(Ext.apply({}, {formValues: this.pageAttributes}, pageTypeRecord.data));

        // run page type callback
        var pageTypeApi = new CMS.pageType.api.API(this.websiteId);
        pageTypeRecord.getBuildFormPanelCallback()(pageTypeApi, fakeRecord, this.pageAttributes);

        // fake validation
        this.fireEvent('clientvalidation', {}, this.isValid());

        var formConfig = CMS.form.FormConfigHelper.fromConfigToForm(fakeRecord.data.form);

        this.form = this.add({
            xtype: 'CMSgeneratedformpanel',
            border: false,
            bodyStyle: { padding: '20px' },
            idSuffix: this.idSuffix,
            websiteId: this.websiteId,
            cfg: formConfig,
            cls: 'CMSgeneratedformpanel',
            record: fakeRecord,
            plugins: ['CMSradiofieldsetplugin'],
            listeners: {
                scope: this,
                valuechanged: this.valueChangeHandler,
            }
        });

        // update ui
        this.doLayout();

        // special handling of page name field
        var textFields = this.form.find('CMSvar', '_name');
        if (textFields.length) {
            var nameField = textFields[0];

            // focus name field
            nameField.focus(true, 200);

            // submit on enter
            this.mon(nameField, 'specialkey', function (field, e) {
                if (e.getKey() == e.ENTER) {
                    this.fireEvent('CMSsubmitpageform');
                }
            }, this);

            // fake validation of _name - needs be at least 1 char long
            // ignoring any validation settings (as they don't work properly)
            var fakeValidation = function (cmp) {
                this.fireEvent('clientvalidation', {}, (cmp.getValue() && cmp.getValue().length > 0));
            };

            this.mon(nameField, 'valid', fakeValidation, this);
            this.mon(nameField, 'invalid', fakeValidation, this);
        }
    },

    /**
     * Reset Form
     */
    resetForm: function () {
        this.fireEvent('clientvalidation', {}, false);
        this.removeAll();
        this.pageAttributes = null;
    },

    /**
     * Values of this fake form
     *
     * @returns {Object} the form values (key-value-pairs)
     */
    getActualValues: function () {
        return this.getPageMetaFromAttributes(this.pageAttributes);
    },

    /**
     * Convert page meta to attributes
     * @param pageMetaData
     * @returns {Object} an updated pageAttributes object
     */
    getAttributesFromPageMeta: function (pageMetaData) {
        return Ext.apply({}, {
            _name: pageMetaData.name,
            _inNavigation: pageMetaData.inNavigation,
            _navigationTitle: pageMetaData.navigationTitle,
            _date: pageMetaData.date,
            _description: pageMetaData.description,
            _mediaId: pageMetaData.mediaId
        }, pageMetaData.pageAttributes);
    },

    /**
     * Converts attributes back to page meta
     * @param pageAttributes
     * @returns {Object} pageMetaData
     */
    getPageMetaFromAttributes: function (pageAttributes) {
        return Ext.apply({}, {
            name: pageAttributes._name,
            inNavigation: pageAttributes._inNavigation,
            navigationTitle: pageAttributes._navigationTitle,
            date: pageAttributes._date,
            description: pageAttributes._description,
            mediaId: pageAttributes._mediaId,
            pageAttributes: pageAttributes,
        });
    },

    /**
     * Checks if the entered form values are valid using the field
     * validator methods
     *
     * @return {Boolean} <code>true</code> if and only if all the form
     *      values are valid
     */
    isValid: function () {
        return (this.pageAttributes && this.pageAttributes._name && this.pageAttributes._name !== '');
    },

    /** @protected */
    destroy: function () {
        CMS.home.PagePropertyForm.superclass.destroy.apply(this, arguments);
        this.pageAttributes = null;
    }
});

Ext.reg('CMSpagepropertyform', CMS.home.PagePropertyForm);

