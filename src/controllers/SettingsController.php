<?php

/**
 * @package   yii2-dynagrid
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2015 - 2019
 * @version   1.5.1
 */

namespace kartik\dynagrid\controllers;

use kartik\dynagrid\models\DynaGridSettings;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\Controller;
use yii\web\Response;

/**
 * SettingsController will manage the actions for dynagrid settings
 *
 * @package kartik\dynagrid\controllers
 */
class SettingsController extends Controller
{
    /**
     * Fetch dynagrid setting configuration
     *
     * @return mixed
     * @throws  InvalidConfigException
     */
    public function actionGetConfig()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new DynaGridSettings();
        $out = ['status' => '', 'content' => ''];
        $request = Yii::$app->request;
        if ($model->load($request->post()) && $model->validate()) {
            $validate = $model->validateSignature($request->post('configHashData', ''));
            if ($validate === true) {
                $out = ['status' => 'success', 'content' => var_export($model->getDataConfig(), true)];
            } else {
                $out = ['status' => 'error', 'content' => '<div class="alert alert-danger">' . $validate . '</div>'];
            }
        }
        return $out;
    }
}