<?php
/**
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2015 - 2017
 * @package yii2-dynagrid
 * @version 1.4.6
 */

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\dynagrid\Dynagrid;
use kartik\dynagrid\Module;
use kartik\dynagrid\models\DynaGridSettings;

/**
 * @var DynaGridSettings $model
 * @var string           $requestSubmit
 */
$module = Module::fetchModule();
$listOptions = ['class' => 'form-control dynagrid-detail-list'];
$data = $model->getDtlList();
if (count($data) == 0) {
    $listOptions['prompt'] = Yii::t('kvdynagrid', 'Select...', ['category' => $model->category]);
}
$dataConfig = print_r($model->data, true);
$form = ActiveForm::begin();
$params = ['category' => Dynagrid::getCat($model->category)];
$hint = Yii::t(
    'kvdynagrid',
    "Set a name to save the state of your current grid {category}. You can alternatively select a saved {category} from the list below to edit or delete.",
    $params
);
if ($model->storage === DynaGrid::TYPE_DB && $model->dbUpdateNameOnly) {
    $hint .= ' <em>' . Yii::t(
            'kvdynagrid',
            'NOTE: When editing an existing record, only the {category} name will be modified (and not the settings).',
            $params
        ) . '</em>';
} else {
    $hint .= ' <em>' . Yii::t(
            'kvdynagrid',
            'NOTE: When editing an existing record, both the {category} name and its settings will be modified.',
            $params
        ) . '</em>';
}
echo $form->field($model, 'name', [
    'addon' => [
        'append' => [
            'asButton' => true,
            'content' => Html::button(
                '<span class="glyphicon glyphicon-ok"></span>',
                ['title' => Yii::t('kvdynagrid', 'Save'), 'class' => "dynagrid-detail-save btn btn-primary"]
            ) .
            Html::button(
                '<span class="glyphicon glyphicon-remove"></span>',
                ['title' => Yii::t('kvdynagrid', 'Delete'), 'class' => "dynagrid-detail-delete btn btn-danger"]
            )
        ]
    ]
])->textInput(['class' => 'form-control dynagrid-detail-name'])->hint($hint);
echo $form->field($model, 'settingsId')->listBox($data, $listOptions);
?>
    <div class="dynagrid-settings-text">
        <?= $model->getDataConfig() ?>
    </div>
<?php
echo Html::activeHiddenInput($model, 'dynaGridId');
echo Html::activeHiddenInput($model, 'category');
echo Html::activeHiddenInput($model, 'storage');
echo Html::activeHiddenInput($model, 'userSpecific');
echo Html::activeHiddenInput($model, 'dbUpdateNameOnly');
echo Html::hiddenInput('deleteDetailFlag', 0);
echo Html::hiddenInput('configHashData', $model->getHashSignature());
echo Html::hiddenInput($requestSubmit, 1);
echo Html::tag('span', '', ['id' => $model->key, 'class'=>'hide ' . $model->category . '-marker']);
ActiveForm::end();