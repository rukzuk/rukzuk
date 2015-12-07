Ext.ns('CMS.config');

/**
* @member CMS.config
*/
Ext.apply(CMS.config, {
    validation: {
        CMSvar: {
            allowBlank: false,
            minLength: 3,
            maskRe: /[a-zA-Z0-9_]/,
            stripCharsRe: /[^a-zA-Z0-9_]/g, //this is additionally needed to filter chars added via copy&paste / drag&drop
            regex: null //TODO: this is only for compatibility with old modules (pre 1ace50e7f14e0b887ee7273bb9b065418d8089f2) which have this property set
        },
        pageName: {
            allowBlank: false
        },
        templateName: {
            allowBlank: false,
            minLength: 1
        },
        websiteName: {
            allowBlank: false,
            minLength: 3
        },
        moduleName: {
            allowBlank: false,
            minLength: 3
        },
        templateSnippetName: {
            allowBlank: false,
            minLength: 3
        },
        albumName: {
            allowBlank: false,
            minLength: 3
        },
        userGroupName: {
            allowBlank: false,
            minLength: 3
        },
        userFirstName: {
            allowBlank: false,
            minLength: 2
        },
        userLastName: {
            allowBlank: false,
            minLength: 2
        },
        userEmail: {
            allowBlank: false
        },
        userPassword: {
            minLength: 6
        },
        unitHtmlClass: {
            maskRe: /[a-zA-Z0-9 _-]/,
            stripCharsRe: /[^a-zA-Z0-9 _-]/g //this is additionally needed to filter chars added via copy&paste / drag&drop
        },

        /**
        * Tests if the given input is a valid email address.
        *
        * A email address is valid if it
        *     ... contains at least one '@' character
        *     ... does not contain '..'
        *     ... has a local part that
        *         ... has a maximum length of 64 characters
        *         ... conforms either to dot-atom or is a quoted string
        *         ... has a maximum length of 64 characters
        *     ... has a hostname that
        *         ... has a maximum length of 255 characters
        *         ... has a non-empty top level domain
        *         ... has parts separated by '.' that
        *         ... have a maximum length of 63 characters each
        *         ... contain only letters, numbers and '-'
        *         ... don't have '-'
        *             ... as the first character
        *             ... as the last character
        *             ... as the third and forth character
        *
        * NOTICE: the top level domain is not checked anymore becauce the valid options
        * are to manifold and change to fast
        *
        * @param {String} value The email address to check
        * @method emailValidator
        */
        emailValidator:  function (value) {

            if (typeof value !== 'string') {
                return false;
            }

            // Convert to lower case to match case-insensitive
            value = value.toLowerCase();

            // Split email address up and disallow '..'.
            var matches = value.match(/^(.+)@([^@]+)$/);
            if (value.indexOf('..') !== -1 || matches === null) {
                return false;
            }
            var localPart = matches[1];
            var hostnamePart = matches[2];

            // Validate lengths
            if (localPart.length > 64 || hostnamePart.length > 255) {
                return false;
            }

            // Validate local part.
            var localPartValid = false;

            // First try to match the local part on the common dot-atom format.
            // Dot-atom characters are: 1*atext *("." 1*atext)
            // atext: ALPHA / DIGIT / and "!", "#", "$", "%", "&", "'", "*",
            //        "+", "-", "/", "=", "?", "^", "_", "`", "{", "|", "}", "~"
            var dotAtomExp = /^[a-zA-Z0-9\x21\x23\x24\x25\x26\x27\x2a\x2b\x2d\x2f\x3d\x3f\x5e\x5f\x60\x7b\x7c\x7d\x7e]+(\x2e+[a-zA-Z0-9\x21\x23\x24\x25\x26\x27\x2a\x2b\x2d\x2f\x3d\x3f\x5e\x5f\x60\x7b\x7c\x7d\x7e]+)*$/;
            if (localPart.match(dotAtomExp)) {
                localPartValid = true;
            } else {
                // Try quoted string format.

                // Quoted-string characters are: DQUOTE *([FWS] qtext/quoted-pair) [FWS] DQUOTE
                // qtext: Non white space controls, and the rest of the US-ASCII characters not
                //   including "\" or the quote character
                var quotedStringExp = /^\x22([\x20\x09\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e])*[\x01-\x08\x0b\x0c\x0e-\x1f\x7f]?\x22$/;
                if (localPart.match(quotedStringExp)) {
                    localPartValid = true;
                }
            }

            // Validate hostname.
            var hostnamePartValid = true;

            // Split hostname into parts.
            var domainPart,
                domainParts = hostnamePart.split('.');

            // Validate TLD.
            var tld = domainParts[domainParts.length - 1];
            if (!tld) {
                hostnamePartValid = false;
            }

            // Validate remaining domain parts.
            var domainPartExp = /^[a-z0-9\x2d]{1,63}$/i;

            for (var i = 0; i < domainParts.length - 1; i++) {
                domainPart = domainParts[i];

                // Check dash (-) does not start, end or appear in 3rd and 4th positions.
                if ((domainPart.indexOf('-') === 0) || ((domainPart.length > 2) && (domainPart.indexOf('-', 2) === 2) && (domainPart.indexOf('-', 3) === 3)) || (domainPart.indexOf('-') === domainPart.length - 1)) {
                    hostnamePartValid = false;
                    break;
                }

                // Check each domain part.
                if (!domainPart.match(domainPartExp)) {
                    hostnamePartValid = false;
                    break;
                }
            }

            // Both local part and hostname must be valid.
            return localPartValid && hostnamePartValid;
        }
    }
});
