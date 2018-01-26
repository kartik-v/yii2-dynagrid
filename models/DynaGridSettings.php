<?php

/**
 * @package   yii2-dynagrid
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2015 - 2018
 * @version   1.4.8
 */

namespace kartik\dynagrid\models;

use kartik\base\Config;
use kartik\dynagrid\DynaGrid;
use kartik\dynagrid\DynaGridStore;
use kartik\dynagrid\Module;
use Yii;
use yii\base\Model;
use yii\helpers\Inflector;

/**
 * Model for the dynagrid filter or sort configuration
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.0
 */
class DynaGridSettings extends Model
{
    /**
     * @var string the module identifier if this object is part of a module. If not set, the module identifier will
     * be auto derived based on the \yii\base\Module::getInstance method. This can be useful, if you are setting
     * multiple module identifiers for the same module in your Yii configuration file. To specify children or grand
     * children modules you can specify the module identifiers relative to the parent module (e.g. `admin/content`).
     */
    public $moduleId;

    /**
     * @var string the identifier the dynagrid detail
     */
    public $settingsId;

    /**
     * @var string the dynagrid category (FILTER or SORT)
     */
    public $category;

    /**
     * @var string the dynagrid detail storage type
     */
    public $storage;

    /**
     * @var boolean whether the storage is user specific
     */
    public $userSpecific;

    /**
     * @var boolean whether to update only the name, when editing and saving a filter or sort. This is applicable
     * only for [[$storage]] set to [[Dynagrid::TYPE_DB]]. If set to `false`, it will also overwrite the current
     * `filter` or `sort` settings.
     */
    public $dbUpdateNameOnly = false;

    /**
     * @var string the dynagrid detail setting name
     */
    public $name;

    /**
     * @var string the dynagrid widget id identifier
     */
    public $dynaGridId;

    /**
     * @var string the key for the dynagrid category (FILTER or SORT)
     */
    public $key;

    /**
     * @var array the available list of values data for the specified dynagrid detail category (FILTER or SORT)
     */
    public $data;

