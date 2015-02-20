<?php

/**
 * @package   yii2-dynagrid
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2015
 * @version   1.4.2
 */

namespace kartik\dynagrid\models;

use Yii;
use yii\base\Model;
use kartik\base\Config;
use kartik\dynagrid\Module;
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
    protected $_module;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $this->_module = Config::initModule(Module::classname());
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
            ['pageSize', 'integer', 'min' => $this->_module->minPageSize, 'max' => $this->_module->maxPageSize],
            ['pageSize', 'default', 'value' => $this->_module->defaultPageSize],
            ['theme', 'default', 'value' => $this->_module->defaultTheme],
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
