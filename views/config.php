<?php
/**
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2014
 * @package yii2-dynagrid
 * @version 1.1.0
 */

use yii\helpers\Html;
use yii\helpers\Url;
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
$module = Yii::$app->getModule('dynagrid');
?>
<?php
    Modal::begin([
        'header' => '<h3 class="modal-title"><i class="glyphicon glyphicon-wrench"></i> ' . Yii::t('kvdynagrid', 'Personalize Grid Configuration') . '</h3>',
        'toggleButton' => $toggleButton,
        'size' => Modal::SIZE_LARGE,
        'options' => ['id'=>$id]
    ]);
?>
<?php $form = ActiveForm::begin(['options'=>['data-pjax'=>false]]); ?>
<div class="dynagrid-config-form">
    <div class = "row">
        <div class="col-sm-5">
            <?= $form->field($model, 'pageSize', ['addon'=>['append'=>['content'=>Yii::t('kvdynagrid', 'rows per page')]]])
                ->hint(Yii::t('kvdynagrid', 'Enter any integer between {min} to {max}.', [
                    'min' => $module->minPageSize,
                    'max' => $module->maxPageSize
                ])) ?>
        </div>
        <div class="col-sm-5">
            <?= $form->field($model, 'theme')->dropDownList($model->themeList)
                ->hint(Yii::t('kvdynagrid', 'Select a theme to style your grid.'))?>
        </div>
        <div class="col-sm-2">
            <?= Html::button('<i class="glyphicon glyphicon-save"></i> ' . Yii::t('kvdynagrid', 'Apply'), ['type'=>'button', 'class' => 'dynagrid-submit btn btn-block btn-primary', 'data-pjax'=>false]) ?>
            <?= Html::resetButton('<i class="glyphicon glyphicon-repeat"></i> ' . Yii::t('kvdynagrid', 'Reset'), ['class' => 'dynagrid-reset btn btn-block btn-danger', 'data-pjax'=>false]) ?>
        </div>
    </div>
    <div class="dynagrid-column-label"><?= Yii::t('kvdynagrid', 'Configure Order and Display of Grid Columns')?></div>
    <div class = "row">
        <div class="col-sm-5">
            <?= Sortable::widget($options1);?>
        </div>
        <div class="col-sm-2 text-center">
            <div class="dynagrid-sortable-separator"><i class="glyphicon glyphicon-resize-horizontal"></i></div>
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