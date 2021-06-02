<?php

namespace app\modules\api\v1\controllers;

use app\models\Order;
use app\models\OrderItem;
use app\modules\api\v1\models\UserAddress;
use Yii;
use app\models\{
    CartItem,
    Product
};
use app\modules\api\v1\models\search\CartItemSearch;
use yii\base\BaseObject;
use yii\web\BadRequestHttpException;
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
            'create' => ['POST', 'OPTIONS'],
            'update' => ['PUT', 'PATCH'],
            'delete' => ['POST', 'DELETE'],
            'check-quantity-for-checkout' => ['POST', 'OPTIONS'],
            'checkout' => ['POST', 'OPTIONS'],
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
            'only' => ['index', 'view', 'create', 'update', 'delete', 'check-quantity-for-checkout', 'checkout'],
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
        //unset($actions['delete']);

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
            $productData = Product::find()->where(['id' => $model->product_id])->one();
            $model->price = (!empty($productData) && $productData instanceof Product && !empty($productData->price)) ? $productData->price * $model->quantity : 0;
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
            $productData = Product::find()->where(['id' => $model->product_id])->one();
            $model->price = (!empty($productData) && $productData instanceof Product && !empty($productData->price)) ? $productData->price * $model->quantity : 0;
            $model->save();
        }
        return $model;
    }

//    public function actionDelete($id)
//    {
//        $model = CartItem::findOne($id);
//        if (!$model instanceof CartItem) {
//            throw new NotFoundHttpException('Cart item doesn\'t exist.');
//        }
//    }

    /**
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionCheckQuantityForCheckout()
    {
        $post = Yii::$app->request->post();

        if (empty($post) || empty($post['product_id'])) {
            throw new BadRequestHttpException('Invalid parameter passed. Request must required parameter "product_id"');
        }

        $productIds = explode(",", $post['product_id']);
        $modelCartItems = CartItem::find()->where(['user_id' => Yii::$app->user->identity->id])->andWhere(['in', 'product_id', $productIds])->all();
        $result = [];
        if (!empty($modelCartItems)) {
            foreach ($modelCartItems as $key => $modelCartItemRow) {
                if (!empty($modelCartItemRow) && $modelCartItemRow instanceof CartItem) {
                    $product = $modelCartItemRow->product;
                    if (!empty($product) && $product instanceof Product && $product->available_quantity > 0) {

                        if ($modelCartItemRow->quantity > $product->available_quantity) {

                            $result[] = 1;
                        } elseif ($modelCartItemRow->quantity <= $product->available_quantity) {
                            $result[] = 0;
                        }
                    } else {
                        $result[] = 1;
                    }

                }
            }
        }

        if (in_array('1', $result)) {
            $data['is_all_product_available'] = 0; // return false
        } else {
            $data['is_all_product_available'] = 1;// return true
        }
        return $data;
    }

    public function actionCheckout()
    {
        $post = Yii::$app->request->post();
        if (empty($post) || empty($post['product_id'])) {
            throw new BadRequestHttpException('Invalid parameter passed. Request must required parameter "product_id"');
        }

        $user_id = Yii::$app->user->identity->id;

        $modelOrder = new Order();
        $modelOrder->user_id = $user_id;
        $modelAddress = new UserAddress();
        $postAddress['UserAddress'] = $post;
        $postAddress['UserAddress']['user_id'] = $user_id;
        $postAddress['UserAddress']['type'] = UserAddress::SHIPPING;
        $postAddress['UserAddress']['address'] = "not set";

        if ($modelAddress->load($postAddress) && $modelAddress->validate()) {
            $modelAddressFind = UserAddress::find()->where(['user_id' => $user_id, 'type' => UserAddress::SHIPPING, 'street' => $modelAddress->street, 'city' => $modelAddress->city, 'state' => $modelAddress->state, 'country' => $modelAddress->country, 'zip_code' => $modelAddress->zip_code])->one();

            if (!empty($modelAddressFind) && $modelAddressFind instanceof UserAddress) {
                $modelOrder->user_address_id = $modelAddressFind->id;
            } else {
                $modelAddress->address = $modelAddress->street . ", " . $modelAddress->city . ", " . $modelAddress->zip_code . ", " . $modelAddress->state . ", " . $modelAddress->country;
                $modelAddress->save();
                $modelOrder->user_address_id = $modelAddress->id;
            }
        } else {
            return $modelAddress;
        }

        $productIds = explode(",", $post['product_id']);
        $modelCartItems = CartItem::find()->where(['user_id' => $user_id])->andWhere(['in', 'product_id', $productIds])->all();
        $cartTotal = CartItem::find()->where(['user_id' => $user_id])->andWhere(['in', 'product_id', $productIds])->sum('price');
        //p(Yii::$app->formatter->asCurrency($cartTotal));

        $modelOrder->total_amount = (!empty($cartTotal)) ? $cartTotal : 0.00;

        if (!empty($modelCartItems)) {
            $modelOrder->save();
            foreach ($modelCartItems as $key => $modelCartItemRow) {
                if (!empty($modelCartItemRow) && $modelCartItemRow instanceof CartItem) {
                    $modelOrderItem = new OrderItem();
                    $modelOrderItem->product_id = $modelCartItemRow->product_id;
                    $modelOrderItem->quantity = $modelCartItemRow->quantity;
                    $modelOrderItem->color = $modelCartItemRow->color;
                    $modelOrderItem->size = $modelCartItemRow->size;
                    if ($modelOrderItem->save()) {
                        // Delete from cart
                        $modelCartItemRow->delete();
                    }
                }
            }
        } else {
            throw new NotFoundHttpException('Cart items doesn\'t exist.');
        }
        return $modelOrder;
    }

}