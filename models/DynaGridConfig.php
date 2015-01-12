<?php

/**
 * @package   yii2-dynagrid
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2014
 * @version   1.4.0
 */

namespace kartik\dynagrid\models;

use Yii;
use yii\base\Model;
use kartik\dynagrid\DynaGridStore;

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
    public $filterList = [];
    public $sortList = [];
    public $pageSize;
    public $filterId = null;
    public $sortId = null;
    public $hiddenKeys;
    public $visibleKeys;
    public $footer = null;
    public $theme;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $module = Yii::$app->getModule('dynagrid');
        return [
            [
                [
                    'id',
                    'hiddenColumns',
                    'visibleColumns',
                    'pageSize',
                    'filterId',
                    'sortId',
                    'theme',
                    'hiddenKeys',
                    'visibleKeys'
                ],
                'safe'
            ],
            [['pageSize', 'theme'], 'required'],
            ['pageSize', 'integer', 'min' => $module->minPageSize, 'max' => $module->maxPageSize],
            ['pageSize', 'default', 'value' => $module->defaultPageSize],
            ['theme', 'default', 'value' => $module->defaultTheme],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'hiddenColumns' => Yii::t('kvdynagrid', 'Hidden / Fixed Columns'),
            'visibleColumns' => Yii::t('kvdynagrid', 'Visible Columns'),
            'pageSize' => Yii::t('kvdynagrid', 'Page Size'),
            'filterId' => Yii::t('kvdynagrid', 'Default Filter'),
            'sortId' => Yii::t('kvdynagrid', 'Default Sort'),
            'theme' => Yii::t('kvdynagrid', 'Grid Theme'),
        ];
    }
}
