Change Log: `yii2-dynagrid`
===========================

## Version 1.5.4

**Date:** 29-Apr-2022

- (enh #240): Enhance compatibility for PHP 8.1.

## Version 1.5.3

**Date:** 04-Mar-2022

- Enhance compatibility for PHP 8.1.

## Version 1.5.2

**Date:** 03-Sep-2021

- (enh #228): Enhancements to support Bootstrap v5.x.
- (enh #227): Correct grid pagination assignment.
- (enh #206): Update Spanish Translations.
- (enh #205): Update Italian translations.
- (enh #204): Correct namespace.

## Version 1.5.1

**Date:** 24-Feb-2019

- (bug #211): Correct bootstrap Modal class parsing.
- (enh #202, #203): Correct attribute label parsing.
- Update README.
- (enh #197): Correct current controller action refresh.

## Version 1.5.0

**Date:** 11-Sep-2018

- (enh #195): Ability to configure all icons.
- (bug #194): Correct dynagrid sortable initializations.

## Version 1.4.9

**Date:** 10-Sep-2018

- Move all source code to `src` directory.
- Updates to support Bootstrap v4.x.
- (enh #192): Update Spanish Translations.
- (enh #191): Correct m140101_100000_dynagrid.php.
- (enh #189): Update Ukranian Translations.

## Version 1.4.8

**Date:** 15-Feb-2018

- Update copyright year to current.
- (enh #188): Reset and cleanup bootstrap modals on pjax reload.
- (enh #183, #182): Update DynaGridStore.php for yii 2.0.13 support.
- (enh #180): Enhance dynagrid config form when all settings are hidden.
- (enh #177, #178): Update French Translations.
- (enh #176): Update German Translations.

## Version 1.4.7

**Date:** 23-Oct-2017

- (enh #170): Add ability to configure one's own module identifier.

## Version 1.4.6

**Date:** 15-Sep-2017

- (enh #169): Enhance to generate unique input identifiers for dynagrid config & settings (allows multiple dynagrids to be rendered on same page).
- (enh #168): Allow pageSize to be null when allowPageSetting is `false`.
- (enh #167): Enhance dialog for confirmation & prompts to use Krajee Dialog extension.
- (bug #166): Fix bug in client script causing dynagrid to break after filtering.
- (enh #165): Eliminate dependency on Yii session component for module init.
- (enh #163): Add ability to configure db connection component.
- Code optimizations for Dynagrid jQuery plugin.
- (enh #161): Enhance dynagrid form submission to prevent enter key submitting the form wrongly.
- Chronological ordering of issues for change log.
- (bug #156): Correct dynagrid detail minified JS file.
- (enh #154): Hash signature validation for security.
- (enh #153): Enhance filter/sort settings update for database storage.

## Version 1.4.5

**Date:** 07-Apr-2017

- Generate message files for all common languages.
- Update copyright year to current.
- Add github contribution and issue/PR logging templates.
- Code cleanup and enhance PHP Doc for all classes.
- (enh #151): Enhance pagination application for SqlDataProvider.
- (bug #149): Added missing hash tag for dynagrid id.
- (enh #148): Update Portuguese BR Translations.
- (enh #141, #145): Refresh data provider when using ArrayDataProvider .
- (enh #143): Update Greek Translations.
- (enh #138): Update German Translations.
- (enh #136): Update Dutch Translations.
- (enh #134): Update Simplified Chinese Translations.
- (enh #131): Add Unicode support for column name.
- (enh #128): Update Czech Translations.
- (enh #126): Multiple selection bug workaround.
- Implement trait and enhance #105 for better translations.
- Add branch alias for dev-master latest release.
- (enh #123): Update Hungarian Translations.
- (enh #119): More correct ordering info via `getAttributeOrders` for sort.
- (enh #116): Add Estonian Translations.
- (enh #114): Add `getColumns` method.
- (enh #109): Add Simplified Chinese Translations.
- (enh #105): Add Italian Translations and enhance translation for dynagrid category.
- (enh #96): Add Polish Translations.
- (enh #95): Update Spanish Translations.
- (enh #93): Update Portuguese BR Translations.
- (bug #92): Automatically move dynagrid related modal dialog content after dynagrid container.

## Version 1.4.4

**Date:** 04-Aug-2015

- (enh #84): Better validation for checking jquery plugin existence before reinit.
- (bug #73): Validate theme if it does not exist and set it to default module theme.
- (bug #69): Fix bug for `initS2Loading`.
- (bug #64): Apply sort correctly on init.
- (enh #63): Add Czech translations.

## Version 1.4.3

**Date:** 16-Jun-2015

- (enh #67): Set composer ## Version dependencies.
- (enh #66): Add column icon indicators for visible and hidden columns.
- (enh #63): Add Czech translations.
- (enh #62): Update German translations .
- (enh #61): Ability to disable `pagination` and `sort` for the grid.
- (enh #60): New property `allowPageSetting`.
- (enh #59): Allow setting unlimited page size.

## Version 1.4.2

**Date:** 22-May-2015

- (enh #57): Add Russian translations.
- (enh #56): Reload Dynagrid jquery plugins after pjax grid refresh.
- (bug #54): Fix dynagrid config save when `allowThemeSetting` is `false`.
- (enh #52): Add Dutch translations.
- (enh #49): Add migrations to allow module based "auto install".
- (enh #40): Add `allowThemeSetting` to allow display/setup of theme.

## Version 1.4.1

**Date:** 13-Feb-2015

- Set copyright year to current.
- (enh #45): Allow dynagrid to be used as a sub-module.

## Version 1.4.0

**Date:** 12-Jan-2015

- Revamp to use new Krajee base Module and TranslationTrait.
- Code formatting updates as per Yii2 standards.
- (enh #43): Spanish translations added.
- (bug #35): Typo fix for extra `{` in DynaGridDetail.

## Version 1.3.0

**Date:** 25-Nov-2014

- Fix #34: Correct deletion of filter and sort settings
- Fix #33: Ensure correct form submission for pjax 
- Set release to stable
- Set dependency on Krajee base components
- PSR4 alias change

## Version 1.2.0

**Date:** 25-Oct-2014 

- (bug #30): Fix dynagrid validation when no filterModel is supplied.
- (enh #29): Enhance refresh of dynagrid active form and plugin elements for PJAX reload.
- (enh #28): Add support to delete and modify personalization settings at grid level, or filter and sort level.
- (enh #27): Add support to save grid sort.
- (enh #26): Add support to save grid search/filters.
- (enh #25): Implement dynagrid storage as an object.


## Version 1.1.0

**Date:** 19-Sep-2014 

- PSR 4 alias change
- (enh #24): Enhance dynagrid to be more extensible by allowing to change grid options at runtime.
- (enh #23): added support for Pjax based rendering
- (enh #22): modified entire modal to be auto-moved after the dynagrid container
- (bug #21): Ensure correct merging of gridOptions with theme options.


## Version 1.0.0

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

- (enh #20): Private variables changed to protected.
- (enh #19): If storage is empty display all valid columns
- (bug #18): Allow empty keys on DB.
- (bug #17): Enable display of other toolbar buttons when `showPersonalize` is false.
- (bug #16): Correct order of visible columns (DB) in the personalize modal.
- (enh #15): Added `showPersonalize` property to configure whether personalize will be shown.
- (bug #13): You must setup a unique identifier for DynaGrid within "options['id']".
- (bug #12): Mention that the personalization is stored with the widget ID.
- (bug #7): Before and after do not work.
- (bug #6): Bug in getDataFromDb.
- (bug #4): Only variables should be passed by reference.
- (bug #2): Class 'kartik\sortable\Sortable' not found.