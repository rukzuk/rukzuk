Ext.ns('CMS.api');

/**
 * X-doc-API - available as global <b>CMS</b> object in the editor iframe
 *
 * @internal Abstraction layer for the API methods publicly available to content pages.
 *           The main functionality is provided by the underlying {@link CMS.api.ApiPlugin} instance.
 *           The plugin will make sure all methods of this class are copied to the content page's CMS object.
 *
 * @class CMS.api.API
 * @version 1.2 (2014-02-03)
 * @alias CMS
 */
CMS.api.API = Ext.extend(Ext.util.Observable, /** @lends CMS.api.API.prototype */ {

    /**
     * @property pluginInstance
     * @type CMS.api.ApiPlugin
     * @private
     */
    pluginInstance: null,

    constructor: function (plugin) {
        this.pluginInstance = plugin;
        CMS.api.API.superclass.constructor.apply(this, arguments);
        this.console = CMS.console;
    },

    /**
     * The names of the methods which can be called via the CMS
     * object inside the iframe.
     * @property methods
     * @type Array
     * @private
     */
    methods:  ['get', 'getSelected', 'set', 'setName', 'setInfo', 'setStyleSets', 'refresh', 'preventRendering', 'remove', 'openInsertWindow', 'openInsertExtensionMenu', 'moveUp', 'moveDown', 'duplicate', 'select', 'deselect', 'getInsertable', 'insert', 'openFormPanel', 'openTreePanel', 'getColorById', 'applyRichTextEditorConfig', 'createPreviewTicket', 'updateFormFieldConfig', 'processInsertedUnits', 'getVisualHelpersState', 'getModule', 'i18n', 'getResolutions', 'getCurrentResolution', 'getImageUrl', 'getAllUnitIds', 'getMediaUrl'],

    /**
     * Informs the CMS that the module developer wants
     * to stop the next reload from happening.
     * NOTE: Call to this function is only allowed in the following event handlers:
     *      {@link CMS.api.API.event:beforeRenderPage|beforeRenderPage}
     *      {@link CMS.api.API.event:formValueChange|formValueChange}
     */
    preventRendering: function () {
        this.pluginInstance.preventRendering();
    },

    /**
     * Gets the configuration of the unit.
     * @param {String} id The id of the unit
     * @param {Boolean} [includeFormValues=true] include the formValues
     * @return {Object} The configuration of the unit
     */
    get: function (id, includeFormValues) {
        return this.pluginInstance.getUnitConfig(id, includeFormValues);
    },

    /**
     * Returns the unit ids of <b>all</b> units of a specific module
     *
     * @param {String} moduleId - unit ids from the given module id
     * @returns {Array<String>}
     * @since 1.1 (2014-01-08)
     */
    getAllUnitIds: function (moduleId) {
        return this.pluginInstance.getUnitIds(moduleId);
    },

    /**
     * Gets the configuration of the currently selected unit.
     * @param {Boolean} [includeFormValues] include the formValues; default = true
     * @return {Object} The configuration of the unit
     */
    getSelected: function (includeFormValues) {
        return this.pluginInstance.getSelectedUnitConfig(includeFormValues);
    },

    /**
     * Gets meta-data of a module
     * @param {String} moduleId The id of the module
     * @return {Object} The configuration of the module - null if it fails
     */
    getModule: function (moduleId) {
        return this.pluginInstance.getModuleConfig(moduleId);
    },

    /**
     * Store the given key value pair in the configuration
     * of the specified unit and updates the editor window
     *
     * @param {String} id The id of the unit
     * @param {String} key The name of the unit property which is changed
     * @param {Mixed} value The new value of the unit property
     * @return {Boolean} Whether the key value pair could be successfully set
     */
    set: function (id, key, value) {
        return this.pluginInstance.setUnitConfig(id, key, value);
    },

    /**
     * Updates the specified unit's name
     *
     * @param {String} id The id of the unit
     * @param {String} name The new name of the unit
     * @return {Boolean} Whether the name could be successfully updated
     * @deprecated Does not effect the unit's name anymore; Use {@link CMS.api.API#setInfo} visualize unit settings
     */
    setName: function (id, name) {
        //
        return true;
    },

    /**
     * Allows to set meta information which should visualize unit
     * settings (replaces {@link CMS.api.API#setName})
     *
     * @param {String} id The id of the unit
     * @param {String} key The name of the meta property which is changed
     * @param {Mixed} value The new value of the meta property
     * @return {Boolean} Whether the key value pair could be successfully set
     */
    setInfo: function (id, key, value) {
        return this.pluginInstance.setUnitMetaInfo(id, key, value);
    },

    /**
     * Allows to set meta information which should visualize unit
     * settings (replaces {@link CMS.api.API#setName})
     *
     * @param {String} id The id of the unit
     * @param {String} key The name of the meta property which is changed
     * @param {Mixed} value The new value of the meta property
     * @return {Boolean} Whether the key value pair could be successfully set
     */
    setStyleSets: function (id, value) {
        return this.pluginInstance.setStyleSets(id, value);
    },

    /**
     * Re-renders the specified unit or if no unit id was
     * given the whole page.
     * @param {String} [unitId] The id of the
     * unit which should be re-rendered
     */
    refresh: function (unitId) {
        this.pluginInstance.refresh(unitId);
    },

    /**
     * Removes the specified unit.
     * @param {String} unitId The id of the
     * unit which is to be deleted.
     * @param {Boolean} [showConfirmation] Whether the
     * CMS should
     * ask the user for confirmation before removing the unit
     * @param {Boolean} [callback] The function
     * which will be executed when the unit is actually deleted
     * or not
     * @param {Object} [scope] The object in whose
     * scope the callback function should be executed
     */
    remove: function (unitId, showConfirmation, callback, scope) {
        this.pluginInstance.removeUnit(unitId, showConfirmation, callback, scope);
    },

    /**
     * Selects the specified unit in both tree and iframe.
     * @param {String} unitId The id of the unit which
     * should be selected
     */
    select: function (unitId) {
        this.pluginInstance.selectUnit(unitId);
    },

    /**
     * Deselects the specified unit in both tree and iframe.
     */
    deselect: function () {
        this.pluginInstance.deselectUnits();
    },

    /**
     * Tells the CMS to open the "Insert Unit" window
     * @param {String} [unitId] The id of the unit in whose
     * context the insert window should be opened
     * @param {Integer} [position] The default position choice in the dialog.
     * Possible values:<ul>
     <li>-1 - above</li>
     <li> 0 - inside</li>
     <li> 1 - below (default)</li>
     </ul>
     */
    openInsertWindow: function (unitId, position) {
        this.pluginInstance.openInsertWindow(unitId, position);
    },

    /**
     * Opens the Insert Extension (Style) Menu
     */
    openInsertExtensionMenu: function () {
        this.pluginInstance.openInsertExtensionMenu();
    },

    /**
     * Opens the form group which corresponds to the given form group id.
     * If no argument is passed, it will just bring the form panel to front.
     * @param {String} [varName] The name of the unit property whose
     * form group has to be opened
     * @param {Boolean} [showPanel=true] <tt>false</tt> to prevent activation of the containing form panel.
     */
    openFormPanel: function (varName, showPanel) {
        if (typeof showPanel == 'string') {
            console.warn('Invalid API call openFormPanel(', varName, ',', showPanel, '). Ignoring first argument.');
            varName = showPanel;
            showPanel = true;
        }
        this.pluginInstance.openFormPanel(varName, showPanel !== false);
    },

    /**
     * Opens the structure tree panel
     */
    openTreePanel: function () {
        this.pluginInstance.openTreePanel();
    },

    /**
     * Moves the unit up in the structure tree
     * @param unitId
     * @return {Boolean} Whether the unit could successfully be moved or not
     */
    moveUp: function (unitId) {
        return this.pluginInstance.moveUp(unitId);
    },

    /**
     * Moves the unit down in the structure tree
     * @param unitId
     * @return {Boolean} Whether the unit could successfully be moved or not
     */
    moveDown: function (unitId) {
        return this.pluginInstance.moveDown(unitId);
    },

    /**
     * Duplicates the given unit
     * @param {String} unitId The id of the unit which is to be duplicated
     * @return {Boolean} Whether the duplication was successful or not
     */
    duplicate: function (unitId) {
        return this.pluginInstance.duplicate(unitId);
    },

    /**
     * Returns the sibling and child units of the given unit
     * which can be inserted (TODO: this is only valid in the page context? Are these Ghost Units?)
     * @param {String} unitId The id of the unit in whose context
     * units are going to be inserted
     * @return {Object} The insertable units
     */
    getInsertable: function (unitId) {
        return this.pluginInstance.getInsertableUnits(unitId);
    },

    /**
     * Inserts the given unit in the context of the given owner unit at
     * the given position
     * @param {String} unitId The id of the unit which should be inserted
     * @param {String} position The position where it should be inserted
     * (above, below, inside)
     * @param {String} ownerUnitId The id of the unit in whose context
     * the new unit should be inserted
     * @return {Boolean} Whether the insertion was successful or not
     */
    insert: function (unitId, position, ownerUnitId) {
        return this.pluginInstance.insertUnit(unitId, position, ownerUnitId);
    },

    /**
     * Returns the rgba values for the given color id
     * @param {String} colorId The id of the color
     * @return {String} The color as rgba string
     */
    getColorById: function (colorId) {
        return this.pluginInstance.getColorById(colorId);
    },

    /**
     * Applies the given RichTextEditor config object to the default config
     * set in the module of the unit for a specific section of a unit
     * @param {String} unitId The id of the unit
     * @param {String} section The name of the section
     * @param {Object} config The RichTextEditor config object which will be
     * merged with the default config
     *
     * @example
     CMS.applyRichTextEditorConfig('MUNIT-9b6e6080-c9df-4754-a688-5a5c347810e7-MUNIT', 'text', {
       bold: false,
       italic: true,
       customStyles: [{
         label: 'Headline 1',
         element: 'h1',
         classes: ''
       }, {
         label: 'Headline 2',
         element: 'h2',
         classes: ''
       }, {
         label: 'API-Style',
         element: 'p',
         classes: 'myAPIStyle'
       }]
     });

     // Another Example with all possible properties
     CMS.applyRichTextEditorConfig('MUNIT-9b6e6080-c9df-4754-a688-5a5c347810e7-MUNIT', 'text', {
       bold: true,
       strikethrough: true,
       italic: true,
       subscript: true,
       underline: true,
       superscript: true,
       bullist: true,
       link: true,
       numlist: true,
       table: true,
       enterKey: "paragraph", // none|paragraph|linebreak
       customStyles: [{
         label: "Headline 1",
         element": "h1",
         classes": "myClass"
       }],
       linkTargets: [
         ["", "Same Window"],
         ["_blank", "New Window"]
       ]
     });
     */
    applyRichTextEditorConfig: function (unitId, section, config) {
        this.pluginInstance.applyRichTextEditorConfig(unitId, section, config);
    },

    /**
     * @callback CMS.api.API~createTicketCallback
     * @param {Object} ticket resulting ticket object
     * @param {String} ticket.id - ticket id
     * @param {String} ticket.url - the most important part of the ticket, the url
     * @param {Boolean} ticket.protect
     * @param {Object} ticket.credentials
     * @param {String} ticket.credentials.username
     * @param {String} ticket.credentials.password
     * @param {Number} ticket.ticketLifetime
     * @param {Number} ticket.sessionLifetime
     * @param {Number} ticket.remainingCalls
     */

    /**
     * Creates a preview ticket for the current page or template so external users can preview it
     * without the need of having a regular user account.
     * @param {CMS.api.API~createTicketCallback} callback The callback function to be called after the ticket has been created
     * @param {Object} [options] Configuration of the ticket, e.g. to protect it with credentials
     * @param {Boolean} options.protect
     * @param {Object} options.credentials
     * @param {String} options.credentials.username
     * @param {String} options.credentials.password
     * @param {Number} options.ticketLifetime
     * @param {Number} options.sessionLifetime
     * @param {Number} options.remainingCalls
     * @example
     CMS.createPreviewTicket(function (ticket) { alert(ticket.url); }, {
       protect: false,
       credentials: {
         username: 'test',
         password: 'test'
       },
       ticketLifetime: 60,
       sessionLifetime: 60,
       remainingCalls: 1
     }, this);
     * @param {Object} [scope] The object in whose scope the callback function should be executed
     */
    createPreviewTicket: function (callback, options, scope) {
        this.pluginInstance.createPreviewTicket(callback, options, scope);
    },

    /**
     * Updates the configuration of a form field (e.g. the options of a combobox/drop-down element)
     * @param {String} unitId The id of the unit
     * @param {String} key The variable name of the form field
     * @param {Object} config The form field config object
     *
     * @example
     CMS.updateFormFieldConfig(unitId, 'additionalSelector', {
       fieldLabel: 'Filter',
       options: [
         ['.text a', 'Link'],
         ['.text p', 'Text']
       ]
     });
     *
     */
    updateFormFieldConfig: function (unitId, key, config) {
        this.pluginInstance.updateFormFieldConfig(unitId, key, config);
    },

    /**
     * Processes all units which were inserted before the current page/template reload.
     * @deprecated Use {@link CMS.api.API#getAllUnitIds} after a reload
     * @param {Object} [filter] A filter object, e.g. to filter by moduleId
     * @param {Function} [callback] The callback function to be called for each inserted unit
     * @param {Object} [scope] The object in whose scope the callback function should be executed
     * @return {Array} An array with objects describing the inserted units
     */
    processInsertedUnits: function (filter, callback, scope) {
        return this.pluginInstance.processInsertedUnits(filter, callback, scope);
    },

    /**
     * Returns the state of the visual helpers (if they should be visible/hidden)
     * @return {Object} visual helpers state object
     */
    getVisualHelpersState: function () {
        return this.pluginInstance.getVisualHelpersState();
    },

    /**
     * Gives the text in current application language for given input
     * @param {Object} input An object with texts for all available languages (e.g.
     *      {de: "Hallo Welt!", en: "Hello World!"}
     * @return {String|Mixed} The text for the current application language of
     *      whatever the input was if the input was not translatable
     */
    i18n: function (input) {
        return this.pluginInstance.translateInput(input);
    },

    /**
     * @typedef ResolutionObject
     * @type {Object}
     * @property {String} name Name of the breakpoint
     * @property {number} width The resolution width
     * @property {String} id The id of the resolution
     */

    /**
     * @typedef Resolutions
     * @type {Object}
     * @property {Boolean} enabled - Weather responsive breakpoints are enabled or not
     * @property {Array<ResolutionObject>} data - list of resolution objects
     */

    /**
     * Get configured resolutions (breakpoints) for responsive CSS
     * @returns {Resolutions} resolutions
     */
    getResolutions: function () {
        return this.pluginInstance.getResolutions();
    },

    /**
     * Returns the id currently active resolution
     * @returns {String} The id of the current resolution breakpoint
     */
    getCurrentResolution: function () {
        return this.pluginInstance.getCurrentResolution();
    },

    /**
     * Get image URL by Media DB item
     * @param {String} mediaDbId - for example: MDB-uuid-MDB
     * @param {Number} [width] - width of the image; 0 = original size (height is determined automatically)
     * @param {Number} [quality] - quality 0 (bad) - 100 (best) - works only on formats that support it, like jpg
     * @returns {String}
     */
    getImageUrl: function (mediaDbId, width, quality) {
        return this.pluginInstance.getImageUrl(mediaDbId, width, quality);
    },

    /**
     * Get Media URL by Media DB ID
     *
     * @param {String} mediaDbId
     * @param {Boolean} [download] - weather the response should download the file (content-disposition: attachment)
     * @since 1.2 (2014-02-03)
     * @returns {String}
     */
    getMediaUrl: function (mediaDbId, download) {
        return this.pluginInstance.getImageUrl(mediaDbId, download);
    },

    destroy: function () {
        this.purgeListeners();
        this.pluginInstance = null;
        this.console = null;
    }

    /**
     * Remove listener for a CMS Event
     * @method un
     * @memberOf CMS.api.API.prototype
     * @param {String} event - The name of the event
     * @see {@link CMS.api.API#on}
     */

     /**
     * Register listener for a CMS Event
     * @method on
     * @memberOf CMS.api.API.prototype
     * @param {String} event - The name of the event, use one of the following:
     *        <ul>
     *          <li>{@link CMS.api.API.event:formValueChange|formValueChange}</li>
     *          <li>{@link CMS.api.API.event:unitSelect|unitSelect}</li>
     *          <li>{@link CMS.api.API.event:unitDeselect|unitDeselect}</li>
     *          <li>{@link CMS.api.API.event:showEditor|showEditor}</li>
     *          <li>{@link CMS.api.API.event:beforeRenderPage|beforeRenderPage}</li>
     *          <li>{@link CMS.api.API.event:afterRenderPage|afterRenderPage}</li>
     *          <li>{@link CMS.api.API.event:beforeRenderUnit|beforeRenderUnit}</li>
     *          <li>{@link CMS.api.API.event:afterRenderUnit|afterRenderUnit}</li>
     *          <li>{@link CMS.api.API.event:beforeInsertUnit|beforeInsertUnit}</li>
     *          <li>{@link CMS.api.API.event:beforeMoveUnit|beforeMoveUnit}</li>
     *          <li>{@link CMS.api.API.event:beforeRemoveUnit|beforeRemoveUnit}</li>
     *          <li>{@link CMS.api.API.event:afterRemoveUnit|afterRemoveUnit}</li>
     *          <li>{@link CMS.api.API.event:unitTreeSelect|unitTreeSelect}</li>
     *          <li>{@link CMS.api.API.event:unitTreeDeselect|unitTreeDeselect}</li>
     *          <li>{@link CMS.api.API.event:treeMouseEnter|treeMouseEnter}</li>
     *          <li>{@link CMS.api.API.event:treeMouseOut|treeMouseOut}</li>
     *          <li>{@link CMS.api.API.event:unitFrameSelect|unitFrameSelect}</li>
     *          <li>{@link CMS.api.API.event:unitFrameDeselect|unitFrameDeselect}</li>
     *          <li>{@link CMS.api.API.event:frameMouseEnter|frameMouseEnter}</li>
     *          <li>{@link CMS.api.API.event:frameMouseOut|frameMouseOut}</li>
     *          <li>{@link CMS.api.API.event:visualHelpersStateChange|visualHelpersStateChange}</li>
     *        </ul>
     * @param {CMS.api.API~EventFilter|String} [filter] The id of a unit or a filter object
     * @param {CMS.api.API~eventListenerCallback} handler The callback function
     * @param {Object} [scope] The execution context for the event handler
     */

    /**
     * @event formValueChange
     * @memberOf CMS.api.API
     * @desc A form value has changed. You can prevent the reload/unitReplace by calling {@link CMS.api.API#preventRendering}.
     * @param {Object} config - Config Object
     * @param {String} config.key - name of the form field (defined in moduleData.json or using visual module editor)
     * @param {Mixed} config.newValue - the current value of the form field
     * @param {Mixed} config.oldValue - the previous value before it was changed
     * @param {String} config.unitId - unitId where the event occurred
     */

    /**
     * @event unitSelect
     * @memberOf CMS.api.API
     * @param {Object} config - Config Object
     * @param {String} config.unitId - unitId where the event occurred
     * @param {Boolean} config.editable - tells weather the unit is editable in current mode
     */

    /**
     * @event unitDeselect
     * @memberOf CMS.api.API
     * @param {Object} config - Config Object
     * @param {String} config.unitId - unitId where the event occurred
     * @param {Boolean} config.editable - tells weather the unit is editable in current mode
     */

    /**
     * @event showEditor
     * @memberOf CMS.api.API
     * @param {String} unitId - id of the unit which is open in editor
     */

    /**
     * @event beforeRenderPage
     * @desc Page is about to reload. You can cancel this reload/unit-replace by calling {@link CMS.api.API#preventRendering}.
     * @memberOf CMS.api.API
     */

    /**
     * @event afterRenderPage
     * @deprecated Use {@link CMS.api.API#getAllUnitIds} after a reload
     * @memberOf CMS.api.API
     */

    /**
     * @event beforeRenderUnit
     * @memberOf CMS.api.API
     * @param {String} unitId - id of the unit which is open in editor
     */

    /**
     * @event afterRenderUnit
     * @memberOf CMS.api.API
     * @param {String} unitId - id of the unit which is open in editor
     */

    /**
     * @event beforeInsertUnit
     * @memberOf CMS.api.API
     * @param {Object} config - Config Object
     * @param {String} config.parentUnitId - id of the parent unit
     * @param {String} config.name - name of the inserted module
     * @param {String} config.websiteId - id of the website
     * @param {String} config.moduleId - id of the module which is about to be inserted (as a unit)
     * @param {String} config.unitId - unitId where the event occurred
     */
    /**
     * @event beforeMoveUnit
     * @memberOf CMS.api.API
     * @param {Object} config - Config Object
     * @param {String} config.parentUnitId - id of the parent unit
     * @param {String} config.index - position of the unit in its current level (0-based)
     * @param {String} config.previousSiblingId - id of the unit which is the previous sibling (up)
     * @param {String} config.nextSiblingId - id of the unit which is the next sibling (down)
     */

    /**
     * @event beforeRemoveUnit
     * @memberOf CMS.api.API
     * @param {String} unitId - id of the unit which is open in editor
     */

    /**
     * @event afterRemoveUnit
     * @memberOf CMS.api.API
     * @param {String} unitId - id of the unit which is open in editor
     */

    /**
     * @event unitTreeSelect
     * @memberOf CMS.api.API
     * @param {Object} config - Config Object
     * @param {String} config.unitId - unitId where the event occurred
     * @param {Boolean} config.editable - tells weather the unit is editable in current mode
     */

    /**
     * @event unitTreeDeselect
     * @memberOf CMS.api.API
     * @param {Object} config - Config Object
     * @param {String} config.unitId - unitId where the event occurred
     * @param {Boolean} config.editable - tells weather the unit is editable in current mode
     */

    /**
     * @event treeMouseEnter
     * @memberOf CMS.api.API
     * @param {Object} config - Config Object
     * @param {String} config.unitId - unitId where the event occurred
     * @param {Boolean} config.editable - tells weather the unit is editable in current mode
     */

    /**
     * @event treeMouseOut
     * @memberOf CMS.api.API
     * @param {Object} config - Config Object
     * @param {String} config.unitId - unitId where the event occurred
     * @param {Boolean} config.editable - tells weather the unit is editable in current mode
     */

    /**
     * @event unitFrameSelect
     * @memberOf CMS.api.API
     * @param {Object} config - Config Object
     * @param {String} config.unitId - unitId where the event occurred
     * @param {Boolean} config.editable - tells weather the unit is editable in current mode
     */

    /**
     * @event unitFrameDeselect
     * @memberOf CMS.api.API
     * @param {Object} config - Config Object
     * @param {String} config.unitId - unitId where the event occurred
     * @param {Boolean} config.editable - tells weather the unit is editable in current mode
     */

    /**
     * @event frameMouseEnter
     * @memberOf CMS.api.API
     * @param {Object} config - Config Object
     * @param {String} config.unitId - unitId where the event occurred
     * @param {Boolean} config.editable - tells weather the unit is editable in current mode
     */

    /**
     * @event frameMouseOut
     * @memberOf CMS.api.API
     * @param {Object} config - Config Object
     * @param {String} config.unitId - unitId where the event occurred
     * @param {Boolean} config.editable - tells weather the unit is editable in current mode
     */

    /**
     * @event visualHelpersStateChange
     * @memberOf CMS.api.API
     * @since 8db1dfb (2013-03-11)
     * @param {Object} config - Config Object
     * @param {Boolean} config.enabled - tells weather visual helpers are enabled or disabled
     */

    /**
     * Fired after changing the resolution in the client
     * @event resolutionChange
     * @memberOf CMS.api.API
     * @since 2014-1-21
     * @param {Object} config - A configuration Object
     * @param {Object} config.newResolutionId The data object of the new resolution
     * @param {Array} config.allResolutions An array containing the data of all available resolutions
     */

    /**
     * Event listener function
     * @callback CMS.api.API~eventListenerCallback
     * @param {Object} config - config object depends on type of event
     * @return undefined
     */


    /**
     * Event filter
     * @typedef CMS.api.API~EventFilter
     * @type {Object}
     * @property {String} moduleId - only listen to event of units from the given module id
     */

});
