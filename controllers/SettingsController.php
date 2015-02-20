<?php

/**
 * @package   yii2-dynagrid
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2015
 * @version   1.4.2
 */

namespace kartik\dynagrid\controllers;

use Yii;
use yii\helpers\Json;
use kartik\grid\GridView;
use yii\web\BadRequestHttpException;
use kartik\dynagrid\models\DynaGridSettings;
use kartik\dynagrid\DynaGridStore;

class SettingsController extends \yii\web\Controller
{
    /**
     * Fetch setting
     *
     * @return string
     */
    public function actionGetConfig()
    {
        $model = new DynaGridSettings();
        $out = ['status' => '', 'content' => ''];
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $out = ['status' => 'success', 'content' => print_r($model->getDataConfig(), true)];
        }
        echo Json::encode($out);
    }
}