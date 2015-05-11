<?php

/**
 * @package   yii2-dynagrid
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2015
 * @version   1.4.2
 */

namespace kartik\dynagrid;

use Yii;
use kartik\base\Config;
use kartik\dynagrid\models\DynaGridConfig;
use kartik\dynagrid\models\DynaGridSettings;
use kartik\grid\CheckboxColumn;
use kartik\grid\GridView;
use kartik\sortable\Sortable;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\helpers\Json;
use yii\helpers\Html;
use yii\base\InvalidConfigException;
use yii\web\Cookie;

/**
 * Enhance GridView by allowing you to dynamically edit grid configuration. The dynagrid
 * allows you to set your own grid theme, pagesize, and column order/display settings.
 * The widget allows you to manage the order and visibility of columns dynamically
 * at runtime. It also allows you to save this configuration or retrieve the saved
 * configuration to/from session, cookie, or database.
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.0
 */
class DynaGrid extends \yii\base\Widget
{
    const TYPE_SESSION = 'session';
    const TYPE_COOKIE = 'cookie';
    const TYPE_DB = 'db';

    const ORDER_FIX_LEFT = 'fixleft';
    const ORDER_FIX_RIGHT = 'fixright';
    const ORDER_MIDDLE = 'middle';

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
     * @var array widget options for \kartik\widgets\GridView that will be rendered
     * by the DynaGrid widget
     */
    public $gridOptions;

    /**
     * @var bool whether the DynaGrid configuration button class should match
     * the grid panel style.
     */
    public $matchPanelStyle;

    /**
     * @var array the HTML attributes for the dynagrid personalize toggle button which will
     * render the DynaGrid configuration form within a Bootstrap Modal container.
     */
    public $toggleButtonGrid;

    /**
     * @var array the HTML attributes for the filter configuration button which will
     * render the Filter settings form within a Bootstrap Modal container.
     */
    public $toggleButtonFilter;

    /**
     * @var array the HTML attributes for the sort configuration button which will
     * render the Sort settings form within a Bootstrap Modal container.
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
     * @var string the message to display after applying and submitting the configuration and
     * until refreshed grid is reloaded
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
     * @var array the HTML attributes for the save/apply action button. If this is set to `false`, it will not be
     *     displayed. The following special variables are supported:
     * - `icon`: string the glyphicon class suffix for the button. Defaults to `save`.
     * - `label`: string the label for the action button. Defaults to empty string.
     * - `title`: string the title for the action button. Defaults to `Save grid settings`.
     */
    public $submitButtonOptions = [];

    /**
     * @var array|boolean the HTML attributes for the reset action button. If this is set to `false`, it will not be
     *     displayed. The following special variables are supported:
     * - `icon`: string the glyphicon class suffix for the button. Defaults to `repeat`.
     * - `label`: string the label for the action button. Defaults to empty string.
     * - `title`: string the title for the action button. Defaults to `Abort any changes and reset settings`.
     */
    public $resetButtonOptions = [];

    /**
     * @var array|boolean the HTML attributes for the delete/trash action button. If this is set to `false`, it will
     *     not be displayed. The following special variables are supported:
     * - `icon`: string the glyphicon class suffix for the button. Defaults to `trash`.
     * - `label`: string the label for the action button. Defaults to empty string.
     * - `title`: string the title for the action button. Defaults to `Remove saved grid settings`.
     */
    public $deleteButtonOptions = [];

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
     * @var kartik\dynagrid\models\DynaGridConfig model
     */
    protected $_model;

    /**
     * @var bool flag to check if the grid configuration form has been submitted
     */
    protected $_isSubmit = false;

