Ext.ns('SB.image');

/**
* @class SB.image.MultiImageStack
* @extends Ext.BoxComponent
* Displays a stack of images
*/
SB.image.MultiImageStack = Ext.extend(Ext.BoxComponent, {
    /**
    * @cfg {Array} imagesrcs
    * Required. An array of image urls.
    */
    imagesrcs: [],

    /**
    * @cfg {Mixed} imagealts
    * Optional. An array of alt texts to be used for the images, or a single
    * text to be used for every image. Defaults to <tt>' '</tt>
    */
    imagealts: ' ',

    /**
    * @cfg {Integer} imageSize
    * The width/height of the individual images. They will be scaled to fit.
    * Defaults to 100.
    */
    imageSize: 100,

    /**
    * @cfg {Number} spread
    * The sum of rotation angles that are applied to the individual images.
    * Defaults to 45.
    */
    spread: 45,

    /**
    * @cfg {Number} rotationoffset
    * The rotation center is positioned horizontally centered. Its y-coordinate is determined as follows:
    * <tt>y = imagebottom - rotationoffset*imageheight</tt>.
    * The default <tt>rotationoffset</tt> is 20, i.e. the center of rotation is 20% beneath the image's base line.
    */
    rotationoffset: 20,

    /**
    * @cfg {String} background
    * The background to aply to the images. Defaults to black.
    */
    background: 'black',


    initComponent: function () {
        if (!Ext.isArray(this.imagesrcs) || !this.imagesrcs.length) {
            throw 'MultiImageStack: imagesrcs must be a non-empty array';
        }
        this.imageSize = parseInt(this.imageSize, 10);
        if (isNaN(this.imageSize) || this.imageSize <= 0) {
            throw 'MultiImageStack: invalid imageSize';
        }
        if (!Ext.isArray(this.imagealts)) {
            this.imagealts = [this.imagealts || ''];
        }
        SB.image.MultiImageStack.superclass.initComponent.apply(this, arguments);
        this.detectSupport();
    },

    onRender: function (self, position) {
        if (!this.el) {
            this.el = document.createElement('div');
            this.el.className = 'sb-multiimage-cmp';
            var wrapper = document.createElement('div');
            wrapper.style.position = 'relative';
            this.el.appendChild(wrapper);
            // determine padding
            if (this.imagesrcs.length == 1) {
                wrapper.style.paddingBottom = wrapper.style.paddingRight = this.imageSize + 'px';
            } else {
                if (this.prefix !== false) {
                    // warning: serious rotation math ahead
                    var alpha = (this.spread / 2) * (2 * Math.PI / 360);
                    var r = this.rotationoffset / 100;
                    var sa = Math.sin(alpha);
                    var ca = Math.cos(alpha);
                    var xOff = (ca - 1) / 2 + sa * (1 + r);
                    var yOffTop = Math.sqrt(1 / 4 + (r + 1) * (r + 1)) - (r + 1);
                    var yOffBottom = sa / 2 + (1 - ca) * r;
                    xOff = parseInt(this.imageSize * Math.abs(xOff), 10) + 1;
                    yOffTop = parseInt(this.imageSize * Math.abs(yOffTop), 10) + 1;
                    yOffBottom = parseInt(this.imageSize * Math.abs(yOffBottom), 10) + 1;
                    wrapper.style.paddingLeft = xOff + 'px';
                    wrapper.style.paddingTop = yOffTop + 'px';
                    wrapper.style.paddingBottom = (this.imageSize + yOffBottom) + 'px';
                    wrapper.style.paddingRight = (this.imageSize + xOff) + 'px';
                } else {
                    wrapper.style.paddingBottom = wrapper.style.paddingRight = (this.imageSize * (1 + (this.rotationoffset || 10) / 100)) + 'px';
                }
            }
            // render images
            var inner = '';
            for (var l = this.imagesrcs.length, i = l - 1; i >= 0; i--) {
                var alt = this.imagealts[i] || '';
                var src = this.imagesrcs[i];
                var style = '';
                if (l > 1) {
                    if (this.prefix !== false) {
                        // rotate one image
                        var angle = (i * this.spread / (l - 1)) - (this.spread / 2);
                        style = this.prefix + 'transform: rotate(' + angle + 'deg); ' + this.prefix + 'transform-origin: 50% ' + (this.rotationoffset + 100) + '%;';
                    } else {
                        // offset one image
                        var offset = parseInt(this.imageSize * ((this.rotationoffset || 10) / 100) * (i / (l - 1)), 10);
                        style = 'left: ' + offset + 'px; top: ' + offset + 'px;';
                    }
                }
                /*jslint laxbreak: true*/
                inner += '<div class="sb-multiimage-wrapper" style="' + style + '">'
                            // nested divs are required in gecko for rotation + boxshadow
                            + '<div class="sb-multiimage-bg" style="line-height:' + this.imageSize + 'px;'
                                    + 'width:' + this.imageSize + 'px; height:' + this.imageSize + 'px;'
                                    + 'background: ' + this.background + '">'
                                + '<img class="sb-multiimage" src="' + src + '" alt="' + alt + '"><span>&#160</span>'
                            + '</div>'
                        + '</div>';
                /*jslint laxbreak: false*/
            }
            wrapper.innerHTML = inner;
        }
        SB.image.MultiImageStack.superclass.onRender.apply(this, arguments);
    },

    /**
    * @private
    * detects support for CSS3 [prefix]transform property, and stores the found prefix in this.prefix.
    */
    detectSupport: function () {
        this.prefix = false;
        var prefixes = ['', '-moz-', '-webkit-', '-o-'];
        Ext.each(prefixes, function (prefix) {
            var cssProp = (prefix + 'transform').replace(/-./g, function (s) {
                return s[1].toUpperCase();
            });
            if (typeof document.body.style[cssProp] != 'undefined') {
                this.prefix = prefix;
                return false;
            }
        }, this);
    }

});

Ext.reg('sb-multiimage', SB.image.MultiImageStack);
