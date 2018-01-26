<?php

/**
 * @package   yii2-dynagrid
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2015 - 2018
 * @version   1.4.8
 */

namespace kartik\dynagrid;

use kartik\base\Config;
use kartik\base\Widget;
use kartik\dynagrid\models\DynaGridSettings;
use Yii;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use yii\bootstrap\Modal;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * DynaGrid detail widget to save/store grid sort OR grid filter (search criteria) configuration.
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.2.0
 */
class DynaGridDetail extends Widget
{
    use DynaGridTrait;

    /**
     * @var string the modal container identifier
     */
    public $id;

    /**
     * @var string the key to uniquely identify the element
     */
    public $key;

    /**
     * @var DynaGridSettings the settings model
     */
    public $model;

    /**
     * @var array the HTML attributes for the toggle button that will open the editable form for the filter or sort.
     */
    public $toggleButton = [];

    /**
     * @var string the message to display after applying and submitting the configuration and until refreshed grid is
     * reloaded
     */
    public $submitMessage;

    /**
     * @var string the message to display after deleting the configuration and until refreshed grid is reloaded
     */
    public $deleteMessage;

    /**
     * @var array HTML attributes for the submission message container
     */
    public $messageOptions;

    /**
     * @var string the confirmation warning message before deleting the record.
     */
    public $deleteConfirmation;

    /**
     * @var array configuration settings for the Krajee dialog widget that will be used to render alerts and
     * confirmation dialog prompts
     * @see http://demos.krajee.com/dialog
     */
    public $krajeeDialogSettings = [];

    /**
     * @var boolean flag to check if the pjax is enabled for the grid
     */
    public $isPjax;

    /**
     * @var string the identifier for pjax container
     */
    public $pjaxId;

    /**
     * @var string request param name which will show the grid configuration submitted
     */
    protected $_requestSubmit;

    /**
     * @var boolean flag to check if the grid configuration form has been submitted
     */
    protected $_isSubmit = false;

    /**
     * @var Module the current module
     */
    protected $_module;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (empty($this->model) || !$this->model instanceof DynaGridSettings) {
            throw new InvalidConfigException(
                "You must enter a valid 'model' for DynaGridDetail extending from '" .
                DynaGridSettings::classname() . "'"
            );
        }
        parent::init();
        $this->_module = Config::getModule($this->moduleId, Module::classname());
        $this->_requestSubmit = $this->options['id'] . '-dynagrid-detail';
        $request = Yii::$app->request;
        $this->_isSubmit = !empty($_POST[$this->_requestSubmit]) &&
            $this->model->load($request->post()) &&
            $this->model->validate();
        $this->registerAssets();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->saveDetail();
        $params = ['title' => static::getCat($this->model->category, true)];
        $title = Yii::t('kvdynagrid', 'Save / Edit Grid {title}', $params);
        $icon = "<i class='glyphicon glyphicon-{$this->model->category}'></i> ";
        Modal::begin(
            [
                'header' => '<h3 class="modal-title">' . $icon . $title . '</h3>',
                'toggleButton' => $this->toggleButton,
                'options' => ['id' => $this->id],
            ]
        );
        echo $this->render(
            $this->_module->settingsView,
            ['model' => $this->model, 'moduleId' => $this->moduleId, 'requestSubmit' => $this->_requestSubmit]
        );
        Modal::end();
        parent::run();
    }

    /**
     * Check and validate any detail record to save or delete
     *
     * @throws InvalidCallException
     */
    protected function saveDetail()
    {
        if (!$this->_isSubmit) {
            return;
        }
        $out = $this->model->validateSignature(Yii::$app->request->post('configHashData', ''));
        if ($out !== true) {
            throw new InvalidCallException($out);
        }
        $delete = ArrayHelper::getValue($_POST, 'deleteDetailFlag', 0) == 1;
        if ($delete) {
            $this->model->deleteSettings();
        } else {
            $this->model->saveSettings();
        }
        Yii::$app->controller->refresh();
        if ($delete) {
            $this->model->deleteSettings();
        }
    }

    /**
     * Register client assets
     */
    protected function registerAssets()
    {
        $view = $this->getView();
        DynaGridDetailAsset::register($view);
        Html::addCssClass($this->messageOptions, 'dynagrid-submit-message');
        if (isset($this->_module->settingsConfigAction)) {
            $action = $this->_module->settingsConfigAction;
        } else {
            $action = '/' . $this->moduleId . '/settings/get-config';
        }
        $action = (array)$action;
        $options = Json::encode(
            [
                'submitMessage' => Html::tag('div', $this->submitMessage, $this->messageOptions),
                'deleteMessage' => Html::tag('div', $this->deleteMessage, $this->messageOptions),
                'deleteConfirmation' => $this->deleteConfirmation,
                'configUrl' => Url::to($action),
                'modalId' => $this->id,
                'dynaGridId' => $this->model->dynaGridId,
                'dialogLib' => ArrayHelper::getValue($this->krajeeDialogSettings, 'libName', 'krajeeDialog'),
            ]
        );
        $id = "#{$this->model->key}";
        $dynagrid = $this->model->dynaGridId;
        $js = "jQuery('{$id}').dynagridDetail({$options});\njQuery('#{$dynagrid}').after(jQuery('{$id}'));";
        // pjax related reset
        if ($this->isPjax) {
            $js .= "jQuery('#{$this->pjaxId}').on('pjax:end', function() {\n
                jQuery('{$id}').dynagridDetail({$options});\n
            });";
        }
        $view->registerJs($js);
    }
}
