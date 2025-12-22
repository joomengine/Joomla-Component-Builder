# v6.1.4-beta3

- Fix: Add missing import of `GuidHelper` in `FieldXML.php` to avoid fatal error when compiling subforms. #1277
- Refactor the File Upload Manager to achieve improved maintainability, greater customization flexibility, and easier extensibility.

# v6.1.4-beta

- Add Development Valuation Model to calculate JCB component pricing.
- Add functionality to push JCB packages to Self hosted Git system that uses a self signed SSL.
- Add functionality that will include Validation Rules linked to fields in JCB packages. #1273
- Refactor and Improve Router Helper Class to new standard Site Router practice.
- Refactor and Improve JCB Search engine for better searching of JCB areas.
- Fix bug in the search editor in JCB Search Engine.
- Fix issue where Custom Gets linked to a Site view did not ship correctly with JCB packages. #1272
- Fix issue where the Icon linked to a Custom Admin Menu did not ship with JCB packages. #1271
- Fix and Improve Normalize Class to better handle different files Linked to JCB packages. #1274
- Remove batch feature from JCB compiler Templates due to lack of use and outdated code.
- Refactor Utilities/Response classes for Joomla 6+ compatibility and improved version-agnostic behavior.

# v6.1.4-alpha

- Fix the linked [new] button to use the correct init_default params.
- Fix the Components list view to better display long website urls.
- Fix language text area css. #1264
- Refactor the JS selText method.
- Add fix for empty currentFullPath in the Structuresingle class.
- Fix the buildSelectPart method to not access an empty select value in the Queries class.
- Fix that ensure that we always load and int into loadUserById.
- Fix the subform permission behaviour to use removeField.
- Add version 6 to the version 5 update server, for easier upgrade path.
- Fixed linked admin views with New and New & Close buttons to correctly initialize fields using global unique IDs and default conventions.
- Resolved missing language strings when linked admin views are also configured as site edit views.
- Corrected misconfiguration of the isModal property for site-linked views to default to false since modals are not yet supported on the site area.
- Enhanced security in edit views by moving access validation into the model, ensuring protection even when Joomla bypasses the controller layer.
- Added user access verification logic in the model to prevent unauthorized direct item loading.
- Refactored the view class to introduce a class constructor that initializes key dependencies more reliably.
- Began aligning the view class structure with Joomla 6's updated MVC architecture for future compatibility.
- Improved initialization handling in the view to reduce dependency errors during rendering.
- Fixed a bug caused by missing option settings in the model configuration.
- Updated and refined the site view class for greater consistency with backend view logic.
- Continued progressive improvements across view classes to ensure compatibility with Joomla's evolving MVC patterns.
- Fix permission issue in the list view. #1266
- Refactor the list view buttons.
- Refactor the getActions helper method.
- Rename Custom Buttons tab to Toolbar for clearer grouping of toolbar functions. #1268
- Add options for overriding default toolbars in all Admin, Site, and Custom Admin Views. #1268
- Refactor toolbar into version specific classes to resolve getToolbar errors. #1267
- Add method to unify getDatabase handling across Joomla versions and prevent undefined method calls. #1270
- Fix php8.3 deprecation warnings in compiler templates. #1265
- Refactor and enhance the JCB Email Helper class for a more robust and maintainable design.
- Fix an issue where the Email Helper send() function was executed twice.
- Fix a issue where layouts added to the PHP Ajax Model were not loaded during compilation.
- Add new functionality to the toolbar override system, enabling the inclusion of custom buttons via placeholders.
- Fix the Compiler success message to display both build seconds and project weeks.

# v6.1.3

- Stability update addressing a post-release field issue. #1262
- Fix a security vulnerability in custom code redirect URL validation.
- Add an alert to the Compiler view when no Components exist. #1263

# v6.1.2

