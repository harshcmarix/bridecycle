<?php

namespace app\modules\api\v2\controllers;

use Yii;
use app\models\FavouriteProduct;
use app\modules\api\v2\models\search\FavouriteProductSearch;
use yii\base\BaseObject;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\rest\ActiveController;
use yii\filters\auth\{
    HttpBasicAuth,
    CompositeAuth,
    HttpBearerAuth,
    QueryParamAuth
};
use yii\filters\Cors;

/**
 * FavouriteProductController implements the CRUD actions for FavouriteProduct model.
 */
class FavouriteProductController extends ActiveController
{
    /**
     * @var string
     */
    public $modelClass = 'app\models\FavouriteProduct';

    /**
     * @var string
     */
    public $searchModelClass = 'app\modules\api\v2\models\search\FavouriteProductSearch';

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
            'only' => ['index-list', 'view', 'create', 'update', 'delete'],
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
        unset($actions['create']);
        unset($actions['view']);
        unset($actions['delete']);
        return $actions;
    }

    /**
     * Lists all FavouriteProduct models.
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
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIndexList()
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
        $model = new FavouriteProduct();
        $postData = Yii::$app->request->post();
        $favouriteProduct['FavouriteProduct'] = $postData;
        $favouriteProduct['FavouriteProduct']['user_id'] = Yii::$app->user->identity->id;

        $modelFavourite = FavouriteProduct::find()->where(['product_id' => $favouriteProduct['FavouriteProduct']['product_id'], 'user_id' => Yii::$app->user->identity->id])->one();
        if (!empty($modelFavourite)) {
            throw new BadRequestHttpException('This product has been already favourited!');
        }
        if ($model->load($favouriteProduct) && $model->validate()) {
            $model->save();
        }

        return $model;
    }

    /**
     * @param $id
     * @return false|int
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $model = FavouriteProduct::find()->where(['id' => $id])->one();

        if (!$model instanceof FavouriteProduct) {
            throw new NotFoundHttpException('Favourite Product doesn\'t exist.');
        }
        $model->delete();
    }
}