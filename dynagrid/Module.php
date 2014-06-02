<?php

/**
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2014
 * @package yii2-dynagrid
 * @version 1.0.0
 */

namespace kartik\dynagrid;

use Yii;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;

/**
 * The dynamic grid module for Yii Framework 2.0.
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.0
 */
class Module extends \yii\base\Module
{
    const LAYOUT_1 = "<hr>{dynagrid}<hr>\n{summary}\n{items}\n{pager}";
    const LAYOUT_2 = "{dynagrid}";
    const COOKIE_EXPIRY = 8640000; // 100 days

    /**
     * @var array the settings for the cookie to be used in saving the dynagrid setup
     * @see \yii\web\Cookie
     */
    public $cookieSettings = [];

    /**
     * @var array the settings for the database table to store the dynagrid setup
     * The following parameters are supported:
     * - tableName: string, the name of the database table, that will store the dynagrid settings.
     *   Defaults to `tbl_dynagrid`.
     * - idAttr: string, the attribute name for the configuration id . Defaults to `id`.
     * - dataAttr: string, the attribute name for grid column data configuration. Defaults to `data`.
     */
    public $dbSettings = [];

    /**
     * @var array the default global configuration for the kartik\dynagrid\DynaGrid widget
     */
    public $dynaGridOptions = [];

    /**
     * @var string the view for displaying and saving the dynagrid configuration
     */
    public $configView = 'config';

    /**
     * @var array the theme configuration for the gridview
     */
    public $themeConfig = [
        'simple-default' => ['panel' => false, 'bordered' => false, 'striped' => false, 'hover' => true, 'layout' => self::LAYOUT_1],
        'simple-bordered' => ['panel' => false, 'striped' => false, 'hover' => true, 'layout' => self::LAYOUT_1],
        'simple-condensed' => ['panel' => false, 'striped' => false, 'condensed' => true, 'hover' => true, 'layout' => self::LAYOUT_1],
        'simple-striped' => ['panel' => false, 'layout' => self::LAYOUT_1],
        'panel-default' => ['panel' => ['type' => GridView::TYPE_DEFAULT, 'before' => self::LAYOUT_2]],
        'panel-primary' => ['panel' => ['type' => GridView::TYPE_PRIMARY, 'before' => self::LAYOUT_2]],
        'panel-info' => ['panel' => ['type' => GridView::TYPE_INFO, 'before' => self::LAYOUT_2]],
        'panel-danger' => ['panel' => ['type' => GridView::TYPE_DANGER, 'before' => self::LAYOUT_2]],
        'panel-success' => ['panel' => ['type' => GridView::TYPE_SUCCESS, 'before' => self::LAYOUT_2]],
        'panel-warning' => ['panel' => ['type' => GridView::TYPE_WARNING, 'before' => self::LAYOUT_2]],
    ];

    /**
     * @var int the default theme for the gridview. Defaults to 'panel-primary'.
     */
    public $defaultTheme = 'panel-primary';

    /**
     * @var int the default pagesize for the gridview. Defaults to 10.
     */
    public $defaultPageSize = 10;

    /**
     * @var int the minimum pagesize for the gridview. Defaults to 5.
     */
    public $minPageSize = 5;

    /**
     * @var int the maximum pagesize for the gridview. Defaults to 100.
     */
    public $maxPageSize = 100;

    /**
     * @var array the the internalization configuration for this module
     */
    public $i18n = [];

    public function init()
    {
        parent::init();
        $this->initI18N();
        $this->initSettings();

    }

    /**
     * Initialize module level settings
     */
    public function initSettings()
    {
        $this->dbSettings += [
            'tableName' => 'tbl_dynagrid',
            'idAttr' => 'id',
            'dataAttr' => 'data'
        ];
        $this->cookieSettings += [
            'httpOnly' => true,
            'expire' => time() + self::COOKIE_EXPIRY,
        ];
        $this->dynaGridOptions = ArrayHelper::merge([
            'storage' => DynaGrid::TYPE_SESSION,
            'gridOptions' => [],
            'matchPanelStyle' => true,
            'toggleButton' => [],
            'options' => [],
            'sortableOptions' => [],
            'userSpecific' => true,
            'columns' => [],
            'submitMessage' => Yii::t('kvdynagrid', 'Saving and applying configuration') . ' &hellip;',
            'submitMessageOptions' => [],
        ], $this->dynaGridOptions);

    }

    /**
     * Initialize i18n configuration for the module
     */
    public function initI18N()
    {
        Yii::setAlias('@kvdynagrid', dirname(__FILE__));
        if (empty($this->i18n)) {
            $this->i18n = [
                'class' => 'yii\i18n\PhpMessageSource',
                'basePath' => '@kvdynagrid/messages',
                'forceTranslation' => true
            ];
        }
        Yii::$app->i18n->translations['kvdynagrid'] = $this->i18n;
    }
}