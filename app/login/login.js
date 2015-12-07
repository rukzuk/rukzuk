/**
 * Login Script
 * This file is part of rukzuk
 */

/* Helper Functions */
/**
 * @private
 * hides all panels and shows one
 * @param {String} [name] name of the panel
 *
 */
function show_panel(name) {
    $('.panel').hide();
    if (name) {
        $('#' + name + '-panel').show();
    }
}

function hide_msg() {
    $('#form .message').hide();
}

function show_msg(msgCls) {
    $('#form .' + msgCls).show();
}

function show_msg_server(text) {
    $('#form .message.fromServer').text(text).show();
}


function get_fullscreen() {
    return localStorage.getItem('CMSfullscreenchoice') === 'true';
}

function store_fullscreen(status) {
    // remember state for next login
    localStorage.setItem('CMSfullscreenchoice', status);
}

function ajax_fail() {
    show_msg('msgServerUnreachable');
}


/* Helper Methods */
// Read a page's GET URL variables and return them as an associative array.
function getUrlVars() {
    var vars = [], param;
    var params = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for (var i = 0; i < params.length; i++) {
        param = params[i].split('=');
        vars.push(param[0]);
        vars[param[0]] = param[1];
    }
    return vars;
}

/* DOM Ready*/
$(function () {

    //
    // GET params
    //

    // url to backend
    var urlPrefix = '/' + ('v-' + Date.now()) + '/app/service/';

    // optin: reset password token set?
    var urlVars = getUrlVars();
    var pwToken = urlVars.pwToken;
    var simpleMode = (urlVars.mode === 'simple');
    var successAction = urlVars.action;
    var predefinedUsername = urlVars.username ? decodeURIComponent(urlVars.username) : null;

    if (simpleMode) {
        $('body').addClass('simpleMode');
    }

    //
    // Default panel to show
    //

    if (pwToken) {
        show_panel('optin');
        $('#optin_pw').focus();

    } else {
        show_panel('login');

        // set predefined username if we got one
        if (predefinedUsername) {
            // give password manager some time to fill fields
            setTimeout(function () {
                var $user = $('#username');
                var $password = $('#password');
                // force username in simple mode (relogin after lost session)
                if (simpleMode) {
                    // fill up if pw manager leave user empty or the entered username is not the current user!
                    if ($user.val() === '' || $user.val() != predefinedUsername) {
                        $user.val(predefinedUsername);
                    }
                    // prevent change of username
                    $user.attr('disabled', 'disabled');
                    $password.focus();
                } else {
                    // set username if field is empty (pw manager has no data)
                    if ($user.val() === '') {
                        $user.val(predefinedUsername);
                        $password.focus();
                    }
                }
            }, 50);
        } else {
            $('#username').focus();
        }
    }

    // start animation
    setTimeout(function () { $('body').addClass('fadeIn'); }, 10);

    //
    // LOGIN PANEL
    //

    // set fullscreen checkbox
    if (successAction == 'embedded') {
        if (get_fullscreen()) {
            $('#fullscreen').attr('checked', 'checked');
        } else {
            $('#fullscreen').removeAttr('checked');
        }
    } else {
        $('body').addClass('background');
        $('div.fullscreen').hide();
    }

    // simple validation
    $('#username, #password').keypress(function (event) {
        // hide msg on real input
        if (event.which !== 0 && event.charCode !== 0) {
            hide_msg();
        }
    });

    // Submit form via ajax
    $('#login_form').submit(function (event) {
        var self = this;

        // disable possible old animation
        $('#form').removeClass('animated shake');

        // values
        var $user = $('#username');
        var $password = $('#password');

        // sync ajax call!
        $.ajax({
            type: 'POST',
            url: urlPrefix + 'user/login',
            data: {params: JSON.stringify({password: $password.val(), username: $user.val()}) },
            dataType: 'json',
            async: false // done will be called sync!!
        }).done(function (response) {
                if (response.success)  {
                    //loginSuccess = true;

                    if (successAction == 'embedded') {
                        // send a message to our hosting window
                        if (window.parent) {
                            window.parent.postMessage({action: 'login', successful: true}, location.protocol + '//' + location.host);
                        }

                        // fullscreen handling
                        var fullscreen_state = $('#fullscreen').is(':checked');
                        store_fullscreen(fullscreen_state);
                        if (window.parent && window.parent.CMS) {
                            window.parent.CMS.app.FullScreenHelper.toggleFullScreen(fullscreen_state);
                        }

                    } else {
                        // standalone mode
                        $('#login_form').attr('action', '/?redir');
                    }
                    // submit form (remove this listener and submit the form
                    $(self).off(event);
                    self.submit();
                } else {
					$.each(response.error, function (i, error) {
						if (error.code === 9) {
							if (error.param.hasOwnProperty('redirect')) {
								window.parent.location.replace(error.param.redirect);
								return;
							}
							window.setTimeout(function () {
								$('#form').addClass('animated shake');
							}, 10);
							show_msg('msgExpired');
						} else {
							// chrome will not trigger animation if this is added immediately
							window.setTimeout(function () {
								$('#form').addClass('animated shake');
							}, 10);
							show_msg('msgEmailOrPwIncorrect');

							// reset password
							$password.val('');
							// focus password if username is disabled
							if ($user.is(':disabled')) {
								$password.focus();
							} else {
								$user.focus();
							}
						}
					});
                }
            }).fail(ajax_fail);

        // prevent default action
        return false;
    });

    //
    // LOST-PW PANEL
    //
    $('#lostpwlink').click(function () {
        show_panel('lostpw');
        $('#lostpw_email').focus();
        return false;
    });

    $('#lostpwback').click(function () {
        show_panel('login');
        return false;
    });

    $('#lostpw_email').keypress(function (event) {
        // hide msg on real input
        if (event.which !== 0 && event.charCode !== 0) {
            hide_msg();
        }
        if (event.which == 13) {
            $('#lostpwBtn').click();
            return false;
        }
    });


    $('#lostpwBtn').click(function () {
        $('#form').removeClass('animated shake');
        var email = $('#lostpw_email').val();
        var data = {params: JSON.stringify({'email': email}) };

        $.ajax({
            type: 'POST',
            url: urlPrefix + 'user/renewpassword',
            data: data,
            dataType: 'json'
        }).done(function (response) {
            if (response.success)  {
                if (response.data && response.data.redirect) {
                    window.parent.location = response.data.redirect + '?email=' + email;
                } else {
                    show_msg('msgCredentialsRequested');
                    show_panel();
                }
            } else {
                $('#form').addClass('animated shake');
                //show_msg_server(response.error[0].text);
                show_msg('msgEmailNotFound');
                $('#lostpw_email').val('');
                $('#lostpw_email').focus();
            }
        }).fail(ajax_fail);

        return false;
    });


    // OPTIN PANEL

    var validateOptin = function (event) {
        // hide msg on real input
        if (event.which !== 0 && event.charCode !== 0) {
            hide_msg();
        }

        var pw = $('#optin_pw').val();
        var pw2 = $('#optin_pw2').val();

        // messages
        if (pw.length <= 5) {
            show_msg('msgPwInvalid');
        } else if (pw != pw2) {
            show_msg('msgPwMismatch');
        }

        if (pw.length > 5 && pw2.length > 5 && pw == pw2) {
            $('#optinBtn').removeAttr('disabled');
            hide_msg();
        } else {
            $('#optinBtn').attr('disabled', 'disabled');
        }
    };

    // register validate option on several events
    $('#optin_pw, #optin_pw2').keyup(validateOptin).blur(validateOptin);

    // register enter key handler
    $('#optin_pw, #optin_pw2').keyup(function (event) {
        if (event.which == 13 && !$('#optinBtn').attr('disabled')) {
            $('#optinBtn').click();
            return false;
        }
    });

    // start with disabled button
    $('#optinBtn').attr('disabled', 'disabled');

    $('#optinBtn').click(function () {
        $('#form').removeClass('animated shake');
        var pw = $('#optin_pw').val();
        var data = {params: JSON.stringify({'code': pwToken, password: pw, username: predefinedUsername}) };

        $.ajax({
            type: 'POST',
            url: urlPrefix + 'user/optin',
            data: data,
            dataType: 'json'
        }).done(function (response) {
            if (response.success)  {

                if (successAction == 'embedded') {
                    // send a message to our hosting window
                    if (window.parent) {
                        window.parent.postMessage({action: 'pwrecovery', successful: true}, location.protocol + '//' + location.host);
                    }
                } else {
                    show_msg('msgPasswordChanged');
                    show_panel('login');
                }
            } else {
                $('#form').addClass('animated shake');
                show_msg_server(response.error[0].text);
                $('#optin_pw').val('');
                $('#optin_pw2').val('');
                $('#optin_pw').focus();
            }
        }).fail(ajax_fail);

        return false;
    });
});
