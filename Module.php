<?php

/**
 * @package   yii2-dynagrid
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2015 - 2017
 * @version   1.4.6
 */

namespace kartik\dynagrid;

use Yii;
use kartik\base\Config;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;

/**
 * The dynamic grid module for Yii Framework 2.0.
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.0
 */
class Module extends \kartik\base\Module
{
    /**
     * Dynagrid module name
     */
    const MODULE = 'dynagrid';
    /**
     * Dynagrid layout type 1
     */
    const LAYOUT_1 = "<hr>{dynagrid}<hr>\n{summary}\n{items}\n{pager}";
    /**
     * Dynagrid layout type 2
     */
    const LAYOUT_2 = "&nbsp;";
    /**
     * Cookie expiry (used for dynagrid configuration storage)
     */
    const COOKIE_EXPIRY = 8640000; // 100 days
    /**
     * Session key variable name for storing the dynagrid configuration encryption salt.
     */
    const SALT_SESS_KEY = "krajeeDGConfigSalt";

    /**
     * @var array the settings for the cookie to be used in saving the dynagrid setup
     * @see \yii\web\Cookie
     */
    public $cookieSettings = [];

    /**
     * @var array the settings for the database table to store the dynagrid setup
     * The following parameters are supported:
     * - tableName: _string_, the name of the database table, that will store the dynagrid settings.
     *   Defaults to `tbl_dynagrid`.
     * - idAttr: _string_, the attribute name for the configuration id . Defaults to `id`.
     * - filterAttr: _string_, the attribute name for the filter setting id. Defaults to `filter_id`.
     * - sortAttr: _string_, the attribute name for the filter setting id. Defaults to `sort_id`.
     * - dataAttr: _string_, the attribute name for grid column data configuration. Defaults to `data`.
     */
    public $dbSettings = [];

    /**
     * @var array the settings for the detail database table to store the dynagrid filter and sort settings.
     * The following parameters are supported:
     * - tableName: _string_, the name of the database table, that will store the dynagrid settings.
     *   Defaults to `tbl_dynagrid_dtl`.
     * - idAttr: _string_, the attribute name for the detail configuration id. Defaults to `id`.
     * - categoryAttr: _string_, the attribute name for the detail category (values currently possible are 'filter' or
     *     'sort'). Defaults to `category`.
     * - nameAttr: _string_, the attribute name for the filter or sort name. Defaults to `name`.
     * - dataAttr: _string_, the attribute name for grid detail (filter/sort) configuration. Defaults to `data`.
     * - dynaGridIdAttr: _string_, the attribute name for the dynagrid identifier. Defaults to `dynagrid_id`.
     */
    public $dbSettingsDtl = [];

    /**
     * @var array the default global configuration for the kartik\dynagrid\DynaGrid widget
     */
    public $dynaGridOptions = [];

    /**
     * @var string the view for displaying and saving the dynagrid configuration
     */
    public $configView = 'config';

    /**
     * @var string the view for displaying and saving the dynagrid detail settings
     * for filter and sort
     */
    public $settingsView = 'settings';

    /**
     * @var mixed the action URL for displaying the dynagrid detail configuration settings
     * on the dynagrid detail settings form
     */
    public $settingsConfigAction = '/dynagrid/settings/get-config';

