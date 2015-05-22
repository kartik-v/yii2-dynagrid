version 1.4.2
=============
**Date:** 22-May-2015

- (enh #40): Add `allowThemeSetting` to allow display/setup of theme.
- (enh #49): Add migrations to allow module based "auto install".
- (enh #52): Add Dutch translations.
- (bug #54): Fix dynagrid config save when `allowThemeSetting` is `false`.
- (enh #56): Reload Dynagrid jquery plugins after pjax grid refresh.
- (enh #57): Add Russian translations.

version 1.4.1
=============
**Date:** 13-Feb-2015

- (enh #45): Allow dynagrid to be used as a sub-module.
- Set copyright year to current.

version 1.4.0
=============
**Date:** 12-Jan-2015

- (bug #35): Typo fix for extra `{` in DynaGridDetail.
- (enh #43): Spanish translations added.
- Code formatting updates as per Yii2 standards.
- Revamp to use new Krajee base Module and TranslationTrait.

version 1.3.0
=============
**Date:** 25-Nov-2014

- PSR4 alias change
- Set dependency on Krajee base components
- Set release to stable
- Fix #33: Ensure correct form submission for pjax 
- Fix #34: Correct deletion of filter and sort settings

version 1.2.0
=============
**Date:** 25-Oct-2014 

- (enh #25): Implement dynagrid storage as an object.
- (enh #26): Add support to save grid search/filters.
- (enh #27): Add support to save grid sort.
- (enh #28): Add support to delete and modify personalization settings at grid level, or filter and sort level.
- (enh #29): Enhance refresh of dynagrid active form and plugin elements for PJAX reload.
- (bug #30): Fix dynagrid validation when no filterModel is supplied.


version 1.1.0
=============
**Date:** 19-Sep-2014 

- (bug #21): Ensure correct merging of gridOptions with theme options.
- (enh #22): modified entire modal to be auto-moved after the dynagrid container
- (enh #23): added support for Pjax based rendering
- (enh #24): Enhance dynagrid to be more extensible by allowing to change grid options at runtime.
- PSR 4 alias change


version 1.0.0
=============
**Date:** 01-May-2014

Initial release (01-May-2014). The major features provided by the **yii2-dynagrid** module are:

- Personalize, set, and save grid page size at runtime. You can set the minimum and maximum page size allowed.
- Personalize the grid columns display through drag and drop. Reorder grid columns and set the visibility of needed columns, and allow users to save this setting. 
  Control which columns can be reordered by users through predefined columns setup. Predetermine which of your desired columns will be always fixed to the left or right by 
  default.
- Personalize grid appearance and set the grid theme. This will offer advanced customization to the grid layout. It allows users to virtually style grid 
  anyway they want, based on how you define themes and extend them to your users. With yii2-grid extension, and panels, you can easily setup themes for 
  users in many ways. You have an ability to setup multiple themes in your module configuration, and allow users to select one of them. The extension by 
  default includes some predefined themes for you to get started.
- Allow you to save the dynamic grid configuration specific to each user or global level. One of the following storage options are made available to store 
  the personalized grid configuration:
  - Session Storage (default)
  - Cookie Storage 
  - Database Storage
- The extension automatically validates and loads the saved configuration based on the stored settings.

Additional Fixes in v1.0.0:

- (bug #2): Class 'kartik\sortable\Sortable' not found.
- (bug #4): Only variables should be passed by reference.
- (bug #6): Bug in getDataFromDb.
- (bug #7): Before and after do not work.
- (bug #12): Mention that the personalization is stored with the widget ID.
- (bug #12): Mention that the personalization is stored with the widget ID.
- (bug #12): Mention that the personalization is stored with the widget ID.
- (bug #13): You must setup a unique identifier for DynaGrid within "options['id']".
- (enh #15): Added `showPersonalize` property to configure whether personalize will be shown.
- (bug #16): Correct order of visible columns (DB) in the personalize modal.
- (bug #17): Enable display of other toolbar buttons when `showPersonalize` is false.
- (bug #18): Allow empty keys on DB.
- (enh #19): If storage is empty display all valid columns
- (enh #20): Private variables changed to protected.

