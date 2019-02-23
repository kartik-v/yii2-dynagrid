<?php

/**
 * @package   yii2-dynagrid
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2015 - 2019
 * @version   1.5.1
 */

namespace kartik\dynagrid;

use kartik\base\Config;
use kartik\base\Widget;
use kartik\dialog\Dialog;
use kartik\dynagrid\models\DynaGridConfig;
use kartik\dynagrid\models\DynaGridSettings;
use kartik\grid\GridView;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\data\DataProviderInterface;
use yii\data\Sort;
use yii\data\SqlDataProvider;
use yii\db\ActiveQuery;
use yii\db\ActiveQueryInterface;
use yii\db\QueryInterface;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\Json;

/**
 * Enhance GridView by allowing you to dynamically edit grid configuration. The dynagrid allows you to set your own grid
 * theme, pagesize, and column order/display settings. The widget allows you to manage the order and visibility of
 * columns dynamically at runtime. It also allows you to save this configuration or retrieve the saved configuration
 * to/from session, cookie, or database.
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.0
 */
class DynaGrid extends Widget
{
    use DynaGridTrait;

    /**
     * @var string session storage type
     */
    const TYPE_SESSION = 'session';

    /**
     * @var string cookie storage type
     */
    const TYPE_COOKIE = 'cookie';

    /**
     * @var string database storage type
     */
    const TYPE_DB = 'db';

    /**
     * @var string fix column to the left
     */
    const ORDER_FIX_LEFT = 'fixleft';

    /**
     * @var string fix column to the right
     */
    const ORDER_FIX_RIGHT = 'fixright';

    /**
     * @var string fix column to the middle
     */
    const ORDER_MIDDLE = 'middle';

    /**
     * @var string the module identifier if this widget is part of a module. If not set, the module identifier will
     * be auto derived based on the \yii\base\Module::getInstance method. This can be useful, if you are setting
     * multiple module identifiers for the same module in your Yii configuration file. To specify children or grand
     * children modules you can specify the module identifiers relative to the parent module (e.g. `admin/content`).
     */
    public $moduleId;

    /**
     * @var string the type of storage for the dynagrid configuration.
     * - [[DynaGrid::TYPE_SESSION]]: Save the config in a session variable for the current session.
     * - [[DynaGrid::TYPE_COOKIE]]: Save the config in a cookie for retrieval. You need to setup the
     *   [[Module::cookieSettings]] property to control the cookie expiry and other settings.
     * - [[DynaGrid::TYPE_DB]]: Save the config to a database. You need to setup the [[Module::dbSettings]]
     *   property to setup the database table and attributes for storage.
     */
    public $storage;

    /**
     * @var string the initial grid theme to be set
     */
    public $theme;

    /**
     * @var boolean whether settings are stored specific to each user
     */
    public $userSpecific;

    /**
     * @var boolean whether to update only the name, when editing and saving a filter or sort. This is applicable
     * only for [[$storage]] set to [[Dynagrid::TYPE_DB]]. If set to `false`, it will also overwrite the current
     * `filter` or `sort` settings.
     */
    public $dbUpdateNameOnly = false;

    /**
     * @var boolean whether to show the personalize button group. Defaults to `true`.
     */
    public $showPersonalize = true;

    /**
     * @var boolean whether to show the filter save widget button. Defaults to `true`.
     */
    public $showFilter = true;

    /**
     * @var boolean whether to show the sort save widget button. Defaults to `true`.
     */
    public $showSort = true;

    /**
     * @var boolean whether to enable multiple sort. Defaults to `true`.
     */
    public $enableMultiSort = true;

    /**
     * @var boolean whether to allow setup of the pagination. Defaults to `true`.
     */
    public $allowPageSetting = true;

    /**
     * @var boolean whether to allow display/setup of the theme. Defaults to `true`.
     */
    public $allowThemeSetting = true;

    /**
     * @var boolean whether to allow display/setup of the filter in the personalize grid form
     */
    public $allowFilterSetting = true;

    /**
     * @var boolean whether to allow display/setup of the sort in the personalize grid form
     */
    public $allowSortSetting = true;

    /**
     * @var array widget options for \kartik\widgets\GridView that will be rendered by the DynaGrid widget
     */
    public $gridOptions;

    /**
     * @var boolean whether the DynaGrid configuration button class should match the grid panel style.
     */
    public $matchPanelStyle;

    /**
     * @var array the HTML attributes for the dynagrid personalize toggle button which will render the DynaGrid
     * configuration form within a Bootstrap Modal container.
     */
    public $toggleButtonGrid;

    /**
     * @var array the HTML attributes for the filter configuration button which will render the Filter settings form
     * within a Bootstrap Modal container.
     */
    public $toggleButtonFilter;

    /**
     * @var array the HTML attributes for the sort configuration button which will render the Sort settings form
     * within a Bootstrap Modal container.
     */
    public $toggleButtonSort;

    /**
     * @var array HTML options for the DynaGrid widget
     */
    public $options;

    /**
     * @var array the sortable widget options
     */
    public $sortableOptions;

    /**
     * @var array the HTML attributes for the sortable columns header
     */
    public $sortableHeader = ['class' => 'alert alert-info dynagrid-sortable-header'];

    /**
     * @var array the grid columns configuration
     */
    public $columns;

    /**
     * @var string the message to display after applying and submitting the configuration and until refreshed grid is
     * reloaded
     */
    public $submitMessage;

    /**
     * @var string the message to display after deleting the configuration and
     * until refreshed grid is reloaded
     */
    public $deleteMessage;

    /**
     * @var array HTML attributes for the submission message container
     */
    public $messageOptions;

    /**
     * @var string the confirmation warning message before deleting a personalization configuration or setting.
     */
    public $deleteConfirmation;

