version 1.0.0
=============
Initial release. The major features provided by the **yii2-dynagrid** module are:

- Personalize, set, and save grid page size at runtime. You can set the minimum and maximum page size allowed.
- Personalize the grid columns display. Reorder grid columns and set the visibility of needed columns, and save this setting. Control which 
  columns can be reordered by users through setup. Predetermine  your desired columns to be fixed to the left or right by default.
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