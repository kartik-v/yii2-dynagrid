<?php

/**
 * @package   yii2-dynagrid
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2015
 * @version   1.4.2
 */

namespace kartik\dynagrid;

use Yii;
use yii\helpers\Json;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\base\Model;
use yii\base\InvalidConfigException;
use yii\bootstrap\Modal;
use kartik\base\Config;

/**
 * DynaGrid detail widget to save/store grid sort OR
 * grid filter (search criteria) configuration.
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.2.0
 */
class DynaGridDetail extends \kartik\base\Widget
{
    /**
     * @var string the modal container identifier
     */
    public $id;

    /**
     * @var string the key to uniquely identify the element
     */
    public $key;

    /**
     * @var Model the settings model
     */
    public $model;

    /**
     * @var array the HTML attributes for the toggle button
     * that will open the editable form for the filter or sort.
     */
    public $toggleButton = [];

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
     * @var string the confirmation warning message before deleting the record.
     */
    public $deleteConfirmation;

    /**
     * @var bool flag to check if the pjax is enabled for the grid
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
     * @var bool flag to check if the grid configuration form has been submitted
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
        if (empty($this->model) || !$this->model instanceof Model) {
            throw new InvalidConfigException("You must enter a valid 'model' for DynaGridDetail.");
        }
        parent::init();
        $this->_module = Config::initModule(Module::classname());
        $this->_requestSubmit = $this->options['id'] . '-dynagrid-detail';
        $this->_isSubmit = !empty($_POST[$this->_requestSubmit]) && $this->model->load(Yii::$app->request->post()) && $this->model->validate();
        $this->registerAssets();
    }

    /**
     * Register client assets
     */
    protected function registerAssets()
    {
        $view = $this->getView();
        DynaGridDetailAsset::register($view);
        Html::addCssClass($this->messageOptions, 'dynagrid-submit-message');
        $options = Json::encode([
            'submitMessage' => Html::tag('div', $this->submitMessage, $this->messageOptions),
            'deleteMessage' => Html::tag('div', $this->deleteMessage, $this->messageOptions),
            'deleteConfirmation' => $this->deleteConfirmation,
            'configUrl' => Url::to([$this->_module->settingsConfigAction]),
            'modalId' => $this->id
        ]);
        $id = "#{$this->model->key}";
        $dynagrid = $this->model->dynaGridId;
        $js = <<< JS
jQuery('{$id}').dynagridDetail({$options});
jQuery('{$dynagrid}').after(jQuery('{$id}'));
JS;

        // pjax related reset
        if ($this->isPjax) {
            $js .= "jQuery('#{$this->pjaxId}').on('pjax:complete', function() {\n
                jQuery('{$id}').dynagridDetail({$options});\n
            });";
        }

        $view->registerJs($js);

    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->saveDetail();
        $title = Yii::t('kvdynagrid', "Save / Edit Grid {title}", ['title' => ucfirst($this->model->category)]);
        $icon = "<i class='glyphicon glyphicon-{$this->model->category}'></i> ";
        Modal::begin([
            'header' => '<h3 class="modal-title">' . $icon . $title . '</h3>',
            'toggleButton' => $this->toggleButton,
            'options' => ['id' => $this->id]
        ]);
        echo $this->render($this->_module->settingsView, [
            'model' => $this->model,
            'requestSubmit' => $this->_requestSubmit
        ]);
        Modal::end();
        parent::run();
    }

    /**
     * Check and validate any detail record
     * to save or delete
     *
     * @return void
     */
    protected function saveDetail()
    {
        if (!$this->_isSubmit) {
            return;
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
}
