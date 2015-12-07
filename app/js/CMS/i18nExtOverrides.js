Ext.ns('CMS');

/**
 * Internationalize Ext Components
 * Based on ext-lang-de.js
 */
CMS.i18nExtOverrides = function () {

    var langConfig = CMS.language.libs[CMS.app.lang].config;

    Ext.UpdateManager.defaults.indicatorText = '<div class="loading-indicator">'
                                               + CMS.i18n('Übertrage Daten…', 'framework.updateManagerLoadingIndicatorText')
                                               + '</div>';

    if (Ext.View) {
        Ext.View.prototype.emptyText = '';
    }

    if (Ext.grid.GridPanel) {
        Ext.grid.GridPanel.prototype.ddText = CMS.i18n('{0} Zeile(n) ausgewählt', 'framework.gridPanelDdText');
    }

    if (Ext.TabPanelItem) {
        Ext.TabPanelItem.prototype.closeText = CMS.i18n('Diesen Tab schließen', 'framework.tabPanelCloseText');
    }

    if (Ext.form.BasicForm) {
        Ext.form.BasicForm.prototype.waitTitle = CMS.i18n('Bitte warten…', 'framework.formPleaseWait');
    }

    if (Ext.form.Field) {
        Ext.form.Field.prototype.invalidText = CMS.i18n('Der Wert des Feldes ist nicht korrekt', 'framework.fieldInvalidText');
    }

    if (Ext.LoadMask) {
        Ext.LoadMask.prototype.msg = CMS.i18n('Übertrage Daten…', 'framework.loadMaskText');
    }

    Date.monthNames = langConfig.date.monthNames;

    Date.getShortMonthName = function (month) {
        return Date.monthNames[month].substring(0, 3);
    };

    // create monthNumbers based on position in array of monthNames
    Ext.each(Date.monthNames, function (month, idx) {
        Date.monthNumbers[Date.getShortMonthName(idx)] = idx;
    });

    Date.getMonthNumber = function (name) {
        return Date.monthNumbers[name.substring(0, 1).toUpperCase() + name.substring(1, 3).toLowerCase()];
    };

    Date.dayNames = langConfig.date.dayNames;

    Date.getShortDayName = function (day) {
        return Date.dayNames[day].substring(0, 3);
    };

    if (Ext.MessageBox) {
        Ext.MessageBox.buttonText = {
            ok: CMS.i18n('OK', 'framework.msgBoxOk'),
            cancel: CMS.i18n('Abbrechen', 'framework.msgBoxCancel'),
            yes: CMS.i18n('Ja', 'framework.msgBoxYes'),
            no: CMS.i18n('Nein', 'framework.msgBoxNo')
        };
    }

    if (Ext.util.Format) {
        Ext.util.Format.__number = Ext.util.Format.number;
        Ext.util.Format.number = function (v, format) {
            return Ext.util.Format.__number(v, format || langConfig.numberFormat);
        };

        Ext.util.Format.date = function (v, format) {
            if (!v) {
                return '';
            }
            if (!(v instanceof Date)) {
                v = new Date(Date.parse(v));
            }
            return v.dateFormat(format || langConfig.date.format);
        };
    }

    if (Ext.DatePicker) {
        Ext.apply(Ext.DatePicker.prototype, {
            todayText: CMS.i18n('Heute', 'framework.datePicker'),
            minText: CMS.i18n('Dieses Datum liegt von dem erstmöglichen Datum', 'framework.datePickerMin'),
            maxText: CMS.i18n('Dieses Datum liegt nach dem letztmöglichen Datum', 'framework.datePickerMax'),
            disabledDaysText: '',
            disabledDatesText: '',
            monthNames: Date.monthNames,
            dayNames: Date.dayNames,
            nextText: CMS.i18n('Nächster Monat (Strg/Control + Rechts)', 'framework.datePickerNext'),
            prevText: CMS.i18n('Vorheriger Monat (Strg/Control + Links)', 'framework.datePickerPrev'),
            monthYearText: CMS.i18n('Monat auswählen (Strg/Control + Hoch/Runter, um ein Jahr auszuwählen)', 'framework.datePickerMonthYear'),
            todayTip: CMS.i18n('Heute ({0}) (Leertaste)', 'framework.datePickerTodayTooltip'),
            format: langConfig.date.format,
            okText: CMS.i18n('&#160;OK&#160;', 'framework.datePickerOk'),
            cancelText: CMS.i18n('Abbrechen', 'framework.datePickerCancel'),
            startDay: langConfig.date.startDay
        });
    }

    if (Ext.PagingToolbar) {
        Ext.apply(Ext.PagingToolbar.prototype, {
            beforePageText: CMS.i18n('Seite', 'framework.pagingBeforePageText'),
            afterPageText: CMS.i18n('von {0}', 'framework.pagingAfterPageText'),
            firstText: CMS.i18n('Erste Seite', 'framework.pagingFirstText'),
            prevText: CMS.i18n('vorherige Seite', 'framework.pagingPrevText'),
            nextText: CMS.i18n('nächste Seite', 'framework.pagingNextText'),
            lastText: CMS.i18n('letzte Seite', 'framework.pagingLastText'),
            refreshText: CMS.i18n('Aktualisieren', 'framework.pagingRefreshText'),
            displayMsg: CMS.i18n('Anzeige Eintrag {0} - {1} von {2}', 'framework.pagingDisplayMsg'),
            emptyMsg: CMS.i18n('Keine Daten vorhanden', 'framework.pagingEmptyMsg')
        });
    }

    if (Ext.form.TextField) {
        Ext.apply(Ext.form.TextField.prototype, {
            minLengthText: CMS.i18n('Bitte geben Sie mindestens {0} Zeichen ein', 'framework.textFieldMinText'),
            maxLengthText: CMS.i18n('Bitte geben Sie maximal {0} Zeichen ein', 'framework.textFieldMaxText'),
            blankText: CMS.i18n('Dieses Feld darf nicht leer sein', 'framework.textFieldBlankText'),
            regexText: '',
            emptyText: null
        });
    }

    if (Ext.form.NumberField) {
        Ext.apply(Ext.form.NumberField.prototype, {
            minText: CMS.i18n('Der Mindestwert für dieses Feld ist {0}', 'framework.numberFieldMinText'),
            maxText: CMS.i18n('Der Maximalwert für dieses Feld ist {0}', 'framework.numberFieldMaxText'),
            nanText: CMS.i18n('{0} ist keine Zahl', 'framework.numberFieldNotANumberText'),
            decimalSeparator:  langConfig.decimalSeparator
        });
    }

    if (Ext.form.DateField) {
        Ext.apply(Ext.form.DateField.prototype, {
            disabledDaysText: CMS.i18n('nicht erlaubt', 'framework.dateFieldDisabledDays'),
            disabledDatesText: CMS.i18n('nicht erlaubt', 'framework.dateFieldDisabledDates'),
            minText: CMS.i18n('Das Datum in diesem Feld muss nach dem {0} liegen', 'framework.dateFieldMinText'),
            maxText: CMS.i18n('Das Datum in diesem Feld muss vor dem {0} liegen', 'framework.dateFieldMaxText'),
            invalidText: CMS.i18n('{0} ist kein gültiges Datum - es muss im Format {1} eingegeben werden', 'framework.dateFieldInvalidText'),
            format: langConfig.date.format,
            altFormats: langConfig.date.altFormats
        });
    }

    if (Ext.form.ComboBox) {
        Ext.apply(Ext.form.ComboBox.prototype, {
            loadingText: CMS.i18n('Lade Daten …', 'framework.comboBoxLoadingText'),
            valueNotFoundText: undefined
        });
    }

    if (Ext.form.VTypes) {
        Ext.apply(Ext.form.VTypes, {
            emailText: CMS.i18n('Dieses Feld sollte eine E-Mail-Adresse enthalten. Format: user@example.com', 'framework.validateEmailText'),
            urlText: CMS.i18n('Dieses Feld sollte eine URL enthalten. Format: http://www.example.com', 'framework.validateUrlText'),
            alphaText: CMS.i18n('Dieses Feld darf nur Buchstaben enthalten und _', 'framework.validateAlphaText'),
            alphanumText: CMS.i18n('Dieses Feld darf nur Buchstaben und Zahlen enthalten und _', 'framework.validateAlphaNumText')
        });
    }

    if (Ext.grid.GridView) {
        Ext.apply(Ext.grid.GridView.prototype, {
            sortAscText: CMS.i18n('Aufsteigend sortieren', 'framework.gridViewSortAsc'),
            sortDescText: CMS.i18n('Absteigend sortieren', 'framework.gridViewSortDesc'),
            lockText: CMS.i18n('Spalte sperren', 'framework.gridViewLock'),
            unlockText: CMS.i18n('Spalte freigeben (entsperren)', 'framework.gridViewUnlock'),
            columnsText: CMS.i18n('Spalten', 'framework.gridViewColumns')
        });
    }

    if (Ext.grid.GroupingView) {
        Ext.apply(Ext.grid.GroupingView.prototype, {
            emptyGroupText: CMS.i18n('(Keine)', 'framework.groupingEmptyGroup'),
            groupByText: CMS.i18n('Dieses Feld gruppieren', 'framework.groupingGroupBy'),
            showGroupsText: CMS.i18n('In Gruppen anzeigen', 'framework.groupingShowGroups')
        });
    }

    if (Ext.grid.PropertyColumnModel) {
        Ext.apply(Ext.grid.PropertyColumnModel.prototype, {
            nameText: CMS.i18n('Name', 'framework.propertyColumnName'),
            valueText: CMS.i18n('Wert', 'framework.propertyColumnValue'),
            dateFormat: langConfig.date.format
        });
    }

    if (Ext.grid.BooleanColumn) {
        Ext.apply(Ext.grid.BooleanColumn.prototype, {
            trueText: CMS.i18n('wahr', 'framework.booleanTrue'),
            falseText: CMS.i18n('falsch', 'framework.booleanFalse')
        });
    }

    if (Ext.grid.NumberColumn) {
        Ext.apply(Ext.grid.NumberColumn.prototype, {
            format: langConfig.numberFormat
        });
    }

    if (Ext.grid.DateColumn) {
        Ext.apply(Ext.grid.DateColumn.prototype, {
            format: langConfig.date.format
        });
    }

    if (Ext.layout.BorderLayout && Ext.layout.BorderLayout.SplitRegion) {
        Ext.apply(Ext.layout.BorderLayout.SplitRegion.prototype, {
            splitTip: CMS.i18n('Ziehen, um Größe zu ändern.', 'framework.borderLayoutSplitTooltip'),
            collapsibleSplitTip: CMS.i18n('Ziehen, um Größe zu ändern. Doppelklick um Panel auszublenden.', 'framework.borderLayoutCollapsibleSplitTooltip')
        });
    }

    if (Ext.form.TimeField) {
        Ext.apply(Ext.form.TimeField.prototype, {
            minText: CMS.i18n('Die Zeit muss gleich oder nach {0} liegen', 'framework.timeFieldMinText'),
            maxText: CMS.i18n('Die Zeit muss gleich oder vor {0} liegen', 'framework.timeFieldMaxText'),
            invalidText: CMS.i18n('{0} ist keine gültige Zeit', 'framework.timeFieldInvalidText'),
            format: langConfig.date.timeFormat
        });
    }

};
