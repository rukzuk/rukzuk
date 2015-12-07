Ext.ns('CMS.api');

/**
 * Helper which does various unit-centric tasks for the API.
 *
 * @class CMS.api.ApiUnitHelper
 * @extends Ext.util.Observable
 */
CMS.api.ApiUnitHelper = Ext.extend(Ext.util.Observable, {
    /** @lends CMS.api.ApiUnitHelper.prototype */

    /**
     * Stores the plugin instance that this helper belongs to
     * @property owner
     * @type CMS.api.PluginInstance
     */
    owner: null,

    unitStore: null,

    constructor: function (cfg) {
        CMS.api.ApiUnitHelper.superclass.constructor.apply(this, arguments);
        Ext.apply(this, cfg);
    },

    /**
     * Checks if the given id has a more or less sane format and if there is a
     * corresponding unit
     *
     * @param {String} unitId The id of a unit (hopefully)
     * @param {Boolean} showWarning (Optional) Whether console.warn should be
     *      called or not
     * @return {Boolean} Whether the id is valid or not
     * @private
     */
    isValidUnitId: function (unitId, showWarning) {
        var errorMessage = '';
        if (unitId && typeof unitId == 'string') {
            var unit = this.unitStore.getById(unitId);
            if (!unit) {
                errorMessage = CMS.i18n('Zur Id {unitId} konnte keine Unit gefunden werden').replace('{unitId}', unitId);
            }
        } else {
            errorMessage = CMS.i18n('{unitId} ist keine valide Unit Id.').replace('{unitId}', unitId ? unitId.toString() : unitId);
        }
        if (errorMessage && showWarning !== false) {
            console.warn(errorMessage);
        }
        return !errorMessage;
    },

    /**
     * Sets the editable flags for all form values of the unit
     *
     * @param {Object} unitDescriptor The descriptor object
     * @param {CMS.data.UnitRecord} unit The unit which is described
     * @private
     */
    setEditableFlags: function (unitDescriptor, unit) {
        Ext.iterate(unitDescriptor.formValues, function (formValue) {
            unitDescriptor.formValues[formValue] = {
                editable: unit.isFormValueEditable(formValue, this.owner.mode),
                value: unitDescriptor.formValues[formValue]
            };
        }, this);
    },

    /**
     * Adds the ids of all children, ghost children and the parent unit of
     * the unit to it
     *
     * @param {Object} unitDescriptor The descriptor object
     * @param {CMS.data.UnitRecord} unit The unit which is described
     * @private
     */
    setRelations: function (unitDescriptor, unit) {
        if (unitDescriptor.children) {
            // Instead of giving the module developer each child unit
            // as whole we give him/her the id of each child.
            unitDescriptor.children = Ext.pluck(unitDescriptor.children, 'id');
        } else {
            unitDescriptor.children = [];
        }
        if (unitDescriptor.ghostChildren) {
            // Instead of giving the module developer each ghost child units
            // as whole we give him/her the id of each child.
            unitDescriptor.ghostChildren = Ext.pluck(unitDescriptor.ghostChildren, 'id');
        } else {
            unitDescriptor.ghostChildren = [];
        }
        var parentUnit = unit.store.getParentUnit(unit);
        if (parentUnit) {
            var parentModule = parentUnit.getModule();
            unitDescriptor.parentUnitId = parentUnit.id;
            unitDescriptor.parentModuleId = parentModule.id;
        }
    },

    /**
     * Adds the id and name of all form groups to the descriptor object
     *
     * @param {Object} unitDescriptor The descriptor object
     * @param {CMS.data.UnitRecord} unit The unit which is described
     * @private
     */
    setFormGroup: function (unitDescriptor, unit) {
        var formGroup = [];
        var forms = unit.getModule().get('form');
        Ext.each(forms, function (form) {
            formGroup.push({
                id: form.id,
                name: form.name
            });
        });
        unitDescriptor.allFormGroups = formGroup;
    },

    /**
     * Returns an object which has all the properties of a unit which might be of
     * interest to a module developer
     *
     * @param {CMS.data.UnitRecord} unit The unit whose configuration should be
     *      computed
     * @param {Boolean} [excludeFormValues] exclude the formValues object to speed
     *      up fetch of data, default: false
     * @return {Object} An object describing the unit
     * @private
     */
    computeUnitDescriptor: function (unit, excludeFormValues) {
        var unitDescriptor = SB.util.cloneObject(unit.data);
        var theModule = unit.getModule();

        // formValues
        if (excludeFormValues === true) {
            // remove formValues
            delete unitDescriptor.formValues;
        } else {
            // merge formValues of the unit with the default formValues of the module
            var moduleFormValues = SB.util.cloneObject(theModule.get('formValues'));
            unitDescriptor.formValues = Ext.applyIf(unitDescriptor.formValues, moduleFormValues);

            this.setEditableFlags(unitDescriptor, unit);
        }

        // unit specific stuff
        this.setRelations(unitDescriptor, unit);
        this.setFormGroup(unitDescriptor, unit);

        // The state of the tree node is of no concern to the
        // module developer
        delete unitDescriptor.expanded;

        Ext.apply(unitDescriptor, {
            editable: unit.isEditableInMode(this.owner.mode),
            deletable: unit.isDeletableInMode(this.owner.mode),
            upmovable: unit.isMovable(this.owner.mode, 'up'),
            downmovable: unit.isMovable(this.owner.mode, 'down'),
            name: unit.getUIName(),
            nameRaw: this.saveParse(unit.get('name'))
        });

        // Breaks all references
        return SB.util.cloneObject(unitDescriptor);
    },

    /**
     * Returns an object which has all the properties of a module which are relevant
     * for a module-dev (X-doc-API)
     *
     * @param {Record} theModule module record
     * @returns {Object} The module descriptor object:
     *      <pre><code>
     *      {
     *          id: *,
     *          name: *,
     *          extensionModule: *,
     *          allowedChildModuleTypes: *,
     *          icon: *
     *      }
     *      </code></pre>
     * @private
     */
    computeModuleDescriptor: function (theModule) {
        // mapping to ensure strings
        var allowedChildModuleTypeMapping = {};
        allowedChildModuleTypeMapping[''] = ''; // none
        allowedChildModuleTypeMapping['*'] = 'all'; // all
        allowedChildModuleTypeMapping[CMS.config.moduleTypes.extension] = 'extension';

        return {
            id: theModule.get('id'),
            name: CMS.translateInput(theModule.get('name')),
            nameRaw: this.saveParse(theModule.get('name')),
            extensionModule: CMS.data.isExtensionModuleRecord(theModule),
            allowedChildModuleTypes: allowedChildModuleTypeMapping[theModule.get('allowedChildModuleType')],
            icon: CMS.config.urls.absoluteBasePath + CMS.config.urls.moduleIconPath + theModule.get('icon')
        };
    },

    /**
     * Parses JSON whithout throwing an exception
     * @private
     */
    saveParse: function (raw) {
        var result;
        try {
            result = JSON.parse(raw);
        } catch (e) {
            result = raw;
        }
        return result;
    }
});
