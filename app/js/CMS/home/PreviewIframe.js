Ext.ns('CMS.home');

/**
 * @class CMS.home.PreviewIframe
 * @extends SB.TheIframe
 * An iframe that can be used for previews. It can handle mouse events.
 */
CMS.home.PreviewIframe = Ext.extend(SB.TheIframe, {
    initComponent: function () {
        this.proxyEvents = this.proxyEvents.concat(['mousedown']); // mousedown for menu
        this.on('onload', function (self, el) {
            console.log('[PreviewIframe] domready!', el);
            try {
                // Enable bubbling of mouse events from within the iframe element
                // Requires extension on Ext.Element
                el.relayEvents(this.getFrameDocument(), this.proxyEvents);

            } catch (e) {
                console.error('[PreviewIframe] Could not initialize iframe. SOP issue?');
            }
        }, this);
    }
});