    /**
     * @var Module the Dynagrid module
     */
    protected $_module;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'moduleId',
                    'category',
                    'storage',
                    'userSpecific',
                    'dbUpdateNameOnly',
                    'name',
                    'dynaGridId',
                    'settingsId',
                    'key',
                    'data',
                ],
                'safe',
            ],
            [['name'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        if ($this->category === DynaGridStore::STORE_FILTER) {
            return [
                'name' => Yii::t('kvdynagrid', 'Filter Name'),
                'settingsId' => Yii::t('kvdynagrid', 'Saved Filters'),
                'dataConfig' => Yii::t('kvdynagrid', 'Filter Configuration'),
            ];
        } elseif ($this->category === DynaGridStore::STORE_SORT) {
            return [
                'name' => Yii::t('kvdynagrid', 'Sort Name'),
                'settingsId' => Yii::t('kvdynagrid', 'Saved Sorts'),
                'dataConfig' => Yii::t('kvdynagrid', 'Sort Configuration'),
            ];
        }
        return [];
    }

    /**
     * Gets the DynaGridStore configuration instance
     *
     * @return DynaGridStore
     */
    public function getStore()
    {
        $settings = [
            'id' => $this->dynaGridId,
            'moduleId' => $this->moduleId,
            'name' => $this->name,
            'category' => $this->category,
            'storage' => $this->storage,
            'userSpecific' => $this->userSpecific,
            'dbUpdateNameOnly' => $this->dbUpdateNameOnly,
        ];
        if (!empty($this->settingsId)) {
            $settings['dtlKey'] = $this->settingsId;
        }
        return new DynaGridStore($settings);
    }

    /**
     * Fetches grid configuration settings from store
     *
     * @return mixed
     */
    public function fetchSettings()
    {
        return $this->getStore()->fetch();
    }

    /**
     * Saves grid configuration settings to store
     */
    public function saveSettings()
    {
        $this->getStore()->save($this->data);
    }

    /**
     * Deletes grid configuration settings from store
     */
    public function deleteSettings()
    {
        $master = new DynaGridStore(
            [
                'id' => $this->dynaGridId,
                'moduleId' => $this->moduleId,
                'category' => DynaGridStore::STORE_GRID,
                'storage' => $this->storage,
                'userSpecific' => $this->userSpecific,
                'dbUpdateNameOnly' => $this->dbUpdateNameOnly,
            ]
        );
        $config = $this->storage == DynaGrid::TYPE_DB ? null : $master->fetch();
        $master->deleteConfig($this->category, $config);
        $this->getStore()->delete();
    }

    /**
     * Gets list of values (for filter or sort category)
     *
     * @return mixed
     */
    public function getDtlList()
    {
        return $this->getStore()->getDtlList($this->category);
    }

    /**
     * Gets data configuration as a HTML list markup
     *
     * @return string
     */
    public function getDataConfig()
    {
        $data = $this->getStore()->fetch();
        if (!is_array($data) || empty($data) &&
            ($this->category !== DynaGridStore::STORE_SORT && $this->category !== DynaGridStore::STORE_SORT)
        ) {
            return '';
        }
        $attrLabel = $this->getAttributeLabel('dataConfig');
        $out = "<label>{$attrLabel}</label>\n<ul>";
        if ($this->category === DynaGridStore::STORE_FILTER) {
            foreach ($data as $attribute => $value) {
                $label = isset($attribute['label']) ? $attribute['label'] : Inflector::camel2words($attribute);
                $value = is_array($value) ? print_r($value, true) : $value;
                $out .= "<li>{$label} = {$value}</li>";
            }
        } else {
            foreach ($data as $attribute => $dir) {
                $label = isset($attribute['label']) ? $attribute['label'] : Inflector::camel2words($attribute);
                $icon = $dir === SORT_DESC ? 'glyphicon glyphicon-sort-by-alphabet-alt' : 'glyphicon glyphicon-sort-by-alphabet';
                $d = $dir === SORT_DESC ? Yii::t('kvdynagrid', 'descending') : Yii::t('kvdynagrid', 'ascending');
                $out .= "<li>{$label} <span class='{$icon}'></span> <span class='label label-default'>{$d}</span></li>";
            }
        }
        $out .= '</ul>';
        return $out;
    }

    /**
     * Gets a hashed signature for specific attribute data passed between server and client
     *
     * @param array $attribs the list of attributes whose data is to be hashed
     *
     * @return string the hashed signature output
     * @throws \yii\base\InvalidConfigException
     */
    public function getHashSignature($attribs = [])
    {
        $out = '';
        if (empty($attribs)) {
            $attribs = ['moduleId', 'dynaGridId', 'category', 'storage', 'userSpecific', 'dbUpdateOnly'];
        }
        foreach ($attribs as $key => $attr) {
            if (isset($this->$attr)) {
                $out .= $attr === 'userSpecific' || $attr === 'dbUpdateOnly' ? !!$this->$attr : $this->$attr;
            }
        }
        $module = $this->getModule();
        return Yii::$app->security->hashData($out, $module->configEncryptSalt);
    }

    /**
     * Validate signature of the hashed data submitted via hidden fields from the filter/sort update form
     *
     * @param string $hashData the hashed data to match
     * @param array  $attribs the list of attributes against which data hashed is to be validated
     *
     * @return boolean|string returns true if valid else the validation error message
     */
    public function validateSignature($hashData = '', $attribs = [])
    {
        $origHash = $this->getHashSignature($attribs);
        $params = YII_DEBUG ? '<pre>OLD HASH:<br>' . $origHash . '<br>NEW HASH:<br>' . $hashData . '</pre>' : '';
        $module = $this->getModule();
        return (Yii::$app->security->validateData($hashData, $module->configEncryptSalt) && $hashData === $origHash) ?
            true :
            Yii::t(
                'kvdynagrid',
                'Operation disallowed! Invalid request signature detected for dynagrid settings. {params}',
                ['params' => $params]
            );
    }

    /**
     * Returns the Dynagrid module instance
     *
     * @return Module
     */
    protected function getModule()
    {
        if (!isset($this->_module)) {
            $this->_module = Config::getModule($this->moduleId, Module::className());
        }
        return $this->_module;
    }
}
