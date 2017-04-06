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
use yii\helpers\Inflector;
use kartik\base\Config;
use kartik\dynagrid\Module;
use kartik\dynagrid\DynaGrid;
use kartik\dynagrid\DynaGridStore;

/**
 * Model for the dynagrid filter or sort configuration
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.0
 */
class DynaGridSettings extends Model
{
    /**
     * @var string the dynagrid detail identifier
     */
    public $id;
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
     * @var string the dynagrid detail setting name
     */
    public $name;
    /**
     * @var string the dynagrid widget id identifier
     */
    public $dynaGridId;
    /**
     * @var string the identifier the dynagrid detail being edited
     */
    public $editId;
    /**
     * @var string the key for the dynagrid category (FILTER or SORT)
     */
    public $key;
    /**
     * @var array the available list of values data for the specified dynagrid detail category (FILTER or SORT)
     */
    public $data;
    /**
     * @var Module the dynagrid module object instance
     */
    protected $_module;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->_module = Config::initModule(Module::classname());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'category', 'storage', 'userSpecific', 'name', 'dynaGridId', 'editId', 'key', 'data'], 'safe'],
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
                'editId' => Yii::t('kvdynagrid', 'Saved Filters'),
                'dataConfig' => Yii::t('kvdynagrid', 'Filter Configuration'),
            ];
        } elseif ($this->category === DynaGridStore::STORE_SORT) {
            return [
                'name' => Yii::t('kvdynagrid', 'Sort Name'),
                'editId' => Yii::t('kvdynagrid', 'Saved Sorts'),
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
            'name' => $this->name,
            'category' => $this->category,
            'storage' => $this->storage,
            'userSpecific' => $this->userSpecific
        ];
        if (isset($this->id) && !empty($this->id)) {
            $settings['dtlKey'] = $this->id;
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
        $master = new DynaGridStore([
            'id' => $this->dynaGridId,
            'category' => DynaGridStore::STORE_GRID,
            'storage' => $this->storage,
            'userSpecific' => $this->userSpecific
        ]);
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
            foreach ($data as $attribute => $direction) {
                $label = isset($attribute['label']) ? $attribute['label'] : Inflector::camel2words($attribute);
                $icon = $direction === SORT_DESC ? "glyphicon glyphicon-sort-by-alphabet-alt" : "glyphicon glyphicon-sort-by-alphabet";
                $dir = $direction === SORT_DESC ? Yii::t('kvdynagrid', 'descending') : Yii::t('kvdynagrid', 'ascending');
                $out .= "<li>{$label} <span class='{$icon}'></span> <span class='label label-default'>{$dir}</span></li>";
            }
        }
        $out .= "</ul>";
        return $out;
    }
}
