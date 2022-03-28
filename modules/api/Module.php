<?php

namespace app\modules\api;


use yii\base\Module as BaseModule;
use yii\web\Response;

/**
 * Class Module
 * @package app\modules\api
 */
class Module extends BaseModule
{
    /**
     * Set custom options
     */
    public function init()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        \Yii::$app->response->charset = 'UTF-8';

        parent::init();

//        if (!empty(\Yii::$app->request->url) && !in_array(basename(\Yii::$app->request->url), ['login', 'forgot-password', 'verify-reset-password', 'reset-password'])) {
//            if (!isset(\Yii::$app->request->headers['selected_language']) || empty(\Yii::$app->request->headers['selected_language'])) {
//                //throw new BadRequestHttpException('Invalid parameter passed. Request must required parameter in headers "selected_language"');
//                throw new \yii\base\Exception('Invalid parameter passed. Request must required parameter in headers "selected_language"');
//            } else {
//                \Yii::$app->language = \Yii::$app->request->headers['selected_language'];
//            }
//        }

        //if (!empty(\Yii::$app->request->url) && !in_array(basename(\Yii::$app->request->url), ['login', 'forgot-password', 'verify-reset-password', 'reset-password'])) {
            if (!isset(\Yii::$app->request->headers['selected_language']) || empty(\Yii::$app->request->headers['selected_language'])) {
                //throw new BadRequestHttpException('Invalid parameter passed. Request must required parameter in headers "selected_language"');
                //throw new \yii\base\Exception('Invalid parameter passed. Request must required parameter in headers "selected_language"');
                //\Yii::$app->language = 'en-US';
                \Yii::$app->language = 'english';
            } else {
                \Yii::$app->language = \Yii::$app->request->headers['selected_language'];
            }
        //}

        // Setup module version automatically based on the api request
        $absoluteUrl = explode('/', \Yii::$app->request->absoluteUrl);
        if (count($absoluteUrl) > 2) {
            $version = $absoluteUrl[count($absoluteUrl) - 2];
            if (!empty($version)) {
                $versionNumber = sprintf("%.1f", str_replace(['v'], '', $version));
                $this->setVersion($versionNumber);
            }
        }

//        \Yii::$app->setComponents([
//            'urlManager' => [
//                'class' => 'yii\web\UrlManager',
//                'enablePrettyUrl' => true,
//                'showScriptName'=>false,
//                'rules' => [
//                    'POST <module:[\w-]+>/<controller:[\w-]+>' => '<module>/<controller>/create',
//                ],
//            ]
//        ]);

        //p(\Yii::$app->get('urlManager'));

//        \Yii::configure($this, require(__DIR__ . '/config/web.php'));

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
