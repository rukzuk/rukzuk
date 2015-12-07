Ext.ns('CMS.liveView');

/**
 * @class CMS.liveView.PreviewArea
 * @extends Ext.Container
 * A container with a {@link CMS.liveView.TheEditableIframe}
 */
CMS.liveView.PreviewArea = Ext.extend(Ext.Container, {
    cls: 'CMSpreviewarea',
    layout: {
        type: 'hbox',
        pack: 'start',
        align: 'stretch'
    },
    mode: 'template',
    unitStore: null,
    bubbleEvents: ['CMSopenmodule', 'CMSresume', 'CMScancel', 'CMSselectunit', 'CMSselectsection', 'CMSremoveunit', 'CMSresetunit', 'CMShoverunit', 'CMSduplicateunit', 'CMSvisualhelpers', 'CMSresolutionchanged'],

    /**
    * @property websiteId
    * @type String
    * The currently opened website's id
    */
    websiteId: '',

    initComponent: function () {
        console.log('[PreviewArea] initComponent');

        this.plugins = ['CMSiframeresolutionswitcher'];

        this.items = {
            xtype: 'CMStheeditableiframe',
            ref: 'iframe',
            cls: 'CMSiframe',
            websiteId: this.websiteId,
            type: this.mode,
            unitStore: this.unitStore,
            listeners: {
                select: this.handleSelect,
                selectSection: this.handleSelectSection,
                hover: this.handleHover,
                scope: this
            }
        };

        CMS.liveView.PreviewArea.superclass.initComponent.apply(this, arguments);
    },

    /**
    * Render specified document
    * @param {Object} cfg
    * An object containing the properties <ul>
<li><tt>record</tt>: The {@link CMS.data.PageRecord} or {@link CMS.data.TemplateRecord} to be rendered, or its id as a <tt>String</tt></li>
<li><tt>unitId</tt>: A <tt>String</tt> denoting a unit to be replaced (optional)</li>
</ul>
    */
    renderDocument: function (cfg) {
        console.log('[PreviewArea] rendering', cfg);
        this.loading = true;
        var ifr = this.iframe;
        if (cfg.record && (cfg.record.data || typeof cfg.record == 'string')) {
            ifr.renderPageOrTemplate(cfg.record, function () {
                this.loading = false;
            }, this, cfg.unitId);
        }
    },

    /**
     * @private
     * handler for the select event that is fired when a unit is clicked
     */
    handleSelect: function (selectedUnitEl) {
        console.log('[PA] handleSelect', arguments);
        var id = selectedUnitEl && selectedUnitEl.id;
        var unit;
        if (id) {
            unit = this.unitStore.getById(id);
        }
        if (unit) {
            this.selectUnit(unit, selectedUnitEl);
        }
    },

    /**
     * @private
     * handler for the selectSection event that is fired when a section is clicked
     */
    handleSelectSection: function (selectedUnitEl, section, event) {
        console.log('[PA] handleSelectSection', arguments);
        var id = selectedUnitEl && selectedUnitEl.id;
        var CMSvarName = section.getAttribute(CMS.config.inlineSectionHTMLAttribute);
        var unit;
        if (id) {
            unit = this.unitStore.getById(id);
        }
        if (!unit) {
            console.warn('[PA] handleSelectSection: unit of section \'' + CMSvarName + '\' with id ' + id + ' not found!');
            return;
        }

        var formGroup = unit.getModule().getFormGroupOfField(CMSvarName);

        //abort if the section isn't a field of the unit
        if (!formGroup) {
            console.warn('[PA] handleSelectSection: \'' + CMSvarName + '\' is not a valid section of unit ' + id + '!');
            return;
        }

        //abort if user is in page mode and the tab of the field isn't visible
        if (!unit.isFormValueEditable(CMSvarName, this.mode)) {
            console.info('[PA] handleSelectSection: editing of section \'' + CMSvarName + '\' is not allowed');
            return;
        }

        this.fireEvent('CMSselectsection', {
            unit: unit,
            section: section,
            event: event
        });
    },

    /**
     * Select a unit, and fire the CMSselectunit event. If the unit is already selected, nothing happens,
     * unless <tt>forceRefresh</tt> is <tt>true</tt>
     * @param {CMS.data.UnitRecord|String} unit The unit to be selected
     * @param {Ext.Element} element (optional) An Element that is to be focused
     * @param {Boolean} forceRefresh (optional) <tt>true</tt> to select unit even if it is already selected.
     * This is useful for a page refresh.
     */
    selectUnit: function (unit, element, forceRefresh) {
        if (typeof unit == 'string') {
            unit = this.unitStore.getById(unit);
        }

        if (!unit) {
            return;
        }
        //Check if user has selected a unit without a renderer
        /*if (!unit.isEditableInMode(this.mode)) {
            //Remove current selection
            this.iframe.setSelectedUnitEl(null);
            //Reset selected id property
            this.selectedId = null;
            return;
        }*/
        var id = unit && unit.id;
        var run = (!!forceRefresh || id != this.selectedId); // filter select event fired by call below
        this.selectedId = id;
        if (!run) {
            return;
        }
        this.iframe.selectUnitById(id); // fires select event
        this.fireEvent('CMSselectunit', {
            unit: unit,
            element: element
        });
    },

    /**
    * Outline a unit
    * @param {String} id The id of the unit to be outlined, or null to remove outlines
    */
    outlineUnit: function (id) {
        if (this.loading) {
            return;
        }
        var run = (id != this.hoveredId);
        this.hoveredId = id;
        if (run) {
            if (id === null) {
                this.iframe.setHoveredUnitEl(null);
            } else {
                this.iframe.selectUnitById(id, true);
            }
        }
    },

    /**
     * @private
     * Handler for hover event fired by iFrame component
     */
    handleHover: function (selectedUnitEl) {
        if (!selectedUnitEl) {
            return;
        }
        this.fireEvent('CMShoverunit', selectedUnitEl.id);
    }
});

Ext.reg('CMSpreviewarea', CMS.liveView.PreviewArea);
