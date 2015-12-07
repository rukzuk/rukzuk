Ext.ns('CMS.form');

/**
 * Extended form field wrapper for responsive form fields
 *
 * @class CMS.form.FormFieldWrapperResponsive
 * @extends CMS.form.FormFieldWrapper
 */
CMS.form.FormFieldWrapperResponsive = Ext.extend(CMS.form.FormFieldWrapper, {


    /**
     * All resolutions of this Website
     * @property Object[] resolutions
     */
    resolutions: undefined,

    /**
     * Current resolution object
     * @property Object currentResolution
     */
    currentResolution: undefined,

    /**
     * The internal representation of the value
     * @property formValue
     */
    formValue: undefined,

    /**
     * Inheritance Level of the current displayed value
     * (is 0 if there is a value for the current resolution)
     * @property {Number} inheritLevel
     */
    inheritLevel: 0,

    initComponent: function () {
        // default formValue object (new object)
        this.formValue = {type: 'bp'};

        this.addClass('CMSformfieldwrapperresponsive');

        // initial value
        // non-responsive form fields manager the value on their own, they get the initial value (after unit creation)
        // form the params array (the form field config) as the 'value' property.
        // The Responsive Wrapper needs to manage this value, therefore we grab it here and call setValue.
        // May be overwritten after initComponent by calls of setValue from CMS.form.GeneratedFormPanel
        var ffValue = this.items.value;

        CMS.form.FormFieldWrapperResponsive.superclass.initComponent.call(this);

        this.add({
            xtype: 'box',
            html: '&nbsp;',
            cls: 'indicator',
            listeners: {
                afterrender: function () {
                    this.mon(this.responsiveIndicator.getEl(), 'click', this.handleResponsiveIndicatorClick, this);
                },
                beforedestroy: function () {
                    if (this.rendered) {
                        Ext.QuickTips.unregister(this.el);
                    }
                },
                scope: this
            },
            /**
             * @property {Ext.BoxComponent} responsiveIndicator
             */
            ref: 'responsiveIndicator'
        });

        // register CMSresolutionchanged if we are responsive
        this.registerResolutionChangedEvent();

        // set value
        this.setValue(ffValue, CMS.config.theDefaultResolution);
    },

    /**
     * Registers to CMSresolutionchanged events by {@link CMS.layout.IframeWorkbenchPanel}
     * @private
     */
    registerResolutionChangedEvent: function () {
        // get resolution information source object
        var iframeWorkbenchPanel = this.findParentByType('CMSiframeworkbenchpanel');
        if (iframeWorkbenchPanel) {
            // get currentResolution
            this.handleResolutionChanged(iframeWorkbenchPanel.getCurrentResolution(), iframeWorkbenchPanel.getAllResolutions());
            // register to changes of the current resolution
            this.mon(iframeWorkbenchPanel, 'CMSresolutionchanged', this.handleResolutionChanged, this);
        } else {
            // use the default resolution if this component is used outside of an iframeWorkbenchPanel (e.g. ModuleEditor)
            this.handleResolutionChanged(CMS.config.theDefaultResolution, [CMS.config.theDefaultResolution]);
        }
    },

    /**
     * Handles clicks to the responsive indicator,
     * @private
     */
    handleResponsiveIndicatorClick: function () {
        console.log('[FormFieldWrapperResponsive] handleResponsiveIndicatorClick');
        this.resetCurrentValue();
        this.updateChildResponsiveFields();
        this.updateParentResponsiveFields();
    },

    /**
     * Resets the current resolution value, the value is inherited afterwards
     * @private
     */
    resetCurrentValue: function () {
        // reset value of current resolution only if this is NOT inherited and not default res
        if (!this.isInherited() && !this.isDefaultResolution()) {
            this.removeResolutionFormValue(this.currentResolution.id);
            this.updateView();
        }
    },

    /**
     * Removes the value of the formValues object for the given resolution
     * @param resolutionId
     * @private
     */
    removeResolutionFormValue: function (resolutionId) {
        var oldFormValue = SB.util.cloneObject(this.formValue);
        delete this.formValue[resolutionId];
        var newFormValue = SB.util.cloneObject(this.formValue);
        this.fireValueChangedEvent(newFormValue, oldFormValue);
    },

    /**
     * Updates the current resolution and triggers the update of the underlying UI
     * @param resData
     * @private
     */
    handleResolutionChanged: function (newResData, allResolutionData) {
        console.log('[FormFieldWrapperResponsive]', this.getParamValueKey(), ' handleResolutionChanged newResData', newResData, 'was', this.currentResolution);
        console.log('[FormFieldWrapperResponsive]', this.getParamValueKey(), 'handleResolutionChanged allResolutionData', allResolutionData);

        // update current resolution
        this.currentResolution = newResData;

        // set resolution data
        this.resolutions = allResolutionData;

        // setup resolutions as collection
        this.resolutionCollection = new Ext.util.MixedCollection(false, function (p) {
            return p.id;
        });

        if (this.resolutions) {
            this.resolutionCollection.addAll(this.resolutions);
        }

        // update view
        this.updateView();
    },

    /**
     * The value of the underlying component
     * @returns {*|null} calls the underlying getValue of the component if
     *                   the component has this method, otherwise it returns null
     * @override
     */
    getValue: function () {
        console.log('[FormFieldWrapperResponsive]', this.getParamValueKey(), 'getValue() this.formValue:', this.formValue);
        return this.formValue;
    },

    /**
     * Sets the value of the underlying form field (if present)
     * Is called by owing components to change the value of a form field
     * @param value either a responsive value object or just he plain value
     * @param [resolution] - use this resolution for non responsive values  - if false-y the current resolution is used
     * @public
     * @override
     */
    setValue: function (value, resolution) {
        console.log('[FormFieldWrapperResponsive]', this.getParamValueKey(), 'setValue', value);

        // handle different value types
        if (Ext.isObject(value) && value.type === 'bp') {
            // break reference to UnitRecord
            this.formValue = SB.util.cloneObject(value);
        } else {
            this.setValueForResolution(value, resolution);
        }

        // update view (push internal value to the component)
        this.updateView();
    },

    /**
     * Update the internal representation of a value
     * depending on the responsiveness of this component
     * @param rawValue the raw value as gotten from the component (NOT an object)
     * @param {String|Object} [resolution] resolution - if undefined, the current resolution is used
     * @private
     */
    setValueForResolution: function (rawValue, resolution) {
        resolution = resolution || this.currentResolution;
        resolution = resolution.id ? resolution.id : resolution;
        console.log('[FormFieldWrapperResponsive] setValueForResolution', rawValue, resolution);
        this.formValue[resolution] = rawValue;
        return this.formValue;
    },

    /**
     * Updates the visible value of the underlying component
     * depending on the current resolution (breakpoint)
     * @private
     */
    updateView: function () {
        console.log('[FormFieldWrapperResponsive] updateView', this.currentResolution);

        if (!this.currentResolution) {
            return;
        }

        var displayValue = this.formValue[this.currentResolution.id];
        var inheritLevel = 0;
        var displayValueResolutionId = this.currentResolution.id;

        // find next defined value
        if (displayValue === undefined) {
            // create sorted array of resolution ids
            var resIds = this.resolutionCollection.keys;
            // determine the start where we begin to search for a value
            var startIdx = resIds.indexOf(this.currentResolution.id);
            // search for a value
            for (var i = startIdx; i >= 0; i--) {
                var id = resIds[i];
                displayValue = this.formValue[id];
                inheritLevel++;
                if (displayValue !== undefined) {
                    displayValueResolutionId = id;
                    break;
                }
            }
        }

        // still no value, this is only the case if the default resolution also does not have a values
        // so we inherited nothing, but this nothing still needs to have an origin, which is defined to be the default res
        if (displayValue === undefined) {
            displayValueResolutionId = CMS.config.theDefaultResolution.id;
        }

        // update class state
        this.setValueRaw(displayValue);
        this.inheritLevel = inheritLevel;

        // is the value inherited? (and we are not in the default resolution)
        if (this.isInherited()) {
            this.addClass('valueInherit');
        } else {
            this.removeClass('valueInherit');
        }

        // update class of displayValueResolutionId
        // currently based on the ID of the resolution
        // this must be changed if the ID is not res[1-3]
        Ext.each(this.resolutions, function (res) {
            this.removeClass('bp-' + res.id);
        }, this);
        this.addClass('bp-' + displayValueResolutionId);

        // Update tooltip
        var displayValueResolutionName = this.resolutionCollection.item(displayValueResolutionId).name;
        this.updateIndicatorToolTip(inheritLevel, this.currentResolution.id, displayValueResolutionName);

        console.log('[FormFieldWrapperResponsive] updateView displayValue', displayValue, 'inheritLevel', inheritLevel);

        this.updateUpperValue();
    },

    updateUpperValue: function () {
        // an upper resolution has a true-fy value
        var hasUpperValue = false;
        // all resolution ids
        var resIds = this.resolutionCollection.keys;
        // determine the start where we begin to search for a value
        var startIdx = resIds.indexOf(this.currentResolution.id) - 1;
        // search for a value
        for (var i = startIdx; i >= 0; i--) {
            var id = resIds[i];
            if (this.formValue[id]) {
                hasUpperValue = true;
                break;
            }
        }

        if (hasUpperValue) {
            this.addClass('hasUpperValue');
        } else {
            this.removeClass('hasUpperValue');
        }
    },

    /**
     * Update the tooltip of the indicator element
     * @private
     * @param inheritLevel
     * @param currentResolutionId
     * @param displayValueResolutionName
     */
    updateIndicatorToolTip: function thisFn(inheritLevel, currentResolutionId, displayValueResolutionName) {
        if (this.responsiveIndicator.rendered) {
            var indicatorEl = this.responsiveIndicator.getEl();
            var tooltipText;
            if (currentResolutionId === 'default') {
                tooltipText = CMS.i18n('Feld ist responsive, wechsel die Auslösung um Werte für andere Endgeräte zu setzen', 'formfieldwrapperresponsive.isResponsiveToolTip');
            } else if (inheritLevel === 0) {
                tooltipText = String.format(CMS.i18n('Wert zurücksetzen um Vererbung zu aktivieren', 'formfieldwrapperresponsive.removeValueOfCurrentResTooltip'), displayValueResolutionName);
            } else {
                tooltipText = String.format(CMS.i18n('Wert wird von Auflösung „{0}“ geerbt', 'formfieldwrapperresponsive.valueIsInheritedToolTip'), displayValueResolutionName);
            }

            Ext.QuickTips.unregister(indicatorEl);
            Ext.QuickTips.register({
                target: indicatorEl.id,
                title: tooltipText,
                align: 'b-t?'
            });
        } else {
            this.responsiveIndicator.on('afterrender', function () {
                this.updateIndicatorToolTip(inheritLevel, currentResolutionId, displayValueResolutionName);
            }, this);
        }
    },

    /**
     * Updates the state (inheritance of the parent elements).
     * If a single
     * @private
     */
    updateParentResponsiveFields: function () {
        var parent = this.findParentByType('CMSformfieldwrapperresponsive', true);
        console.log('[FormFieldWrapperResponsive] updateParentResponsiveFields', this, parent);

        // do we have a proper parent?
        if (parent) {
            // update display and saved value
            var parentCmp = parent.getFormField();
            // send change event if parentCmp is there and has an inherited value
            if (parentCmp) {
                if (parent.isInherited()) {
                    parent.itemValueChangeHandler(parentCmp.getValue());
                } else {
                    // reset parent value if all children are inherited
                    var children = parent.find('xtype', 'CMSformfieldwrapperresponsive');
                    var allChildrenAreInherited = children.every(function (c) { return c.isInherited(); });
                    if (allChildrenAreInherited) {
                        parent.resetCurrentValue();
                    }
                }
            }
            // call next parents
            parent.updateParentResponsiveFields();
        }
    },

    /**
     * Remove responsive values of all responsive child form items
     * @private
     */
    updateChildResponsiveFields: function () {
        var children = this.find('xtype', 'CMSformfieldwrapperresponsive');
        Ext.each(children, function (child) {
            child.resetCurrentValue();
        });
    },

    /**
     * Tells weather the current value is inherited or not.
     * Is always false if the current resolution is default.
     * The method {@see CMS.form.FormFieldWrapperResponsive#updateView} changes that value
     * @returns {boolean}
     * @public
     */
    isInherited: function () {
        return (!this.isDefaultResolution() && this.inheritLevel > 0);
    },

    /**
     * Returns true if the current resoltuion is the default resolution
     * @returns {boolean}
     */
    isDefaultResolution: function () {
        return (this.currentResolution.id === 'default');
    },

    /**
     * Handler for value change events of the underlying component (form element)
     * Call this if you want to manually change the form elements data (instead of the internal and/or display values)
     * @param newVal
     * @param oldVal
     * @protected
     * @override
     */
    itemValueChangeHandler: function (newVal, oldVal) {
        // update the internal representation of the formValue
        var oldResponsiveValue = SB.util.cloneObject(this.formValue);
        this.setValueForResolution(newVal);
        var newResponsiveValue = SB.util.cloneObject(this.formValue);

        // TODO: check if we always have the correct old value (checkbox null vs false)!

        // Setting the value (param name: value) in the params object is
        // only required as this class is also used in the module editor (where you can define a default value this way)
        this.setParamValue(SB.util.cloneObject(newResponsiveValue));

        // update view the current value is inherited
        // (and this has changed now, because we are in the itemChange handler)
        if (this.isInherited()) {
            // update our view
            this.updateView();
            // update parent view AND data
            this.updateParentResponsiveFields();
        }

        this.fireValueChangedEvent(newResponsiveValue, oldResponsiveValue);
    }

});

Ext.reg('CMSformfieldwrapperresponsive', CMS.form.FormFieldWrapperResponsive);
