yii2-dynagrid
=============

A Yii 2.0 extension that makes a Yii 2 GridView dynamic by controlling sorting and visibility of columns. You can use the standard `yii\grid\GridView`
widget with this extension or the enhanced `kartik\grid\GridView` extension from the yii2-grid package.The extension inspired largely by the 
[ecolumns](http://www.yiiframework.com/extension/ecolumns/) extension in Yii 1.0.

> NOTE: This extension depends on the [kartik-v/yii2-widgets](https://github.com/kartik-v/yii2-widgets) extension which in turn depends on the 
[yiisoft/yii2-bootstrap](https://github.com/yiisoft/yii2/tree/master/extensions/bootstrap) extension. Check the 
[composer.json](https://github.com/kartik-v/yii2-dynagrid/blob/master/composer.json) for this extension's requirements and dependencies. 
Note: Yii 2 framework is still in active development, and until a fully stable Yii2 release, your core yii2-bootstrap packages (and its dependencies) 
may be updated when you install or update this extension. You may need to lock your composer package versions for your specific app, and test 
for extension break if you do not wish to auto update dependencies.

### Demo
You can see detailed [documentation](http://demos.krajee.com/dynagrid) on usage of the extension.

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
$columns = Columns::widget([
    // to be done
]); 
```

## License

**yii2-dynagrid** is released under the BSD 3-Clause License. See the bundled `LICENSE.md` for details.