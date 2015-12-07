Ext.ns('CMS.liveView');

/**
* Plugin for {@link SB.TheIframe} to enable selection of contained elements
* using two different styles
*/
CMS.liveView.TheIframeSelectionPlugin = {
    init: function (frame) {
        var oldDestroy = frame.destroy;

        var CMShover = 'CMShover';
        var CMSselected = 'CMSselected';
        var CMSeditable = 'CMSeditable';

        Ext.apply(frame, {
            /**
            * @private
            * Handle click on an element
            * @param {HTMLElement} unitEl The clicked unit
            * @param {Boolean} suppressEvent <tt>true</tt> to suppress firing the <tt>select</tt> event
            * and the CMSunitframe(de)select events. Defaults to false.
            * @param {Boolean} isEditable Whether the unit is editable or not
            */
            setSelectedUnitEl: function (unitEl, suppressEvent, isEditable) {
                console.log('[PI] setSelectedUnitEl', unitEl);
                if (this.selectedUnitEl) {
                    if (this.selectedUnitEl == unitEl) {
                        return;
                    }
                    //console.log('[PI] restoring style of', this.selectedUnitEl);
                    this.selectedExtEl.removeClass(CMSselected);
                    if (this.selectedExtEl.hasClass(CMSeditable)) {
                        this.selectedExtEl.removeClass(CMSeditable);
                    }
                    if (!suppressEvent) {
                        this.fireEvent('CMSunitframedeselect', this.selectedUnitEl.id);
                    }
                }
                if (this.hoveredUnitEl) {
                    // console.log('[PI] restoring style of', this.hoveredUnitEl);
                    this.hoveredExtEl.removeClass(CMShover);
                }

                // scroll
                this.scrollToView(unitEl);

                this.selectedUnitEl = unitEl; // may be null
                if (!unitEl) {
                    this.fireEvent('select', null);
                    return;
                }
                this.selectedExtEl = new Ext.Element(unitEl);

                // set css classes
                this.selectedExtEl.addClass(CMSselected);
                if (isEditable) {
                    this.selectedExtEl.addClass(CMSeditable);
                }

                if (!suppressEvent) {
                    this.fireEvent('CMSunitframeselect', unitEl.id);
                    this.fireEvent('select', unitEl);
                }
            },

            /**
            * @private
            * Handle hover on a unit. Fires the <tt>hover</tt> event.
            * @param {HTMLElement} unitEl
            * @param {Boolean} suppressEvent <tt>true</tt> to suppress firing the <tt>hover</tt> event.
            * Defaults to false.
            * @param {Boolean} isEditable Whether the unit is editable or not
            */
            setHoveredUnitEl: function (unitEl, suppressEvent, isEditable) {
                //console.log('[PI] enter setHoveredUnitEl', unitEl ? unitEl.id : unitEl, suppressEvent);
                if (this.hoveredUnitEl) {
                    if (this.hoveredUnitEl == unitEl) {
                        if (!suppressEvent) {
                            // console.log('[PI] fire hover');
                            this.fireEvent('hover', unitEl);
                        }
                        // console.log('returning');
                        return;
                    }
                    // restore original style
                    if (this.hoveredUnitEl != this.selectedUnitEl) { // keep selected style
                        //console.log('[PI] restoring style of', this.hoveredUnitEl);
                        this.hoveredExtEl.removeClass(CMShover);
                        if (this.hoveredExtEl.hasClass(CMSeditable)) {
                            this.hoveredExtEl.removeClass(CMSeditable);
                        }
                    }
                    if (!suppressEvent) {
                        this.fireEvent('CMSframemouseout', this.hoveredUnitEl.id);
                    }
                }
                this.hoveredUnitEl = unitEl;
                this.hoveredExtEl = new Ext.Element(unitEl);
                if (unitEl) {
                    // set css classes
                    if (unitEl != this.selectedUnitEl) { // keep selected style
                        this.hoveredExtEl.addClass(CMShover);
                        if (isEditable) {
                            this.hoveredExtEl.addClass(CMSeditable);
                        }
                    }
                    if (!suppressEvent) {
                        this.fireEvent('CMSframemouseenter', unitEl.id);
                    }
                }
                if (!suppressEvent) {
                    // console.log('[PI] fire hover');
                    this.fireEvent('hover', unitEl);
                }
                this.hoveredUnitEl = unitEl;
            },

            destroy: function () {
                delete this.hoveredUnitEl;
                delete this.selectedUnitEl;
                oldDestroy.apply(this, arguments);
            }
        });
    }
};
