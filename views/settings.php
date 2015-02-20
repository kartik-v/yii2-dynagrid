<?php
/**
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2015
 * @package yii2-dynagrid
 * @version 1.4.2
 */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use kartik\form\ActiveForm;
use yii\bootstrap\Modal;
use kartik\sortable\Sortable;
use kartik\select2\Select2;
use kartik\dynagrid\Module;

$module = Module::fetchModule();
$listOptions = ['class' => 'form-control dynagrid-detail-list'];
$data = $model->getDtlList();
if (count($data) == 0) {
    $listOptions['prompt'] = Yii::t('kvdynagrid', 'Select...', ['category' => $model->category]);
}
$dataConfig = print_r($model->data, true);
?>
<?php $form = ActiveForm::begin(); ?>
<?= $form->field($model, 'name', [
    'addon' => [
        'append' => [
            'asButton' => true,
            'content' =>
                Html::button('<span class="glyphicon glyphicon-ok"></span>',
                    ['title' => Yii::t('kvdynagrid', 'Save'), 'class' => "dynagrid-detail-save btn btn-primary"]) .
                Html::button('<span class="glyphicon glyphicon-remove"></span>',
                    ['title' => Yii::t('kvdynagrid', 'Delete'), 'class' => "dynagrid-detail-delete btn btn-danger"])
        ]
    ]
])->textInput(['class' => 'form-control dynagrid-detail-name'])->hint(Yii::t('kvdynagrid',
    "Set a name to save the state of your current grid {category}. You can alternatively select a saved {category} from the list below to edit or delete.",
    ['category' => $model->category])); ?>
<?= $form->field($model, 'editId')->listBox($data, $listOptions); ?>
<?php //$form->field($model, 'dataConfig')->textArea(['class'=>'form-control dynagrid-settings-text', 'readOnly'=>true]) ?>
    <div class="dynagrid-settings-text"><?= $model->getDataConfig() ?></div>
<?= Html::activeHiddenInput($model, 'id', ['id' => $model->key]) ?>
<?= Html::activeHiddenInput($model, 'category'); ?>
<?= Html::activeHiddenInput($model, 'storage'); ?>
<?= Html::activeHiddenInput($model, 'userSpecific'); ?>
<?= Html::activeHiddenInput($model, 'dynaGridId'); ?>
<?= Html::hiddenInput('deleteDetailFlag', 0); ?>
<?= Html::hiddenInput($requestSubmit, 1); ?>
<?php ActiveForm::end(); ?>