<?php

namespace app\modules\api\v1\controllers;

use app\models\Product;
use Yii;
use app\models\ProductRating;
use app\modules\api\v1\models\search\ProductRatingSearch;
use yii\web\{
    NotFoundHttpException,
    ForbiddenHttpException
};
use yii\base\BaseObject;
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
 * ProductRatingController implements the CRUD actions for ProductRating model.
 */
class ProductRatingController extends ActiveController
{
    /**
     * @var string
     */
    public $modelClass = 'app\models\ProductRating';

    /**
     * @var string
     */
    public $searchModelClass = 'app\modules\api\v1\models\search\ProductRatingSearch';

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
        unset($actions['create']);
        unset($actions['view']);
        unset($actions['update']);
        unset($actions['delete']);
        return $actions;
    }

    /**
     * Lists all ProductRating models.
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
     * Displays a single ProductRating model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = ProductRating::find()->where(['product_id' => $id])->andWhere(['IN', 'status', [ProductRating::STATUS_PENDING, ProductRating::STATUS_APPROVE]])->all();

        $modelProduct = Product::findOne($id);
        if (!$modelProduct instanceof Product) {
            throw new NotFoundHttpException('Product doesn\'t exist.');
        }

        if (empty($model)) {
            throw new NotFoundHttpException('Product rating doesn\'t exist.');
        }

        $totalRatings = count($model);

        $ratings = [];
        $i = $j = $k = $l = $m = $sumRatings = $rating = 0;
        foreach ($model as $dataRatings) {
            if ($dataRatings->rating == 5) {
                $i++;
            }
            if ($dataRatings->rating == 4) {
                $j++;
            }
            if ($dataRatings->rating == 3) {
                $k++;
            }
            if ($dataRatings->rating == 2) {
                $l++;
            }
            if ($dataRatings->rating == 1) {
                $m++;
            }
            $sumRatings += $dataRatings->rating;
        }

        $ratings['5'] = $i;
        $ratings['4'] = $j;
        $ratings['3'] = $k;
        $ratings['2'] = $l;
        $ratings['1'] = $m;

        if ($totalRatings != 0) {
            $rating = $sumRatings / $totalRatings;
        }
        $ratings['averageRatings'] = number_format((float)$rating, 1, '.', '');
        // $ratings['averageRatings'] = (int)$rating;
        return $ratings;
    }

    /**
     * Creates a new ProductRating model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ProductRating();
        $postData = Yii::$app->request->post();
        $productRating['ProductRating'] = $postData;
        $productRating['ProductRating']['user_id'] = Yii::$app->user->identity->id;
        $productRating['ProductRating']['status'] = ProductRating::STATUS_PENDING;
        $alreadyExist = ProductRating::find()->where(['product_id' => $productRating['ProductRating']['product_id'], 'user_id' => $productRating['ProductRating']['user_id']])->all();
        if (!empty($alreadyExist)) {
            throw new ForbiddenHttpException('You have already reviewed this product');
        }
        if ($model->load($productRating) && $model->validate()) {
            $model->save();
        }

        return $model;
    }


}