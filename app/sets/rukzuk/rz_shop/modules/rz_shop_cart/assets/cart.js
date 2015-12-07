/* global $:false */
$(function () {

    var updateShippingAdr = function () {
        var isChecked = $(this).is(':checked');
        var shippingInputs = $(this).parentsUntil('.adrGroup').last().siblings('input, select, label');
        shippingInputs.prop('disabled', !isChecked);
        shippingInputs[isChecked ? 'show' : 'hide']();
        return shippingInputs;
    };

    var $cart = $('.rz_shop_cart');

    // shipping address checkbox toggle
    $cart.find('input[name*=use_shipping_address]').click(function () {
        updateShippingAdr.call(this).val('');
    }).each(updateShippingAdr);

    // skip validation for "back to cart" clicks
    $cart.find('button[value=toCart]').click(function () {
        this.form.setAttribute("novalidate", "");
    });

    // prevent submit via enter
    $cart.find('input, select').keypress(function (event) {
        return event.keyCode != 13;
    });

});
