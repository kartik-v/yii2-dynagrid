<?php

/**
 * @package   yii2-dynagrid
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2015 - 2017
 * @version   1.4.5
 */

namespace kartik\dynagrid\models;

use Yii;
use yii\base\Model;
use kartik\base\Config;
use kartik\dynagrid\Module;

/**
 * Model for the dynagrid configuration
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.0
 */
class DynaGridConfig extends Model
{
    /**
     * @var string the dynagrid widget identifier
     */
    public $id;
    /**
     * @var array the hidden grid columns
     */
    public $hiddenColumns = [];
    /**
     * @var array the visible grid columns
     */
    public $visibleColumns = [];
    /**
     * @var array the widget options for the [[\kartik\sortable\Sortable]] widget
     */
    public $widgetOptions = [];
    /**
     * @var array the list of saved grid themes
     */
    public $themeList = [];
    /**
     * @var array the list of saved grid filters
     */
    public $filterList = [];
    /**
     * @var array the list of saved grid sort
     */
    public $sortList = [];
    /**
     * @var integer the grid page size
     */
    public $pageSize;
    /**
     * @var string the filter identifier
     */
    public $filterId = null;
    /**
     * @var string the sort identifier
     */
    public $sortId = null;
    /**
     * @var string|null the footer content for the dynagrid configuration form
     */
    public $footer = null;
    /**
     * @var string the currently selected grid theme
     */
    public $theme;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        /**
         * @var Module $module
         */
        $module = Config::initModule(Module::classname());
        return [
            [['id', 'hiddenColumns', 'visibleColumns', 'pageSize', 'filterId', 'sortId', 'theme'], 'safe'],
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
