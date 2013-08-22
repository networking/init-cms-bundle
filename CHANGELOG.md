CHANGELOG
=========

### 2013-08-22
* [BC BREAK] Introduced the ContentRoute Object depends on a new field templateName
  * Use the Template Key and not the template path to select templates in the page form
  * Added field templateName to the ContentRoute object so that the same twig template can be
    used but with different parameters
  * Added ContentRouteListener so that the controller field is automatically updated/inserted when
    selecting a template see UPGRADE.md
  * Addition of the "controller" setting for templates, allows for the definition and saving of the controller
    to be used with a specific template.

* Fixed the validation of duplicate full Page URL
* Added setting "error_type" to the config/cms/mopa_boostrap.yml
* Fixed bug: LayoutBlockListener was listening for the wrong gallery object
* Fixed bug: infinite loop caused by flushing persisted page when calling autoPageDraft() method
