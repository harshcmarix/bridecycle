<?php

namespace app\modules\api\controllers;

use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\Cors;
use yii\rest\ActiveController;

/**
 * Class SiteController
 * @package app\modules\api\controllers
 */
class SiteController extends ActiveController
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
     * @return array
     */
    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD', 'OPTIONS'],
            'view' => ['GET', 'HEAD', 'OPTIONS'],
            'create' => ['POST', 'OPTIONS'],
            'update' => ['PUT', 'PATCH'],
            'delete' => ['DELETE'],
        ];
    }

    /**
     * @return array|array[]
     */
//    public function behaviors()
//    {
//        $behaviors = parent::behaviors();
//        $auth = $behaviors['authenticator'] = [
//            'class' => CompositeAuth::class,
//            'only' => ['index', 'create', 'update', 'delete', 'view'],
//            'authMethods' => [
//                ['class' => HttpBasicAuth::class],
//                ['class' => HttpBearerAuth::class],
//                ['class' => QueryParamAuth::class, 'tokenParam' => 'accessToken']
//            ]
//        ];
//
//        unset($behaviors['authenticator']);
//        // re-add authentication filter
//        $behaviors['authenticator'] = $auth;
//
//        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
//        $behaviors['authenticator']['except'] = ['options'];
//
//        $behaviors['corsFilter'] = [
//            'class' => Cors::class,
//            'cors' => [
//                'Access-Control-Expose-Headers' => ['X-Pagination-Per-Page', 'X-Pagination-Current-Page', 'X-Pagination-Total-Count ', 'X-Pagination-Page-Count'],
//            ],
//        ];
//
//        return $behaviors;
//    }

    /**
     * @return array
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['view']);
        unset($actions['delete']);
        return $actions;
    }

    /**
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIndex()
    {
        $model = new $this->searchModelClass;
        p("In");
        $requestParams = \Yii::$app->getRequest()->getBodyParams();
        if (empty($requestParams)) {
            $requestParams = \Yii::$app->getRequest()->getQueryParams();
        }
        return $model->search($requestParams);
    }
}