- Add Joomla 6 build option
- Fix the template and layout linker for packages.
- Add native module builder for Joomla 4/5
- Refactor dynamic get methods into dedicated classes
- Move Joomla DB handling into compiler injector flow
- Fix auto-check(in) method for Joomla 4/5 compatibility
- Migrates view HTML classes to use getModel() directly instead of the deprecated magic get() calls to model methods.
- Refactores event handling (contentPrepare, titlePrepare, contentBeforeDisplay, contentAfterDisplay) to use Joomla 5's native event dispatcher via the model's new getDispatcher() method.
- Updates table classes to properly support NULL values, both in the store() method and in table variable definitions. #1245
- Extractes the setAutoCheckIn() and setCheckInCall() logic into a dedicated CheckInNow class for cleaner design.
- Replace all direct $app->input property calls with the recommended $app->getInput() method across the entire codebase.
- Add Joomla 6 build option :)
- Fix Custom Rule Validation Bug
- Unpublish Joomla 6 Backward Compatibility Plugin
- Refactors the Compiler Model by dropping deprecated calls and adopting Joomla 5+ conventions
- Refactors Compiler Controller to remove deprecated usage and follow Joomla 5+ best practices
- Adds check to ensure expert mode subforms in the Demo Component remain set and unchanged when user lacks edit permissions
- Improves validation handling of Fields by ensuring field validation rules are properly registered
- Replace StringHelper with the correct Super Power key so it loads in the Compiler Controller
- Add functionality to add component changelog into the package if configured.
- Refactor PHP classes for building HTML view files in admin and site views to align with Joomla 5+ standards.
- Fix issues in the site view HtmlView caused by the refactor, including missing class calls and outdated function references.
- Fix issue where the DatabaseSchemaCheckAll pulled all Component Builder tables into DB on install. #1253
- Fix issue in the Dynamic Get when the value key is 0 it wouldn't add the 'WHERE' statement to the generated code. #1254
- Change radio buttons with a empty option to save Datatype CHAR instead of TINYINT as other radio buttons. #1252
- Add extra xml details to Modules built with JCB that adds advance options to the module. #1248
- Remove setDocumentTitle method from the admin view since it is not being used by Joomla. #1255
- Refactor compiler Move file update to classes.
- Refactor compiler Move plugin and module fields and rules files mover to classes.
- Fix issue where custom powers was not added to PowerloaderHelper. #1256
- Fix normalization issue when compiling on Windows systems. #1219
- Fix installer issue on Joomla 6 where components containing multiple plugins failed during installation.
- Completely refactors the Compiler Dashboard to align with Joomla 5+ architecture and Bootstrap 5 standards.
- Add a default VARCHAR(36) length to easily create fields intended to store GUID values.
- Update the file uploader in the Demo Component for more dynamic file display and naming.
- Update the SQL file dump generator to handle large SQL dumps in batches, ensuring safe and reliable database imports.
- Update example hints in the field expert mode options.

# v5.1.2

- Fix the template and layout linker for packages.
- Add native module builder for Joomla 4/5
- Refactor dynamic get methods into dedicated classes
- Move Joomla DB handling into compiler injector flow
- Fix auto-check(in) method for Joomla 4/5 compatibility
- Migrates view HTML classes to use getModel() directly instead of the deprecated magic get() calls to model methods.
- Refactores event handling (contentPrepare, titlePrepare, contentBeforeDisplay, contentAfterDisplay) to use Joomla 5's native event dispatcher via the model's new getDispatcher() method.
- Updates table classes to properly support NULL values, both in the store() method and in table variable definitions. #1245
- Extractes the setAutoCheckIn() and setCheckInCall() logic into a dedicated CheckInNow class for cleaner design.
- Replace all direct $app->input property calls with the recommended $app->getInput() method across the entire codebase.
- Add Joomla 6 build option
- Fix Custom Rule Validation Bug
- Unpublish Joomla 6 Backward Compatibility Plugin
- Refactors the Compiler Model by dropping deprecated calls and adopting Joomla 5+ conventions
- Refactors Compiler Controller to remove deprecated usage and follow Joomla 5+ best practices
- Adds check to ensure expert mode subforms in the Demo Component remain set and unchanged when user lacks edit permissions
- Improves validation handling of Fields by ensuring field validation rules are properly registered
- Replace StringHelper with the correct Super Power key so it loads in the Compiler Controller
- Add functionality to add component changelog into the package if configured.
- Refactor PHP classes for building HTML view files in admin and site views to align with Joomla 5+ standards.
- Fix issues in the site view HtmlView caused by the refactor, including missing class calls and outdated function references.
- Fix issue where the DatabaseSchemaCheckAll pulled all Component Builder tables into DB on install. #1253
- Fix issue in the Dynamic Get when the value key is 0 it wouldn't add the 'WHERE' statement to the generated code. #1254
- Change radio buttons with a empty option to save Datatype CHAR instead of TINYINT as other radio buttons. #1252
- Add extra xml details to Modules built with JCB that adds advance options to the module. #1248
- Remove setDocumentTitle method from the admin view since it is not being used by Joomla. #1255
- Refactor compiler Move file update to classes.
- Refactor compiler Move plugin and module fields and rules files mover to classes.
- Fix issue where custom powers was not added to PowerloaderHelper. #1256
- Fix normalization issue when compiling on Windows systems. #1219
- Fix installer issue on Joomla 6 where components containing multiple plugins failed during installation.
- Completely refactors the Compiler Dashboard to align with Joomla 5+ architecture and Bootstrap 5 standards.
- Add a default VARCHAR(36) length to easily create fields intended to store GUID values.
- Update the file uploader in the Demo Component for more dynamic file display and naming.
- Update the SQL file dump generator to handle large SQL dumps in batches, ensuring safe and reliable database imports.
- Update example hints in the field expert mode options.

