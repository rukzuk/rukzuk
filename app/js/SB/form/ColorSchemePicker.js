Ext.ns('SB.form');

/**
* TwinTriggerComboBox wich allows chosing a color from a predefined colorscheme set
* @class SB.form.ColorSchemePicker
* @extends SB.form.TwinTriggerComboBox
*/
SB.form.ColorSchemePicker = Ext.extend(SB.form.TwinTriggerComboBox, {

    /**
    * @cfg {String} websiteId
    * Id of the current selected website
    */
    websiteId: '',

    /**
    * @property previewDiv
    * @type {Object}
    * Holds a reference to the preview div
    */
    previewDiv: null,

    /**
    * @property colorDiv
    * @type {Object}
    * Holds a reference to the color div
    */
    colorDiv: null,

    /**
    * @cfg {String} emptyText
    * The text which is display if no color
    * has been selected
    */
    emptyText: 'Bitte wählen...',

    /**
    * @cfg {String} unknownColor
    * The text which is display if a color
    * has been set which is not found in the scheme
    */
    unknownColor: 'Unbekannte Farbe',

    // set an initial value
    value: null,

    constructor: function (config) {
        //Apply constructor params
        Ext.apply(this, config);

        var SbTpl = '<tpl for=".">' +
                    '    <div class="x-combo-list-item">' +
                    '        <div class="ux-colorpicker-wrap" style="width:12px; height:12px; float:left; border:1px solid black;">' +
                    '            <div style="width: 100%; height: 100%; background-color: {value};"></div>' +
                    '        </div>' +
                    '        <div style="float:left; padding-left: 5px">{name}</div>' +
                    '    </div>' +
                    '</tpl>';

        Ext.apply(this, {
            name: 'color',
            triggerAction: 'all',
            emptyText: this.emptyText,
            mode: 'local',
            editable: false,
            forceSelection: true,
            valueField: 'id',
            displayField: 'name',
            tpl: SbTpl
        });
        SB.form.ColorSchemePicker.superclass.constructor.call(this);
    },

    initComponent: function () {
        try {
            var websiteStore = CMS.data.WebsiteStore.getInstance();
            var websiteRecord = websiteStore.getById(this.websiteId);
            this.store = new Ext.data.JsonStore({
                id: 0,
                fields: ['id', 'name', 'value'],
                data: websiteRecord.get('colorscheme')
            });
            this.mon(websiteStore, 'update', function () {
                this.store.loadData(websiteRecord.get('colorscheme'));
                if (!this.store.getById(this.value)) {
                    this.clearValue();
                } else {
                    // HACK to update the preview
                    this.setValue(this.value);
                }
            }, this);
        } catch (e) {
            console.warn('[ColorSchemePicker] could not get website data, use dummy data', e);
            this.store = new Ext.data.JsonStore({
                id: 0,
                fields: ['id', 'name', 'value'],
                data: []
            });
        }
        SB.form.ColorSchemePicker.superclass.initComponent.apply(this, arguments);
    },


    /**
    * Renders an hidden overlaying 12x12px div into the dropdownbox wich will be used to
    * preview the selected color.
    */
    onRender: function () {
        SB.form.ColorSchemePicker.superclass.onRender.apply(this, arguments);

        // adjust styles
        this.wrap.applyStyles({
            position: 'relative'
        });

        this.previewDiv = Ext.DomHelper.append(this.el.up('div.x-form-field-wrap'), {
            tag: 'div',
            cls: 'ux-colorpicker-wrap',
            style: 'display:none; position:absolute; left:4px; top:4px; width:12px; height:12px; border:1px solid black;'
        });

        // add color-preview-div
        this.colorDiv = Ext.DomHelper.append(this.previewDiv, {
            tag: 'div',
            style: 'width:100%; height:100%;'
        });
    },

    /**
    * Removes dom elements
    */
    onDestroy: function () {
        this.previewDiv = null;
        this.colorDiv = null;
        SB.form.ColorSchemePicker.superclass.onDestroy.apply(this, arguments);
    },

    /**
    * Displays and changes the color from the color-preview-div
    */
    setValue: function (value) {
        if (value && this.rendered) {
            var colorRecord = this.store.getById(value);
            var color;

            if (colorRecord) {
                color = colorRecord.get('value');
                this.updatePreview(color);
            } else {
                color = SB.color.parseColor(SB.util.getColorFromColorId(value));
                color = color && SB.color.hsvaToRgba(color);

                if (color) {
                    value = 'rgba(' + [color.r, color.g, color.b, color.a].join(', ') + ')';
                    this.updatePreview(value);
                } else {
                    this.updatePreview(null);
                    value = this.unknownColor;
                }

            }
        }
        SB.form.ColorSchemePicker.superclass.setValue.call(this, value);
    },

    /**
     * @private
     * updates the preview div with the given color
     * @param [color] color to be set as background of preview div, if not defined or null hides preview
     */
    updatePreview: function (color) {
        if (!color) {
            this.el.setStyle('padding-left', '3px');
            this.previewDiv.style.display = 'none';
        } else {
            this.el.setStyle('padding-left', '20px');
            this.previewDiv.style.display = '';
            this.colorDiv.style.backgroundColor = color;
        }
    },

    /**
    * Hides the color-preview-div
    */
    clearValue: function () {
        if (this.rendered) {
            this.updatePreview();
        }
        SB.form.ColorSchemePicker.superclass.clearValue.apply(this, arguments);
        // use null as empty value
        this.setValue(null);
    },

    /**
    * Returns the selected color ID(!)
    */
    getValue: function () {
        var colorRecord = this.store.getById(this.value);
        if (this.value && colorRecord) {
            return colorRecord.get('id');
        } else {
            return SB.form.ColorSchemePicker.superclass.getValue.apply(this, arguments);
        }
    }
});

Ext.reg('ux-colorschemepicker', SB.form.ColorSchemePicker);
