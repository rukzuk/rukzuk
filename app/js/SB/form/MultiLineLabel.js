Ext.ns('SB.form');

/**
* @class SB.form.MultiLineLabel
* @extends Ext.form.Label
* Simple extension to Ext.form.Label allowing text with linebreaks
*/
SB.form.MultiLineLabel = Ext.extend(Ext.form.Label, {

    autoLink: false,

    initComponent: function () {
        delete this.style;
        return SB.form.MultiLineLabel.superclass.initComponent.apply(this, arguments);
    },


    onRender: function (ct, position) {
        if (!this.el) {
            this.el = document.createElement('label');
            this.el.id = this.getId();
            this.el.innerHTML = this.text ? this.processText(this.text) : (this.html || '');
            if (this.forId) {
                this.el.setAttribute('for', this.forId);
            }
        }
        Ext.form.Label.superclass.onRender.call(this, ct, position);
    },

    setText: function (t, encode) {
        var e = encode === false;
        this[!e ? 'text' : 'html'] = t;
        delete this[e ? 'text' : 'html'];

        if (this.rendered) {
            if (encode !== false) {
                this.el.dom.innerHTML = this.processText(t);

            } else {
                this.el.dom.innerHTML  = t;
            }
        }
        return this;
    },

    processText: function (t) {
        // replace <br> with \n and \n with <br> (backwards compatibility)
        t = Ext.util.Format.htmlEncode(t.replace(/<br *\/?>/g, '\n')).replace(/\n/g, '<br> ');
        if (this.autoLink) {
            t = this.doAutoLinks(t, {'target': '_blank', 'class': 'external'});
        }
        return t;
    },

    doAutoLinks: function (text, attributes) {
        var url_pattern = /(^|\s)(\b(https?|ftp):\/\/[\-A-Z0-9+\u0026@#\/%?=~_|!:,.;]*[\-A-Z0-9+\u0026@#\/%=~_|])/gi;
        var tag_attributes = '', key;

        for (key in attributes) {
            if (attributes.hasOwnProperty(key)) {
                tag_attributes += ' ' + key + '="' + attributes[key] +  '"';
            }
        }

        return text.replace(url_pattern, function (match, textBefore, url) {
            var link;
            link = '<a href="' + url + '"' + tag_attributes + '>' + url + '</a>';
            return '' + textBefore + link;
        });
    }


});

Ext.reg('ux-multilinelabel', SB.form.MultiLineLabel);
