Ext.ns('CMS.form');

/**
 * TabPanel that acts as a FormElement
 * @class CMS.form.TabbedFieldSet
 * @extends Ext.TabPanel
 */
CMS.form.TabbedFieldSet = Ext.extend(Ext.TabPanel, {
    autoTabs: true,
    autoHeight: true,
    value: '',

    initComponent: function () {

        this.addClass('CMStabbedfieldset');

        CMS.form.TabbedFieldSet.superclass.initComponent.apply(this, arguments);

        this.updateEmptyState();

        // initial value
        this.setValue(this.value);

        // only allow CMStabpages as children!
        this.on('beforeadd', function (container, cmp, index) {
            console.info('[TabbedFieldSet] added container', container, 'cmp', cmp, 'idx', index);
            // unwrap if its wrapped
            var formField = cmp.getFormField ? cmp.getFormField() : cmp;
            if (!formField.isXType('CMStabpage')) {
                return false;
            } else {
                this.removeClass('CMStabbedfieldset-empty');
                // TODO: select newly added TabPage (not trivial, as you don't know if this was the users ADD action or just a rebuild of the form!)
                // TODO: Selection should be handled in FormTabEditor
            }
        }, this);


        var activeTabIndex;
        // if a child tab page is updated then the editor will remove the component
        // and add a new one; this will select the last active tab (default Ext.TabPanel
        // behavior); So...
        this.on('beforeremove', function (fieldSet, tabpage) {
            // ... -> we have to remember the active tab before removing it ...
            if (tabpage === this.activeTab) {
                activeTabIndex = this.items.indexOf(tabpage);
            } else {
                activeTabIndex = -1;
            }
        }, this);

        this.on('remove', function () {
            // ...and restore the selection after the old (updated) tab page has bee removed
            if (activeTabIndex >= 0) {
                this.setActiveTab(activeTabIndex);
                activeTabIndex = -1;
            }
            this.updateEmptyState();
        }, this);

        // proxy change events
        this.on('beforetabchange', function (container, newTab, oldTab) {
            if (Ext.isNumber(newTab)) {
                newTab = this.get(newTab);
            }
            if (Ext.isNumber(oldTab)) {
                oldTab = this.get(oldTab);
            }
            if (newTab && oldTab) {
                this.fireEvent('change', this, newTab.getValue(), oldTab.getValue());
            }
        }, this);

        this.on('afterrender', function () {
            /** {@see CMS.form.CheckboxFieldSet#afterRender} */
            this.setValue(this.getValue());
        });

    },

    getValue: function () {
        var activeTab = this.getActiveTab();

        if (Ext.isNumber(activeTab)) {
            activeTab = this.get(activeTab);
        }

        if (activeTab && activeTab.getValue) {
            return activeTab.getValue();
        } else {
            return null;
        }
    },

    setValue: function (value) {
        Ext.each(this.items, function (item) {
            if (item.getValue() === value) {
                this.suspendEvents(false);
                this.setActiveTab(item);
                this.resumeEvents();
                return false;
            }
        }, this);
    },


    /**
     * @private
     */
    updateEmptyState: function () {
        if (this.items && !this.items.length) {
            this.addClass('CMStabbedfieldset-empty');
            this.on('afterrender', function () {
                this.el.set({
                    'data-hint': CMS.i18n('Mindestens eine Tab Seite hinzufügen…', 'tabbedfieldset.editModeAddTabPagesHintText')
                });
            }, this);
        }
    }
});

CMS.form.TabbedFieldSet.prototype.setRawValue = CMS.form.TabbedFieldSet.prototype.setValue;
CMS.form.TabbedFieldSet.prototype.getRawValue = CMS.form.TabbedFieldSet.prototype.getValue;

Ext.reg('CMStabbedfieldset', CMS.form.TabbedFieldSet);
