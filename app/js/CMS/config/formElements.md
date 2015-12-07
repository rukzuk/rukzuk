# Form Elements v2

Form elements used for dynamic forms (GeneratedFormPanel).
This version 2 improves the creation of forms by hand - i.e. using a text editor.
The result is translated to the old format for compatibility reasons.

## Definition of Form Elements

Folder: formElements
File: JSON File per Element
Format: The old full-style (v1) format with `descr` and `params` objects.

Each form element takes a `descr` and a `params` property, where `descr` is
used to render a menu item in form editor, and provides additional information
"allowChildNodes" and "hasValue", which are used internally in form editor.
User configurable items are stored in the 'params' array. Each item is an Ext
config object that will produce a corresponding form item.
A fixed value can be applied using `xtype: null`.

### Example

```js
  // create a "Text field" entry that provides a value
  descr: { Text: 'Text Field', iconCls: 'field', hasValue: true },
  params: [{
      name: 'xtype',  //
      value: 'text', // establishes a form item with xtype=text
      xtype: null // the user cannot change this
  }, {
      name: 'title', // the form item's title property can be changed
      xtype: 'textfield', // ...via a textfield element...
      value: 'Default', // ...which is prefilled with "Default"...
      fieldLabel: 'Enter title' // ...and labeled "Enter title"
  }]
```

### NOTE

If you want to translate string anywhere in params array you can't use `CMS.i18n()` function
because the objects of params will be copied to the newly created modules moduleData.json.
Use the special string-based `__i18n_KEY` macro.


## Form Elements Index

The index provides O(1) access to form Elements and their params without changing
the format. This index is generated on build time (see /tools/form/tasks/createFormElements.js).

```json
{
    "SplitCol": {
        idx: 3,
        paramIdx: {
            "xtype": 1,
            "layout": 2
        }
    }
}
```

## formConfig

FormConfig is a simplified format for ExtJS form definitions.

### Build a formConfig object

Soon it should be possible to use this format instead the full-style (copy) used in "formGroupData" of form.json in modules.
For now we use this format in PageType Forms as well as WebsiteSettings Forms.

```js
var formConfig = [{
  "type": "Button",
  "CMSvar": "myNum",
  "fieldLabel": "Super Button 1"
}, {
  "type": "Button",
  "CMSvar": "myNum",
  "fieldLabel": "Super button 2",
  "_items": [{
	  "type": "Button",
	  "CMSvar": "myNum",
	  "fieldLabel": "Super Button 1"
	}]
}]
```

### Convert legacy to formConfig

Use `grunt convertForm` task.

#### Known Issues

* ImagePicker may be converted to a FileDownloadPicker (because they have the same xtype)