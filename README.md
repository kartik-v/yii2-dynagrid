yii2-dynagrid
=============

The yii2-dynagrid module is a great enhancement to the [kartik-v/yii2-grid](https://github.com/kartik-v/yii2-grid) module. It turbo charges your grid view 
by making it dynamic and personalized for each user. It allows users the ability to set and save their own grid configuration. The major features provided 
by this module are:

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

> NOTE: This extension depends on the [kartik-v/yii2-grid](https://github.com/kartik-v/yii2-grid) extension which in turn depends on the 
[yiisoft/yii2-bootstrap](https://github.com/yiisoft/yii2/tree/master/extensions/bootstrap) extension. Check the 
[composer.json](https://github.com/kartik-v/yii2-dynagrid/blob/master/composer.json) for this extension's requirements and dependencies. 
Note: Yii 2 framework is still in active development, and until a fully stable Yii2 release, your core yii2-bootstrap packages (and its dependencies) 
may be updated when you install or update this extension. You may need to lock your composer package versions for your specific app, and test 
for extension break if you do not wish to auto update dependencies.

### Demo
You can see detailed [documentation](http://demos.krajee.com/dynagrid) on usage of the extension or view a [complete demo](http://demos.krajee.com/dynagrid-demo).

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
$ php composer.phar require kartik-v/yii2-dynagrid "dev-master"
```

or add

```
"kartik-v/yii2-dynagrid": "dev-master"
```

to the ```require``` section of your `composer.json` file.

## Usage

### Columns

```php
use kartik\dynagrid\Columns;
$columns = DynaGrid::widget([
    // to be done
]); 
```

## License

**yii2-dynagrid** is released under the BSD 3-Clause License. See the bundled `LICENSE.md` for details.