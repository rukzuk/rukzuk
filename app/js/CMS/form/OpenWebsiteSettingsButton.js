Ext.ns('CMS.form');

/**
* @class CMS.form.OpenWebsiteSettingsButton
* @extends Ext.Button
* A button which opens the website settings
*/
CMS.form.OpenWebsiteSettingsButton = Ext.extend(Ext.Button, {

    layout: 'fit',

    websiteId: '',

    text: '',

    tooltip: '',

    /**
     * settingsId
     */
    settingsId: '',

    initComponent: function () {
        var isAllowedToEditWebsiteSettings = false;
        try {
            var website = CMS.data.WebsiteStore.getInstance().getById(this.websiteId);
            isAllowedToEditWebsiteSettings = website && CMS.app.userInfo.canEditWebsiteSettings(website);
        } catch(e) {
            console.warn('[OpenWebsiteSettingsButton] could not load website rights');
        }

        // button settings
        this.disabled = !isAllowedToEditWebsiteSettings;
        this.addClass('smallBtn');

        CMS.form.OpenWebsiteSettingsButton.superclass.initComponent.apply(this, arguments);

        this.on('click', this.clickHandler, this);
    },

    clickHandler: function () {
        (new CMS.websiteSettings.WebsiteSettingsWindow({
            websiteId: this.websiteId,
            settingsId: this.settingsId
        })).show();
    },

    getValue: Ext.emptyFn,

});

Ext.reg('CMSopenwebsitesettings', CMS.form.OpenWebsiteSettingsButton);
