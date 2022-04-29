<?php
/**
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2015 - 2022
 * @package yii2-dynagrid
 * @version 1.5.4
 */

use kartik\base\Config;
use kartik\dynagrid\Dynagrid;
use kartik\dynagrid\models\DynaGridSettings;
use kartik\dynagrid\Module;
use kartik\form\ActiveForm;
use yii\helpers\Html;

/**
 * @var DynaGridSettings $model
 * @var Module $module
 * @var string $moduleId
 * @var string $requestSubmit
 * @var string $iconConfirm
 * @var string $iconRemove
 */
/** @noinspection PhpUnhandledExceptionInspection */
$module = Config::getModule($moduleId, Module::class);
$id = $model->dynaGridId;
$listOptions = ['id' => "settingsId-{$id}", 'class' => 'form-control dynagrid-detail-list'];
$data = $model->getDtlList();
if (count($data) == 0) {
    $listOptions['prompt'] = Yii::t('kvdynagrid', 'Select...', ['category' => $model->category]);
}
$dataConfig = print_r($model->data, true);
$form = ActiveForm::begin();
$params = ['category' => Dynagrid::getCat($model->category)];
$hint = Yii::t(
    'kvdynagrid',
    'Set a name to save the state of your current grid {category}. You can alternatively select a saved {category} from the list below to edit or delete.',
    $params
);
if ($model->storage === DynaGrid::TYPE_DB && $model->dbUpdateNameOnly) {
    $hint .= ' <em>'.Yii::t(
            'kvdynagrid',
            'NOTE: When editing an existing record, only the {category} name will be modified (and not the settings).',
            $params
        ).'</em>';
} else {
    $hint .= ' <em>'.Yii::t(
            'kvdynagrid',
            'NOTE: When editing an existing record, both the {category} name and its settings will be modified.',
            $params
        ).'</em>';
}
/** @noinspection PhpUnhandledExceptionInspection */
echo $form->field($model, 'name', [
    'addon' => [
        'append' => [
            'asButton' => true,
            'content' => Html::button(
                    $iconConfirm,
                    ['title' => Yii::t('kvdynagrid', 'Save'), 'class' => 'dynagrid-detail-save btn btn-primary']
                ).
                Html::button(
                    $iconRemove,
                    ['title' => Yii::t('kvdynagrid', 'Delete'), 'class' => 'dynagrid-detail-delete btn btn-danger']
                ),
        ],
    ],
])->textInput(['class' => 'form-control dynagrid-detail-name', 'id' => "name-{$id}"])->hint($hint);
/** @noinspection PhpUnhandledExceptionInspection */
echo $form->field($model, 'settingsId')->listBox($data, $listOptions);
?>
    <div class="dynagrid-settings-text">
        <?= $model->getDataConfig() ?>
    </div>
<?php
echo Html::activeHiddenInput($model, 'moduleId', ['id' => "moduleId-{$id}"]);
echo Html::activeHiddenInput($model, 'dynaGridId', ['id' => "dynaGridId-{$id}"]);
echo Html::activeHiddenInput($model, 'category', ['id' => "category-{$id}"]);
echo Html::activeHiddenInput($model, 'storage', ['id' => "storage-{$id}"]);
echo Html::activeHiddenInput($model, 'userSpecific', ['id' => "userSpecific-{$id}"]);
echo Html::activeHiddenInput($model, 'dbUpdateNameOnly', ['id' => "dbUpdateNameOnly-{$id}"]);
echo Html::hiddenInput('deleteDetailFlag', 0);
echo Html::hiddenInput('configHashData', $model->getHashSignature());
echo Html::hiddenInput($requestSubmit, 1);
echo Html::tag('span', '', ['id' => $model->key, 'class' => 'hide '.$model->category.'-marker']);
ActiveForm::end();