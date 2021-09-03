<?php

/**
 * @package   yii2-dynagrid
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2015 - 2021
 * @version   1.5.2
 */

namespace kartik\dynagrid;

use kartik\base\Config;
use kartik\base\Widget;
use kartik\dynagrid\models\DynaGridSettings;
use Yii;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
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
                DynaGridSettings::class . "'"
            );
        }
        parent::init();
        $this->_module = Config::getModule($this->moduleId, Module::class);
        $this->_requestSubmit = $this->options['id'] . '-dynagrid-detail';
        $request = Yii::$app->request;
        $this->_isSubmit = !empty($_POST[$this->_requestSubmit]) &&
            $this->model->load($request->post()) &&
            $this->model->validate();
        $this->registerAssets();
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function run()
    {
        $this->saveDetail();
        $notBs3 = !$this->isBs(3);
        $params = ['title' => static::getCat($this->model->category, true)];
        $catIcon = 'icon' . ucfirst($this->model->category);
        $icon = $this->$catIcon;
        $title = Yii::t('kvdynagrid', 'Save / Edit Grid {title}', $params) ;
        /**
         * @var Widget $modalClass
         */
        $modalClass = $this->getBSClass('Modal');
        $hdr = $icon . $title;
        $modalOpts =  ['toggleButton' => $this->toggleButton, 'options' => ['id' => $this->id]];
        if ($notBs3) {
            $modalOpts['title'] = $hdr;
        } else {
            $modalOpts['header'] = '<h3 class="modal-title">' . $hdr . '</h3>';
        }
        $modalClass::begin($modalOpts);
        echo $this->render(
            $this->_module->settingsView,
            [
                'model' => $this->model,
                'moduleId' => $this->moduleId,
                'requestSubmit' => $this->_requestSubmit,
                'notBs3' => !$this->isBs(3),
                'iconConfirm' => $this->iconConfirm,
                'iconRemove' => $this->iconRemove
            ]
        );
        $modalClass::end();
        parent::run();
    }

    /**
     * Check and validate any detail record to save or delete
     *
     * @throws InvalidCallException
     * @throws InvalidConfigException
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
