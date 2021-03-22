<?php

namespace app\modules\api\controllers;

use app\modules\api\models\Login;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\Cors;
use yii\rest\ActiveController;
use yii\web\ForbiddenHttpException;

class UserController extends ActiveController
{
    /**
     * @var string
     */
    public $modelClass = 'app\modules\api\models\User';

    /**
     * @var string
     */
    public $searchModelClass = 'app\modules\api\models\search\UserSearch';

    /**
     * @var $hiddenFields Array of hidden fields which not needed in APIs
     */
    protected $hiddenFields = ['password_hash', 'authKey', 'access_token', 'access_token_expired_at'];

    /**
     * @return array
     */
    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD', 'OPTIONS'],
            'view' => ['GET', 'HEAD', 'OPTIONS'],
            'create' => ['POST', 'OPTIONS'],
            'update' => ['PUT', 'PATCH'],
            'login' => ['POST', 'OPTIONS'],
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $auth = $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'only' => ['index', 'view', 'create', 'update'],
            'authMethods' => [
                ['class' => HttpBearerAuth::class],
                ['class' => QueryParamAuth::class, 'tokenParam' => 'accessToken']
            ]
        ];

        unset($behaviors['authenticator']);
        // re-add authentication filter
        $behaviors['authenticator'] = $auth;

        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = ['options'];

        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                'Access-Control-Expose-Headers' => ['X-Pagination-Per-Page', 'X-Pagination-Current-Page', 'X-Pagination-Total-Count ', 'X-Pagination-Page-Count'],
            ],
        ];

        return $behaviors;
    }

    /**
     * @return Login
     * @throws ForbiddenHttpException
     */
    public function actionLogin()
    {
        $model = new Login();
        $data['Login'] = \Yii::$app->request->post();

        if ($model->load($data) && $model->validate()) {
            if (!$model->login()) {
                throw new ForbiddenHttpException('Unable to process your request. Please contact administrator');
            }
        }

        return $model;
    }
}