    /**
     * @var array configuration settings for the Krajee dialog widget that will be used to render alerts and
     * confirmation dialog prompts
     * @see http://demos.krajee.com/dialog
     */
    public $krajeeDialogSettings = [];

    /**
     * @var array the HTML attributes for the save/apply action button. If this is set to `false`, it will not be
     * displayed. The following special variables are supported:
     * - `icon`: _string_, the icon class suffix for the button. Defaults to `save`.
     * - `prefix`: _string_, the icon class prefix for the button. Defaults to `glyphicon glyphicon-` for [[bsVersion]]
     *    set to `3.x` and `fas fa-` for [[bsVersion]] set to `4.x`.
     * - `label`: _string_, the label for the action button. Defaults to empty string.
     * - `title`: _string_, the title for the action button. Defaults to `Save grid settings`.
     */
    public $submitButtonOptions = [];

    /**
     * @var array|boolean the HTML attributes for the reset action button. If this is set to `false`, it will not be
     * displayed. The following special variables are supported:
     * - `icon`: _string_, the icon class suffix for the button. Defaults to `repeat` for [[bsVersion]]
     *    set to `3.x` and `redo` for [[bsVersion]] set to `4.x`.
     * - `prefix`: _string_, the icon class prefix for the button. Defaults to `glyphicon glyphicon-` for [[bsVersion]]
     *    set to `3.x` and `fas fa-` for [[bsVersion]] set to `4.x`.
     * - `label`: _string_, the label for the action button. Defaults to empty string.
     * - `title`: _string_, the title for the action button. Defaults to `Abort any changes and reset settings`.
     */
    public $resetButtonOptions = [];

    /**
     * @var array|boolean the HTML attributes for the delete/trash action button. If this is set to `false`, it will
     * not be displayed. The following special variables are supported:
     * - `icon`: _string_, the icon class suffix for the button. Defaults to `trash` for [[bsVersion]]
     *    set to `3.x` and `trash-alt` for [[bsVersion]] set to `4.x`.
     * - `prefix`: _string_, the icon class prefix for the button. Defaults to `glyphicon glyphicon-` for [[bsVersion]]
     *    set to `3.x` and `fas fa-` for [[bsVersion]] set to `4.x`.
     * - `label`: _string_, the label for the action button. Defaults to empty string.
     * - `title`: _string_, the title for the action button. Defaults to `Remove saved grid settings`.
     */
    public $deleteButtonOptions = [];

    /**
     * @var string the icon that will be displayed on the dynagrid personalize button as well as the personalize modal
     * dialog header. This is not HTML encoded.  Defaults to `<i class="glyphicon glyphicon-wrench"></i>` for
     * [[bsVersion]] set to `3.x` and `<i class="fas fa-fw fa-wrench"></i>` for [[bsVersion]] set to `4.x`.
     */
    public $iconPersonalize;

    /**
     * @var string the icon that will be displayed as the label for grid filter personalization button. This is not
     * HTML encoded. Defaults to `<i class="glyphicon glyphicon-filter"></i>` for [[bsVersion]] set to `3.x` and
     * `<i class="fas fa-fw fa-filter"></i>` for [[bsVersion]] set to `4.x`.
     */
    public $iconFilter;

    /**
     * @var string the icon that will be displayed as the label for grid sort personalization button. This is not
     * HTML encoded. Defaults to `<i class="glyphicon glyphicon-sort"></i>` for [[bsVersion]] set to `3.x` and
     * `<i class="fas fa-fw fa-sort"></i>` for [[bsVersion]] set to `4.x`.
     */
    public $iconSort;

    /**
     * @var string the icon for the save button within the dynagrid configuration form. This is not HTML encoded.
     * Defaults to `<i class="glyphicon glyphicon-save"></i>` for [[bsVersion]] set to `3.x` and
     * `<i class="fas fa-save"></i>` for [[bsVersion]] set to `4.x`.
     */
    public $iconConfirm;

    /**
     * @var string the icon for the save button within the dynagrid configuration form. This is not HTML encoded.
     * Defaults to `<i class="glyphicon glyphicon-remove"></i>` for [[bsVersion]] set to `3.x` and
     * `<i class="fas fa-times"></i>` for [[bsVersion]] set to `4.x`.
     */
    public $iconRemove;

    /**
     * @var string the icon that will be displayed for each VISIBLE column heading in the column reordering pane.
     * This is not HTML encoded. Defaults to `<i class="glyphicon glyphicon-eye-open"></i>` for [[bsVersion]]
     *    set to `3.x` and `<i class="fas fa-eye"></i>` for [[bsVersion]] set to `4.x`.
     */
    public $iconVisibleColumn;

    /**
     * @var string the icon that will be displayed for each HIDDEN column heading in the column reordering pane.
     * This is not HTML encoded.  Defaults to `<i class="glyphicon glyphicon-eye-close"></i>` for [[bsVersion]]
     *    set to `3.x` and `<i class="fas fa-eye-slash"></i>` for [[bsVersion]] set to `4.x`.
     */
    public $iconHiddenColumn;

    /**
     * @var string the icon that will be displayed separating the visible and hidden columns in the column reordering
     * pane. This is not HTML encoded.  Defaults to `<i class="glyphicon glyphicon-resize-horizontal"></i>` for
     * [[bsVersion]] set to `3.x` and `<i class="fas fa-arrows-alt-h"></i>` for [[bsVersion]] set to `4.x`.
     */
    public $iconSortableSeparator;

    /**
     * @var array the cached columns configuration
     */
    protected $_columns = [];

    /**
     * @var array the user configured visible widget columns
     */
    protected $_visibleColumns = [];

