/**
* @class SB.Element
* A namespace for utility functions to manipulate DOM Elements (or Ext Elements)
*/
Ext.ns('SB.Element');

(function () {

    var elCache = [];
    var styleCache = [];

    /*
    * @private
    * resolve el argument to a DOM element
    */
    var getDom = function (el) {
        if (typeof el == 'string') {
            return document.getElementById(el);
        }
        if (Ext.elCache[el.id] == el) {
            return el.dom;
        }
        return el;
    };

    /**
    * Force style to an element, even if CSS contains an <tt>!important</tt> rule
    * @method forceStyle
    * @param {String|Ext.Element|HTMLElement} el The element to apply the style to, or its id
    * @param {Object} styles The styles to apply in the form <tt>{ property1: value1, ... }</tt>, e.g.
<pre><code>{
    border: 'none',
    'margin-left': '5px',
    'float': 'left'
}</code></pre>
    * @param {Boolean} noCache <tt>true</tt> to prevent caching the original style.
    * Use this if you don't want to use {@link #restoreStyle} on the element
    */
    SB.Element.forceStyle = function (el, styles, noCache) {
        try {
            el = getDom(el);
            var origText = el.style.cssText;
            var additionalText = '';
            Ext.iterate(styles, function (prop, val) {
                additionalText += prop + ':' + val + ' !important;';
            });
            el.style.cssText += ';' + additionalText;
            if (!noCache && elCache.indexOf(el) == -1) {
                elCache.push(el);
                 //extract and cache the computed additional cssText since browsers tend to convert e.g. hash colors to rgb()
                var computedAdditionalText = el.style.cssText.substr(origText.length);
                styleCache.push(computedAdditionalText);
            }
        } catch (ignore) {} // IE
    };

    /**
    * Restore an element's style that has previously been altered with {@link #forceStyle}
    * @method restoreStyle
    * @param {String|Ext.Element|HTMLElement} el The element to reset. If this element
    * has not been altered by {@link #forceStyle}, this method does nothing.
    */
    SB.Element.restoreStyle = function (el) {
        try {
            el = getDom(el);
            var index = elCache.indexOf(el);
            if (index == -1) {
                return;
            }
            el.style.cssText = el.style.cssText.replace(styleCache[index], ''); //remove the cached style string
            elCache.splice(index, 1);
            styleCache.splice(index, 1);
        } catch (ignore) {} // IE
    };

    /**
    * Clear the element style cache that is used by {@link #restoreStyle}
    * @method clearStyleCache
    */
    SB.Element.clearStyleCache = function () {
        elCache = [];
        styleCache = [];
    };

    /**
    * This is a fake implementation of <tt>document.adoptNode</tt>, which is required to use nodes in a different document (iframe)
    * See https://developer.mozilla.org/en/DOM/document.adoptNode for details.
    * Note that this implementation is not perfect, and should only be used as a workaround
    * @param {HTMLElement} node A HTMLElement from a different (or the same) document
    * @return HTMLElement The cloned node
    */
    if (document.adoptNode) {
        SB.Element.adoptNode = function () {
            return document.adoptNode.apply(document, arguments);
        };
    } else {
        SB.Element.adoptNode = function (node) {
            var result = document.createElement(node.tagName);
            var attrs = node.attributes;
            for (var i = 0, l = attrs.length; i < l; i++) {
                result.setAttribute(attrs.item(i).nodeName, attrs.item(i).nodeValue);
            }
            node.parentNode.removeChild(node);
            return result;
        };
    }
})();
