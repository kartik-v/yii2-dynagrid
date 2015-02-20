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
    public $id;
    public $category;
    public $storage;
    public $userSpecific;
    public $name;
    public $dynaGridId;
    public $editId;
    public $key;
    public $data;
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
        return $this->store->fetch();
    }

    /**
     * Saves grid configuration settings to store
     */
    public function saveSettings()
    {
        $this->store->save($this->data);
    }

    /**
     * Deletes grid configuration settings from store
     *
     * @return void
     * @throws \yii\base\InvalidConfigException
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
        $this->store->delete();
    }

    public function getDtlList()
    {
        return $this->store->getDtlList($this->category);
    }

    /**
     * Gets data configuration
     *
     * @return string
     */
    public function getDataConfig()
    {
        $data = $this->store->fetch();
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
                $out .= "<li>{$label} = {$value}</li>";
            }
        } else {
            foreach ($data as $attribute => $direction) {
                $label = isset($attribute['label']) ? $attribute['label'] : Inflector::camel2words($attribute);
                $icon = $direction === SORT_DESC ? "glyphicon glyphicon-sort-by-alphabet-alt" : "glyphicon glyphicon-sort-by-alphabet";
                $dir = $direction === SORT_DESC ? Yii::t('kvdynagrid', 'descending') : Yii::t('kvdynagrid',
                    'ascending');
                $out .= "<li>{$label} <span class='{$icon}'></span> <span class='label label-default'>{$dir}</span></li>";
            }
        }
        $out .= "</ul>";
        return $out;
    }
}
