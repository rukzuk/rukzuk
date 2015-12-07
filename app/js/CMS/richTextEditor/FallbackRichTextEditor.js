Ext.ns('CMS.richTextEditor');

/**
* @class CMS.richTextEditor.FallbackRichTextEditor
* @extends CMS.richTextEditor.RichTextEditor
*/
CMS.richTextEditor.FallbackRichTextEditor = Ext.extend(CMS.richTextEditor.RichTextEditor, {

    /**
    * @private
    * Returns additional TinyMCE settings which will be merged with {@link #getTinyMCEConfigDefaults} later on
    * @return {Object} TinyMCE settings
    */
    getTinyMCEConfig: function () {
        return {
            content_css: 'js/tiny_mce/themes/sbcms/skins/o2k7/content.css'
        };
    },

    /**
    * @private
    * This method will be called after each TinyMCE is initialized. Use this e.g. to add custom event listeners.
    * @param {tinyMCE.Editor} editor
    */
    onTinyMCESetup: function (editor) {
        var rte = this;

        editor.onFocus.add(function (ed) {
            //clicking in the toolbar shouldn't be handled as the editor where focused, so return
            if (rte.toolbar.isActive()) {
                return;
            }
            console.log('[fRTE] editor focused', ed);

            rte.toolbar.enable();
            rte.toolbar.syncControls(ed.settings.CMSenabledControls, ed.settings.CMScustomStyles);
            rte.syncButtonStates(ed, ed.getBody(), { parents: [] });
        });

        editor.onBlur.add(function (ed) {
            //clicking in the toolbar shouldn't be handled as the editor where blurred, so return
            if (rte.toolbar.isActive()) {
                return;
            }
            console.log('[fRTE] editor blurred', ed);

            delete rte.currentNode;

            rte.toolbar.disable();
        });
    }

});
