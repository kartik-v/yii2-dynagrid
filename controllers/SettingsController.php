<?php

/**
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2014
 * @package yii2-grid
 * @version 1.3.0
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
     */
    public function actionGetConfig()
    {
        $model = new DynaGridSettings();
        $out = ['status'=>'', 'content'=>''];
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $out = ['status'=>'success', 'content'=>print_r($model->getDataConfig(),true)];
        }
        echo Json::encode($out);
    }
}