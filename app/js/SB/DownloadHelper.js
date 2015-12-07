Ext.ns('CMS.app');

/**
* @class SB.DownloadHelper
* @extends Ext.util.Observable
* A helper class for downloading files in a hidden iframe
*/
SB.DownloadHelper = Ext.extend(Ext.util.Observable, {

    constructor: function () {
        var cls = SB.DownloadHelper;
        SB.DownloadHelper.superclass.constructor.apply(this, arguments);
        /**
        *
        */
        this.iframes = [];
        if (!cls.loadHandlers) {
            // We store the handler function references in a globally reachable object (the class)
            // This is required for IE, since setting iframe.onload is not supported, and the onload attribute
            // is a string, so it's tricky to pass references to 'this'.
            cls.loadHandlers = {};
        }
        if (!cls.instances) {
            cls.instances = [];
        }
        cls.instances.push(this);
    },


    /**
    * @cfg {Integer} timeout
    * The timeout (in s) after which the hidden iframe is removed. Note that browsers don't notify us about successful downloads, so we'll have to guess.
    */
    timeout: 30,

    /**
     * Start a new download. Note that browsers don't notify us about successful downloads.
     * @param {String} url The download's url. For correct failure handling, this must be on the same origin as the current site.
     * @param {Object} opts (Optional) Additional options:<ul>
     <li>failure {Function}: A callback function to be executed on download failure</li>
     <li>scope {Object}: The callback functions' scope. Defaults to the iframe Element.</li>
     <li>timeout {Integer}: Timeout in seconds before the download is expected to have finished. If ommitted, the downloader instance's timeout is used.
     Use <tt>-1</tt> to never time out. Note that this might flood the browser's DOM with hidden iframes</li>
     </ul>
     * @return Ext.Element The hidden iframe for this download
     */
    startDownload: function (url, opts) {

        opts = opts || {};
        var iframe = Ext.get(document.createElement('iframe'));
        var id = iframe.dom.id;
        var self = this;

        // declare callback. We also use this to clean up after timeout.
        SB.DownloadHelper.loadHandlers[id] = function (loaded) {

            if (iframe.timeoutId) {
                window.clearTimeout(iframe.timeoutId);
            }

            var responseText;
            if (loaded) {
                try {
                    responseText = iframe.dom.contentDocument.body.innerHTML;
                    responseText = responseText.replace(/^<pre[^>]*>/i, '').replace(/<\/pre[^>]*>$/i, '');
                } catch (e) { // different domain or 404 error in IE. (Other browsers won't do anything on 404.)
                    console.warn('[DownloadHelper] Could not access response body');
                }
            }

            if (responseText) {
                // if response body present, we treat this as en error
                CMS.app.ErrorManager.push(CMS.i18n(null, 'SB.DownloadHelper.dlUrlError').replace('{url}', url).replace('{response}', responseText));
                /**
                * @event error
                * Fired when a download yields an error
                * @param iframe {Ext.Element} The iframe that the downlod happened in
                * @param url {String} The download file's url
                * @param isTimeout {Boolean} <tt>true</tt> if the download timed out, <tt>false</tt> for other error.
                */
                self.fireEvent('error', iframe, url);
                if (opts.failure) {
                    opts.failure.call(opts.scope || iframe);
                } else {
                    CMS.Message.error(CMS.i18n(null, 'SB.DownloadHelper.downloadError'), CMS.i18n(null, 'SB.DownloadHelper.downloadErrorUrl'));
                }
            }

            // cleanup
            iframe.dom.onload = null;
            iframe.dom.onerror = null;
            Ext.removeNode(iframe);
            self.iframes.remove(iframe);
            delete SB.DownloadHelper.loadHandlers[id];
            self = null;
            iframe = null;
            opts = null;
        };

        iframe.set({
            src: url
        });
        iframe.setStyle({
            position: 'absolute',
            width: '1px',
            height: '1px',
            left: '-100000px',
            top: '-100000px',
            visibility: 'hidden'
        });
        // this callback will only be fired onload, which means the browser is actually displaying content, so the download has effectively failed.
        // In general, no onload event is fired with successful downloads.
        iframe.dom.setAttribute('onload', 'SB.DownloadHelper.loadHandlers["' + id + '"](true);');
        iframe.dom.setAttribute('onerror', 'SB.DownloadHelper.loadHandlers["' + id + '"](true);');
        iframe.appendTo(Ext.getBody());

        var timeout = 1000 * (opts.timeout || this.timeout);
        if (timeout > 0) {
            iframe.timeoutId = window.setTimeout(SB.DownloadHelper.loadHandlers[id], timeout);
        }

        this.iframes.push(iframe);
        return iframe;
    },

    /**
    * Clean up (remove pending iframes and timeout listeners)
    */
    destroy: function () {
        Ext.each(this.iframes, function (iframe) {
            if (iframe.timeoutId) {
                window.clearTimeout(iframe.timeoutId);
            }
            iframe.dom.onload = null;
            iframe.dom.onerror = null;
            Ext.removeNode(iframe);
        });
        SB.DownloadHelper.instances.remove(this);
        if (!SB.DownloadHelper.instances.length) {
            delete SB.DownloadHelper.instances;
            delete SB.DownloadHelper.loadHandlers;
        }
    }
});
