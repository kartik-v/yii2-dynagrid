<?php
/**
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2015 - 2017
 * @package yii2-dynagrid
 * @version 1.4.7
 */

use kartik\base\Config;
use kartik\dynagrid\models\DynaGridConfig;
use kartik\dynagrid\Module;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use kartik\sortable\Sortable;
use yii\bootstrap\Modal;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @var yii\web\View   $this
 * @var DynaGridConfig $model
 * @var ActiveForm     $form
 * @var Module         $module
 * @var string         $moduleId
 * @var boolean        $allowPageSetting
 * @var boolean        $allowThemeSetting
 * @var boolean        $allowFilterSetting
 * @var boolean        $allowSortSetting
 * @var array          $toggleButtonGrid
 */

$dynagridId = substr($model->id, 0, -9);
$options1 = ArrayHelper::merge(
    $model->widgetOptions,
    [
        'items' => $model->visibleColumns,
        'connected' => true,
        'options' => ['class' => 'sortable-visible'],
    ]
);
$options2 = ArrayHelper::merge(
    $model->widgetOptions,
    [
        'items' => $model->hiddenColumns,
        'connected' => true,
        'options' => ['class' => 'sortable-hidden'],
    ]
);
$module = Config::getModule($moduleId, Module::className());
$cols = (int)$allowPageSetting + (int)$allowThemeSetting + (int)$allowFilterSetting + (int)$allowSortSetting;
$col = $cols == 0 ? 0 : 12 / $cols;
?>
<?php
Modal::begin(
    [
        'header' => '<h3 class="modal-title"><i class="glyphicon glyphicon-wrench"></i> ' .
            Yii::t('kvdynagrid', 'Personalize Grid Configuration') . '</h3>',
        'footer' => $model->footer,
        'toggleButton' => $toggleButtonGrid,
        'size' => Modal::SIZE_LARGE,
        'options' => ['id' => $id],
    ]
);
?>
<?php $form = ActiveForm::begin(['options' => ['data-pjax' => false]]); ?>
    <div class="dynagrid-config-form">
        <?php if ($col != 0) : ?>
            <div class="row">
                <?php if ($allowPageSetting) : ?>
                    <div class="col-sm-<?= $col ?>">
                        <?= $form->field(
                            $model,
                            'pageSize',
                            ['addon' => ['append' => ['content' => Yii::t('kvdynagrid', 'rows per page')]]]
                        )->textInput(['class' => 'form-control', 'id' => "pageSize-{$dynagridId}"])
                                 ->hint(
                                     Yii::t(
                                         'kvdynagrid',
                                         'Integer between {min} to {max}',
                                         ['min' => $module->minPageSize, 'max' => $module->maxPageSize]
                                     )
                                 ) ?>
                    </div>
                <?php endif; ?>
                <?php if ($allowThemeSetting) : ?>
                    <div class="col-sm-<?= $col ?>">
                        <?= $form->field($model, 'theme')->widget(
                            Select2::classname(),
                            [
                                'data' => $model->themeList,
                                'options' => [
                                    'placeholder' => Yii::t('kvdynagrid', 'Select a theme...'),
                                    'id' => "theme-{$dynagridId}",
                                ],
                                'pluginOptions' => ['allowClear' => true],
                            ]
                        )->hint(Yii::t('kvdynagrid', 'Select theme to style grid')); ?>
                    </div>
                <?php else : ?>
                    <?= Html::activeHiddenInput($model, 'theme', ['id' => "theme-{$dynagridId}"]) ?>
                <?php endif; ?>
                <?php if ($allowFilterSetting) : ?>
                    <div class="col-sm-<?= $col ?>">
                        <?= $form->field($model, 'filterId')->widget(
                            Select2::classname(),
                            [
                                'data' => $model->filterList,
                                'options' => [
                                    'placeholder' => Yii::t('kvdynagrid', 'Select a filter...'),
                                    'id' => "filterId-{$dynagridId}",
                                ],
                                'pluginOptions' => ['allowClear' => true],
                            ]
                        )->hint(Yii::t('kvdynagrid', 'Set default grid filter criteria')) ?>
                    </div>
                <?php endif; ?>
                <?php if ($allowSortSetting) : ?>
                    <div class="col-sm-<?= $col ?>">
                        <?= $form->field($model, 'sortId')->widget(
                            Select2::classname(),
                            [
                                'data' => $model->sortList,
                                'options' => [
                                    'placeholder' => Yii::t('kvdynagrid', 'Select a sort...'),
                                    'id' => "sortId-{$dynagridId}",
                                ],
                                'pluginOptions' => ['allowClear' => true],
                            ]
                        )->hint(Yii::t('kvdynagrid', 'Set default grid sort criteria')) ?>
                    </div>
                <?php endif; ?>
                <?= Html::hiddenInput('deleteFlag', 0) ?>
            </div>
        <?php endif; ?>
        <div class="dynagrid-column-label">
            <?= Yii::t('kvdynagrid', 'Configure Order and Display of Grid Columns') ?>
        </div>
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