# v4.1.2

- Fix the template and layout linker for packages.
- Add native module builder for Joomla 4/5
- Refactor dynamic get methods into dedicated classes
- Move Joomla DB handling into compiler injector flow
- Fix auto-check(in) method for Joomla 4/5 compatibility
- Migrates view HTML classes to use getModel() directly instead of the deprecated magic get() calls to model methods.
- Refactores event handling (contentPrepare, titlePrepare, contentBeforeDisplay, contentAfterDisplay) to use Joomla 5's native event dispatcher via the model's new getDispatcher() method.
- Updates table classes to properly support NULL values, both in the store() method and in table variable definitions. #1245
- Extractes the setAutoCheckIn() and setCheckInCall() logic into a dedicated CheckInNow class for cleaner design.
- Replace all direct $app->input property calls with the recommended $app->getInput() method across the entire codebase.
- Add Joomla 6 build option
- Fix Custom Rule Validation Bug
- Unpublish Joomla 6 Backward Compatibility Plugin
- Refactors the Compiler Model by dropping deprecated calls and adopting Joomla 5+ conventions
- Refactors Compiler Controller to remove deprecated usage and follow Joomla 5+ best practices
- Adds check to ensure expert mode subforms in the Demo Component remain set and unchanged when user lacks edit permissions
- Improves validation handling of Fields by ensuring field validation rules are properly registered
- Replace StringHelper with the correct Super Power key so it loads in the Compiler Controller
- Add functionality to add component changelog into the package if configured.
- Refactor PHP classes for building HTML view files in admin and site views to align with Joomla 5+ standards.
- Fix issues in the site view HtmlView caused by the refactor, including missing class calls and outdated function references.
- Fix issue where the DatabaseSchemaCheckAll pulled all Component Builder tables into DB on install. #1253
- Fix issue in the Dynamic Get when the value key is 0 it wouldn't add the 'WHERE' statement to the generated code. #1254
- Change radio buttons with a empty option to save Datatype CHAR instead of TINYINT as other radio buttons. #1252
- Remove setDocumentTitle method from the admin view since it is not being used by Joomla. #1255
- Refactor compiler Move file update to classes.
- Refactor compiler Move plugin and module fields and rules files mover to classes.
- Fix issue where custom powers was not added to PowerloaderHelper. #1256
- Fix normalization issue when compiling on Windows systems. #1219
- Fix installer issue on Joomla 6 where components containing multiple plugins failed during installation.
- Completely refactors the Compiler Dashboard to align with Joomla 5+ architecture and Bootstrap 5 standards.
- Add a default VARCHAR(36) length to easily create fields intended to store GUID values.
- Update the file uploader in the Demo Component for more dynamic file display and naming.
- Update the SQL file dump generator to handle large SQL dumps in batches, ensuring safe and reliable database imports.
- Update example hints in the field expert mode options.

# v3.2.5

- Add [AllowDynamicProperties] in the base view class for J5
- Move the _prepareDocument  above the display call in the base view class
- Remove all backward compatibility issues, so JCB will not need the [Backward Compatibility] plugin to run.
- Added new import powers for custom import of spreadsheets.
- Move the setDocument and _prepareDocument above the display in the site view and custom admin view.
- Update the trashhelper layout to work in Joomla 5.
- Add AllowDynamicProperties (Joomla 4+5) to view class to allow Custom Dynamic Get methods to work without issues.
- Fix Save failed issue in dynamicGet. #1148
- Move all [TEXT, EDITOR, TEXTAREA] fields from [NOT NULL] to [NULL]
- Add the DateHelper class and improve the date methods.
- Add simple SessionHelper class.
- Add first classes for the new import engine.
- Improve the [VDM Registry] to be Joomla Registry Compatible
- Move all registries to the [VDM Registry] class
- Fix Checked Out to be null and not 0. (#1194)
- Fix created_by, modified_by, checked_out fields in the compiler of the SQL. (#1194)
- Update all core date fields in table class. (#1188)
- Update created_by, modified_by, checked_out fields in table class.
- Implementation of the decentralized Super-Power CORE repository network. (#1190)
- Fix the noticeboard to display Llewellyn's Joomla Social feed