define(['CMS', 'rz_root/notlive/js/baseJsModule'], function (CMS, JsModule) {
    // function to generate and apply the custom RTE config
    var setRTEConfig = function (unitId) {
        var unit = CMS.get(unitId);

        // generate custom styles from unit config
        var rteStyles = [];
        var numberOfHeadlines = 6;
        var i;

        for (i = 1; i <= numberOfHeadlines; i++) {
            if (unit.formValues['enableRteHeadline' + i].value) {
                rteStyles.push({
                    label: unit.formValues['rteHeadline' + i + 'Title'].value,
                    element: 'h' + i,
                    classes: ''
                });
            }
        }

        var numberOfStyles = 4;
        for (i = 1; i <= numberOfStyles; i++) {
            if (unit.formValues['enableRteStyle' + i].value) {
                rteStyles.push({
                    label: unit.formValues['rteStyle' + i + 'Title'].value,
                    element: unit.formValues['rteStyle' + i + 'Element'].value,
                    classes: 'rteStyle' + i
                });
            }
        }

        CMS.applyRichTextEditorConfig(unitId, 'text', {
            bold: unit.formValues.enableRteBold.value,
            italic: unit.formValues.enableRteItalic.value,
            strikethrough: unit.formValues.enableRteStrikethrough.value,
            underline: unit.formValues.enableRteUnderline.value,
            subscript: unit.formValues.enableRteSubscript.value,
            superscript: unit.formValues.enableRteSuperscript.value,
            bullist: unit.formValues.enableRteList.value,
            numlist: unit.formValues.enableRteList.value,
            link: unit.formValues.enableRteLink.value,
            table: unit.formValues.enableRteTable.value,
            customStyles: rteStyles
        });
    };

    return JsModule.extend({

        initUnit: function (unitId) {
            var selectedUnit = CMS.getSelected(false);
            if (selectedUnit && selectedUnit.id === unitId) {
                setRTEConfig(unitId);
            }
        },

        onUnitSelect: function (config) {
            setRTEConfig(config.unitId);
        },

        onFormValueChange: function (config) {
            if (config.key.toLowerCase().indexOf('rte') >= 0) {
                var unitId = config.unitId;
                setRTEConfig(unitId);
                CMS.preventRendering();
            }
        }
    });
});
