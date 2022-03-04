<?php
/**
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2015 - 2022
 * @package yii2-dynagrid
 * @version 1.5.3
 */

use kartik\base\Config;
use kartik\base\Lib;
use kartik\base\Widget;
use kartik\dynagrid\models\DynaGridConfig;
use kartik\dynagrid\Module;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use kartik\sortable\Sortable;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @var string $id
 * @var yii\web\View $this
 * @var DynaGridConfig $model
 * @var ActiveForm $form
 * @var Module $module
 * @var string $moduleId
 * @var boolean $allowPageSetting
 * @var boolean $allowThemeSetting
 * @var boolean $allowFilterSetting
 * @var boolean $allowSortSetting
 * @var string $iconPersonalize
 * @var string $iconSortableSeparator
 * @var array $toggleButtonGrid
 * @var boolean $notBs3
 * @var Widget $modalClass
 */

$dynagridId = Lib::substr($model->id, 0, -9);
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
/** @noinspection PhpUnhandledExceptionInspection */
$module = Config::getModule($moduleId, Module::class);
$cols = (int)$allowPageSetting + (int)$allowThemeSetting + (int)$allowFilterSetting + (int)$allowSortSetting;
$col = $cols == 0 ? 0 : 12 / $cols;
?>
<?php
$hdr = $iconPersonalize.' '.Yii::t('kvdynagrid', 'Personalize Grid Configuration');
$modalOpts = [
    'footer' => $model->footer,
    'toggleButton' => $toggleButtonGrid,
    'size' => $modalClass::SIZE_LARGE,
    'options' => ['id' => $id],
];
if ($notBs3) {
    $modalOpts['title'] = $hdr;
} else {
    $modalOpts['header'] = '<h3 class="modal-title">'.$hdr.'</h3>';
}
$modalClass::begin($modalOpts);
?>

    <div class="dynagrid-config-form">

        <?php
        $form = ActiveForm::begin(['options' => ['data-pjax' => false]]); ?>

        <?php
        if ($col > 0) : ?>
            <div class="row">
                <?php
                if ($allowPageSetting) : ?>
                    <div class="col-sm-<?= $col ?>">
                        <?= $form->field($model, 'pageSize', [
                            'addon' => ['append' => ['content' => Yii::t('kvdynagrid', 'rows per page')]],
                        ])->textInput(['class' => 'form-control', 'id' => "pageSize-{$dynagridId}"])->hint(
                            Yii::t(
                                'kvdynagrid',
                                'Integer between {min} to {max}',
                                ['min' => $module->minPageSize, 'max' => $module->maxPageSize]
                            )
                        );
                        ?>
                    </div>
                <?php
                endif; ?>
                <?php
                if ($allowThemeSetting) : ?>
                    <div class="col-sm-<?= $col ?>">
                        <?= $form->field($model, 'theme')->widget(
                            Select2::class,
                            [
                                'data' => $model->themeList,
                                'options' => [
                                    'placeholder' => Yii::t('kvdynagrid', 'Select a theme...'),
                                    'id' => "theme-{$dynagridId}",
                                ],
                                'pluginOptions' => ['allowClear' => true],
                            ]
                        )->hint(Yii::t('kvdynagrid', 'Select theme to style grid'));
                        ?>
                    </div>
                <?php
                endif; ?>
                <?php
                if ($allowFilterSetting) : ?>
                    <div class="col-sm-<?= $col ?>">
                        <?= $form->field($model, 'filterId')->widget(
                            Select2::class,
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
                <?php
                endif; ?>
                <?php
                if ($allowSortSetting) : ?>
                    <div class="col-sm-<?= $col ?>">
                        <?= $form->field($model, 'sortId')->widget(
                            Select2::class,
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
                <?php
                endif; ?>
            </div>
        <?php
        endif; ?>
        <div class="dynagrid-column-label">
            <?= Yii::t('kvdynagrid', 'Configure Order and Display of Grid Columns') ?>
        </div>
        <div class="row">
            <div class="col-sm-5">
                <?= Sortable::widget($options1); ?>
            </div>
            <div class="col-sm-2 text-center">
                <div class="dynagrid-sortable-separator"><?= $iconSortableSeparator ?></div>
            </div>
            <div class="col-sm-5">
                <?= Sortable::widget($options2); ?>
            </div>
        </div>
        <?= $allowThemeSetting ? '' : Html::activeHiddenInput($model, 'theme', ['id' => "theme-{$dynagridId}"]) ?>
        <?= Html::hiddenInput('deleteFlag', 0) ?>
        <?= Html::hiddenInput($model->id, 1) ?>
        <?= Html::hiddenInput('visibleKeys') ?>

        <?php
        ActiveForm::end(); ?>

    </div> <!-- .dynagrid-config-form -->

<?php
$modalClass::end(); ?>