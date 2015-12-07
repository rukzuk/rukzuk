Ext.ns('CMS.config');

/**
* @member CMS.config
*/
Ext.apply(CMS.config, {
    tinyMCE: {

        allHTMLElementTypes: {
            //block level elements
            address: true,
            blockquote: true,
            div: true,
            h1: true,
            h2: true,
            h3: true,
            h4: true,
            h5: true,
            h6: true,
            p: true,
            pre: true,
            ul: true,
            //inline elements
            a: false,
            abbr: false,
            acronym: false,
            q: false,
            cite: false,
            code: false,
            em: false,
            span: false
        },

        /*
         * command reference: http://www.tinymce.com/wiki.php/Command_identifiers
         * browser commands: https://developer.mozilla.org/en/Midas & http://msdn.microsoft.com/en-us/library/ms533049%28VS.85%29.aspx
         */
        controlsConfig: {
            bold: {
                id: 'bold',
                execCommand: 'Bold',
                validElements: '-b/strong'
            },
            italic: {
                id: 'italic',
                execCommand: 'Italic',
                validElements: '-em/i'
            },
            underline: {
                id: 'underline',
                execCommand: 'Underline',
                validElements: 'span[style<text-decoration: underline;?text-decoration:underline;?text-decoration: underline; ?text-decoration: line-through;?text-decoration:line-through;?text-decoration: line-through; ]'
            },
            strikethrough: {
                id: 'strikethrough',
                execCommand: 'Strikethrough',
                validElements: 'span[style<text-decoration: underline;?text-decoration:underline;?text-decoration: underline; ?text-decoration: line-through;?text-decoration:line-through;?text-decoration: line-through; ]'
            },
            subscript: {
                id: 'subscript',
                execCommand: 'Subscript',
                validElements: '-sub'
            },
            superscript: {
                id: 'superscript',
                execCommand: 'Superscript',
                validElements: '-sup'
            },
            bullist: {
                id: 'bullist',
                execCommand: 'InsertUnorderedList',
                validElements: 'ul,li'
            },
            numlist: {
                id: 'numlist',
                execCommand: 'InsertOrderedList',
                validElements: 'ol,li'
            },
            link: {
                id: 'link',
                execCommand: 'mceLink',
                validElements: '-a[!href|target|title]'
            },
            unlink: {
                id: 'unlink',
                execCommand: 'unlink',
                validElements: ''
            },
            charmap: {
                id: 'insertchar',
                execCommand: 'mceCharMap',
                validElements: ''
            },
            undo: {
                id: 'undo',
                execCommand: 'Undo',
                validElements: ''
            },
            redo: {
                id: 'redo',
                execCommand: 'Redo',
                validElements: ''
            },
            style: {
                id: 'style',
                execCommand: '',
                validElements: ''
            },
            tableMenu: {
                id: 'tableMenu',
                execCommand: '',
                validElements: 'table[class],caption[class],thead,tfoot,tbody,tr[class],td[class|colspan|rowspan],th[class|colspan|rowspan]'
            },
            tableInsert: {
                id: 'tableInsert',
                execCommand: '',
                validElements: ''
            },
            tableDelete: {
                id: 'tableDelete',
                execCommand: 'mceTableDelete',
                validElements: ''
            },
            tableDeleteCol: {
                id: 'tableDeleteCol',
                execCommand: 'mceTableDeleteCol',
                validElements: ''
            },
            tableDeleteRow: {
                id: 'tableDeleteRow',
                execCommand: 'mceTableDeleteRow',
                validElements: ''
            },
            tableInsertColAfter: {
                id: 'tableInsertColAfter',
                execCommand: 'mceTableInsertColAfter',
                validElements: ''
            },
            tableInsertColBefore: {
                id: 'tableInsertColBefore',
                execCommand: 'mceTableInsertColBefore',
                validElements: ''
            },
            tableInsertRowAfter: {
                id: 'tableInsertRowAfter',
                execCommand: 'mceTableInsertRowAfter',
                validElements: ''
            },
            tableInsertRowBefore: {
                id: 'tableInsertRowBefore',
                execCommand: 'mceTableInsertRowBefore',
                validElements: ''
            },
            tableSplitCells: {
                id: 'tableSplitCells',
                execCommand: 'mceTableSplitCells',
                validElements: ''
            },
            tableMergeCells: {
                id: 'tableMergeCells',
                execCommand: 'mceTableMergeCells',
                validElements: ''
            }
        },

        alwaysEnabledControls: ['undo', 'redo', 'charmap']
    }
});