    /**
     * @var bool flag to check if the pjax is enabled for the grid
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
     * Initializes the widget
     *
     * @throws InvalidConfigException
     * @return void
     */
    public function init()
    {
        parent::init();
        if (empty($this->options['id'])) {
            throw new InvalidConfigException("You must setup a unique identifier for DynaGrid within \"options['id']\".");
        }
        $this->_module = Config::initModule(Module::classname());
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
        if (empty($this->columns) || !is_array($this->columns)) {
            throw new InvalidConfigException("The 'columns' configuration must be setup as a valid array.");
        }
        if (empty($this->gridOptions['dataProvider']) && empty($this->gridOptions['filterModel'])) {
            throw new InvalidConfigException("You must setup either the gridOptions['filterModel'] or gridOptions['dataProvider'].");
        }
        if (!empty($this->gridOptions['filterModel']) && !method_exists($this->gridOptions['filterModel'], 'search')) {
            throw new InvalidConfigException("The gridOptions['filterModel'] must implement a 'search' method in order to apply saved filters.");
        }
        if (empty($this->gridOptions['dataProvider'])) {
            $this->initDataProvider($this->gridOptions['filterModel']);
        }
        if (empty($this->gridOptions['filterModel'])) {
            $this->showFilter = false;
            $this->allowFilterSetting = false;
        }
        if (empty($this->theme)) {
            $this->theme = $this->_module->defaultTheme;
        }
        if (empty($this->_pageSize)) {
            $this->_pageSize = $this->_module->defaultPageSize;
        }
        $this->_requestSubmit = $this->options['id'] . '-dynagrid';
        $this->_model = new DynaGridConfig;
        $this->_isSubmit = !empty($_POST[$this->_requestSubmit]) && $this->_model->load(Yii::$app->request->post()) && $this->_model->validate();
        $this->_store = new DynaGridStore([
            'id' => $this->options['id'],
            'storage' => $this->storage,
            'userSpecific' => $this->userSpecific
        ]);
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
     * Initialize the data provider
     *
     * @return void
     */
    protected function initDataProvider($searchModel)
    {
        $this->gridOptions['dataProvider'] = $searchModel->search(Yii::$app->request->getQueryParams());
    }

    /**
     * Prepares the columns for the dynagrid
     *
     * @return void
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
     *
     * @return void
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
     * @throws \yii\base\InvalidConfigException
     */
    protected function matchColumnString($column)
    {
        $matches = [];
        if (!preg_match('/^([\w\.]+)(:(\w*))?(:(.*))?$/', $column, $matches)) {
            throw new InvalidConfigException("Invalid column configuration for '{$column}'. The column must be specified in the format of 'attribute', 'attribute:format' or 'attribute:format: label'.");
        }
        return $matches;
    }

    /**
     * Applies the current grid configuration
     *
     * @return void
     */
    protected function applyGridConfig()
    {
        $config = $this->getGridConfig();
        if ($this->_isSubmit) {
            $delete = ArrayHelper::getValue($_POST, 'deleteFlag', 0) == 1;
            $this->saveGridConfig($config, $delete);
            Yii::$app->controller->refresh();
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
     * @param bool $current whether it is the currently set grid configuraton
     *
     * @return array
     */
    protected function getGridConfig($current = false)
    {
        if ($current) {
            return [
                'page' => $this->_pageSize,
                'keys' => $this->_visibleKeys,
                'theme' => $this->theme,
                'filter' => $this->_filterId,
                'sort' => $this->_sortId
            ];
        }
        return !$this->_isSubmit ? $this->_store->fetch() : [
            'page' => $this->_model->pageSize,
            'theme' => $this->_model->theme,
            'keys' => explode(',', $_POST['visibleKeys']),
            'filter' => $this->_model->filterId,
            'sort' => $this->_model->sortId
        ];
    }

    /**
     * Update configuration
     *
     * @param array   $config the dynagrid configuration
     * @param boolean $delete the deletion flag
     *
     * @return void
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
     * @param array the configuration to load
     *
     * @throws \yii\base\InvalidConfigException
     * @return void
     */
    protected function loadGridConfig($config)
    {
        if ($config === false) {
            $this->_visibleKeys = []; //take visible keys from grid config
            $this->_pageSize = $this->_module->defaultPageSize; //take pagesize from module configuration
            foreach ($this->_columns as $key => $column) {
                if ($this->isReorderable($column) && ArrayHelper::getValue($column, 'visible', true) === true) {
                    $this->_visibleKeys[] = $key;
                }
            }
        } else {
            $this->parseData($config);
        }
    }

    /**
     * Can the column be reordered
     *
     * @param mixed $column
     *
     * @return mixed
     */
    protected function isReorderable($column)
    {
        return (is_array($column) && ArrayHelper::getValue($column, 'order',
                self::ORDER_MIDDLE) != self::ORDER_MIDDLE) ? false : true;
    }

    /**
     * Parses the encoded grid configuration and fetches
     * - grid master settings: the theme, pagesize, visible keys
     * - grid detail settings: filter and sort configuration
     *
     * @param array $data the stored data to be parsed
     *
     * @return void
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
        $this->parseDetailData($data, DynaGridStore::STORE_FILTER);
        $this->parseDetailData($data, DynaGridStore::STORE_SORT);
    }

    /**
     * Parses the grid detail configuration (for filter or sort).
     *
     * @param array  $data the stored data to be parsed
     * @param string $category one of 'filter' or 'sort'
     *
     * @return void
     */
    protected function parseDetailData($data, $category)
    {
        $dtlKey = "_{$category}Id";
        if (!empty($this->$dtlKey)) {
            $store = new DynaGridStore([
                'id' => $this->options['id'],
                'storage' => $this->storage,
                'userSpecific' => $this->userSpecific,
                'category' => $category,
                'dtlKey' => $this->$dtlKey
            ]);
            $config = $store === null ? false : $store->fetch();
            if ($config !== false) {
                $this->_detailConfig[$category] = $config;
            }
        }
    }

    /**
     * Sets widget columns for display in [[\kartik\sortable\Sortable]]
     *
     * @return void
     */
    protected function setWidgetColumns()
    {
        $this->_visibleColumns = $this->getSortableHeader($this->_model->getAttributeLabel('visibleColumns'));
        $this->_hiddenColumns = $this->getSortableHeader($this->_model->getAttributeLabel('hiddenColumns'));
        $visibleSettings = [];

        // Ensure visible keys is not empty. If it is so, then grid will display all columns.
        $this->_visibleKeys = array_filter($this->_visibleKeys);
        $showAll = !is_array($this->_visibleKeys) || empty($this->_visibleKeys);

        foreach ($this->_columns as $key => $column) {
            $order = ArrayHelper::getValue($column, 'order', self::ORDER_MIDDLE);
            $disabled = ($order == self::ORDER_MIDDLE) ? false : true;
            $widgetColumns = [
                'content' => $this->getColumnLabel($key, $column),
                'options' => ['id' => $key]
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
                'options' => $this->sortableHeader
            ]
        ];
    }

    /**
     * Fetches the column label
     *
     * @param mixed $key
     * @param mixed $column
     *
     * @return string
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
                    $class = explode("\\", $column['class']);
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
        if ($provider instanceof yii\data\ActiveDataProvider && $provider->query instanceof yii\db\ActiveQueryInterface) {
            $model = new $provider->query->modelClass;
            return $model->getAttributeLabel($attribute);
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
     * @param Model $model
     *
     * @return void
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
        $model->footer = $this->renderActionButton('delete') .
            $this->renderActionButton('reset') .
            $this->renderActionButton('submit');
        $themes = array_keys($this->_module->themeConfig);
        $model->themeList = array_combine($themes, $themes);
    }

    /**
     * Renders the action button
     *
     * @return array
     */
    protected function renderActionButton($type)
    {
        $tag = "{$type}ButtonOptions";
        $options = $this->$tag;
        if ($options === false) {
            return '';
        }
        $defaultOptions = static::getDefaultButtonOptions($type);
        if (!is_array($options)) {
            $options = $defaultOptions;
        } else {
            $options = ArrayHelper::merge($defaultOptions, $options);
        }
        $icon = ArrayHelper::remove($options, 'icon', '');
        $label = '';
        if (!empty($icon)) {
            $label = '<span class="glyphicon glyphicon-' . $icon . '"></span> ';
        }
        $label .= ArrayHelper::remove($options, 'label', '');
        Html::addCssClass($options, "dynagrid-{$type}");
        return Html::button($label, $options);
    }

    /**
     * Get the default action button option settings
     *
     * @return array
     */
    protected static function getDefaultButtonOptions($type)
    {
        if ($type === 'submit') {
            return [
                'type' => 'button',
                'icon' => 'save',
                'label' => Yii::t('kvdynagrid', 'Apply'),
                'title' => Yii::t('kvdynagrid', 'Save grid settings'),
                'class' => 'btn btn-primary',
                'data-pjax' => false
            ];
        }
        if ($type === 'reset') {
            return [
                'type' => 'reset',
                'icon' => 'repeat',
                'label' => Yii::t('kvdynagrid', 'Reset'),
                'title' => Yii::t('kvdynagrid', 'Abort any changes and reset settings'),
                'class' => 'btn btn-default',
                'data-pjax' => false
            ];
        }
        if ($type === 'delete') {
            return [
                'type' => 'button',
                'icon' => 'trash',
                'label' => Yii::t('kvdynagrid', 'Trash'),
                'title' => Yii::t('kvdynagrid', 'Remove saved grid settings'),
                'class' => 'btn btn-danger',
                'data-pjax' => false
            ];
        }
    }

    /**
     * Applies the grid filter
     *
     * @return void
     */
    protected function applyFilter()
    {
        if (empty($this->gridOptions['filterModel'])) {
            return;
        }
        $class = get_class($this->gridOptions['filterModel']);
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
     *
     * @return void
     */
    protected function applySort()
    {
        if (!empty($this->_detailConfig[DynaGridStore::STORE_SORT])) {
            $order = $this->_detailConfig[DynaGridStore::STORE_SORT];
            $dataProvider = $this->gridOptions['dataProvider'];
            $sort = $dataProvider->getSort();
            $sort->defaultOrder = $order;
            $dataProvider->setSort($sort);
            $this->gridOptions['dataProvider'] = $dataProvider;
        }
    }

    /**
     * Applies the page size
     *
     * @return void
     */
    protected function applyPageSize()
    {
        if (!empty($this->_pageSize)) {
            $dataProvider = $this->gridOptions['dataProvider'];
            $pagination = $dataProvider->getPagination();
            $pagination->pageSize = $this->_pageSize;
            $dataProvider->setPagination($pagination);
            $this->gridOptions['dataProvider'] = $dataProvider;
        }
    }

    /**
     * Applies the configured theme
     */
    protected function applyTheme()
    {
        $theme = $this->_module->themeConfig[$this->theme];
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
     */
    protected function initGrid()
    {
        $dynagrid = '';
        $dynagridFilter = '';
        $dynagridSort = '';
        $model = new DynaGridSettings;
        if ($this->showPersonalize) {
            $this->setToggleButton('grid');
            if ($this->allowFilterSetting || $this->allowSortSetting) {
                $store = new DynaGridStore([
                    'id' => $this->options['id'],
                    'category' => DynaGridStore::STORE_GRID,
                    'storage' => $this->storage,
                    'userSpecific' => $this->userSpecific
                ]);
                if ($this->allowFilterSetting) {
                    $this->_model->filterId = $this->_filterId;
                    $this->_model->filterList = $store->getDtlList(DynaGridStore::STORE_FILTER);
                }
                if ($this->allowSortSetting) {
                    $dataProvider = $this->gridOptions['dataProvider'];
                    $sort = $dataProvider->getSort();
                    $sort->enableMultiSort = $this->enableMultiSort;
                    $dataProvider->setSort($sort);
                    $this->_model->sortId = $this->_sortId;
                    $this->_model->sortList = $store->getDtlList(DynaGridStore::STORE_SORT);
                }
            }
            $dynagrid = $this->render($this->_module->configView, [
                'model' => $this->_model,
                'toggleButtonGrid' => $this->toggleButtonGrid,
                'id' => $this->_gridModalId,
                'allowThemeSetting' => $this->allowThemeSetting,
                'allowFilterSetting' => $this->allowFilterSetting,
                'allowSortSetting' => $this->allowSortSetting
            ]);
        }
        $model->dynaGridId = $this->options['id'];
        $model->storage = $this->storage;
        $model->userSpecific = $this->userSpecific;
        if ($this->showFilter) {
            $this->setToggleButton('filter');
            $model->category = DynaGridStore::STORE_FILTER;
            $model->key = $this->_filterKey;
            $model->data = array_filter($this->gridOptions['filterModel']->attributes);
            $dynagridFilter = DynaGridDetail::widget([
                'id' => $this->_filterModalId,
                'model' => $model,
                'toggleButton' => $this->toggleButtonFilter,
                'submitMessage' => $this->submitMessage,
                'deleteMessage' => $this->deleteMessage,
                'messageOptions' => $this->messageOptions,
                'deleteConfirmation' => $this->deleteConfirmation,
                'isPjax' => $this->_isPjax,
                'pjaxId' => $this->_pjaxId,
            ]);
        }
        if ($this->showSort) {
            $this->setToggleButton('sort');
            $model->category = DynaGridStore::STORE_SORT;
            $model->key = $this->_sortKey;
            $model->data = $this->gridOptions['dataProvider']->getSort()->getOrders();
            $dynagridSort = DynaGridDetail::widget([
                'id' => $this->_sortModalId,
                'model' => $model,
                'toggleButton' => $this->toggleButtonSort,
                'submitMessage' => $this->submitMessage,
                'deleteMessage' => $this->deleteMessage,
                'messageOptions' => $this->messageOptions,
                'deleteConfirmation' => $this->deleteConfirmation,
                'isPjax' => $this->_isPjax,
                'pjaxId' => $this->_pjaxId,
            ]);
        }
        $tags = ArrayHelper::getValue($this->gridOptions, 'replaceTags', []);
        $tags += [
            '{dynagrid}' => $dynagrid,
            '{dynagridFilter}' => $dynagridFilter,
            '{dynagridSort}' => $dynagridSort
        ];
        $this->gridOptions['replaceTags'] = $tags;
        $this->registerAssets();
    }

    /**
     * Sets the personalization toggle button
     *
     * @param string $cat the category 'grid', 'filter', or 'sort'
     */
    protected function setToggleButton($cat)
    {
        $setting = 'toggleButton' . ucfirst($cat);
        $btnClass = ($this->matchPanelStyle && $cat == 'grid' && !empty($this->gridOptions['panel'])) ?
            'btn btn-' . ArrayHelper::getValue($this->gridOptions['panel'], 'type', 'default') :
            'btn btn-default';
        Html::addCssClass($this->$setting, $btnClass);
        if ($cat == 'grid') {
            $this->toggleButtonGrid = ArrayHelper::merge([
                'label' => '<i class="glyphicon glyphicon-wrench"></i>',
                'title' => Yii::t('kvdynagrid', 'Personalize grid settings'),
                'data-pjax' => false
            ], $this->toggleButtonGrid);
        } else {
            $this->$setting = ArrayHelper::merge([
                'label' => "<i class='glyphicon glyphicon-{$cat}'></i>",
                'title' => Yii::t('kvdynagrid', "Save / edit grid {category}", ['category' => $cat]),
                'data-pjax' => false
            ], $this->$setting);
        }
    }

    /**
     * Registers client assets
     *
     * @return void
     */
    protected function registerAssets()
    {
        $view = $this->getView();
        DynaGridAsset::register($view);
        Html::addCssClass($this->messageOptions, 'dynagrid-submit-message');
        $options = Json::encode([
            'submitMessage' => Html::tag('div', $this->submitMessage, $this->messageOptions),
            'deleteMessage' => Html::tag('div', $this->deleteMessage, $this->messageOptions),
            'deleteConfirmation' => $this->deleteConfirmation,
            'modalId' => $this->_gridModalId
        ]);
        $dynagrid = $this->options['id'];
        $id = "jQuery('[name=\"{$this->_requestSubmit}\"]')";

        // move the modal after the dynagrid container to avoid runtime conflict
        $js = "jQuery('#{$dynagrid}').after(jQuery('#{$this->_gridModalId}'));\n";

        // the core dynagrid form validation
        $js = "{$id}.dynagrid({$options});\n";

        // pjax related reset
        if ($this->_isPjax) {
            $js .= " $('#{$this->_pjaxId}').on('pjax:complete', function () {
                {$id}.dynagrid({$options});
                {$id}.dynagrid('reset');
            });";
        }
        $view->registerJs($js);
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        echo Html::tag('div', GridView::widget($this->gridOptions), $this->options);
        parent::run();
    }

    /**
     * Is column visible
     *
     * @param mixed $column
     *
     * @return mixed
     */
    protected function isVisible($column)
    {
        return (!is_array($column) || empty($column['visible']) || $column['visible'] === true) &&
        (empty($column['hidden']) || $column['hidden'] !== true);
    }
}
