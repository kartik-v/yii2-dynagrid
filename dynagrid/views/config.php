<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use kartik\widgets\ActiveForm;
use yii\bootstrap\Modal;
use kartik\sortable\Sortable;
use kartik\widgets\Select2;
use kartik\widgets\TouchSpin;

/**
 * @var yii\web\View $this
 * @var kartik\dynagrid\models\DynaGridConfig $model
 * @var yii\widgets\ActiveForm $form
 */

$options1 = ArrayHelper::merge($model->widgetOptions, [
    'items'=>$model->visibleColumns,
    'connected'=>true,
    'options'=>['class'=>'sortable-visible']
]);
$options2 = ArrayHelper::merge($model->widgetOptions, [
    'items'=> $model->hiddenColumns,
    'connected'=>true,
    'options'=>['class'=>'sortable-hidden']
]);
?>
<?php
    Modal::begin([
        'header' => '<h3 class="modal-title">' . Yii::t('kvdynagrid', 'Personalize Grid Configuration') . '</h3>',
        'toggleButton' => [
            'label' => '<i class="glyphicon glyphicon-wrench"></i> '. Yii::t('kvdynagrid', 'Configure Grid'),
            'class' => 'btn btn-default'
        ],
        'size' => Modal::SIZE_LARGE
    ]);
?>
<?php $form = ActiveForm::begin(); ?>
<div class="dynagrid-config-form">
    <div class = "row">
        <div class="col-sm-4">
            <?= $form->field($model, 'pageSize', ['addon'=>['append'=>['content'=>Yii::t('kvdynagrid', 'rows per page')]]]) ?>
        </div>
        <div class="col-sm-4">
            <?= $form->field($model, 'theme')->dropDownList($model->themeList) ?>
        </div>
        <div class="col-sm-2">
            &nbsp;
        </div>
        <div class="col-sm-2 text-right">
            <?= Html::button('<i class="glyphicon glyphicon-save"></i> ' . Yii::t('kvdynagrid', 'Apply'), ['type'=>'button', 'class' => 'dynagrid-submit btn btn-block btn-primary']) ?>
            <?= Html::resetButton('<i class="glyphicon glyphicon-repeat"></i> ' . Yii::t('kvdynagrid', 'Reset'), ['class' => 'dynagrid-reset btn btn-block btn-danger']) ?>
        </div>
    </div>
    <label class="control-label"><?= Yii::t('kvdynagrid', 'Configure Columns Order and Display')?></label>
    <div class = "row">
        <div class="col-sm-5">
            <?= Sortable::widget($options1);?>
        </div>
        <div class="col-sm-2 text-center">
            <div style="font-size:3em; color: #999;"><i class="glyphicon glyphicon-resize-horizontal"></i></div>
        </div>
        <div class="col-sm-5">
            <?= Sortable::widget($options2);?>
        </div>
    </div>

    <?= Html::hiddenInput($model->id, 1) ?>
    <?= Html::hiddenInput('visibleKeys') ?>
<?php ActiveForm::end(); ?>
</div>
<?php Modal::end();?>