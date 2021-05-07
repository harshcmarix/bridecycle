<?php

namespace app\modules\api\v1\controllers;

use Yii;
use app\models\{
    CartItem,
    Product
};
use app\modules\api\v1\models\search\CartItemSearch;
use yii\web\NotFoundHttpException;
use yii\rest\ActiveController;
use yii\filters\auth\{
    HttpBasicAuth,
    CompositeAuth,
    HttpBearerAuth,
    QueryParamAuth
};
use yii\filters\Cors;

/**
 * CartItemController implements the CRUD actions for CartItem model.
 */
class CartItemController extends ActiveController
{
    /**
     * @var string
     */
    public $modelClass = 'app\models\CartItem';

    /**
     * @var string
     */
    public $searchModelClass = 'app\modules\api\v1\models\search\CartItemSearch';

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
        unset($actions['create']);
        unset($actions['update']);
       
        return $actions;
    }

    /**
     * Lists all CartItem models.
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
     * Displays a single CartItem model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new CartItem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new CartItem();
        $postData = Yii::$app->request->post();
        $cartIteam['CartItem'] = $postData;
        $cartIteam['CartItem']['user_id'] = Yii::$app->user->identity->id;
        if ($model->load($cartIteam) && $model->validate()) {
            $productData = Product::find()->where(['id'=>$model->product_id])->one();
            $model->price = !empty($productData->price) ? $productData->price * $model->quantity : 0; 
            $model->save();
        }

       return $model;
    }

    /**
     * Updates an existing CartItem model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = CartItem::findOne($id);
        if (!$model instanceof CartItem) {
            throw new NotFoundHttpException('Cart item doesn\'t exist.');
        }
        $postData = Yii::$app->request->post();
        $cartIteam['CartItem'] = $postData;
        $cartIteam['CartItem']['user_id'] = Yii::$app->user->identity->id;
        if ($model->load($cartIteam) && $model->validate()) {
            $productData = Product::find()->where(['id'=>$model->product_id])->one();
            $model->price = !empty($productData->price) ? $productData->price * $model->quantity : 0; 
            $model->save();
        }
        return $model;
    }
}