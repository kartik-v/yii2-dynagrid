<?php

/**
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2014
 * @package yii2-dynagrid
 * @version 1.0.0
 */

namespace kartik\dynagrid\controllers;

use Yii;
use kartik\dynagrid\models\DynaGridConfig;

class ConfigController extends \yii\web\Controller
{
    /**
     * Dyna grid configuration setup
     */
    public function actionSetup()
    {
        $model = new DynaGridConfig;
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            Yii::$app->session->setFlash('success', Yii::t('kvdynagrid', 'The grid configuration has been saved successfully.'));
            return $this->refresh();
        } else {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return $this->renderPartial('config', ['model' => $model]);
        }
    }
}