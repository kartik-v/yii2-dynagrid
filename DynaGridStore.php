<?php

/**
 * @package   yii2-dynagrid
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2015 - 2017
 * @version   1.4.7
 */

namespace kartik\dynagrid;

use kartik\base\Config;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Object;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\Cookie;

/**
 * Dynagrid storage configuration object
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.2.0
 */
class DynaGridStore extends Object
{
    /**
     * Grid configuration storage
     */
    const STORE_GRID = 'grid';
    /**
     * Grid filter configuration storage
     */
    const STORE_FILTER = 'filter';
    /**
     * Grid sort configuration storage
     */
    const STORE_SORT = 'sort';

    /**
     * @var string the module identifier if this object is part of a module. If not set, the module identifier will
     * be auto derived based on the \yii\base\Module::getInstance method. This can be useful, if you are setting
     * multiple module identifiers for the same module in your Yii configuration file. To specify children or grand
     * children modules you can specify the module identifiers relative to the parent module (e.g. `admin/content`).
     */
    public $moduleId;

    /**
     * @var string the category of data to store
     */
    public $category = self::STORE_GRID;

    /**
     * @var string the dynagrid identifier
     */
    public $id;

    /**
     * @var string the name to identify the filter or sort.
     * This is applicable only if category is one of:
     * [[DynaGridStore::STORE_FILTER]] or [[DynaGridStore::STORE_SORT]]
     */
    public $name = null;

    /**
     * @var string the type of storage for the dynagrid configuration.
     * - [[DynaGrid::TYPE_SESSION]]: Save the config in a session variable for the current session.
     * - [[DynaGrid::TYPE_COOKIE]]: Save the config in a cookie for retrieval. You need to setup the
     *   [[Module::cookieSettings]] property to control the cookie expiry and other settings.
     * - [[DynaGrid::TYPE_DB]]: Save the config to a database. You need to setup the [[Module::dbSettings]]
     *   property to setup the database table and attributes for storage.
     */
    public $storage = DynaGrid::TYPE_SESSION;

    /**
     * @var boolean whether settings are stored specific to each user
     */
    public $userSpecific = true;

    /**
     * @var boolean whether to update only the name, when editing and saving a filter or sort. This is applicable
     * only for [[$storage]] set to [[Dynagrid::TYPE_DB]]. If set to `false`, it will also overwrite the current
     * `filter` or `sort` settings.
     */
    public $dbUpdateNameOnly = false;

    /**
     * @var string the detail key identifier if available
     */
    public $dtlKey;

    /**
     * @var Module the current module
     */
    protected $_module;

    /**
     * @var string generated storage key for dynagrid master record
     */
    protected $_mstKey;

    /**
     * @var string generated storage key for dynagrid detail record (filter & sort)
     */
    protected $_dtlKey;

    /**
     * @var boolean is this a master record
     */
    private $_isMaster;

    /**
     * Parses configuration for session or cookie storage
     *
     * @return array the store configuration
     *
     * @param array json decoded config array
     */
    protected static function parseConfig($config)
    {
        if ($config === false) {
            return [];
        }
        $config = is_string($config) ? Json::decode($config) : $config;
        return !is_array($config) || empty($config) ? [] : $config;
    }

    /**
     * Fetches and return the list of detail values for
     * session or cookie storage
     *
     * @param array $config the storage configuration
     * @param string $cat the detail category
     *
     * @return array
     */
    protected static function getDtlListOther($config, $cat)
    {
        if ($config === false) {
            return [];
        }
        if (!is_array($config) || empty($config[$cat])) {
            return [];
        }
        $data = [];
        foreach ($config[$cat] as $key => $val) {
            $data[$key] = $val['name'];
        }
        return $data;
    }

    /**
     * Initializes the object
     *
     * @throws InvalidConfigException
     */
    public function init()
    {
        $this->_module = Config::getModule($this->moduleId, Module::className());
        $this->_isMaster = ($this->category == self::STORE_GRID) ? true : false;
        if ($this->_module == null || !$this->_module instanceof Module) {
            throw new InvalidConfigException(
                'The "dynagrid" module MUST be setup in your Yii configuration file and assigned to "\kartik\dynagrid\Module" class.'
            );
        }
        if (!isset($this->id)) {
            throw new InvalidConfigException('The dynagrid "id" property must be entered.');
        }
        $this->setKey();
    }

    /**
     * Sets the unique storage key
     */
    public function setKey()
    {
        $this->_mstKey = $this->generateKey(true);
        if (!$this->_isMaster) {
            $this->_dtlKey = empty($this->dtlKey) ? $this->generateKey(false) : $this->dtlKey;
        }
    }

