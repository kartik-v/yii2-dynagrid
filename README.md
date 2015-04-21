yii2-dynagrid
=============

[![Latest Stable Version](https://poser.pugx.org/kartik-v/yii2-dynagrid/v/stable)](https://packagist.org/packages/kartik-v/yii2-dynagrid)
[![License](https://poser.pugx.org/kartik-v/yii2-dynagrid/license)](https://packagist.org/packages/kartik-v/yii2-dynagrid)
[![Total Downloads](https://poser.pugx.org/kartik-v/yii2-dynagrid/downloads)](https://packagist.org/packages/kartik-v/yii2-dynagrid)
[![Monthly Downloads](https://poser.pugx.org/kartik-v/yii2-dynagrid/d/monthly)](https://packagist.org/packages/kartik-v/yii2-dynagrid)
[![Daily Downloads](https://poser.pugx.org/kartik-v/yii2-dynagrid/d/daily)](https://packagist.org/packages/kartik-v/yii2-dynagrid)

The **yii2-dynagrid**  module is a great complementary addition to the [kartik-v/yii2-grid](https://github.com/kartik-v/yii2-grid) module, enhancing
it with personalization features. It turbo charges your grid view by making it dynamic and personalized for each user. It allows users the ability to 
set and save their own grid configuration. The major features provided by this module are:

- Personalize, set, and save grid page size at runtime. You can set the minimum and maximum page size allowed.
- Personalize, set, and save grid data filters at runtime. A user can define and save his/her own personalized grid data filters.
- Personalize, set, and save grid column sorts at runtime. A user can define and save his/her own personalized grid column sorts.
- Personalize the grid columns display through drag and drop. Reorder grid columns and set the visibility of needed columns, and allow users to save this setting. 
  Control which columns can be reordered by users through predefined columns setup. Predetermine which of your desired columns will be always fixed to the left or right by 
  default.
- Personalize grid appearance and set the grid theme. This will offer advanced customization to the grid layout. It allows users to virtually style grid 
  anyway they want, based on how you define themes and extend them to your users. Since the extension uses the [yii2-grid](https://github.com/kartik-v/yii2-grid)
  extension,it offers all the styling options the **yii2-grid** extension provides, including the various grid column enhancements, bootstrap panels, and other grid styles. 
  This will allow you to easily setup themes for users in   many ways. You have an ability to setup multiple themes in your module configuration, and allow users to select one of them. The extension by 
  default includes some predefined themes for you to get started.
- Allow you to save the dynamic grid configuration specific to each user or global level. Implements a `DynaGridStore` object to manage dynagrid personalizations independent of storage. One of the following storage options are made available to store 
  the personalized grid configuration:
  - Session Storage (default)
  - Cookie Storage 
  - Database Storage
- The extension automatically validates and loads the saved configuration based on the stored settings.

> NOTE: This extension depends on the [kartik-v/yii2-grid](https://github.com/kartik-v/yii2-grid) and [kartik-v/yii2-sortable](https://github.com/kartik-v/yii2-sortable) extensions which in turn depends on the 
[yiisoft/yii2-bootstrap](https://github.com/yiisoft/yii2/tree/master/extensions/bootstrap) extension. Check the 
[composer.json](https://github.com/kartik-v/yii2-dynagrid/blob/master/composer.json) for this extension's requirements and dependencies. 

## Latest Release
The latest version of the module is v1.4.2. Refer the [CHANGE LOG](https://github.com/kartik-v/yii2-dynagrid/blob/master/CHANGE.md) for details.
- With v1.2.0, the Dynagrid is revamped to include a `DynaGridStore` object and ability to save, update, or delete grid personalizations including grid filter and sort.
- With v1.1.0, the DynaGrid includes Pjax support. To enable Pjax processing, set `gridOptions['pjax']` property to `true` and control pjax behavior using `gridOptions['pjaxSettings']`.

### Demo
You can see detailed [documentation](http://demos.krajee.com/dynagrid) or view a [complete demo](http://demos.krajee.com/dynagrid-demo) on usage of the extension.

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

> Note: You must set the `minimum-stability` to `dev` in the **composer.json** file in your application root folder before installation of this extension.

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

### Module

Setup the module in your Yii configuration file with a name `dynagrid` as shown below. In addition, you must also register 
the `gridview` module as described in the [yii2-grid documentation](http://demos.krajee.com/grid#module).

```php
'modules'=>[
   'dynagrid'=>[
        'class'=>'\kartik\dynagrid\Module',
        // other settings (refer documentation)
    ],
    'gridview'=>[
        'class'=>'\kartik\grid\Module',
        // other module settings
    ],
],
```

### DynaGrid

The DynaGrid widget can be used to render the personalized & dynamic version of `kartik\Grid\GridView` in the following way:

```php
use kartik\dynagrid\DynaGrid;
use kartik\grid\GridView;
$columns = [
    ['class'=>'kartik\grid\SerialColumn', 'order'=>DynaGrid::ORDER_FIX_LEFT],
    'id',
    'name',
    [
        'attribute'=>'publish_date',
        'filterType'=>GridView::FILTER_DATE,
        'format'=>'raw',
        'width'=>'170px',
        'filterWidgetOptions'=>[
            'pluginOptions'=>['format'=>'yyyy-mm-dd']
        ],
    ],
    [
        'class'=>'kartik\grid\BooleanColumn',
        'attribute'=>'status', 
        'vAlign'=>'middle',
    ],
    [
        'class'=>'kartik\grid\ActionColumn',
        'dropdown'=>false,
        'order'=>DynaGrid::ORDER_FIX_RIGHT
    ],
    ['class'=>'kartik\grid\CheckboxColumn',  'order'=>DynaGrid::ORDER_FIX_RIGHT],
];
    
echo DynaGrid::widget([
    'columns'=>$columns,
    'storage'=>DynaGrid::TYPE_COOKIE,
    'theme'=>'panel-danger',
    'gridOptions'=>[
        'dataProvider'=>$dataProvider,
        'filterModel'=>$searchModel,
        'panel'=>['heading'=>'<h3 class="panel-title">Library</h3>'],
    ],
    'options'=>['id'=>'dynagrid-1'] // a unique identifier is important
]);
```

> Warning: You need to be careful, in case you are using the new `\kartik\grid\FormulaColumn` in your grid layout. The reordering of 
columns by users will render your formulae invalid, since the column indices will change. It is recommended, you set the order of all 
such formulae columns and its dependencies to `DynaGrid::ORDER_FIX_LEFT`, so that such column positions are pre-fixed.

## License

**yii2-dynagrid** is released under the BSD 3-Clause License. See the bundled `LICENSE.md` for details.