    /**
     * @var array the hidden widget columns for user configuration
     */
    protected $_hiddenColumns = [];

    /**
     * @var array the stored visible keys
     */
    protected $_visibleKeys = [];

    /**
     * @var integer the grid pagesize
     */
    protected $_pageSize;

    /**
     * @var integer the grid filter id
     */
    protected $_filterId = null;

    /**
     * @var integer the grid sort id
     */
    protected $_sortId = null;

    /**
     * @var array the dynagrid detail configuration settings (for filter and sort)
     */
    protected $_detailConfig = [];

    /**
     * @var Module the current module
     */
    protected $_module;

    /**
     * @var string request param name which will show the grid configuration submitted
     */
    protected $_requestSubmit;

    /**
     * @var DynaGridConfig model
     */
    protected $_model;

    /**
     * @var boolean flag to check if the grid configuration form has been submitted
     */
    protected $_isSubmit = false;

    /**
     * @var boolean flag to check if the pjax is enabled for the grid
     */
    protected $_isPjax;

    /**
     * @var string the identifier for the grid settings modal dialog
     */
    protected $_gridModalId;

    /**
     * @var string the identifier for the filter settings modal dialog
     */
    protected $_filterModalId;

    /**
     * @var string the identifier for the sort settings modal dialog
     */
    protected $_sortModalId;

    /**
     * @var string the unique element identifier for the hidden filter input
     */
    protected $_filterKey;

    /**
     * @var string the unique element identifier for the hidden sort input
     */
    protected $_sortKey;

    /**
     * @var string the identifier for pjax container
     */
    protected $_pjaxId;

    /**
     * @var DynaGridStore the storage instance
     */
    protected $_store;

    /**
     * @var array the configuration for icons set as `$key => $setting`, where `$key` is the icon property in DynaGrid
     * widget and the `$setting` is an array of 2 values - the first value in the array is the icon suffix CSS class for
     * Bootstrap 3.x and the second value in the array is the icon suffix CSS class for Bootstrap 4.x.
     */
    protected static $_icons = [
        'iconVisibleColumn' => ['eye-open', 'eye'],
        'iconHiddenColumn' => ['eye-close', 'eye-slash'],
        'iconSortableSeparator' => ['resize-horizontal', 'arrows-alt-h'],
        'iconPersonalize' => ['wrench', 'wrench fa-fw'],
        'iconFilter' => ['filter', 'filter fa-fw'],
        'iconSort' => ['sort', 'sort fa-fw'],
        'iconConfirm' => ['ok', 'check'],
        'iconRemove' => ['remove', 'times'],
    ];

    /**
     * Is column visible
     *
     * @param mixed $column
     *
     * @return mixed
     */
    protected static function isVisible($column)
    {
        return is_array($column) && !ArrayHelper::getValue($column, 'visible', true) ? false : true;
    }

    /**
     * Get the default action button option settings
     *
     * @param string $type the button type
     *
     * @return array the button settings
     * @throws InvalidConfigException
     */
    protected function getDefaultButtonOptions($type)
    {
        $isBs4 = $this->isBs4();
        if ($type === 'submit') {
            return [
                'type' => 'button',
                'icon' => 'save',
                'label' => Yii::t('kvdynagrid', 'Apply'),
                'title' => Yii::t('kvdynagrid', 'Save grid settings'),
                'class' => 'btn btn-primary',
                'data-pjax' => false,
            ];
        }
        if ($type === 'reset') {
            return [
                'type' => 'reset',
                'icon' => $isBs4 ? 'redo' : 'repeat',
                'label' => Yii::t('kvdynagrid', 'Reset'),
                'title' => Yii::t('kvdynagrid', 'Abort any changes and reset settings'),
                'class' => 'btn ' . $this->getDefaultBtnCss(),
                'data-pjax' => false,
            ];
        }
        if ($type === 'delete') {
            return [
                'type' => 'button',
                'icon' => $isBs4 ? 'trash-alt' : 'trash',
                'label' => Yii::t('kvdynagrid', 'Trash'),
                'title' => Yii::t('kvdynagrid', 'Remove saved grid settings'),
                'class' => 'btn btn-danger',
                'data-pjax' => false,
            ];
        }
        return [];
    }

    /**
     * Gets the columns for the dynagrid
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->gridOptions['columns'];
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     * @throws \Exception
     */
    public function run()
    {
        $this->initWidget();
        echo Html::tag('div', GridView::widget($this->gridOptions), $this->options);
        parent::run();
    }

    /**
     * Initialize the module based on module identifier
     * @throws InvalidConfigException
     */
    protected function initModule()
    {
        if (!isset($this->moduleId)) {
            $this->_module = Module::getInstance();
            if (isset($this->_module)) {
                $this->moduleId = $this->_module->id;
                return;
            }
            $this->moduleId = Module::MODULE;
        }
        $this->_module = Config::getModule($this->moduleId, Module::class);
        if (empty($this->gridOptions['bsVersion']) && isset($this->bsVersion)) {
            $this->gridOptions['bsVersion'] = $this->bsVersion;
        }
        if (isset($this->_module->bsVersion) && !isset($this->bsVersion)) {
            $this->bsVersion = $this->_module->bsVersion;
        }
    }

    /**
     * Initializes icon properties with default values
     * @throws InvalidConfigException
     */
    protected function initIcons()
    {
        $isBs4 = $this->isBs4();
        $prefix = $this->getDefaultIconPrefix();
        foreach (static::$_icons as $icon => $setting) {
            if (!isset($this->$icon)) {
                $css = !$isBs4 ? $setting[0] : $setting[1];
                $this->$icon = Html::tag('i', '', ['class' => $prefix . $css]);
            }
        }
    }

