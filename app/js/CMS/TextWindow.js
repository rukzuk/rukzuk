Ext.ns('CMS');

/**
 * A window that displays a String or the JSON serialization of a given object
 * @class CMS.TextWindow
 * @extends Ext.Window
 */
CMS.TextWindow = Ext.extend(Ext.Window, {
    /** @lends CMS.TextWindow.prototype */

    constrainHeader: true,
    maximizable: true,
    width: 800,
    height: 600,
    autoScroll: true,
    bodyStyle: 'padding: 10px; font-family: monospace;',
    layout: 'fit',
    cls: 'CMStextwindow',

    /**
     * The string or object to display
     * @property object
     * @type Object|String
     */
    object: undefined,

    /** @protected */
    initComponent: function () {
        this.title = this.title || CMS.i18n('JSON Template');
        this.tbar = [{
            text: CMS.i18n('Alles auswählen'),
            handler: this.selectBtnHandler,
            scope: this
        }];
        var text;
        if (typeof this.object == 'string') {
            text = this.object;
        } else {
            text = JSON.stringify(this.object, undefined, 2) || '';
            if (Ext.isGecko3) {
                text = text.replace(/(\n +)("(\.|[^"])*" *:\[)([^\]].+\n)/g, '$1$2$1  $4'); // fix incorrect array representation in FF3
            }
            /*jslint laxbreak: true*/
            text = text.replace(/&/g, '&amp;').replace(/>/g, '&gt;').replace(/</g, '&lt;') // htmlEncode
            .replace(/(^|\n)( *"(\\.|[^"])*" *): */g, '$1<span class="key">$2</span>: ') // hilite keys
            .replace(/((:|\n) *)("(\\.|[^"])*")/g, '$1<span class="string">$3</span>') // hilite strings
            .replace(/((:|\n) *)(true|false|null)\b/g, '$1<span class="keyword">$3</span>') //hilite keywords
            .replace(/((:|\n) *)(-?0|([1-9][0-9]*)(\.[0-9]+)?([eE][\-+]?[0-9]+)?)/g, '$1<span class="number">$3</span>'); //hilite numbers
            /*jslint laxbreak: false*/
        }
        this.html = '<pre><code>' + text + '</code></pre>';
        Ext.Window.prototype.initComponent.apply(this, arguments);
    },

    /** @private */
    selectBtnHandler: function () {
        var range;
        if (document.selection) {
            range = document.body.createTextRange();
            range.moveToElementText(this.body.dom);
            range.select();
        } else if (window.getSelection) {
            range = document.createRange();
            range.selectNode(this.body.dom);
            window.getSelection().addRange(range);
        }
    }
});
