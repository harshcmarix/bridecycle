<?php

namespace app\modules\api\v2\controllers;

use Yii;
use app\models\SearchHistory;
use yii\web\NotFoundHttpException;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
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
    public $searchModelClass = 'app\modules\api\v2\models\search\SearchHistorySearch';

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
            'only' => ['index', 'view', 'create', 'update', 'delete'],
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
        unset($actions['delete']);

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
        return $model->search($requestParams, Yii::$app->user->identity->id);
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

    /**
     * @param $id
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $model = SearchHistory::find()->where(['id' => $id])->one();
        if (!$model instanceof SearchHistory) {
            throw new NotFoundHttpException(getValidationErrorMsg('search_history_not_exist', \Yii::$app->language));
        }

        $modelSearchHistories = SearchHistory::find()->where(['user_id' => Yii::$app->user->identity->id])->andWhere(['LIKE', 'search_text', $model->search_text])->all();
        if (!empty($modelSearchHistories)) {
            foreach ($modelSearchHistories as $key => $modelSearchHistory) {
                if (!empty($modelSearchHistory) && $modelSearchHistory instanceof SearchHistory) {
                    $modelSearchHistory->delete();
                }
            }
        }
    }

}