    /**
     * Initializes widget settings and options
     *
     * @throws InvalidConfigException
     */
    protected function initWidget()
    {
        if (empty($this->options['id'])) {
            throw new InvalidConfigException(
                "You must setup a unique identifier for DynaGrid within \"options['id']\"."
            );
        }
        $this->initModule();
        $this->_gridModalId = $this->options['id'] . '-grid-modal';
        $this->_filterModalId = $this->options['id'] . '-filter-modal';
        $this->_sortModalId = $this->options['id'] . '-sort-modal';
        $this->_filterKey = $this->options['id'] . '-filter-key';
        $this->_sortKey = $this->options['id'] . '-sort-key';
        $this->_pjaxId = $this->options['id'] . '-pjax';
        foreach ($this->_module->dynaGridOptions as $key => $setting) {
            if (is_array($setting) && !empty($setting) && !empty($this->$key)) {
                $this->$key = ArrayHelper::merge($setting, $this->$key);
            } elseif (!isset($this->$key)) {
                $this->$key = $setting;
            }
        }
        $this->initIcons();
        if (empty($this->columns) || !is_array($this->columns)) {
            throw new InvalidConfigException("The 'columns' configuration must be setup as a valid array.");
        }
        if (empty($this->gridOptions['dataProvider']) && empty($this->gridOptions['filterModel'])) {
            throw new InvalidConfigException(
                "You must setup either the gridOptions['filterModel'] or gridOptions['dataProvider']."
            );
        }
        if (!empty($this->gridOptions['filterModel']) && !method_exists($this->gridOptions['filterModel'], 'search')) {
            throw new InvalidConfigException(
                "The gridOptions['filterModel'] must implement a 'search' method in order to apply saved filters."
            );
        }
        if (empty($this->gridOptions['dataProvider'])) {
            $this->initDataProvider($this->gridOptions['filterModel']);
        }
        /** @var DataProviderInterface $dataProvider */
        $dataProvider = $this->gridOptions['dataProvider'];
        if ($dataProvider->getSort() === false) {
            $this->showSort = false;
            $this->allowSortSetting = false;
        }
        if ($dataProvider->getPagination() === false) {
            $this->allowPageSetting = false;
        }
        if (empty($this->gridOptions['filterModel'])) {
            $this->showFilter = false;
            $this->allowFilterSetting = false;
        }
        if (empty($this->theme)) {
            $this->theme = $this->_module->defaultTheme;
        }
        if (!isset($this->_pageSize) || $this->_pageSize === null) {
            $this->_pageSize = $this->_module->defaultPageSize;
        }
        $this->_requestSubmit = $this->options['id'] . '-dynagrid';
        $this->_model = new DynaGridConfig(['moduleId' => $this->moduleId]);
        $this->_isSubmit = !empty($_POST[$this->_requestSubmit]) && $this->_model->load(Yii::$app->request->post()) &&
            $this->_model->validate();
        $this->_store = new DynaGridStore(
            [
                'id' => $this->options['id'],
                'moduleId' => $this->moduleId,
                'storage' => $this->storage,
                'userSpecific' => $this->userSpecific,
                'dbUpdateNameOnly' => $this->dbUpdateNameOnly,
            ]
        );
        $this->prepareColumns();
        $this->configureColumns();
        $this->applyGridConfig();
        $this->_isPjax = ArrayHelper::getValue($this->gridOptions, 'pjax', false);
        if ($this->_isPjax) {
            $this->gridOptions['pjaxSettings']['options']['id'] = $this->_pjaxId;
        }
        $this->initGrid();
    }

    /**
     * Can the column be reordered
     *
     * @param mixed $column
     *
     * @return boolean
     */
    protected function canReorder($column)
    {
        return is_array($column) && ArrayHelper::getValue($column, 'order', self::ORDER_MIDDLE) != self::ORDER_MIDDLE
            ? false : true;
    }