    /**
     * Fetch configuration from store
     *
     * @param string $col the column attribute
     *
     * @return boolean|array the column configuration
     * @throws InvalidConfigException
     */
    public function fetch($col = 'dataAttr')
    {
        $config = false;
        switch ($this->storage) {
            case Dynagrid::TYPE_SESSION:
                $newConfig = static::parseConfig(Yii::$app->session->get($this->_mstKey, false));
                if (!empty($newConfig)) {
                    $config = $this->fetchConfig($newConfig);
                }
                break;
            case Dynagrid::TYPE_COOKIE:
                $newConfig = static::parseConfig(Yii::$app->request->cookies->getValue($this->_mstKey, false));
                if (!empty($newConfig) && $newConfig !== false) {
                    $config = $this->fetchConfig($newConfig);
                }
                //die('<pre>' . var_dump($config, true) . '</pre>');
                break;
            case Dynagrid::TYPE_DB:
                $key = $this->_isMaster ? $this->_mstKey : $this->_dtlKey;
                $config = $this->getDataFromDb($col, $key);
                if ($this->_isMaster && $col !== 'dataAttr') {
                    //die('<pre>' . var_dump($config, true) . '</pre>');
                }
                break;
            default:
                throw new InvalidConfigException('Unknown storage: ' . $this->storage);
        }
        if ($col != 'dataAttr') {
            return $config;
        }
        return ($config === false) ? false : Json::decode($config);
    }

    /**
     * Delete configuration from store. Both master and detail records will be deleted.
     *
     * @throws InvalidConfigException
     */
    public function delete()
    {
        $key = $this->_isMaster ? $this->_mstKey : $this->_dtlKey;
        switch ($this->storage) {
            case Dynagrid::TYPE_SESSION:
                $config = Yii::$app->session->get($this->_mstKey, false);
                if ($config === false || !is_string($config)) {
                    return;
                }
                $config = Json::decode($config);
                if ($this->_isMaster) {
                    unset($config[self::STORE_GRID]);
                } else {
                    unset($config[$this->category][$this->_dtlKey]);
                }
                Yii::$app->session->set($this->_mstKey, Json::encode($config));
                break;
            case Dynagrid::TYPE_COOKIE:
                $settings = $this->_module->cookieSettings;
                $config = Yii::$app->request->cookies->getValue($this->_mstKey, false);
                if ($config === false || !is_string($config)) {
                    return;
                }
                $config = Json::decode($config);
                if ($this->_isMaster) {
                    unset($config[self::STORE_GRID]);
                } else {
                    unset($config[$this->category][$this->_dtlKey]);
                }
                $cookie = new Cookie(['name' => $this->_mstKey, 'value' => $config] + $settings);
                Yii::$app->response->cookies->add($cookie);
                break;
            case Dynagrid::TYPE_DB:
                $connection = 'db';
                if ($this->_isMaster) {
                    extract($this->_module->dbSettings);
                } else {
                    extract($this->_module->dbSettingsDtl);
                }
                /**
                 * @var string $tableName
                 * @var string $idAttr
                 */
                $db = Yii::$app->$connection;
                $db->createCommand()->delete($tableName, [$idAttr => $key])->execute();
                break;
            default:
                throw new InvalidConfigException('Unknown storage: ' . $this->storage);
        }
    }

    /**
     * Delete a key from data configuration for STORE_GRID
     *
     * @param string $key to delete
     * @param mixed  $config configuration data
     *
     * @throws InvalidConfigException
     */
    public function deleteConfig($key, $config)
    {
        if ($this->storage == DynaGrid::TYPE_DB) {
            /**
             * @var string $filterAttr
             * @var string $sortAttr
             * @var string $tableName
             * @var string $idAttr
             */
            $connection = 'db';
            extract($this->_module->dbSettings);
            $attr = $key === self::STORE_FILTER ? $filterAttr : $sortAttr;
            $db = Yii::$app->$connection;
            $db->createCommand()->update($tableName, [$attr => null], [$idAttr => $this->_mstKey])->execute();
            return;
        }
        if ($config === false || !is_array($config)) {
            return;
        }
        unset($config[$key]);
        $this->save($config);
    }

