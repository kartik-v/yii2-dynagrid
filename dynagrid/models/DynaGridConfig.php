<?php

/**
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2014
 * @package yii2-dynagrid
 * @version 1.0.0
 */

namespace kartik\dynagrid\models;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Model for the dynagrid configuration
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.0
 */
class DynaGridConfig extends Model
{
    public $id;
    public $hiddenColumns = [];
    public $visibleColumns = [];
    public $widgetOptions = [];
    public $themeList = [];
    public $pageSize;
    public $hiddenKeys;
    public $visibleKeys;
    public $theme;

    public function rules() {
        $module = Yii::$app->getModule('dynagrid');
        return [
            [['id', 'hiddenColumns', 'visibleColumns', 'pageSize', 'theme', 'hiddenKeys', 'visibleKeys'], 'safe'],
            ['pageSize', 'integer', 'min'=>0, 'max'=>$module->maxPageSize],
            ['pageSize', 'default', 'value'=>$module->defaultPageSize],
            ['theme', 'default', 'value'=>$module->defaultTheme],
        ];
    }

    public function attributeLabels() {
        return [
            'hiddenColumns' => Yii::t('kvdynagrid', 'Hidden/Fixed Columns'),
            'visibleColumns' => Yii::t('kvdynagrid', 'Visible Columns'),
            'pageSize' => Yii::t('kvdynagrid', 'Page Size'),
            'theme' => Yii::t('kvdynagrid', 'Theme'),
        ];
    }

    public function parseColumns($columns) {
        $items = [];
        foreach ($columns as $key => $value) {

        }
    }
}
