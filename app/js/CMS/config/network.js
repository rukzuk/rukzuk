Ext.ns('CMS.config');

(function () {

    var sec = 1000;
    var min = 60 * sec;

    /**
    * @member CMS.config
    */
    Ext.apply(CMS.config, {
        // client-side request compression
        requestCompression: true,
        requestCompressionMinSize: 4096,

        postKeyName: 'params',
        urlPrefix: '/' + ('v-' + Date.now()) + '/app/service/',
        ajaxIntervals: { // in ms
            heartbeat: 1 * min,
            publishBuildUpdate: 2 * sec
        },
        ajaxTimeouts: { // in ms
            'default': 20 * sec,

            buildWebsite: 20 * min,
            cloneWebsite: 5 * min,
            deleteAlbum: 2 * min,
            deleteMedia: 2 * min,
            deleteWebsite: 5 * min,
            editTemplate: 5 * min, /* TODO remove when reparsing is faster */
            exportModule: 2 * min,
            exportTemplate: 2 * min,
            exportWebsite: 10 * min,
            importWebsite: 10 * min,
            publishBuild: 10 * min
        },
        urls: {
            // back-end services
            addGroupsToUser: 'user/addgroups',
            addUsersToGroup: 'group/addusers',
            buildWebsite: 'builder/buildWebsite',
            cancelImport: 'import/cancel',
            changeUserPassword: 'user/changepassword',
            cloneGroup: 'group/copy',
            clonePage: 'page/copy',
            cloneWebsite: 'website/copy',
            copyPasteTemplates: 'template/copypaste',
            createAlbum: 'album/create',
            createGroup: 'group/create',
            createPreviewTicket: 'shortener/createRenderTicket',
            createTemplate: 'template/create',
            createTemplateSnippet: 'templatesnippet/create',
            createUser: 'user/create',
            createWebsite: 'website/create',
            deleteAlbum: 'album/delete',
            deleteGroup: 'group/delete',
            deleteMedia: 'media/delete',
            deleteModule: 'modul/delete',
            deletePage: 'page/delete',
            deleteTemplate: 'template/delete',
            deleteTemplateSnippet: 'templatesnippet/delete',
            deleteUser: 'user/delete',
            deleteWebsite: 'website/delete',
            disablePublishing: 'website/disablePublishing',
            downloadBuild: 'cdn/getBuild',
            downloadMedia: 'cdn/get',
            editAlbum: 'album/edit',
            editColorScheme: 'website/editcolorscheme',
            editGroup: 'group/edit',
            editGroupPageRights: 'group/setpagerights',
            editMedia: 'media/edit',
            editModule: 'modul/edit',
            editModuleMeta: 'modul/editMeta',
            editPage: 'page/edit',
            editPageMeta: 'page/editMeta',
            editResolution: 'website/editresolutions',
            editTemplate: 'template/edit',
            editTemplateMeta: 'template/editMeta',
            editTemplateSnippet: 'templatesnippet/edit',
            editUser: 'user/edit',
            editWebsite: 'website/edit',
            editWebsiteSettings: 'websitesettings/editmultiple',
            exportModule: 'export/modules',
            exportPage: 'export/pages',
            exportTemplate: 'export/templates',
            exportTemplateSnippets: 'export/templatesnippets',
            exportWebsite: 'export/website',
            getAllAlbums: 'album/getall',
            getAllMedia: 'media/getAll',
            getAllModules: 'modul/getall',
            getAllTemplateSnippets: 'templatesnippet/getAll',
            getAllTemplates: 'template/getall',
            getAllUserGroups: 'group/getall',
            getAllUsers: 'user/getall',
            getAllWebsiteBuilds: 'builder/getWebsiteBuilds',
            getAllWebsites: 'website/getAll',
            getAllWebsiteSettings: 'websitesettings/getAll',
            getAllPageTypes: 'page/getAllPageTypes',
            getCurrentUserInfo: 'user/info',
            getMedium: 'media/getById',
            getMultipleMedia: 'media/getMultipleByIds',
            getNavigationPrivileges: 'group/getpagerights',
            getPage: 'page/getById',
            getTemplate: 'template/getbyid',
            getTemplateSnippet: 'templatesnippet/getById',
            getWebsite: 'website/getbyid',
            heartbeat: 'heartbeat/poll',
            importFile: 'import/file',
            importLocal: 'import/local',
            insertPage: 'page/create',
            itemLock: 'lock/lock',
            itemUnlock: 'lock/unlock',
            login: 'user/login',
            logout: 'user/logout',
            moveMedia: 'media/batchmove',
            movePage: 'page/move',
            optinUser: 'user/optin',
            overwriteConflicts: 'import/overwrite',
            previewPageById: 'render/page',
            previewTemplateById: 'render/template',
            publishBuild: 'builder/publish',
            removeGroups: 'user/removegroups',
            removeUserFromGroup: 'group/removeusers',
            renderTemplate: 'render/template',
            renderTemplateById: 'render/template',
            renderPage: 'render/page',
            renderPageById: 'render/page',
            requestPassword: 'user/renewpassword',
            screenshot: 'cdn/getscreen',
            sendFeedback: 'feedback/send',
            sendPasswordMail: 'user/register',
            streamMedia: 'cdn/get',
            upload: 'media/upload',
            validateOptin: 'user/validateoptin',
            viewLog: 'log/get'
        },
        params: {
            downloadMedia: { type: 'download' },
            getAllMedia: { maxIconWidth: 100, maxIconHeight: 100, limit: 50 },
            getAllMediaParamNames: { start: 'start', limit: 'limit', sort: 'sort', dir: 'direction' },
            previewPageById: { mode: 'preview' },
            previewTemplateById: { mode: 'preview' },
            renderTemplate: { mode: 'edit' },
            renderTemplateById: { mode: 'preview' },
            renderPage: { mode: 'edit' },
            renderPageById: { mode: 'preview' },
            streamMedia: { type: 'stream' }
        },
        roots: {
            getAllAlbums: 'data.albums',
            getAllMedia: 'data.media',
            getAllMediaTotal: 'data.total',
            getAllModules: 'data.modules',
            getAllTemplateSnippets: 'data.templatesnippets',
            getAllTemplates: 'data.templates',
            getAllUserGroups: 'data.groups',
            getAllUsers: 'data.users',
            getAllWebsiteBuilds: 'data.builds',
            getAllWebsites: 'data.websites',
            getAllWebsiteSettings: 'data.websiteSettings',
            getAllPageTypes: 'data.pageTypes',
            getMultipleMedia: 'data.media',
            getNavigation: 'data',
            getNavigationPrivileges: 'data.navigation'
        }
    });

    Ext.iterate(CMS.config.urls, function (key, value, urls) {
        // set default params
        if (!CMS.config.params[key]) {
            CMS.config.params[key] = {};
        }
        // convert to absolute URL to prevent problems with form submit
        if (value) {
            urls[key] = SB.util.toAbsoluteUrl((CMS.config.urlPrefix || '') + value);
        } // else leave original (falsy) value for debugging - TrafficManager will show error
    });

    // non-prefixed urls
    Ext.apply(CMS.config.urls, {
        absoluteBasePath: '/app/', // use if you need an absolute address (i.e. use the path inside of TheIframe)
        errorImg: 'images/error.png',
        pluploadFlash: 'js/plupload/js/plupload.flash.swf',
        zipjsWorkerScriptsPath: 'js/zipjs/',
        imagePath: 'images/',
        moduleIconPath: 'images/icons/',
        emptySvgUrl: 'images/empty.svg',

        // base url for the backend image CDN (used in X-doc-API getImageUrl)
        mediaCdnBaseUrl: '/app/service/cdn/get/params/',

        newWebsiteJson: 'exports/websites.json',
        newWebsitePreviewImageUrlTpl: 'exports/{0}/preview.jpg'

        // mocks:
        //shareInfoByWebsiteId: 'mock/shareInfoByWebsiteId.json.php',
        //shareDeleteByWebsiteId: 'mock/basic-response.json.php',
        //shareSave: 'mock/shareSave.json.php'

    });
})();
