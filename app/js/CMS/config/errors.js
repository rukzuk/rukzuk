Ext.ns('CMS.config');

Ext.apply(CMS.config, {
    specialErrorCodes: {
        noSession: '5',
        pastedModulesExisting: '113',
        pastedTemplateExisting: '313',
        importConflict: '11',
        lockRelatedErrors: /^15..$/
    },
    /**
    * @property messageTexts
    * @type Object
    * @member CMS.config
    */
    messageTexts: {
        1: '{msg}'
    },
    /**
    * @property errorTexts
    * @type Object
    * @member CMS.config
    */
    errorTexts: {
        'generic': '__i18n_error_generic',
        'invalidJSON': '__i18n_error_invalidJSON',
        'unexpectedResponse': '__i18n_error_unexpectedResponse',
        'connectionBroken': '__i18n_error_connectionBroken',

        // HTTP status codes
        'Not Found': '__i18n_error_NotFound',
        'Forbidden': '__i18n_error_Forbidden',
        'Internal': '__i18n_error_Internal',
        'Timeout': '__i18n_error_Timeout',
        'Maintenance': '__i18n_error_Maintenance',
    },
    /**
    * @property httpErrors
    * @type Object
    * @member CMS.config
    */
    // Treat HTTP errors as if a successful response with error code were received
    httpErrors: {
        403: [{
            code: 'Forbidden'
        }],
        404: [{
            code: 'Not Found'
        }],
        408: [{
            code: 'Timeout'
        }],
        500: [{
            code: 'Internal'
        }],
        503: [{
            code: 'Maintenance'
        }],
    },
    /**
    * @property genericErrors
    * @type Object
    * @member CMS.config
    */
    // Treat internal errors as if a successful response with error code were received
    genericErrors: {
        invalidJSON: [{
            code: 'invalidJSON'
        }],
        unexpectedResponse: [{
            code: 'unexpectedResponse'
        }],
        connectionBroken: [{
            code: 'connectionBroken'
        }],
        timeout: [{
            code: 'Timeout'
        }],
        unknown: [{}]
    },
    /**
    * @property maxErrorDetails
    * @type Integer
    * @member CMS.config
    */
    maxErrorDetails: 10,
    /**
    * @property maxMessageItems
    * @type Integer
    * @member CMS.config
    */
    maxMessageItems: 10
});