    /**
     * @var array the theme configuration for the gridview
     */
    public $themeConfig = [
        'simple-default' => [
            'panel' => false,
            'bordered' => false,
            'striped' => false,
            'hover' => true,
            'layout' => self::LAYOUT_1
        ],
        'simple-bordered' => ['panel' => false, 'striped' => false, 'hover' => true, 'layout' => self::LAYOUT_1],
        'simple-condensed' => [
            'panel' => false,
            'striped' => false,
            'condensed' => true,
            'hover' => true,
            'layout' => self::LAYOUT_1
        ],
        'simple-striped' => ['panel' => false, 'layout' => self::LAYOUT_1],
        'panel-default' => ['panel' => ['type' => GridView::TYPE_DEFAULT, 'before' => self::LAYOUT_2]],
        'panel-primary' => ['panel' => ['type' => GridView::TYPE_PRIMARY, 'before' => self::LAYOUT_2]],
        'panel-info' => ['panel' => ['type' => GridView::TYPE_INFO, 'before' => self::LAYOUT_2]],
        'panel-danger' => ['panel' => ['type' => GridView::TYPE_DANGER, 'before' => self::LAYOUT_2]],
        'panel-success' => ['panel' => ['type' => GridView::TYPE_SUCCESS, 'before' => self::LAYOUT_2]],
        'panel-warning' => ['panel' => ['type' => GridView::TYPE_WARNING, 'before' => self::LAYOUT_2]],
    ];

    /**
     * @var integer the default theme for the gridview.
     */
    public $defaultTheme = 'panel-primary';

    /**
     * @var integer the default pagesize for the gridview.
     */
    public $defaultPageSize = 10;

    /**
     * @var integer the minimum pagesize for the gridview. Setting pagesize to `0` will display all rows.
     */
    public $minPageSize = 0;

    /**
     * @var integer the maximum pagesize for the gridview.
     */
    public $maxPageSize = 50;

    /**
     * @var string a random salt that will be used to generate a hash signature for tree configuration. If not set, this
     * will be generated using [[\yii\base\Security::generateRandomKey()]] to generate a random key. The randomly
     * generated salt in the second case will be stored in a session variable identified by [[SALT_SESS_KEY]].
     */
    public $configEncryptSalt;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->_msgCat = 'kvdynagrid';
        parent::init();
        $app = Yii::$app;
        if ($app->has('session') && !isset($this->configEncryptSalt)) {
            $session = $app->session;
            if (!$session->get(self::SALT_SESS_KEY)) {
                $session->set(self::SALT_SESS_KEY, $app->security->generateRandomKey());
            }
            $this->configEncryptSalt = $session->get(self::SALT_SESS_KEY);
        } elseif (!isset($this->configEncryptSalt)) {
            $this->configEncryptSalt = '<$0ME_R@ND0M_$@LT>';
        }
        $this->initSettings();
    }

    /**
     * Gets the module instance
     *
     * @param string $module the module name
     *
     * @return Module
     */
    public static function fetchModule($module = self::MODULE)
    {
        return Config::getModule($module);
    }

    /**
     * Initialize module level settings
     */
    public function initSettings()
    {
        $this->dbSettings += [
            'tableName' => 'tbl_dynagrid',
            'idAttr' => 'id',
            'filterAttr' => 'filter_id',
            'sortAttr' => 'sort_id',
            'dataAttr' => 'data'
        ];
        $this->dbSettingsDtl += [
            'tableName' => 'tbl_dynagrid_dtl',
            'idAttr' => 'id',
            'categoryAttr' => 'category',
            'nameAttr' => 'name',
            'dataAttr' => 'data',
            'dynaGridIdAttr' => 'dynagrid_id'
        ];
        $this->cookieSettings += [
            'httpOnly' => true,
            'expire' => time() + self::COOKIE_EXPIRY,
        ];
        $this->dynaGridOptions = ArrayHelper::merge([
            'storage' => DynaGrid::TYPE_SESSION,
            'gridOptions' => [],
            'matchPanelStyle' => true,
            'toggleButtonGrid' => [],
            'options' => [],
            'sortableOptions' => [],
            'userSpecific' => true,
            'columns' => [],
            'submitMessage' => Yii::t('kvdynagrid', 'Saving and applying configuration') . ' &hellip;',
            'deleteMessage' => Yii::t('kvdynagrid', 'Trashing all personalizations') . ' &hellip;',
            'deleteConfirmation' => Yii::t('kvdynagrid', 'Are you sure you want to delete the setting?'),
            'messageOptions' => [],
        ], $this->dynaGridOptions);

    }
}