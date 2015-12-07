/* global define */
define(['jquery', 'CMS', 'rz_root/notlive/js/baseJsModule', 'rz_image/notlive/jquery.mousewheel', 'rz_image/notlive/jquery.panzoom'], function ($, CMS, JsModule) {
    'user strict';

    // global state
    var imageCropperActive = false;
    var imageCropperUnitId;
    var $imageCropperMask;
    var $imageCropperUnitClone;

    // init image cropping
    var initializeModule = function (unitId) {
        var cfg = CMS.get(unitId);

        if (cfg.formValues.imgHeight.editable) {
            setVisualResize(unitId);
        }

        if (cfg.formValues.imgsrc &&
            cfg.formValues.imgsrc.value &&
            cfg.formValues.imgsrc.editable) {
            $('#' + unitId + ' > .cropIcon')
                .css('display', 'block').on('mousedown', function (event) {
                    // mousedown instead of click is needed for the case the user focus is inside an edited form field
                    // we want to init the cropper before formValueChange event, so it can be closed there again
                    // sounds strange, but seems to be the only way the cropper and unit replace doesn't get mixed up completely

                    // left button only
                    if (event.which == 1) {
                        startImageCropper(unitId);
                        event.stopPropagation();
                    }
                });
        }
    };

    // Function to save crop-data
    var storeImageData = function ($image, unitId) {
        var rawData = $image.panzoom('getMatrix');
        var $parent = $image.parent();
        if (rawData) {
            // original image width / height
            var naturalWidth = $image[0].naturalWidth;
            var naturalHeight = $image[0].naturalHeight;

            // extract data from matrix
            var zoomRatio = parseFloat(rawData[0]);
            var cropOffsetX = parseFloat(rawData[4]) * -1;
            var cropOffsetY = parseFloat(rawData[5]) * -1;

            // relative crop ratio (relative to original image size)
            var zoomWithOffset = (naturalWidth * (zoomRatio - 1)) * 0.5;
            var zoomHeightOffset = (naturalHeight * (zoomRatio - 1)) * 0.5;

            var cropXRatio = (cropOffsetX + zoomWithOffset) / (naturalWidth * zoomRatio);
            var cropYRatio = (cropOffsetY + zoomHeightOffset) / (naturalHeight * zoomRatio);

            var cropWidthRatio = ($parent.width() / (naturalWidth * zoomRatio));
            var cropHeightRatio = ($parent.height() / (naturalHeight * zoomRatio));

            var cropZoomData = {
                // offset relative to size of image
                cropXRatio: cropXRatio,
                cropYRatio: cropYRatio,
                // zoom ratio for x and y
                cropWidthRatio: cropWidthRatio,
                cropHeightRatio: cropHeightRatio,
            };

            CMS.set(unitId, 'cropData', JSON.stringify(cropZoomData));
        }
    };

    // logic to close cropper and refresh unit
    var closeImageCropper = function () {
        $('#' + imageCropperUnitId).addClass('rzImageLoading');

        // remove unit clone and panzoom
        if ($imageCropperUnitClone) {
            $imageCropperUnitClone.find('.imageModuleImg').first().panzoom('destroy');
            $imageCropperUnitClone.remove();
            $imageCropperUnitClone = null;
        }

        // remove mask with little animation
        if ($imageCropperMask) {
            $imageCropperMask.removeClass('show');
            window.setTimeout(function () {
                $imageCropperMask.remove();
                $imageCropperMask = null;
            }, 300);
        }

        $(document).off('keyup.cropper');

        // unit replace
        CMS.refresh(imageCropperUnitId);

        // reset state
        imageCropperActive = false;
        imageCropperUnitId = null;
    };

    // Function image cropper
    var startImageCropper = function (unitId) {
        if (imageCropperActive) {
            return;
        }
        imageCropperActive = true;
        imageCropperUnitId = unitId;
        var $unit = $('#' + unitId);
        var $imageWrapper = $unit.find('.responsiveImageWrapper').first();
        var $image = $unit.find('.imageModuleImg').first();

        // keep unit in height
        $unit.height($unit.height());
        $imageWrapper.height($imageWrapper.height());

        // remove crop icon
        $unit.find('.cropIcon').remove();

        // remove CMS classes to prevent outlines (even in the background)
        $unit.removeClass('CMSselected');
        $unit.removeClass('CMSeditable');

        // clone unit and add to body to prevent z-index problems
        $imageCropperUnitClone = $unit.clone();
        $imageCropperUnitClone.addClass('unitClone');
        // remove toolbar from rz_ghost_container
        $imageCropperUnitClone.find('.gcToolbar').remove();
        var $imageCloneWrapper = $imageCropperUnitClone.find('.responsiveImageWrapper').first();
        $imageCloneWrapper.find('.fillHeight').remove();

        // remove children and fix size of clone
        $imageCropperUnitClone.find('.isModule').remove();
        $imageCropperUnitClone.offset($unit.offset());
        $imageCropperUnitClone.css('padding', $unit.css('padding'));
        $imageCropperUnitClone.width($unit.innerWidth());
        $imageCropperUnitClone.height('auto');
        $imageCropperUnitClone.removeAttr('id');

        // get real image path
        var imgsrc = $image.data('cms-origsrc');

        var $imageClone = $('<img>');
        $imageCropperUnitClone.find('.imageModuleImg').first().replaceWith($imageClone);
        $imageClone.attr('src', imgsrc);
        $imageClone.css('opacity', '0');
        // show loading indicator
        $imageCloneWrapper.addClass('rzImageLoading');

        // on image load, mask viewport and launch cropper
        $imageClone.one('load', function () {
            // crop Data
            var cropData = getCropData(unitId);

            // minScale
            var naturalWidth = $imageClone[0].naturalWidth;
            var naturalHeight = $imageClone[0].naturalHeight;
            var minScaleWidth = $imageClone.parent().width() / naturalWidth;
            var minScaleHeight = $imageClone.parent().height() / naturalHeight;
            var minScale = Math.max(minScaleHeight, minScaleWidth);

            // start pan zoom
            $imageClone.panzoom({
                increment: 0.1,
                minScale: minScale,
                // allow at least min scale, otherwise 10
                maxScale: (minScale > 10) ? minScale : 10,
                contain: 'invert'
            });

            // buttons
            var zoomInOut = function (zoomOut) {
                $imageClone.panzoom('zoom', zoomOut, {
                    increment: 0.02,
                    animate: true,
                    focal: {
                        clientX: $imageCloneWrapper.offset().left + ($imageCloneWrapper.width() / 2),
                        clientY: $imageCloneWrapper.offset().top + $imageCloneWrapper.height() / 2
                    }
                });
            };

            $('<div class="rzImageZoomIn">').appendTo($imageCloneWrapper);
            $('<div class="rzImageZoomOut">').appendTo($imageCloneWrapper);

            var zoomInOutInterval;
            $imageCloneWrapper.on('mousedown', '.rzImageZoomIn, .rzImageZoomOut', function (e) {
                var zoomOut = $(e.target).hasClass('rzImageZoomOut');

                zoomInOut(zoomOut);

                window.clearInterval(zoomInOutInterval);
                zoomInOutInterval = window.setInterval(function () {
                    zoomInOut(zoomOut);
                }, 100);
            });
            $imageCloneWrapper.on('mouseup mouseout', '.rzImageZoomIn, .rzImageZoomOut', function () {
                window.clearInterval(zoomInOutInterval);
            });

            // mouse wheel support
            $imageClone.parent().on('mousewheel.focal', function (e) {
                e.preventDefault();
                var delta = e.delta || e.originalEvent.wheelDelta;
                var zoomOut = delta ? delta < 0 : e.originalEvent.deltaY > 0;
                $imageClone.panzoom('zoom', zoomOut, {
                    increment: 0.05,
                    animate: false,
                    focal: e
                });
            });

            // add mask and apply/cancel buttons
            $imageCropperMask = $('<div id="rzImageCropMask"></div>').appendTo('body');
            window.setTimeout(function () {
                $imageCropperMask.addClass('show');
            }, 0);
            var $maskControls = $('<div id="rzImageCropMaskControls"><div class="apply"></div><div class="cancel"></div></div>').appendTo($imageCropperUnitClone);

            // add listeners to close or/and save cropper
            $maskControls.on('click', 'div', function (e) {
                // maybe save cropping
                if ($(e.target).hasClass('apply')) {
                    storeImageData($imageClone, unitId);
                }
                closeImageCropper();
            });

            // close on mask click
            $imageCropperMask.on('click', function () {
                closeImageCropper();
            });

            // save on enter, close on ESC
            $(document).on('keyup.cropper', function (e) {
                if (e.keyCode == 13) {
                    storeImageData($imageClone, unitId);
                    closeImageCropper();
                }
                if (e.keyCode == 27) {
                    closeImageCropper();
                }
            });

            // start position
            var zoomRatio, startX, startY, zoomWithOffset, zoomHeightOffset;
            if (cropData.isInitial) {
                delete cropData.isInitial;
                zoomRatio = minScale;
                var scaledWith = naturalWidth * zoomRatio;
                var scaledHeight = naturalHeight * zoomRatio;
                zoomWithOffset = (naturalWidth * (zoomRatio - 1)) * 0.5;
                zoomHeightOffset = (naturalHeight * (zoomRatio - 1)) * 0.5;
                startX = (($imageClone.parent().width() - scaledWith) / 2) + zoomWithOffset;
                startY = (($imageClone.parent().height() - scaledHeight) / 2) + zoomHeightOffset;
            } else {
                // decode crop x and y
                zoomRatio = $imageClone.parent().width() / (cropData.cropWidthRatio * naturalWidth);
                zoomWithOffset = (naturalWidth * (zoomRatio - 1)) * 0.5;
                zoomHeightOffset = (naturalHeight * (zoomRatio - 1)) * 0.5;
                startX = ((cropData.cropXRatio * (naturalWidth * zoomRatio)) - zoomWithOffset) * -1;
                startY = ((cropData.cropYRatio * (naturalHeight * zoomRatio)) - zoomHeightOffset) * -1;
            }
            // set pan and zoom
            window.setTimeout(function () {
                // move to cropped position
                $imageClone.panzoom('setMatrix', [zoomRatio, 0, 0, zoomRatio, startX, startY]);
                // make image visible and remove loading indicator
                $imageClone.css('opacity', '');
                $imageCloneWrapper.removeClass('rzImageLoading');
            }, 200);
        });

        $image.hide();
        $('body').append($imageCropperUnitClone);
    };

    var getCropData = function (unitId) {
        var cfg = CMS.get(unitId);
        var cropData;
        try {
            cropData = JSON.parse(cfg.formValues.cropData.value);
        } catch (e) {
        }

        if (!cropData) {
            cropData = {
                cropXRatio: 0,
                cropYRatio: 0,
                cropWidthRatio: 1,
                cropHeightRatio: 1,
                isInitial: true
            };
        }
        return cropData;
    };

    // function for visual resize
    var setVisualResize = function (unitId) {
        // return if event drag plugin not loaded
        if ($.fn.drag == 'undefined') {
            return;
        }

        var $unit = $('#' + unitId);
        var $wrapperElm = $unit.find('.responsiveImageWrapper').first();

        var $resizeHeight = $('<span class="resizeHeight"></span>');
        var $uiBlocker = $('<div class="resizeHeightUiBlocker"></div>');

        var startHeight;
        var unitData = CMS.get(unitId);
        var imgSrc = unitData.formValues.imgsrc.value;
        var triggerWidth;

        var startDrag = function () {
            triggerWidth = $unit.width() / 100;
            $('body').append($uiBlocker);
            startHeight = $wrapperElm.height() / $wrapperElm.width() * 100;
            $wrapperElm.css('overflow', 'hidden');

            if (imgSrc !== null) {
                // prevent resize of image due to regular 100% height
                $wrapperElm.find('img').css('height', 'auto');
            }
        };

        var doDrag = function (deltaY, unitId) {
            var delta = (deltaY / triggerWidth);

            var imgHeight = Math.round(startHeight + delta);
            if (imgHeight < 0) {
                imgHeight = 0;
            }

            CMS.set(unitId, 'imgHeight', imgHeight + '%');

            if (imgHeight > 0) {
                $wrapperElm.find('> .fillHeight').css('padding-bottom', imgHeight + '%');
            }
        };

        var endDrag = function () {
            // remove ui blocker
            $uiBlocker.detach();
            // reload if we have a real image
            if (imgSrc !== null) {
                // reset cropping when height changes
                CMS.set(unitId, 'cropData', '');
                // replace unit; therefore css rules don't need to be removed
                CMS.refresh(unitId);
                // overflow is not relevant
            } else {
                // reset overflow fix
                $wrapperElm.css('overflow', '');
                $wrapperElm.css('height', '');
            }
        };

        // drag for resizing image height
        $resizeHeight
            .drag('start', startDrag)
            .drag(function (ev, dd) {
                doDrag(dd.deltaY, unitId);
            })
            .drag('end', endDrag);

        // inject resize handler
        $wrapperElm.append($resizeHeight);
    };

    return JsModule.extend({

        initUnit: function (unitId) {
            var cfg = CMS.getSelected();
            if (unitId === cfg.id) {
                initializeModule(unitId);
            }
        },

        /** @protected */
        onUnitSelect: function (config) {
            initializeModule(config.unitId);
        },

        /** @protected */
        onUnitDeselect: function (config) {
            var $unit = $('#' + config.unitId);
            $unit.find('> .cropIcon').css('display', 'none');
            $unit.find('> .responsiveImageWrapper > .resizeHeight').remove();
        },

        onFormValueChange: function (eventData) {
            // close cropper if active, don't save anything
            if (imageCropperActive) {
                closeImageCropper();
            }

            if (eventData.key == 'imgHeight') {
                var unitId = eventData.unitId;

                // reset cropping when height changes
                CMS.set(unitId, 'cropData', '');
            }

            if (eventData.key == 'imgsrc') {
                // reset cropping when image changes
                CMS.set(eventData.unitId, 'cropData', '');
            }
        }
    });
});
