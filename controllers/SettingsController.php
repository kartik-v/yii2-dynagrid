<?php

/**
 * @package   yii2-dynagrid
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2015
 * @version   1.4.5
 */

namespace kartik\dynagrid\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use kartik\dynagrid\models\DynaGridSettings;

class SettingsController extends Controller
{
    /**
     * Fetch setting
     *
     * @return string
     */
    public function actionGetConfig()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new DynaGridSettings();
        $out = ['status' => '', 'content' => ''];
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $out = ['status' => 'success', 'content' => print_r($model->getDataConfig(), true)];
        }
        return $out;
    }
}