    /**
     * Save configuration to store
     *
     * @param mixed $config configuration data to save
     *
     * @throws InvalidConfigException
     */
    public function save($config)
    {
        $configData = Json::encode($config);
        /**
         * @var string $tableName
         * @var string $idAttr
         * @var string $nameAttr
         * @var string $dataAttr
         * @var string $categoryAttr
         * @var string $dynaGridIdAttr
         * @var string $filterAttr
         * @var string $sortAttr
         */
        switch ($this->storage) {
            case Dynagrid::TYPE_SESSION:
                $oldConfig = Yii::$app->session->get($this->_mstKey, false);
                $newConfig = $this->generateConfig($oldConfig, $configData);
                Yii::$app->session->set($this->_mstKey, $newConfig);
                break;
            case Dynagrid::TYPE_COOKIE:
                $settings = $this->_module->cookieSettings;
                $oldConfig = Yii::$app->request->cookies->getValue($this->_mstKey, false);
                $newConfig = $this->generateConfig($oldConfig, $configData);
                $cookie = new Cookie(['name' => $this->_mstKey, 'value' => $newConfig] + $settings);
                Yii::$app->response->cookies->add($cookie);
                break;
            case Dynagrid::TYPE_DB:
                $key = $this->_isMaster ? $this->_mstKey : $this->_dtlKey;
                $out = $this->getDataFromDb('idAttr', $key);
                $filterData = null;
                $sortData = null;
                $connection = 'db';
                if (!empty($config[self::STORE_FILTER])) {
                    $filterData = $config[self::STORE_FILTER];
                    unset($config[self::STORE_FILTER]);
                }
                if (!empty($config[self::STORE_SORT])) {
                    $sortData = $config[self::STORE_SORT];
                    unset($config[self::STORE_SORT]);
                }
                $configData = Json::encode($config);
                if ($this->_isMaster) {
                    extract($this->_module->dbSettings);
                    $data = [$filterAttr => $filterData, $sortAttr => $sortData, $dataAttr => $configData];
                } else {
                    extract($this->_module->dbSettingsDtl);
                    if ($out != null) {
                        $data = $this->dbUpdateNameOnly ? [$nameAttr => $this->name] :
                            [$nameAttr => $this->name, $dataAttr => $configData];
                    } else {
                        $data = [
                            $nameAttr => $this->name,
                            $dataAttr => $configData,
                            $categoryAttr => $this->category,
                            $dynaGridIdAttr => $this->_mstKey,
                        ];
                    }
                }
                $db = Yii::$app->$connection;
                if ($out != null) {
                    $db->createCommand()->update($tableName, $data, [$idAttr => $key])->execute();
                } else {
                    $data[$idAttr] = $key;
                    $db->createCommand()->insert($tableName, $data)->execute();
                }
                break;
            default:
                throw new InvalidConfigException('Unknown storage: ' . $this->storage);
        }
    }

    /**
     * Fetch and return the list of detail values for a specific master (category = STORE_GRID) instance
     *
     * @param string $cat the detail category
     *
     * @throws InvalidConfigException
     *
     * @return array
     */
    public function getDtlList($cat)
    {
        switch ($this->storage) {
            case Dynagrid::TYPE_SESSION:
                $config = static::parseConfig(Yii::$app->session->get($this->_mstKey, false));
                return static::getDtlListOther($config, $cat);
            case Dynagrid::TYPE_COOKIE:
                $config = static::parseConfig(Yii::$app->request->cookies->getValue($this->_mstKey, false));
                return static::getDtlListOther($config, $cat);
            case Dynagrid::TYPE_DB:
                $s = $this->_module->dbSettingsDtl;
                $connection = ArrayHelper::getValue($s, 'connection', 'db');
                $data = (new Query())
                    ->select([$s['idAttr'], $s['nameAttr']])
                    ->from($s['tableName'])
                    ->where([$s['dynaGridIdAttr'] => $this->_mstKey, $s['categoryAttr'] => $cat])
                    ->all(Yii::$app->$connection);
                return empty($data) ? [] : ArrayHelper::map($data, $s['idAttr'], $s['nameAttr']);
            default:
                throw new InvalidConfigException('Unknown storage: ' . $this->storage);
        }
    }

    /**
     * Generates the storage key
     *
     * @param boolean $master whether to generate key for the master record
     *
     * @return string
     */
    protected function generateKey($master = true)
    {
        $key = $this->id;
        if (!$master) {
            $key .= '_' . $this->category . '_' . hash('crc32', strtolower($this->name));
        }
        if ($this->userSpecific) {
            $key .= '_' . Yii::$app->user->id;
        }
        return $key;
    }

    /**
     * Fetches configuration for session or cookie storage
     *
     * @param array Json::decoded config array
     *
     * @return boolean|array configuration for master or detail
     */
    protected function fetchConfig($config)
    {
        if ($this->_isMaster) {
            return ArrayHelper::getValue($config, self::STORE_GRID, false);
        }
        $cat = ArrayHelper::getValue($config, $this->category, false);
        if ($cat === false || empty($cat)) {
            return false;
        }
        $newConfig = ArrayHelper::getValue($cat, $this->_dtlKey, []);
        $data = empty($newConfig) ? false : ArrayHelper::getValue($newConfig, 'data', false);
        return $data;
    }

    /**
     * Fetch and return the relevant column data from database
     *
     * @param string $col the column type
     * @param string $id the primary key value
     *
     * @return boolean|null|string
     */
    protected function getDataFromDb($col, $id)
    {
        $settings = $this->_isMaster ? $this->_module->dbSettings : $this->_module->dbSettingsDtl;
        $connection = ArrayHelper::getValue($settings, 'connection', 'db');
        $query = (new Query())
            ->select($settings[$col])
            ->from($settings['tableName'])
            ->where([$settings['idAttr'] => $id]);
        return $query->scalar(Yii::$app->$connection);
    }

    /**
     * Gets configuration for session or cookie storage
     *
     * @param string $config the configuration to merge
     * @param string $data the Json::encoded data
     *
     * @return string the Json::encoded configuration
     */
    protected function generateConfig($config, $data)
    {
        $config = static::parseConfig($config);
        if ($this->_isMaster) {
            $config[self::STORE_GRID] = $data;
        } else {
            $config[$this->category][$this->_dtlKey] = ['name' => $this->name, 'data' => $data];
        }
        return Json::encode($config);
    }
}
