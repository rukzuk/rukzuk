Ext.ns('CMS.api');

/**
 * 'Plugin' to control the CMS API adapter object of the iframe.
 *
 * @class CMS.api.PluginInstance
 * @extends Ext.util.Observable
 */
CMS.api.PluginInstance = Ext.extend(Ext.util.Observable, {
    /** @lends CMS.api.PluginInstance.prototype */

    /**
     * The window object of the actual iframe
     *
     * @property iframeWindow
     * @type object
     */
    iframeWindow: null,

    /**
     * The CMS.liveView.TheEditableIframe object which encapsulates
     * the actually iframe
     *
     * @property editableIframe
     * @type CMS.liveView.TheEditableIframe
     */
    editableIframe: null,

    /**
     * The CMS.layout.IframeWorkbenchPanel object to
     * which we are plugged in
     *
     * @property iframeWorkbenchPanel
     * @type CMS.layout.IframeWorkbenchPanel
     */
    iframeWorkbenchPanel: null,

    /**
     * The CMS.layout.IframeWorkbenchPanel object to
     * which we are plugged in
     *
     * @property apiInstance
     * @type CMS.layout.IframeWorkbenchPanel
     */
    apiInstance: null,

    /**
     * The currently selected unit
     *
     * @property currentUnit
     * @type CMS.data.UnitRecord
     */
    currentUnit: null,

    /**
     * Stores which event handling function of the ApiPlugin has to react to which event
     *
     * @property eventMapping
     * @type Object
     */
    eventMapping: null,

    /**
     * Stores which event handling function of the ApiPlugin has to react to which event.
     * These listeners will be persistent until the ApiPlugin gets destroyed
     *
     * @property eventMappingPersistent
     * @type Object
     */
    eventMappingPersistent: null,

    /**
     * Stores all inserted units for each DOM ready slot
     *
     * @property insertedUnitsBuffer
     * @type Object
     */
    insertedUnitsBuffer: null,

    /**
     * Increases with every DOM ready event of the iframe; needed for the insertedUnitsBuffer
     *
     * @property iframeDomReadyCounter
     * @type Number
     */
    iframeOnLoadCounter: 0,

    /**
     * Caches the visual helpers state (whether they should be visible or hidden).
     *
     * @property visualHelpersState
     * @type Object
     */
    visualHelpersState: null,

    /**
     * Stores in which mode the plugin is
     * running (page or template)
     *
     * @property mode
     * @type String
     */
    mode: '',

    /**
     * Helper object which handles unit-centric tasks
     *
     * @property unitHelper
     * @type CMS.api.ApiUnitHelper
     */
    unitHelper: null,

    constructor: function (cmp) {
        this.apiInstance = new CMS.api.API(this);
        window.CmsApi = this.apiInstance;
        this.iframeWorkbenchPanel = cmp;
        this.mode = this.iframeWorkbenchPanel.mode;
        this.editableIframe = cmp.previewarea.iframe;
        this.unitHelper = new CMS.api.ApiUnitHelper({
            unitStore: this.iframeWorkbenchPanel.unitStore,
            owner: this
        });
        this.previewTicketHelper = new CMS.app.PreviewTicketHelper();
        this.editableIframe.on('beforereplace', this.iframeBeforereplaceHandler, this);
        this.editableIframe.on('rendered', this.iframeRenderHandler, this);
        this.editableIframe.on('domready', this.iframeDomReadyHandler, this);
        this.editableIframe.on('onload', this.iframeOnLoadHanlder, this);

        this.visualHelpersState = {
            enabled: true,
            toolbar: true
        };
        this.insertedUnitsBuffer = {};

        CMS.api.PluginInstance.superclass.constructor.apply(this, arguments);

        this.eventMapping = {
            'show': this.showHandler,
            'hide': this.hideHandler,
            'CMSformvaluechange': this.formValueChangeHandler,
            'CMSbeforeinsertunit': this.beforeInsertUnitHandler,
            'CMSunitselected': this.unitSelectHandler,
            'CMSopeneditor': this.openEditorHandler,
            'CMSrender': this.beforeRenderHandler,
            'CMSbeforemove': this.beforeMoveHandler,
            'CMSbeforeremove': this.beforeRemoveHandler,
            'CMSafterremove': this.afterRemoveHandler,
            'CMSunittreeselect': this.unitTreeSelectHandler,
            'CMStreemouseenter': this.treeMouseEnterHandler,
            'CMStreemouseout': this.treeMouseOutHandler,
            'CMSframemouseenter': this.frameMouseEnterHandler,
            'CMSframemouseout': this.frameMouseOutHandler,
            'CMSunitframeselect': this.unitFrameSelectHandler,
            'CMSunitframedeselect': this.unitFrameDeselectHandler,
            'CMSresolutionchanged': this.resolutionChangeHandler,
            'CMSvisualhelpers': this.visualHelpersHandler
        };

        this.eventMappingPersistent = {
            'CMSbeforeinsertunit': this.beforeInsertUnitBufferHandler
        };

        // Setups up all event listeners defined in the eventMappingPersistent object
        console.log('[ApiPlugin] Setting up persistent event listeners');
        this.setupEventsByMapping(this.eventMappingPersistent);

        // SBCMS-847 The CMSunitselected event needs to be handled separately.
        // It may fire before the iframe content is rendered, so this.unitSelectHandler is not called
        // but we need to store currentUnit for later use
        cmp.on('CMSunitselected', function (unit) {
            if (!this.listenersSetup) {
                this.currentUnit = unit;
            }
        }, this);

        cmp.on('destroy', this.destroy, this);
    },

    /******************************************
     *  API methods
     *******************************************/

    /**
     * Tells the component not to render the current iframe.
     */
    preventRendering: function () {
        this.preventRenderingCalled = true;
        if (!this.allowCallToPreventRendering) {
            CMS.console.warn('API method preventRendering() called outside of beforeRender or formValueChange event, will not work as expected!');
        }
    },

    /**
     * Gets the configuration of the specified unit.
     *
     * @param {String} unitId The id of the unit
     * @param {Boolean} [includeFormValues] include the formValues; default = true
     * @return {Object} An object containing the configuration of the unit
     */
    getUnitConfig: function (unitId, includeFormValues) {
        var descriptor = null;
        if (this.isValidUnitId(unitId)) {
            var unit = this.iframeWorkbenchPanel.unitStore.getById(unitId);
            descriptor = this.computeUnitDescriptor(unit, (includeFormValues === false));
        }
        return descriptor;
    },

    /**
     * Get unit ids of all units matching a module id
     *
     * @param {String} moduleId - units from the given module id
     * @returns {Array<String>}
     */
    getUnitIds: function (moduleId) {
        var ids = [];
        this.iframeWorkbenchPanel.unitStore.queryBy(function (record, id) {
            if (record.get('moduleId') == moduleId) {
                ids.push(id);
            }
            return false;
        });

        return ids;
    },

    /**
     * Gets the configuration of the currently selected unit.
     *
     * @param {Boolean} [includeFormValues] include the formValues; default = true
     * @return {Object} An object containing the configuration of the unit
     */
    getSelectedUnitConfig: function (includeFormValues) {
        var descriptor = null;
        var selectedUnit = this.iframeWorkbenchPanel.selectedUnit;
        if (selectedUnit) {
            descriptor = this.computeUnitDescriptor(selectedUnit, (includeFormValues === false));
        }
        return descriptor;
    },

    /**
     * Gets the configuration of the currently selected unit.
     *
     * @return {String} The id of the unit
     */
    getSelectedUnitId: function () {
        var selectedUnit = this.iframeWorkbenchPanel.selectedUnit;
        console.log(selectedUnit);
        if (selectedUnit) {
            return selectedUnit.id;
        } else {
            return false;
        }
    },

    /**
     * Gets meta-data of a module
     *
     * @param {String} moduleId The id of the module
     * @return {Object} The configuration of the module - null if it fails
     */
    getModuleConfig: function (moduleId) {
        var moduleStore = CMS.data.StoreManager.get('module', this.iframeWorkbenchPanel.websiteId);
        var theModule = moduleStore.getById(moduleId);

        if (theModule) {
            // Break all references
            return SB.util.cloneObject(this.unitHelper.computeModuleDescriptor(theModule));
        } else {
            // no module found
            return null;
        }
    },


    /**
     * Refreshes the specified unit or if no id was specified
     * the whole page.
     *
     * @param {String} unitId (optional) The id of the unit which should be re-rendered.
     */
    refresh: function (unitId) {
        var hasApi = this.iframeWindow && this.iframeWindow.CMS;
        if (hasApi) {
            this.iframeWindow.CMS.suspendEventFiring();
        }
        this.iframeWorkbenchPanel.resetRendering();
        if (this.isValidUnitId(unitId, false)) {
            this.iframeWorkbenchPanel.refresh(unitId);
            if (hasApi) {
                this.iframeWindow.CMS.resumeEventFiring();
            }
        } else {
            this.iframeWorkbenchPanel.refresh();
        }
    },

    /**
     * Updates the specified unit property.
     *
     * @param {String} unitId The id of the unit
     * @param {String} key The name of the unit property which is changed
     * @param {Mixed} value The new value of the unit property
     * @return {Boolean} Whether the key value pair could be successfully set
     */
    setUnitConfig: function (unitId, key, value) {
        if (this.isValidUnitId(unitId)) {
            return this.iframeWorkbenchPanel.silentlySetValue(unitId, key, value);
        } else {
            return false;
        }
    },

    /**
     * Allows to set meta information which should visualize unit
     * settings (replaces <code>CMS.setName</code>)
     * NOTICE: Since there is no "info" attribute for a unit the information
     *         will be applied to the units name
     *
     * @function
     * @param {String} id The id of the unit
     * @param {String} key The name of the meta property which is changed
     * @param {Mixed} value The new value of the meta property
     * @return {Boolean} Whether the key value pair could be successfully set
     */
    setUnitMetaInfo: (function () {
        // helper method to apply the info value to the name or to a single
        // name translation
        function applyValue(langKey, text, infoValue, key) {
            if (Ext.isString(infoValue)) {
                try {
                    infoValue = JSON.parse(infoValue);
                } catch (e) {}
            }

            if (Ext.isObject(infoValue)) {
                // the info value is also translated
                // -> get the value to the corresponding language
                infoValue = infoValue[langKey];
            }

            // remove old meta informations
            text = text.replace(/\(.*\)/g, '').trim();
            // ... and add new ones
            if (infoValue !== '') {
                if (key == 'complete') {
                    text = infoValue;
                } else {
                    text += ' (' + infoValue + ')';
                }
            }

            return text;
        }

        return function (id, key, value) {
            if (this.isValidUnitId(id)) {
                if (/[Ⓢ①②③]/.test(value)) {
                    // The breakpoint marker should be removed (they break the translation mechanism
                    // of the unit names); The up-to-date modules are adapted but we still need this
                    // check for old ones
                    return true;
                }

                var unit = this.getUnitConfig(id);
                var name = unit.nameRaw;
                if (!name) {
                    // unit has no own name -> get name from module
                    name = this.getModuleConfig(unit.moduleId).nameRaw;
                }

                if (Ext.isObject(name)) {
                    // multi-language-name (e.g. {de: 'Sprungmarke', en: 'Jump-Label'})
                    // -> merge info value into name object
                    Ext.iterate(name, function (langkey, text) {
                        name[langkey] = applyValue(langkey, text, value, key);
                    });
                    name = JSON.stringify(name);
                } else if (Ext.isString(name)) {
                    name = applyValue(CMS.app.lang, name, value, key);
                }

                this.iframeWorkbenchPanel.setUnitName(id, name);
                return true;
            } else {
                return false;
            }
        };
    }()),

    /**
     * Triggers the duplication of the given unit
     *
     * @param {String} unitId The id of the unit which should be duplicated
     * @param {String} name (optional) The new unit's name. If omitted, the user is prompted for a name.
     *      Note that the user may cancel this.
     */
    duplicate: function (unitId, name) {
        if (this.isValidUnitId(unitId)) {
            this.iframeWorkbenchPanel.duplicateUnit(unitId, name);
        }
    },

    /**
     * Removes the specified unit.
     *
     * @param {String} unitId The id of the unit which is to be removed.
     * @param {Boolean} showConfirmation Whether the CMS should ask the user
     *      for confirmation before removing the unit
     * @param {Function} callback(Optional) The function which will be executed
     *      when the unit is actually deleted or not
     * @param {Object} scope (Optional) The object in whose scope the callback
     *      function should be executed.
     */
    removeUnit: function (unitId, showConfirmation, callback, scope) {
        if (this.isValidUnitId(unitId)) {
            this.iframeWorkbenchPanel.removeUnit(unitId, showConfirmation, callback, scope);
        } else {
            if (Ext.isFunction(showConfirmation)) {
                if (Ext.isObject(callback)) {
                    showConfirmation.call(callback, false);
                } else {
                    showConfirmation(false);
                }
            } else if (Ext.isFunction(callback)) {
                if (Ext.isObject(scope)) {
                    callback.call(scope, false);
                } else {
                    callback(false);
                }
            }
        }
        callback = null;
        scope = null;
    },

    /**
     * Selects the specified unit in both tree and iframe.
     *
     * @param {String} unitId The id of the unit which should be selected
     */
    selectUnit: function (unitId) {
        if (this.isValidUnitId(unitId)) {
            this.iframeWorkbenchPanel.selectUnit({unit: unitId});
        }
    },

    /**
     * Deselects the specified unit in both tree and iframe.
     */
    deselectUnits: function () {
        this.iframeWorkbenchPanel.selectUnit({});
    },

    /**
     * Tells the CMS to open the "Insert unit" window
     *
     * @param {String} unitId (Optional) The id of the unit in whose context the
     *      "Insert unit" window should be opened
     * @param {Integer} position (optional) The default position choice in the dialog.
     *      Possible values:
     *      <ul>
     *        <li>-1 - above</li>
     *        <li> 0 - inside</li>
     *        <li> 1 - below (default)</li>
     *      </ul>
     */
    openInsertWindow: function (unitId, position) {
        if (unitId) {
            if (this.isValidUnitId(unitId)) {
                var unit = this.iframeWorkbenchPanel.unitStore.getById(unitId);
                this.iframeWorkbenchPanel.openInsertWindow(unit, position);
            }
        } else {
            this.iframeWorkbenchPanel.openInsertWindow();
        }
    },

    /**
     * Opens the Insert Extension (Style) Menu
     */
    openInsertExtensionMenu: function () {
        this.iframeWorkbenchPanel.openExtensionMenu();
    },

    /**
     * Opens the form group which corresponds to the given
     * form group id. If no argument is passed, it will just bring the form panel to front.
     *
     * @param {String} varName (optional) The name of the unit property whose
     *      form group has to be opened
     * @param {Boolean} showPanel (optional) <tt>true</tt> to activate the containing form panel.
     *      Defaults to <tt>false</tt>
     */
    openFormPanel: function (varName, showPanel) {
        if (Ext.isDefined(varName)) {
            var formGroupId = this.currentUnit.getModule().getFormGroupOfField(varName);
            if (formGroupId) {
                this.iframeWorkbenchPanel.openFormPanel(formGroupId, showPanel);
            }
        } else {
            this.iframeWorkbenchPanel.openFormPanel();
        }
    },

    /**
     * Opens the structure tree panel
     */
    openTreePanel: function () {
        this.iframeWorkbenchPanel.openTreePanel();
    },

    /**
     * Moves the unit up in the structure tree
     *
     * @param {String} unitId The id of the unit which should be moved
     * @return {Boolean} Whether the unit could successfully be moved or not
     */
    moveUp: function (unitId) {
        return this.moveUnit(unitId, 'up');
    },

    /**
     * Moves the unit down in the structure tree
     *
     * @param {String} unitId The id of the unit which should be moved
     * @return {Boolean} Whether the unit could successfully be moved or not
     */
    moveDown: function (unitId) {
        return this.moveUnit(unitId, 'down');
    },

    /**
     * Returns the sibling and child units of the given unit which can be inserted
     *
     * @param {String} unitId The id of the unit in whose context units are going
     *      to be inserted
     * @return {Object} The insertable units
     */
    getInsertableUnits: function (unitId) {
        if (this.isValidUnitId(unitId)) {
            var unit = this.iframeWorkbenchPanel.unitStore.getById(unitId);
            var returnValue = {
                children: CMS.liveView.InsertUnitHelper.getInstance().getInsertableChildren(unit, this.mode),
                siblings: CMS.liveView.InsertUnitHelper.getInstance().getInsertableSiblings(unit, this.mode)
            };
            // Breaking the references
            return SB.util.cloneObject(returnValue);
        }
        return {
            children: [],
            siblings: []
        };
    },

    /**
     * Inserts the given module as unit in the context of the given owner unit at
     * the given position
     *
     * @param {String} moduleId The id of the module which should be inserted
     * @param {String} position The position where it should be inserted (above,
     *      below, inside)
     * @param {String} ownerUnitId The id of the unit in whose context the new
     *      unit should be inserted
     * @return {Boolean} Whether the insertion was successful or not
     */
    insertUnit: function (moduleId, position, ownerUnitId) {
        var success = false;
        if (this.isValidUnitId(ownerUnitId)) {
            var ownerUnit = this.iframeWorkbenchPanel.unitStore.getById(ownerUnitId);
            var moduleStore = CMS.data.StoreManager.get('module', ownerUnit.store);
            var unit = moduleStore.getById(moduleId).createUnit();
            if (unit) {
                var cfg = CMS.liveView.InsertUnitHelper.getInstance().getInsertObject(unit, position, ownerUnit);
                if (cfg) {
                    this.iframeWorkbenchPanel.insertUnit(cfg);
                    success = true;
                }
            } else {
                CMS.console.warn(CMS.i18n('Zur Id {moduleId} konnte keine Modul gefunden werden').replace('{moduleId}', moduleId));
            }
        }
        return success;
    },

    /**
     * Returns the rgba values for the given color id
     *
     * @param {String} colorId The id of the color
     * @return {String} The color as rgba string or 'none'
     */
    getColorById: function (colorId) {
        if (!colorId) {
            return 'none';
        }

        var returnValue;
        var website = CMS.data.WebsiteStore.getInstance().getById(this.iframeWorkbenchPanel.websiteId);
        if (website) {
            var colors = website.get('colorscheme');
            Ext.each(colors, function (color) {
                if (color.id === colorId) {
                    returnValue = color.value;
                }
            });
        }

        if (!returnValue) {
            // The colorscheme does not contain the given id
            // -> try to extract the id which is coded within the id
            returnValue = SB.util.getColorFromColorId(colorId);
        }

        return returnValue;
    },

    /**
     * Applies the given RichTextEditor config object to the default config
     * set in the module of the unit for a specific section of a unit
     *
     * @param {String} unitId The id of the unit
     * @param {String} section The name of the section
     * @param {Object} config The RichTextEditor config object which will be
     *      merged with the default config
     */
    applyRichTextEditorConfig: function (unitId, section, config) {
        this.iframeWorkbenchPanel.applyRichTextEditorConfig(unitId, section, config);
    },

    /**
     * Creates a preview ticket for the current page or template so external users can preview it
     * without the need of having a regular user account.
     *
     * @param {Function} callback The callback function to be called after the ticket has been created
     * @param {Object} options (Optional) Configuration of the ticket, e.g. to protect it with credentials
     *      e.g. {
     *          protect: false,
     *          credentials: {
     *              username: 'test',
     *              password: 'test'
     *          },
     *          ticketLifetime: 60,
     *          sessionLifetime: 60,
     *          remainingCalls: 1
     *      }
     * @param {Object} scope (Optional) The object in whose scope the callback function should be executed
     */
    createPreviewTicket: function (callback, options, scope) {
        this.previewTicketHelper.createPreviewTicket(this.iframeWorkbenchPanel.websiteId, this.mode, this.iframeWorkbenchPanel.record.id, callback, options, scope);
    },

    /**
     * Updates the configuration of a form field (e.g. the options of a combobox/drop-down element)
     *
     * @param {String} unitId The id of the unit
     * @param {String} key The variable name of the form field
     * @param {Object} config The form field config object
     */
    updateFormFieldConfig: function (unitId, key, config) {
        if (this.isValidUnitId(unitId)) {
            this.iframeWorkbenchPanel.updateFormFieldConfig(unitId, key, config);
            return true;
        } else {
            return false;
        }
    },

    /**
     * Processes all units which were inserted before the current page/template reload.
     *
     * @param {Object} filter (optional) A filter object, e.g. to filter by moduleId
     * @param {Function} callback (Optional) The callback function to be called for each inserted unit
     * @param {Object} scope (Optional) The object in whose scope the callback function should be executed
     * @return {Array} An array with objects describing the inserted units
     */
    processInsertedUnits: function (filter, callback, scope) {
        if (Ext.isFunction(filter)) {
            scope = callback;
            callback = filter;
        } else {
            if (!Ext.isObject(filter)) {
                filter = null;
            }
        }

        var configArray = [];

        // decode and maybe filter all inserted units
        Ext.each(this.insertedUnitsBuffer[this.iframeOnLoadCounter - 1], function (insertedUnit) {
            insertedUnit = Ext.decode(insertedUnit);

            if (filter && filter.moduleId) {
                if (insertedUnit.moduleId == filter.moduleId) {
                    configArray.push(insertedUnit);
                }
            } else {
                configArray.push(insertedUnit);
            }
        });

        // if available, call callback for each unit
        if (Ext.isFunction(callback)) {
            Ext.each(configArray, function (insertedUnit) {
                if (Ext.isObject(scope)) {
                    callback.call(scope, insertedUnit);
                } else {
                    callback(insertedUnit);
                }
            }, this);

            callback = null;
            scope = null;
        }

        return configArray;
    },

    /**
     * Returns the state of the visual helpers (if they should be visible/hidden)
     *
     * @return {Object} visual helpers state object
     * @private
     */
    getVisualHelpersState: function () {
        return SB.util.cloneObject(this.visualHelpersState);
    },

    /**
     * Gives the text in current application language for given input
     *
     * @param {Object} input An object with texts for all available languages (e.g.
     *      {de: "Hallo Welt!", en: "Hello World!"}
     * @return {String|Mixed} The text for the current application language of
     *      whatever the input was if the input was not translatable
     */
    translateInput: function (input) {
        return CMS.translateInput(input);
    },

    /**
     * Get configured resolutions (breakpoints) for responsive CSS
     *
     * @returns {Object}
     */
    getResolutions: function () {
        var resolutions = {};
        var website = CMS.data.WebsiteStore.getInstance().getById(this.iframeWorkbenchPanel.websiteId);
        if (website) {
            resolutions = website.get('resolutions');
        }
        return SB.util.cloneObject(resolutions);
    },

    /**
     * Returns the id currently active resolution
     *
     * @returns {String} The id of the current resolution breakpoint
     */
    getCurrentResolution: function () {
        return this.iframeWorkbenchPanel.getCurrentResolution().id;
    },

    /**
     * Get image URL by Media DB item
     *
     * @param {String} mediaDbId
     * @param {Number} [width]
     * @param {Number} [quality]
     * @returns {String}
     */
    getImageUrl: function (mediaDbId, width, quality) {
        var chainString;
        var chain = [];
        var height = 0;

        quality = Ext.isNumber(quality) ? Number(quality) : null;
        width = Ext.isNumber(width) ? Number(width) : 0;


        if (width > 0) {
            chain.push('r' + width + '_' + height + '_t3'); // t3 ==> resizeScale
        }
        if (quality) {
            chain.push('q' + quality);
        }

        if (chain.length > 0) {
            chainString = chain.join('.');
        }

        return this.mediaItemUrl(mediaDbId, 'image', chainString);
    },

    /**
     * Get Media URL by Media DB item
     *
     * @param {String} mediaDbId
     * @param {Boolean} [download] - weather the response should download the file (content-disposition: attachment)
     * @returns {String}
     */
    getMediaUrl: function (mediaDbId, download) {
        var type = download ? 'download' : 'stream';
        return this.mediaItemUrl(mediaDbId, type);
    },

    /***********************************************
     * Helper methods
     ***********************************************/

    /**
     * @private
     * @param {String} mediaDbId
     * @param {String} type download, image, stream
     * @param {String} [chain] aka operations
     * @returns {String}
     */
    mediaItemUrl: function (mediaDbId, type, chain) {
        var websiteId = this.iframeWorkbenchPanel.websiteId;
        var mediaStore = CMS.data.StoreManager.get('media', websiteId);

        // try to get media record, this only works if media store is loaded
        // if not, we assume the file is new (which should work, but can invalidate caches)
        var mediaRecord = mediaStore.getById(mediaDbId);
        var ulDate = (mediaRecord && mediaRecord.get('dateUploaded')) || Date.now();

        var paramsObj = {
            id: mediaDbId,
            type: type,
            websiteId: websiteId,
            date: ulDate
        };

        if (chain) {
            paramsObj.chain = chain;
        }

        return CMS.config.urls.mediaCdnBaseUrl + encodeURIComponent(JSON.stringify(paramsObj));
    },

    /**
     * Fires the CMS API event.
     * @private
     *
     * @param {String} eventName The name of the event
     * @param {String|Mixed} [unitId] The unit which has triggered the event or its id
     * @param {Object} eventArgs The configuration object
     */
    fireAPIEvent: function (eventName, unitId, eventArgs) {
        this.iframeWorkbenchPanel.resetRendering();

        // CMS.console.log('[PluginInstance] fireAPIEvent', eventName, unitId, eventArgs, this.iframeDomReady);

        if (!this.iframeDomReady) {
            this.eventBuffer = this.eventBuffer || {};
            this.eventBuffer[eventName + unitId] = {
                name: eventName,
                unitId: unitId,
                args: eventArgs
            };

            return;
        }

        // arguments shift
        if (!eventArgs && Ext.isDefined(unitId)) {
            eventArgs = unitId;
            unitId = null;
        }

        // fill unitObj if we are called with a unitId
        var unitObj = {};
        if (unitId) {
            // if called with unit object, unwrap the id
            if (typeof unitId === 'object') {
                unitId = unitId.id;
            }
            // get unit descriptor (same as {@link CMS.api.API.get())
            unitObj = this.getUnitConfig(unitId, false);
        }

        // This is done to break any references and prevent memory leaks
        var eventArgsObj = JSON.stringify(eventArgs);
        unitObj = JSON.stringify(unitObj);

        try {
            this.iframeWindow.CMS.fireEvent(eventName, unitId, eventArgsObj, unitObj);
        } catch (e) {
            // display error
            CMS.console.error('Error in Module Event Handler: ', e.stack);
            // log error
            e.cmstitle = CMS.i18n('Laufzeit-Fehler in Modul-Implementierung');
            CMS.app.ErrorManager.push(e);
        }
    },

    /**
     * Processes all events which occured while the iframe was loading
     * @private
     */
    processBufferedEvents: function () {
        // re-fire all buffered events
        Ext.iterate(this.eventBuffer, function (key, event) {
            CMS.console.log('[PluginInstance] processBufferedEvents', event.name, event.unitId, event.args);
            this.fireEvent(event.name, event.unitId, event.args);
        }, this);
        // clear buffer
        this.eventBuffer = null;
    },

    /**
     * Checks if the given id has a more or less sane format and if there is a
     * corresponding unit
     * @private
     *
     * @param {String} unitId The id of a unit (hopefully)
     * @param {Boolean} showWarning (Optional) Whether console.warn should be
     *      called or not
     * @return {Boolean} Whether the id is valid or not
     */
    isValidUnitId: function (unitId, showWarning) {
        return this.unitHelper.isValidUnitId(unitId, showWarning);
    },

    /**
     * Returns an object which has all the properties of a unit which might
     * be of interest to a module developer
     * @private
     *
     * @param {CMS.data.UnitRecord} unit The unit whose configuration should be
     *      computed
     * @param {Boolean} [excludeFormValues] exclude the formValues object to
     *      speed up fetch of data
     * @return {Object} An object describing the unit
     */
    computeUnitDescriptor: function (unit, excludeFormValues) {
        return this.unitHelper.computeUnitDescriptor(unit, excludeFormValues);
    },

    /**
     * Moves the given unit up or down
     * @private
     *
     * @param {String} unitId The id of the unit which should be moved
     * @param {String} direction The direction in which the unit should be
     *      moved (up or down)
     * @return {Boolean} Whether moving the unit was successful or not
     */
    moveUnit: function (unitId, direction) {
        if (this.isValidUnitId(unitId)) {
            return this.iframeWorkbenchPanel.moveUnit({
                direction: direction,
                unitId: unitId
            });
        } else {
            return false;
        }
    },

    /**
     * Fires the specified mouse event and passes the unit id
     * and editable flag along
     * @private
     *
     * @param {String} unitId The id of the unit which has triggered the event
     * @param {String} eventName The name of the mouse event
     */
    fireMouseEvent: function (unitId, eventName) {
        var unit = this.iframeWorkbenchPanel.unitStore.getById(unitId);
        this.fireAPIEvent(eventName, unit, {
            unitId: unitId,
            editable: unit && unit.isEditableInMode(this.mode)
        });
    },

    /***********************************************
     * Event handlers
     ***********************************************/

    /**
     * Setup Events By Mapping - ensures that the given listeners are always called first!
     * @private
     *
     * @param {Object} eventMapping - key->value of event mapping
     */
    setupEventsByMapping: function (eventMapping) {
        var wbp = this.iframeWorkbenchPanel;
        Ext.iterate(eventMapping, function (key, value) {
            if (typeof key === 'string') {
                key = key.toLowerCase();
                var oldListeners = null;
                if (wbp.events[key]) {
                    // make sure ApiPlugin's listeners are always called first.
                    oldListeners = wbp.events[key].listeners;
                    wbp.events[key].listeners = [];
                }
                wbp.on(key, value, this);
                if (oldListeners) {
                    wbp.events[key].listeners.push.apply(wbp.events[key].listeners, oldListeners);
                    oldListeners = null;
                }
            }
        }, this);
    },

    /**
     * Handler for the 'beforereplace' event of the iframe which is fire when
     * the iframe's src is replaced
     * @private
     */
    iframeBeforereplaceHandler: function () {
        // disable the unit editor to prevent changes while the iframe is busy loading
        var unitEditorEl = this.iframeWorkbenchPanel.unitEditor.getEl();
        if (unitEditorEl) {
            unitEditorEl.mask();
        }
    },

    /**
     * Gets the window object of the render iframe and listens
     * to the hide or show events of the Ext.Component object.
     * @private
     *
     * @param {String} unitId (optional) The id of the unit which has been
     *      rendered
     */
    iframeRenderHandler: function (unitId) {
        // We are only setting up the event if the whole iframe has been rendered
        // but not if only one unit has been rendered.
        if (!unitId) {
            var win = this.iframeWindow = this.editableIframe.getFrameWindow();

            if (this.iframeWindow.CMS && this.iframeWorkbenchPanel) {
                // Setups up all event listeners defined in the eventMapping object
                console.log('[PluginInstance] Setting up event listeners');
                this.setupEventsByMapping(this.eventMapping);
                this.listenersSetup = true;

                var self = this;
                var unloadListener = function () {
                    console.log('[PluginInstance] reacting to the unload event');
                    if (win.removeEventListener) {
                        win.removeEventListener('unload', unloadListener, false);
                    } else {
                        win.detachEvent('onunload', unloadListener);
                    }

                    // Removes all event listeners by iterating over the eventMapping object
                    Ext.iterate(self.eventMapping, function (key, value) {
                        if (typeof key === 'string') {
                            self.iframeWorkbenchPanel.un(key, value, this);
                        }
                    }, self);


                    self.iframeDomReady = false;
                    self.listenersSetup = false;

                    self = null;
                    win = null;
                };
                if (win.addEventListener) {
                    win.addEventListener('unload', unloadListener, false);
                } else {
                    win.attachEvent('onunload', unloadListener);
                }
            }
        }
        this.afterRender(unitId);
    },

    /**
     * Will get called when the iframes DOM is ready; cleans up the buffer for inserted units
     * @private
     */
    iframeDomReadyHandler: function () {
        console.log('[PluginInstance] iframeDomReadyHandler');
        this.iframeDomReady = true;
    },

    /**
     * Will get called when the iframe is fully loaded (all resources and scripts)
     * @private
     */
    iframeOnLoadHanlder: function () {
        console.log('[PluginInstance] iframeOnLoadHanlder');
        this.iframeOnLoadCounter++;

        // cleanup insertedUnitsBuffer
        Ext.iterate(this.insertedUnitsBuffer, function (key) {
            if (key < this.iframeOnLoadCounter - 1) {
                delete this.insertedUnitsBuffer[key];
            }
        }, this);

        this.processBufferedEvents();

        // re-enable the unit editor again after iframe has become ready again
        var unitEditor = this.iframeWorkbenchPanel.unitEditor;
        var unitEditorEl = unitEditor && unitEditor.getEl();
        if (unitEditorEl && !unitEditor.disabled) {
            unitEditorEl.unmask();
        }
    },

    /**
     * Resumes all delegation of methods calls from the iframe to the CMS system.
     * @private
     */
    showHandler: function () {
        window.CmsApi = this.apiInstance;
        this.iframeWindow.CMS.resumeDelegation();
    },

    /**
     * Suspends all delegation of methods calls from
     * the iframe to the CMS system.
     * @private
     */
    hideHandler: function () {
        this.iframeWindow.CMS.suspendDelegation();
    },

    /**
     * Informs the CMS object inside the iframe of a change of a unit.
     * @private
     *
     * @param {Object} config The configuration of the unit
     *      which has triggered the event.
     */
    formValueChangeHandler: function (config) {
        this.allowCallToPreventRendering = true;
        this.preventRenderingCalled = false;
        this.fireAPIEvent('formValueChange', config.unitId, config);
        if (this.preventRenderingCalled) {
            this.preventRenderingByFormValueChange = true;
        }
        this.allowCallToPreventRendering = false;
    },

    /**
     * Informs the CMS object inside the iframe that
     * a unit is about to be inserted.
     * @private
     *
     * @param {Object} config The configuration of the unit
     *      which has triggered the event.
     */
    beforeInsertUnitHandler: function (config) {
        this.fireAPIEvent('beforeInsertUnit', config.id, config);
    },

    /**
     * Saves the unit which is about to be inserted in the buffer.
     * @private
     *
     * @param {Object} config The configuration of the unit which has triggered the event.
     */
    beforeInsertUnitBufferHandler: function (config) {
        // This is done to break any references and prevent memory leaks
        config = JSON.stringify(config);

        // we need to buffer the inserted unit to be able to get it after the iframe reload
        console.log('[ApiPlugin] buffering beforeInsertUnit');
        var counter = this.iframeOnLoadCounter;
        this.insertedUnitsBuffer[counter] = this.insertedUnitsBuffer[counter] || [];
        this.insertedUnitsBuffer[counter].push(config);
    },

    /**
     * Informs the CMS object inside the iframe that the
     * specified unit was selected and that the old unit has
     * been deselected
     * @private
     *
     * @param {CMS.data.UnitRecord} unit The selected unit.
     */
    unitSelectHandler: function (unit) {
        if (this.currentUnit && unit.id !== this.currentUnit.id) {
            this.fireAPIEvent('unitDeselect', this.currentUnit, {
                unitId: this.currentUnit.id,
                editable: this.currentUnit.isEditableInMode(this.mode)
            });
        }
        this.currentUnit = unit;
        this.fireAPIEvent('unitSelect', unit, {
            unitId: unit.id,
            editable: unit.isEditableInMode(this.mode)
        });
    },

    /**
     * Informs the CMS object inside the iframe that the
     * specified unit was selected inside the tree
     * @private
     *
     * @param {String} unitId The id of the unit which has been selected inside
     *      the tree
     */
    unitTreeSelectHandler: function (unitId) {
        var unit = this.iframeWorkbenchPanel.unitStore.getById(unitId);
        if (this.currentUnit && unitId !== this.currentUnit.id) {
            this.fireAPIEvent('unitTreeDeselect', this.currentUnit, {
                unit: this.currentUnit.id,
                editable: this.currentUnit.isEditableInMode(this.mode)
            });
        }
        this.currentUnit = unit;
        this.fireAPIEvent('unitTreeSelect', unit, {
            unitId: unit.id,
            editable: unit.isEditableInMode(this.mode)
        });
    },

    /**
     * Informs the CMS object inside the iframe that the
     * specified unit was selected inside the iframe
     * @private
     *
     * @param {String} unitId The id of the unit which has been selected inside
     *      the iframe
     */
    unitFrameSelectHandler: function (unitId) {
        this.fireMouseEvent(unitId, 'unitFrameSelect');
    },

    /**
     * Informs the CMS object inside the iframe that the
     * specified unit is no longer selected inside the iframe
     * @private
     *
     * @param {String} unitId The id of the unit which has been previously
     *      selected inside the iframe
     */
    unitFrameDeselectHandler: function (unitId) {
        this.fireMouseEvent(unitId, 'unitFrameDeselect');
    },

    /**
     * @private
     * Informs the CMS object inside the iframe that the
     * mouse is hovering over the specified unit inside
     * the tree
     * @param {String} unitId The id of the
     * unit over which the mouse is hovering
     */
    treeMouseEnterHandler: function (unitId) {
        this.fireMouseEvent(unitId, 'treeMouseEnter');
    },

    /**
     * Informs the CMS object inside the iframe that the mouse is no longer
     * hovering over the specified unit inside the tree
     * @private
     *
     * @param {String} unitId The id of the unit over which the mouse was hovering
     */
    treeMouseOutHandler: function (unitId) {
        this.fireMouseEvent(unitId, 'treeMouseOut');
    },

    /**
     * Informs the CMS object inside the iframe that the mouse is hovering over the
     * specified unit inside the iframe
     * @private
     *
     * @param {String} unitId The id of the unit over which the mouse is hovering
     */
    frameMouseEnterHandler: function (unitId) {
        this.fireMouseEvent(unitId, 'frameMouseEnter');
    },

    /**
     * Informs the CMS object inside the iframe that the mouse is no longer
     * hovering over the specified unit inside the iframe
     * @private
     *
     * @param {String} unitId The id of the unit over which the mouse was hovering
     */
    frameMouseOutHandler: function (unitId) {
        this.fireMouseEvent(unitId, 'frameMouseOut');
    },

    /**
     * Informs the CMS object inside the iframe that the
     * any editors defined by the module developer associated
     * with the current unit should now be opened.
     * @private
     *
     * @param {Object} eventArgs
     */
    openEditorHandler: function (eventArgs) {
        this.fireAPIEvent('showEditor', eventArgs.unitId, eventArgs.unitId);
    },

    /**
     * Informs the CMS object inside the iframe that the
     * specified unit is about to be re-renderd
     * @private
     *
     * @param {Object} cfg The object which contains the id of the unit
     */
    beforeRenderHandler: function (cfg) {
        this.allowCallToPreventRendering = true;
        this.preventRenderingCalled = false;
        if (cfg && cfg.unitId) {
            this.fireAPIEvent('beforeRenderUnit', cfg.unitId, cfg.unitId);
        } else {
            this.fireAPIEvent('beforeRenderPage');
        }
        if (this.preventRenderingCalled || this.preventRenderingByFormValueChange) {
            this.iframeWorkbenchPanel.preventRendering();
            this.preventRenderingByFormValueChange = false;
        }
        this.allowCallToPreventRendering = false;
    },

    /**
     * Informs the CMS object inside the iframe that the specified unit is about
     * to be moved
     * @private
     *
     * @param {String} cfg The object which contains the id of the unit, the new
     *      parent, the new next and previous sibling and the new index of the unit
     *      which is about to be moved
     */
    beforeMoveHandler: function (cfg) {
        this.fireAPIEvent('beforeMoveUnit', cfg);
    },

    /**
     * Informs the CMS object inside the iframe that the
     * specified unit is about to be removed
     * @private
     *
     * @param {String} unitId The id of the unit which is about to be removed
     */
    beforeRemoveHandler: function (unitId) {
        this.fireAPIEvent('beforeRemoveUnit', unitId);
    },

    /**
     * Informs the CMS object inside the iframe that the
     * specified unit has been removed
     * @private
     *
     * @param {String} unitId The id of the unit which has been removed
     */
    afterRemoveHandler: function (unitId) {
        this.fireAPIEvent('afterRemoveUnit', unitId);
    },

    /**
     * Informs the CMS object inside the iframe that the specified unit has been
     * re-rendered
     * @private
     *
     * @param {String} unitId (optional) The id of the unit which has been re-rendered
     */
    afterRender: function (unitId) {
        if (this.iframeWindow && this.iframeWindow.CMS) {
            if (unitId) {
                this.fireAPIEvent('afterRenderUnit', unitId, unitId);
            } else {
                this.fireAPIEvent('afterRenderPage');
            }
        }
    },

    /**
     * Informs the CMS object inside the iframe that the visual helpers should
     * be visible/hidden
     * @private
     *
     * @param visible {Boolean} visible true if the helpers should be visible
     * @param toolbar {Boolean} show the toolbar if true
     */
    visualHelpersHandler: function (visible, toolbar) {
        this.visualHelpersState = {
            enabled: visible,
            toolbar: toolbar
        };
        this.fireAPIEvent('visualHelpersStateChange', this.visualHelpersState);
    },

    /**
     * Informs the CMS object inside the iframe that the resolution has been changed
     *
     * @param Object resolutionId The data object of the new resolution
     * @param Array allResData An array containing the data of all available resolutions
     */
    resolutionChangeHandler: function (resolutionId, allResData) {
        var self = this;
        setTimeout(function () {
            self.fireAPIEvent('resolutionChange', {
                newResolutionId: resolutionId,
                allResolutions: allResData
            });
        }, 0);
    },

    destroy: function () {
        if (this.persistentListenersSetup) {
            // Removes all persistent event listeners
            Ext.iterate(this.eventMappingPersistent, function (key, value) {
                if (typeof key === 'string') {
                    this.iframeWorkbenchPanel.un(key, value, this);
                }
            }, this);
        }

        this.editableIframe.un('domready', this.iframeDomReadyHandler, this);
        this.editableIframe.un('onload', this.iframeOnLoadHanlder, this);
        this.editableIframe.un('rendered', this.iframeRenderHandler, this);
        this.purgeListeners();
        this.apiInstance.destroy();
        this.unitHelper.purgeListeners();
        this.unitHelper = null;
        this.previewTicketHelper.destroy();
        this.previewTicketHelper = null;
        this.editableIframe = null;
        this.iframeWorkbenchPanel = null;
        this.insertedUnitsBuffer = null;
    }
});

CMS.api.ApiPlugin = {
    /**
     * Gets called automatically by the Ext.ComponentMgr.createPlugin
     * method.
     */
    init: function (self) {
        return new CMS.api.PluginInstance(self);
    }
};

Ext.preg('CmsApi', CMS.api.ApiPlugin);
