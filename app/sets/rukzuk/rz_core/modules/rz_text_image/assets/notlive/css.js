DynCSS.defineModule('rz_text_image', function (api, v, context) {
    var result = {};
    var paddingTextfield = 0;
    var marginImageContainer = '0 0 ' + v.cssVerticalImageDistance + ' 0';
    var floatTextfield;
    var floatImageContainer;
    var widthTextfield = (100 - parseFloat(v.cssImageWidth)) + '%';

    if (v.cssImagePosition == 'left') {
        paddingTextfield = '0 0 0 ' + v.cssHorizontalImageDistance;
        marginImageContainer = '0 ' + v.cssHorizontalImageDistance + ' ' + v.cssVerticalImageDistance + ' 0';
        floatTextfield = 'right';
        floatImageContainer = 'left';
    } else if (v.cssImagePosition == 'right') {
        paddingTextfield = '0 ' + v.cssHorizontalImageDistance + ' 0 0';
        marginImageContainer = '0 0 ' + v.cssVerticalImageDistance + ' ' + v.cssHorizontalImageDistance;
        floatTextfield = 'left';
        floatImageContainer = 'right';
    } else {
        floatTextfield = 'none';
        floatImageContainer = 'none';
        widthTextfield = '100%';
    }

    if (v.cssImageWrap) {
        paddingTextfield = 0;
        widthTextfield = '100%';
        floatTextfield = 'none';
    } else {
        marginImageContainer = '0 0 ' + v.cssVerticalImageDistance + ' 0';
    }

    if (v.imgsrc === null) {
        widthTextfield = '100%';
        marginImageContainer = '0 0 ' + v.cssVerticalImageDistance + ' 0';
        paddingTextfield = 0;
    }


    result['& > .imageContainer'] = {
        float: floatImageContainer,
        width: v.cssImageWidth,
        margin: marginImageContainer
    };

    result['& > .text'] = {
        float: floatTextfield,
        width: widthTextfield,
        padding: paddingTextfield
    };

    return result;
});