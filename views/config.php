<?php
/**
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2015
 * @package yii2-dynagrid
 * @version 1.4.2
 */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use kartik\sortable\Sortable;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use kartik\dynagrid\Module;
use yii\bootstrap\Modal;

/**
 * @var yii\web\View                          $this
 * @var kartik\dynagrid\models\DynaGridConfig $model
 * @var yii\widgets\ActiveForm                $form
 */

$options1 = ArrayHelper::merge($model->widgetOptions, [
    'items' => $model->visibleColumns,
    'connected' => true,
    'options' => ['class' => 'sortable-visible']
]);
$options2 = ArrayHelper::merge($model->widgetOptions, [
    'items' => $model->hiddenColumns,
    'connected' => true,
    'options' => ['class' => 'sortable-hidden']
]);
$module = Module::fetchModule();
$flag = $allowFilterSetting && $allowSortSetting;
if (!$flag) {
    $col = ($allowFilterSetting || $allowSortSetting) ? 4 : 6;
    if (!$allowThemeSetting) {
        $col = ($allowFilterSetting || $allowSortSetting) ? 6 : 12;
    }
} else {
    $col = 3;
    if (!$allowThemeSetting) {
        $col = 4;
    }
}
?>
<?php
Modal::begin([
    'header' => '<h3 class="modal-title"><i class="glyphicon glyphicon-wrench"></i> ' . Yii::t('kvdynagrid',
            'Personalize Grid Configuration') . '</h3>',
    'footer' => $model->footer,
    'toggleButton' => $toggleButtonGrid,
    'size' => Modal::SIZE_LARGE,
    'options' => ['id' => $id]
]);
?>
<?php $form = ActiveForm::begin(['options' => ['data-pjax' => false]]); ?>
    <div class="dynagrid-config-form">
        <div class="row">
            <div class="col-sm-<?= $col ?>">
                <?= $form->field($model, 'pageSize',
                    ['addon' => ['append' => ['content' => Yii::t('kvdynagrid', 'rows per page')]]])
                    ->textInput(['class' => 'form-control'])
                    ->hint(Yii::t('kvdynagrid', 'Integer between {min} to {max}', [
                        'min' => $module->minPageSize,
                        'max' => $module->maxPageSize
                    ])) ?>
            </div>
            <?php if ($allowThemeSetting): ?>
            <div class="col-sm-<?= $col ?>">
                <?= $form->field($model, 'theme')->widget(Select2::classname(), [
                    'data' => $model->themeList,
                    'options' => ['placeholder' => Yii::t('kvdynagrid', 'Select a theme...')],
                    'pluginOptions' => ['allowClear' => true]
                ])->hint(Yii::t('kvdynagrid', 'Select theme to style grid')); ?>
            </div>
            <?php else:?>
                <?= Html::activeHiddenInput($model, 'theme') ?>
            <?php endif; ?>
            <?php if ($allowFilterSetting): ?>
                <div class="col-sm-<?= $col ?>">
                    <?= $form->field($model, 'filterId')->widget(Select2::classname(), [
                        'data' => $model->filterList,
                        'options' => ['placeholder' => Yii::t('kvdynagrid', 'Select a filter...')],
                        'pluginOptions' => ['allowClear' => true]
                    ])->hint(Yii::t('kvdynagrid', 'Set default grid filter criteria')) ?>
                </div>
            <?php endif; ?>
            <?php if ($allowSortSetting): ?>
                <div class="col-sm-<?= $col ?>">
                    <?= $form->field($model, 'sortId')->widget(Select2::classname(), [
                        'data' => $model->sortList,
                        'options' => ['placeholder' => Yii::t('kvdynagrid', 'Select a sort...')],
                        'pluginOptions' => ['allowClear' => true]
                    ])->hint(Yii::t('kvdynagrid', 'Set default grid sort criteria')) ?>
                </div>
            <?php endif; ?>
            <?= Html::hiddenInput('deleteFlag', 0) ?>
        </div>
        <div class="dynagrid-column-label"><?= Yii::t('kvdynagrid',
                'Configure Order and Display of Grid Columns') ?></div>
        <div class="row">
            <div class="col-sm-5">
                <?= Sortable::widget($options1); ?>
            </div>
            <div class="col-sm-2 text-center">
                <div class="dynagrid-sortable-separator"><i class="glyphicon glyphicon-resize-horizontal"></i></div>
            </div>
            <div class="col-sm-5">
                <?= Sortable::widget($options2); ?>
            </div>
        </div>

        <?= Html::hiddenInput($model->id, 1) ?>
        <?= Html::hiddenInput('visibleKeys') ?>
        <?php ActiveForm::end(); ?>
    </div>
<?php Modal::end(); ?>