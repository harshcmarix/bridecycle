<?php

namespace app\modules\api\v1\controllers;

use Yii;
use app\models\SearchHistory;
use app\modules\api\v1\models\search\SearchHistorySearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\auth\{
    HttpBasicAuth,
    CompositeAuth,
    HttpBearerAuth,
    QueryParamAuth
};
use yii\rest\ActiveController;
use yii\filters\Cors;

/**
 * SearchHistoryController implements the CRUD actions for SearchHistory model.
 */
class SearchHistoryController extends ActiveController
{
    /**
     * @var string
     */
    public $modelClass = 'app\models\SearchHistory';

    /**
     * @var string
     */
    public $searchModelClass = 'app\modules\api\v1\models\search\SearchHistorySearch';

       /**
     * @return array
     */
    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD', 'OPTIONS'],
            'view' => ['GET', 'HEAD', 'OPTIONS'],
            'create' =>['POST','OPTIONS'],
            'update' => ['PUT', 'PATCH'],
            'delete' => ['POST', 'DELETE'],
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
            'only' => ['index','view','create','update','delete'],
            'authMethods' => [
                HttpBasicAuth::class,
                HttpBearerAuth::class,
                QueryParamAuth::class,
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
     * @return array
     */
     public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        unset($actions['update']);
        unset($actions['view']);
        unset($actions['create']);
        return $actions;
    }

    /**
     * Lists all SearchHistory models.
     * @return mixed
     */
    public function actionIndex()
    {
        $model = new $this->searchModelClass;
        $requestParams = Yii::$app->getRequest()->getBodyParams();

        if (empty($requestParams)) {
            $requestParams = Yii::$app->getRequest()->getQueryParams();
        }
        return $model->search($requestParams);
    }
     /**
     * Creates a new SearchHistory model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new SearchHistory();
        $postData = Yii::$app->request->post();
        $searchHistory['SearchHistory'] = $postData;
        $searchHistory['SearchHistory']['user_id'] = Yii::$app->user->identity->id;
        if ($model->load($searchHistory) && $model->validate()) {
            $model->save();
        }

       return $model;
    }
}