    /**
     * Initialize the data provider
     *
     * @param Model $searchModel
     */
    protected function initDataProvider($searchModel)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->gridOptions['dataProvider'] = $searchModel->search(Yii::$app->request->getQueryParams());
    }

    /**
     * Prepares the columns for the dynagrid
     */
    protected function prepareColumns()
    {
        $this->_columns = $this->columns;
        $columns = [];
        foreach ($this->columns as $column) {
            if (is_array($column)) {
                unset($column['order']);
            }
            $columns[] = $column;
        }
        $this->gridOptions['columns'] = $columns;
    }

    /**
     * Reconfigure columns with unique keys
     * @throws InvalidConfigException
     */
    protected function configureColumns()
    {
        $columnsByKey = [];
        foreach ($this->_columns as $column) {
            $columnKey = $this->getColumnKey($column);
            for ($j = 0; true; $j++) {
                $suffix = ($j) ? '_' . $j : '';
                $columnKey .= $suffix;
                if (!array_key_exists($columnKey, $columnsByKey)) {
                    break;
                }
            }
            $columnsByKey[$columnKey] = $column;
        }
        $this->_columns = $columnsByKey;
    }

    /**
     * Generate an unique column key
     *
     * @param mixed $column
     *
     * @return mixed
     * @throws InvalidConfigException
     */
    protected function getColumnKey($column)
    {
        if (!is_array($column)) {
            $matches = $this->matchColumnString($column);
            $columnKey = $matches[1];
        } elseif (!empty($column['attribute'])) {
            $columnKey = $column['attribute'];
        } elseif (!empty($column['label'])) {
            $columnKey = $column['label'];
        } elseif (!empty($column['header'])) {
            $columnKey = $column['header'];
        } elseif (!empty($column['class'])) {
            $columnKey = $column['class'];
        } else {
            $columnKey = null;
        }
        return hash('crc32', $columnKey);
    }

    /**
     * Finds the matches for a string column format
     *
     * @param string $column
     *
     * @return mixed
     * @throws InvalidConfigException
     */
    protected function matchColumnString($column)
    {
        $matches = [];
        if (!preg_match('/^([\w\.]+)(:(\w*))?(:(.*))?$/u', $column, $matches)) {
            throw new InvalidConfigException(
                "Invalid column configuration for '{$column}'. The column must be specified " .
                "in the format of 'attribute', 'attribute:format' or 'attribute:format: label'."
            );
        }
        return $matches;
    }

    /**
     * Applies the current grid configuration
     * @throws InvalidConfigException
     */
    protected function applyGridConfig()
    {
        $config = $this->getGridConfig();
        if ($this->_isSubmit) {
            $delete = ArrayHelper::getValue($_POST, 'deleteFlag', 0) == 1;
            $this->saveGridConfig($config, $delete);
            return Yii::$app->controller->refresh();
        } else {
            $this->loadGridConfig($config);
            $this->setWidgetColumns();
            $this->loadAttributes($this->_model);
        }
        $this->applyFilter();
        $this->applySort();
        $this->applyPageSize();
        $this->applyTheme();
        $this->applyColumns();
    }

    /**
     * Gets the current grid configuration
     *
     * @param boolean $current whether it is the currently set grid configuraton
     *
     * @return array
     * @throws InvalidConfigException
     */
    protected function getGridConfig($current = false)
    {
        if ($current) {
            return [
                'page' => $this->_pageSize,
                'keys' => $this->_visibleKeys,
                'theme' => $this->theme,
                'filter' => $this->_filterId,
                'sort' => $this->_sortId,
            ];
        }
        return !$this->_isSubmit ? $this->_store->fetch() : [
            'page' => $this->_model->pageSize,
            'theme' => $this->_model->theme,
            'keys' => explode(',', $_POST['visibleKeys']),
            'filter' => $this->_model->filterId,
            'sort' => $this->_model->sortId,
        ];
    }

    /**
     * Update configuration
     *
     * @param array $config the dynagrid configuration
     * @param boolean $delete the deletion flag
     * @throws InvalidConfigException
     */
    protected function saveGridConfig($config, $delete)
    {
        if ($delete) {
            $this->_store->delete();
        } else {
            $this->_store->save($config);
        }
    }

    /**
     * Load grid configuration from specific storage
     *
     * @param array $config the configuration to load
     *
     * @throws InvalidConfigException
     */
    protected function loadGridConfig($config = [])
    {
        if ($config === false) {
            $this->_visibleKeys = []; //take visible keys from grid config
            $this->_pageSize = $this->_module->defaultPageSize; //take pagesize from module configuration
            foreach ($this->_columns as $key => $column) {
                if (static::canReorder($column) && static::isVisible($column)) {
                    $this->_visibleKeys[] = $key;
                }
            }
        } else {
            $this->parseData($config);
        }
    }

    /**
     * Parses the encoded grid configuration and fetches
     * - grid master settings: the theme, pagesize, visible keys
     * - grid detail settings: filter and sort configuration
     *
     * @param array $data the stored data to be parsed
     *
     * @return void
     * @throws InvalidConfigException
     */
    protected function parseData($data)
    {
        if (!is_array($data) || empty($data)) {
            return;
        }
        $this->_pageSize = ArrayHelper::getValue($data, 'page', $this->_module->defaultPageSize);
        $this->theme = ArrayHelper::getValue($data, 'theme', $this->_module->defaultTheme);
        if ($this->storage === self::TYPE_DB) {
            $this->_filterId = $this->_store->fetch('filterAttr');
            $this->_sortId = $this->_store->fetch('sortAttr');
        } else {
            $this->_filterId = ArrayHelper::getValue($data, DynaGridStore::STORE_FILTER, '');
            $this->_sortId = ArrayHelper::getValue($data, DynaGridStore::STORE_SORT, '');
        }
        if (!empty($data['keys'])) {
            $this->_visibleKeys = $data['keys'];
        }
        $this->parseDetailData(DynaGridStore::STORE_FILTER);
        $this->parseDetailData(DynaGridStore::STORE_SORT);
    }

    /**
     * Parses the grid detail configuration (for filter or sort).
     *
     * @param string $category one of 'filter' or 'sort'
     * @throws InvalidConfigException
     */
    protected function parseDetailData($category)
    {
        $dtlKey = "_{$category}Id";
        if (!empty($this->$dtlKey)) {
            $store = new DynaGridStore(
                [
                    'id' => $this->options['id'],
                    'moduleId' => $this->moduleId,
                    'storage' => $this->storage,
                    'userSpecific' => $this->userSpecific,
                    'dbUpdateNameOnly' => $this->dbUpdateNameOnly,
                    'category' => $category,
                    'dtlKey' => $this->$dtlKey,
                ]
            );
            $config = $store === null ? false : $store->fetch();
            if ($config !== false) {
                $this->_detailConfig[$category] = $config;
            }
        }
    }

    /**
     * Sets widget columns for display in [[\kartik\sortable\Sortable]] widget
     * @throws InvalidConfigException
     */
    protected function setWidgetColumns()
    {
        $this->_visibleColumns = $this->getSortableHeader($this->_model->getAttributeLabel('visibleColumns'));
        $this->_hiddenColumns = $this->getSortableHeader($this->_model->getAttributeLabel('hiddenColumns'));
        $visibleSettings = [];

        // Ensure visible keys is not empty. If it is so, then grid will display all columns.
        $this->_visibleKeys = array_filter($this->_visibleKeys);
        $showAll = !is_array($this->_visibleKeys) || empty($this->_visibleKeys);
        $indicator = Html::tag('span', $this->iconVisibleColumn, ['class' => 'icon-visible-column']) .
            Html::tag('span', $this->iconHiddenColumn, ['class' => 'icon-hidden-column']);
        foreach ($this->_columns as $key => $column) {
            $order = ArrayHelper::getValue($column, 'order', self::ORDER_MIDDLE);
            $disabled = ($order == self::ORDER_MIDDLE) ? false : true;
            $widgetColumns = [
                'content' => (empty($indicator) ? '' : $indicator . ' ') . $this->getColumnLabel($key, $column),
                'options' => ['id' => $key],
            ];

            if ($showAll && !$disabled) {
                $visibleSettings[$key] = $widgetColumns;
            } elseif (in_array($key, $this->_visibleKeys) && !$disabled) {
                $visibleSettings[$key] = $widgetColumns;
            } else {
                $this->_hiddenColumns[] = $widgetColumns + ['disabled' => $disabled];
            }
        }
        if ($showAll) {
            $this->_visibleColumns = $visibleSettings;
            $this->_visibleKeys = array_keys($this->_visibleColumns);
        } else {
            foreach ($this->_visibleKeys as $key) {
                if (!empty($visibleSettings[$key])) {
                    $this->_visibleColumns[] = $visibleSettings[$key];
                }
            }
        }
    }

    /**
     * Generates the config for sortable widget header
     *
     * @param string $label
     *
     * @return array
     */
    protected function getSortableHeader($label)
    {
        return [
            [
                'content' => $label,
                'disabled' => true,
                'options' => $this->sortableHeader,
            ],
        ];
    }

    /**
     * Fetches the column label
     *
     * @param mixed $key the column key
     * @param mixed $column the column object / configuration
     *
     * @return string
     * @throws InvalidConfigException
     */
    protected function getColumnLabel($key, $column)
    {
        if (is_string($column)) {
            $matches = $this->matchColumnString($column);
            $attribute = $matches[1];
            if (isset($matches[5])) {
                return $matches[5];
            } //header specified is in the format "attribute:format:label"
            return $this->getAttributeLabel($attribute);
        } else {
            $label = $key;
            if (is_array($column)) {
                if (!empty($column['label'])) {
                    $label = $column['label'];
                } elseif (!empty($column['header'])) {
                    $label = $column['header'];
                } elseif (!empty($column['attribute'])) {
                    $label = $this->getAttributeLabel($column['attribute']);
                } elseif (!empty($column['class'])) {
                    $class = explode('\\', $column['class']);
                    $label = Inflector::camel2words(end($class));
                }
            }
            return trim(strip_tags(str_replace(['<br>', '<br/>'], ' ', $label)));
        }
    }

    /**
     * Generates the attribute label
     *
     * @param $attribute
     *
     * @return string
     */
    protected function getAttributeLabel($attribute)
    {
        $provider = $this->gridOptions['dataProvider'];
        /** @var Model $model */
        if ($provider instanceof ActiveDataProvider && $provider->query instanceof ActiveQueryInterface) {
            /** @var ActiveQuery $query */
            $query = $provider->query;
            $model = new $query->modelClass;
            return $model->getAttributeLabel($attribute);
        } elseif ($provider instanceof ActiveDataProvider && $provider->query instanceof QueryInterface) {
            return Inflector::camel2words($attribute);
        } else {
            $models = $provider->getModels();
            if (($model = reset($models)) instanceof Model) {
                return $model->getAttributeLabel($attribute);
            } else {
                return Inflector::camel2words($attribute);
            }
        }
    }

    /**
     * Load configuration attributes into DynaGridConfig model
     *
     * @param DynaGridConfig $model
     * @throws InvalidConfigException
     */
    protected function loadAttributes($model)
    {
        $model->id = $this->_requestSubmit;
        $model->hiddenColumns = $this->_hiddenColumns;
        $model->visibleColumns = $this->_visibleColumns;
        $model->pageSize = $this->_pageSize;
        $model->theme = $this->theme;
        $model->filterId = $this->_filterId;
        $model->sortId = $this->_sortId;
        $model->widgetOptions = $this->sortableOptions;
        $model->footer = $this->renderActionButton('delete') . $this->renderActionButton('reset') .
            $this->renderActionButton('submit');
        $themes = array_keys($this->_module->themeConfig);
        $model->themeList = array_combine($themes, $themes);
    }

    /**
     * Renders the action button
     *
     * @param string $type the button type
     *
     * @return string the rendered button
     * @throws InvalidConfigException
     */
    protected function renderActionButton($type)
    {
        $tag = "{$type}ButtonOptions";
        $options = $this->$tag;
        if ($options === false) {
            return '';
        }
        $defaultOptions = $this->getDefaultButtonOptions($type);
        if (!is_array($options)) {
            $options = $defaultOptions;
        } else {
            $options = ArrayHelper::merge($defaultOptions, $options);
        }
        $icon = ArrayHelper::remove($options, 'icon', '');
        $prefix = ArrayHelper::remove($options, 'prefix', $this->getDefaultIconPrefix());
        $label = '';
        if (!empty($icon)) {
            $label = '<i class="' . $prefix . $icon . '"></i> ';
        }
        $label .= ArrayHelper::remove($options, 'label', '');
        Html::addCssClass($options, "dynagrid-{$type}");
        return Html::button($label, $options);
    }

    /**
     * Applies the grid filter
     */
    protected function applyFilter()
    {
        if (empty($this->gridOptions['filterModel'])) {
            return;
        }
        $class = get_class($this->gridOptions['filterModel']);
        /** @var Model $searchModel */
        if (!empty($this->_detailConfig[DynaGridStore::STORE_FILTER]) && empty($_GET[$class])) {
            $attributes = $this->_detailConfig[DynaGridStore::STORE_FILTER];
            $searchModel = $this->gridOptions['filterModel'];
            $searchModel->setAttributes($attributes);
            $this->gridOptions['filterModel'] = $searchModel;
            $this->initDataProvider($searchModel);
        }
    }

    /**
     * Applies the grid sort
     */
    protected function applySort()
    {
        if (!empty($this->_detailConfig[DynaGridStore::STORE_SORT])) {
            /** @var ActiveDataProvider $dataProvider */
            $dataProvider = $this->gridOptions['dataProvider'];
            $sort = $dataProvider->getSort();
            if (!$sort instanceof Sort) {
                return;
            }
            $sort->defaultOrder = $this->_detailConfig[DynaGridStore::STORE_SORT];
            $dataProvider->setSort($sort);
            $this->gridOptions['dataProvider'] = $dataProvider;
        }
    }

    /**
     * Applies the page size
     */
    protected function applyPageSize()
    {
        if (isset($this->_pageSize) && $this->_pageSize !== '' && $this->allowPageSetting) {
            /** @var \yii\data\BaseDataProvider $dataProvider */
            $dataProvider = $this->gridOptions['dataProvider'];
            if ($dataProvider instanceof ArrayDataProvider) {
                $dataProvider->refresh();
            }
            if ($this->_pageSize > 0) {
                $dataProvider->setPagination(['pageSize' => $this->_pageSize]);
            } else {
                $dataProvider->setPagination(false);
            }
            if ($dataProvider instanceof SqlDataProvider) {
                $dataProvider->prepare(true);
            }
            $this->gridOptions['dataProvider'] = $dataProvider;
        }
    }

    /**
     * Applies the configured theme
     */
    protected function applyTheme()
    {
        $theme = ArrayHelper::getValue($this->_module->themeConfig, $this->theme, $this->_module->defaultTheme);
        if (!is_array($theme) || empty($theme)) {
            return;
        }
        $this->gridOptions = ArrayHelper::merge($theme, $this->gridOptions);
    }

    /**
     * Applies the configured columns
     */
    protected function applyColumns()
    {
        $columns = [];
        $newColumns = [];
        foreach ($this->columns as $column) {
            $order = ArrayHelper::getValue($column, 'order', self::ORDER_MIDDLE);
            if ($order == self::ORDER_FIX_LEFT) {
                $newColumns = $column;
                unset($column['order']);
                $columns[] = $column;
            }
        }
        foreach ($this->_visibleKeys as $key) {
            if (empty($this->_columns[$key])) {
                continue;
            }
            $column = $this->_columns[$key];
            $newColumns = $column;
            if (isset($column['order'])) {
                unset($column['order']);
            }
            if (isset($column['visible'])) {
                unset($column['visible']);
            }
            $columns[] = $column;
        }
        foreach ($this->columns as $column) {
            $order = ArrayHelper::getValue($column, 'order', self::ORDER_MIDDLE);
            if ($order == self::ORDER_FIX_RIGHT) {
                $newColumns = $column;
                unset($column['order']);
                $columns[] = $column;
            }
        }
        $this->columns = $newColumns;
        $this->gridOptions['columns'] = $columns;
    }

    /**
     * Initialize the grid view for dynagrid
     * @throws InvalidConfigException
     * @throws \Exception
     */
    protected function initGrid()
    {
        $dynagrid = '';
        $dynagridFilter = '';
        $dynagridSort = '';
        $isBs4 = $this->isBs4();
        $model = new DynaGridSettings(
            [
                'moduleId' => $this->moduleId,
                'dynaGridId' => $this->options['id'],
                'storage' => $this->storage,
                'userSpecific' => $this->userSpecific,
                'dbUpdateNameOnly' => $this->dbUpdateNameOnly,
            ]
        );
        /** @var ActiveDataProvider $dataProvider */
        $dataProvider = $this->gridOptions['dataProvider'];
        $sort = $dataProvider->getSort();
        $isValidSort = ($sort instanceof Sort);
        if ($this->showPersonalize) {
            $this->setToggleButton(DynaGridStore::STORE_GRID);
            if ($this->allowFilterSetting || $this->allowSortSetting) {
                $store = new DynaGridStore(
                    [
                        'id' => $this->options['id'],
                        'moduleId' => $this->moduleId,
                        'category' => DynaGridStore::STORE_GRID,
                        'storage' => $this->storage,
                        'userSpecific' => $this->userSpecific,
                        'dbUpdateNameOnly' => $this->dbUpdateNameOnly,
                    ]
                );
                if ($this->allowFilterSetting) {
                    $this->_model->filterId = $this->_filterId;
                    $this->_model->filterList = $store->getDtlList(DynaGridStore::STORE_FILTER);
                }
                if ($this->allowSortSetting && $isValidSort) {
                    $sort->enableMultiSort = $this->enableMultiSort;
                    $dataProvider->setSort($sort);
                    $this->_model->sortId = $this->_sortId;
                    $this->_model->sortList = $store->getDtlList(DynaGridStore::STORE_SORT);
                }
            }
            $dynagrid = $this->render(
                $this->_module->configView,
                [
                    'model' => $this->_model,
                    'toggleButtonGrid' => $this->toggleButtonGrid,
                    'id' => $this->_gridModalId,
                    'allowPageSetting' => $this->allowPageSetting,
                    'allowThemeSetting' => $this->allowThemeSetting,
                    'allowFilterSetting' => $this->allowFilterSetting,
                    'allowSortSetting' => $this->allowSortSetting,
                    'moduleId' => $this->moduleId,
                    'isBs4' => $isBs4,
                    'isPjax' => $this->_isPjax,
                    'pjaxId' => $this->_pjaxId,
                    'iconPersonalize' => $this->iconPersonalize,
                    'iconSortableSeparator' => $this->iconSortableSeparator,
                ]
            );
        }
        $opts = [
            'bsVersion' => $this->bsVersion,
            'model' => $model,
            'moduleId' => $this->moduleId,
            'submitMessage' => $this->submitMessage,
            'deleteMessage' => $this->deleteMessage,
            'messageOptions' => $this->messageOptions,
            'deleteConfirmation' => $this->deleteConfirmation,
            'isPjax' => $this->_isPjax,
            'pjaxId' => $this->_pjaxId,
            'krajeeDialogSettings' => $this->krajeeDialogSettings,
            'iconFilter' => $this->iconFilter,
            'iconSort' => $this->iconSort,
            'iconConfirm' => $this->iconConfirm,
            'iconRemove' => $this->iconRemove,
        ];
        if ($this->showFilter) {
            $this->setToggleButton(DynaGridStore::STORE_FILTER);
            $model->category = DynaGridStore::STORE_FILTER;
            $model->key = $this->_filterKey;
            $model->data = array_filter($this->gridOptions['filterModel']->attributes);
            $opts['id'] = $this->_filterModalId;
            $opts['toggleButton'] = $this->toggleButtonFilter;
            $dynagridFilter = DynaGridDetail::widget($opts);
        }
        if ($this->showSort) {
            $this->setToggleButton(DynaGridStore::STORE_SORT);
            $model->category = DynaGridStore::STORE_SORT;
            $model->key = $this->_sortKey;
            $model->data = $isValidSort ? $sort->getAttributeOrders() : [];
            $opts['id'] = $this->_sortModalId;
            $opts['toggleButton'] = $this->toggleButtonSort;
            $dynagridSort = DynaGridDetail::widget($opts);
        }
        $tags = ArrayHelper::getValue($this->gridOptions, 'replaceTags', []);
        $tags += [
            '{dynagrid}' => $dynagrid,
            '{dynagridFilter}' => $dynagridFilter,
            '{dynagridSort}' => $dynagridSort,
        ];
        $this->gridOptions['replaceTags'] = $tags;
        $this->registerAssets();
    }

    /**
     * Sets the personalization toggle button
     *
     * @param string $cat the category 'grid', 'filter', or 'sort'
     * @throws InvalidConfigException
     */
    protected function setToggleButton($cat)
    {
        $setting = 'toggleButton' . ucfirst($cat);
        $isBs4 = $this->isBs4();
        $defaultCss = $isBs4 ? 'outline-secondary' : 'default';
        $btnClass = ($this->matchPanelStyle && $cat == 'grid' && !empty($this->gridOptions['panel'])) ?
            'btn btn-' . ArrayHelper::getValue($this->gridOptions['panel'], 'type', $defaultCss) :
            'btn btn-' . $defaultCss;
        Html::addCssClass($this->$setting, $btnClass);
        if ($cat == DynaGridStore::STORE_GRID) {
            $this->toggleButtonGrid = ArrayHelper::merge(
                [
                    'label' => $this->iconPersonalize,
                    'title' => Yii::t('kvdynagrid', 'Personalize grid settings'),
                    'data-pjax' => false,
                ],
                $this->toggleButtonGrid
            );
        } else {
            $catIcon = 'icon' . ucfirst($cat);
            $this->$setting = ArrayHelper::merge(
                [
                    'label' => $this->$catIcon,
                    'title' => Yii::t(
                        'kvdynagrid',
                        'Save / edit grid {category}',
                        ['category' => static::getCat($cat)]
                    ),
                    'data-pjax' => false,
                ],
                $this->$setting
            );
        }
    }

    /**
     * Registers client assets
     * @throws \Exception
     */
    protected function registerAssets()
    {
        $view = $this->getView();
        DynaGridAsset::register($view);
        Dialog::widget($this->krajeeDialogSettings);
        Html::addCssClass($this->messageOptions, 'dynagrid-submit-message');
        $options = Json::encode(
            [
                'submitMessage' => Html::tag('div', $this->submitMessage, $this->messageOptions),
                'deleteMessage' => Html::tag('div', $this->deleteMessage, $this->messageOptions),
                'deleteConfirmation' => $this->deleteConfirmation,
                'modalId' => $this->_gridModalId,
                'dynaGridId' => $this->options['id'],
                'dialogLib' => ArrayHelper::getValue($this->krajeeDialogSettings, 'libName', 'krajeeDialog'),
            ]
        );
        $id = "jQuery('[name=\"{$this->_requestSubmit}\"]')";
        // the core dynagrid form validation
        $js = "{$id}.dynagrid({$options});\n";
        // pjax related reset
        if ($this->_isPjax) {
            $cleanup = $this->pjaxCleanupJs('grid') . $this->pjaxCleanupJs('filter') . $this->pjaxCleanupJs('sort');
            $js .= " jQuery('#{$this->_pjaxId}').on('pjax:beforeReplace', function () {
                {$cleanup}
            });";
            $js .= " jQuery('#{$this->_pjaxId}').on('pjax:end', function () {
                {$id}.dynagrid({$options});
                {$id}.dynagrid('reset');
            });";
        }
        $view->registerJs($js);
    }

    /**
     * Generates the cleanup client script for modals
     *
     * @param string $type the modal container type 'grid', 'filter', or 'sort'
     *
     * @return string
     */
    protected function pjaxCleanupJs($type)
    {
        $id = "_{$type}ModalId";
        return 'jQuery("#' . $this->$id . '").remove();';
    }
}
