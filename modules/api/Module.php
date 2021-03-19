<?php

namespace app\modules\api;

use \yii\web\Response;
use \yii\base\Module as BaseModule;

/**
 * Class Module
 * @package app\modules\api
 */
class Module extends BaseModule
{
    /**
     * @var string
     */
    public $controllerNamespace = 'app\modules\api\controllers';

    /**
     * Set custom options
     */
    public function init()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        \Yii::$app->response->charset = 'UTF-8';
        parent::init();

        \Yii::configure($this, require(__DIR__ . '/config/web.php'));

        // Code to manage response format globally
        \Yii::$app->response->on(Response::EVENT_BEFORE_SEND, function ($event) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            $responseHandler = $event->sender;
            $response = $responseHandler->data;

            $responseHandler->data = [
                'status' => $responseHandler->statusCode,
                'success' => isset($responseHandler->data['code']) && is_int($responseHandler->data['code']) ? $responseHandler->data['code'] : 1,
                'data' => $response
            ];
        });
    }
}
