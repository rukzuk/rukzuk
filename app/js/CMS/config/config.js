/**
* Namespace for application configuration
* @class CMS.config
* @singleton
*/
Ext.ns('CMS.config');

(function () {
    var linkResolver = CMSSERVER && CMSSERVER.data && CMSSERVER.data.urls && CMSSERVER.data.urls.linkResolver;


    Ext.apply(CMS.config, {
        productName: '__i18n_html.pageTitle',
        debugMode: /debug/.test(window.location.search),
        debugKey: {
            keyCode: Ext.EventObject.F1,
            shiftKey: true,
            ctrlKey: false,
            altKey: false,
            description: 'Shift-F1'
        },
        nan: {
            keyCode: Ext.EventObject.F2,
            shiftKey: true,
            ctrlKey: true,
            altKey: false,
            description: 'Shift-F2',
            url: '%68%74%74%70%3a%2f%2f%6e%79a%6e%2e%63a%74%2f'
        },
        useI18n: true, // DEBUG - should always be true in production version

        /* possible fields to filter CMS.on() events */
        apiFilterFields: 'moduleId,id',

        /*
         * module editor
         */
        moduleTypes: {
            defaultModule: 'default',
            extension: 'extension',
            root: 'root'
        },

        importTypes: {
            website: 'WEBSITE',
            template: 'TEMPLATE',
            templateSnippet: 'TEMPLATESNIPPET',
            module: 'MODULE'
        },

        /* rights for newly created user groups */
        defaultGroupRights: [],

        /* Ext internal */
        ddGroups: {
            modules: 'ddGroupModules',
            templates: 'ddGroupTemplates',
            pages: 'ddGroupPages'
        },

        /* data fields that need to be copied from the unitStore to the corresponding treeNode's attributes for correct rendering */
        treeNodeAttributeData: 'children expanded ghostContainer moduleId id inserted name visibleFormGroups',

        /* define which unit fields are NOT affecting the UI, thus not enforcing a refresh on change */
        nonUiAffectingUnitFields: ['name', 'description', 'visibleFormGroups', 'ghostContainer'],

        /* names of form params which can be changed via the X-doc-API CMS.updateFormFieldConfig() */
        allowedUnitFormFieldParamsApiOverride: ['options', 'fieldLabel', 'locked'],

        /*
         * Insite editing
         */
        insiteEditingEnabled: true,
        unitElStyleMarker: {
            start: '/* {CSS-UNIT:{unitId}} */',
            end: '/* {/CSS-UNIT:{unitId}} */'
        },
        unitElClassName: 'isModule',
        unitElTagName: 'div', // used to speed up DOM querying. '*' to query everything
        inlineSectionHTMLAttribute: 'data-cms-editable',
        inlineSectionTagName: '*', // used to speed up DOM querying. '*' to query everything
        renderBuffer: 100, // time in ms between form changes and render request. Useful to prevent multiple requests when changing slider

        /*
         * rich text editor
         */
        rteLinkHTMLAttribute: 'data-cms-link',
        rteLinkTypeHTMLAttribute: 'data-cms-link-type',
        rteLinkTypeHTMLAttributeValues: {
            internalPage: 'internalPage',
            internalMedia: 'internalMedia',
            internalMediaDownload: 'internalMediaDownload',
            external: 'external',
            mail: 'mail'
        },
        rteLinkAnchorHTMLAttribute: 'data-cms-link-anchor',

        /*
         * media db settings
         */
        media: {
            // the maximal file size for file uploads in media db
            maxFileSize: CMSSERVER && CMSSERVER.data && CMSSERVER.data.quota && CMSSERVER.data.quota.media.maxFileSize / (1024 * 1024) + 'mb'
        },
        //uploadFileExtensions: 'jpg,gif,png,pdf,doc,docx,xls,xlsx,ppt,pptx,css,js,woff,eot,ttf,svg,zip',

        // default website resolutions used when creating a new website
        defaultWebsiteResolutions: {
            enabled: true,
            data: [
                {
                    id: 'res1',
                    width: 980,
                    name: 'Tablet'
                },
                {
                    id: 'res2',
                    width: 767,
                    name: 'Smartphone'
                },
                {
                    id: 'res3',
                    width: 480,
                    name: 'Smartphone small'
                }
            ],
            dataDisabled: [
                {
                    id: 'res4',
                    width: 0,
                    name: 'Custom 1'
                },
                {
                    id: 'res5',
                    width: 0,
                    name: 'Custom 2'
                },
                {
                    id: 'res6',
                    width: 0,
                    name: 'Custom 3'
                }
            ]
        },

        // the default resolution object
        theDefaultResolution: {id: 'default', name: 'Default', width: Number.POSITIVE_INFINITY},

        // formElements Config for Module Editor
        disalbedComponentsInFormEditor: ['CMSclearablemediabutton', 'CMSalbumchooser'], // SBCMS-851 Make sure modules don't hold references to mediaDB items
        /*
         Legacy Form Elements Translation keys. They *might* be still in use.
         This comment keeps also the checkLang task happy (:
         '__i18n_formElements.displayMode'
         '__i18n_formElements.displayModeSliderSpinner'
         '__i18n_formElements.elementSlider'
         '__i18n_formElements.elementUnitChooser'
         '__i18n_formElements.moduleLabel'
         '__i18n_formElements.unitValue'
         */

        // fallback page type, used if the configured page type was deleted
        fallbackPageType: 'page',

        ///////////////////////////////////////////////////////////////////////////
        // External URLs
        ///////////////////////////////////////////////////////////////////////////

        // links
        helpDeskUrl: linkResolver + '/helpAndTutorials',
        tutorialUrl: linkResolver + '/videoEmbedGetStarted',
        connectDomainHelp: linkResolver + '/connectDomainHelp',
        externalFtpHostingHelp: linkResolver + '/externalFtpHostingHelp',
        gettingStartedUrl: linkResolver + '/gettingStarted',

        // iframe links
        // page displaying module marketing info in case the user does not have the right privileges to edit modules
        quotaModuleMarketing: linkResolver + '/quotaModuleMarketing',
        quotaWebsiteMarketing: linkResolver + '/quotaWebsiteMarketing',
        quotaWebhostingMarketing: linkResolver + '/quotaWebhostingMarketing',
        quotaExportMarketing: linkResolver + '/quotaExportMarketing'
    });

})();
