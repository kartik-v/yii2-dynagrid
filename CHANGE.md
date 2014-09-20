version 1.0.0
=============
** Date:** 19-Sep-2014 

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
- (bug #13): You must setup a unique identifier for DynaGrid within "options['id']".
- (enh #15): Added `showPersonalize` property to configure whether personalize will be shown.
- (bug #16): Correct order of visible columns (DB) in the personalize modal.
- (bug #17): Enable display of other toolbar buttons when `showPersonalize` is false.
- (bug #18): Allow empty keys on DB.
- (enh #19): If storage is empty display all valid columns
- (enh #20): Private variables changed to protected.

