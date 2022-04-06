<?php

namespace app\modules\api\v2\controllers;


use app\models\BridecycleToSellerPayments;
use app\models\CartItem;
use app\models\Notification;
use app\models\Order;
use app\models\OrderItem;
use app\models\OrderPayment;
use app\models\PaymentTransferDetails;
use app\models\Product;
use app\models\ProductCategory;
use app\models\ProductStatus;
use app\models\ProductTracking;
use app\models\Sizes;
use app\modules\api\v2\models\User;
use app\modules\api\v2\models\UserAddress;
use Dompdf\Dompdf;
use Dompdf\Options;
use kartik\mpdf\Pdf;
use PayPal\Api\Address;
use PayPal\Api\Amount;
use PayPal\Api\CreditCard;
use PayPal\Api\Details;
use PayPal\Api\FundingInstrument;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Exception\PayPalConnectionException;
use Yii;
use yii\base\Exception;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\Cors;
use yii\helpers\Url;
use yii\rest\ActiveController;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

// CartItemController implements the CRUD actions for CartItem model.
class CartItemController extends ActiveController
{
    /**
     * @var string
     */
    public $modelClass = 'app\models\CartItem';

    /**
     * @var string
     */
    public $searchModelClass = 'app\modules\api\v2\models\search\CartItemSearch';

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
            'add-product-to-checkout' => ['POST', 'OPTIONS'],
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
            'only' => ['index', 'view', 'create', 'update', 'delete', 'check-quantity-for-checkout', 'checkout', 'add-product-to-checkout'],
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
        return $model->search($requestParams, Yii::$app->user->identity->id);
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

            $cartIteamAlreadyAdded = CartItem::find()->where(['product_id' => $model->product_id, 'user_id' => $model->user_id, 'is_checkout' => CartItem::IS_CHECKOUT_NO, 'color' => $model->color, 'size_id' => $model->size])->one();
            //p($cartIteamAlreadyAdded);
            if (!empty($cartIteamAlreadyAdded) && $cartIteamAlreadyAdded instanceof CartItem) {
                //throw new Exception(getValidationErrorMsg('product_already_added_to_cart_exception', Yii::$app->language));
                throw new httpException(403, getValidationErrorMsg('product_already_added_to_cart_exception', Yii::$app->language));
            }

            $productData = Product::find()->where(['id' => $model->product_id])->one();

            if (!empty($productData) && $productData instanceof Product && in_array($productData->status_id, [ProductStatus::STATUS_ARCHIVED, ProductStatus::STATUS_PENDING_APPROVAL])) {
                throw new Exception(getValidationErrorMsg('product_not_available_choose_other_exception', Yii::$app->language));
            }

            //$basePrice = (!empty($productData) && $productData instanceof Product && !empty($productData->price)) ? $productData->price * $model->quantity : 0;
            $basePrice = (!empty($productData) && $productData instanceof Product && !empty($productData->getReferPrice())) ? $productData->getReferPrice() * $model->quantity : 0;
            $taxPrice = (!empty($productData) && $productData instanceof Product && !empty($productData->option_price)) ? $productData->option_price * $model->quantity : 0;
            //$model->price = ($basePrice + $taxPrice);
            $model->price = ($basePrice - $taxPrice);
            $model->tax = $taxPrice;

            // Update Size data start
            $modelSize = Sizes::find()->where(['id' => $model->size])->one();
            if (!empty($modelSize) && $modelSize instanceof Sizes) {
                $model->size_id = $modelSize->id;
                $model->size = $modelSize->size;
            } else {
                $model->size_id = $model->size;
                $model->size = "";
            }
            // Update Size data end

            $model->product_name = (!empty($productData) && $productData instanceof Product && !empty($productData->name)) ? $productData->name : 0;
            $model->category_name = (!empty($productData) && $productData instanceof Product && !empty($productData->category) && $productData->category instanceof ProductCategory && !empty($productData->category->name)) ? $productData->category->name : 0;
            $model->subcategory_name = (!empty($productData) && $productData instanceof Product && !empty($productData->subCategory) && $productData->subCategory instanceof ProductCategory && !empty($productData->subCategory->name)) ? $productData->subCategory->name : 0;
            $model->seller_id = (!empty($productData) && $productData instanceof Product && !empty($productData->user_id)) ? $productData->user_id : 0;
            $model->save(false);
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
        $model = CartItem::find()->where(['id' => $id])->one();
        if (!$model instanceof CartItem) {
            throw new NotFoundHttpException(getValidationErrorMsg('cart_item_not_exist', Yii::$app->language));
        }
        $postData = Yii::$app->request->post();
        $cartIteam['CartItem'] = $postData;
        $cartIteam['CartItem']['user_id'] = Yii::$app->user->identity->id;
        if ($model->load($cartIteam) && $model->validate()) {
            $productData = Product::find()->where(['id' => $model->product_id])->one();

            $basePrice = (!empty($productData) && $productData instanceof Product && !empty($productData->getReferPrice())) ? $productData->getReferPrice() * $model->quantity : 0;
            $taxPrice = (!empty($productData) && $productData instanceof Product && !empty($productData->option_price)) ? $productData->option_price * $model->quantity : 0;
            //$model->price = ($basePrice + $taxPrice);
            $model->price = ($basePrice - $taxPrice);
            $model->tax = ($taxPrice);

            $model->product_name = (!empty($productData) && $productData instanceof Product && !empty($productData->name)) ? $productData->name : 0;
            $model->category_name = (!empty($productData) && $productData instanceof Product && !empty($productData->category) && $productData->category instanceof ProductCategory && !empty($productData->category->name)) ? $productData->category->name : 0;
            $model->subcategory_name = (!empty($productData) && $productData instanceof Product && !empty($productData->subCategory) && $productData->subCategory instanceof ProductCategory && !empty($productData->subCategory->name)) ? $productData->subCategory->name : 0;
            $model->seller_id = (!empty($productData) && $productData instanceof Product && !empty($productData->user_id)) ? $productData->user_id : 0;

            $model->save(false);
        }
        return $model;
    }

    /**
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionCheckQuantityForCheckout()
    {
        $post = Yii::$app->request->post();

        if (empty($post) || empty($post['product_id'])) {
            throw new BadRequestHttpException(getValidationErrorMsg('product_id_required', Yii::$app->language));
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

    /**
     * @return Order|OrderPayment|UserAddress
     * @throws BadRequestHttpException
     * @throws HttpException
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionCheckout()
    {
        $post = Yii::$app->request->post();
//        if (empty($post) || empty($post['product_id'])) {
//            throw new BadRequestHttpException(getValidationErrorMsg('product_id_required', Yii::$app->language));
//        }

        $cartIds = [];
        $productId = [];
        if (empty($post) || empty($post['cart_id'])) {
            throw new BadRequestHttpException(getValidationErrorMsg('cart_id_required', Yii::$app->language));
        } else {
            $cartIds = explode(",", $post['cart_id']);
            $productId = CartItem::find()->select('product_id')->where(['in', 'id', $cartIds])->column();
        }

        if (empty($post) || empty($post['name'])) {
            throw new BadRequestHttpException(getValidationErrorMsg('name_required', Yii::$app->language));
        }

        if (empty($post) || empty($post['contact'])) {
            throw new BadRequestHttpException(getValidationErrorMsg('contact_required', Yii::$app->language));
        }

        if (empty($post) || empty($post['email'])) {
            throw new BadRequestHttpException(getValidationErrorMsg('email_required', Yii::$app->language));
        }

        $user_id = Yii::$app->user->identity->id;


        //$productIdsArr = explode(",", $post['product_id']);
        $productIdsArr = $productId;

        $modeOrders = [];
        if (!empty($cartIds)) {
            foreach ($cartIds as $keyCart => $cartIdsRow) {
                $modelCart = CartItem::find()->where(['id' => $cartIdsRow])->one();
                //p($modelCart);
                $prodModel = Product::find()->where(['id' => $modelCart->product_id])->one();
//                $productActualPrice = "";
//                if (!empty($prodModel) && $prodModel instanceof Product) {
//                    $productActualPrice = ($prodModel->getReferPrice() - $prodModel->option_price);
//                    $prodModel->price = $productActualPrice;
//                    $prodModel->save(false);
//                }

                $modelOrder = new Order();
                $modelOrder->user_id = $user_id;
                $modelOrder->name = $post['name'];
                $modelOrder->contact = $post['contact'];
                $modelOrder->email = $post['email'];
                $modelAddress = new UserAddress();
                $postAddress['UserAddress'] = $post;
                $postAddress['UserAddress']['user_id'] = $user_id;
                $postAddress['UserAddress']['type'] = UserAddress::SHIPPING;
                $postAddress['UserAddress']['address'] = "not set";

                $modelOrderPayment = new OrderPayment();
                $postOrderPayment['OrderPayment'] = $post;
                if ($modelAddress->load($postAddress) && $modelAddress->validate()) {
                    if ($modelOrderPayment->load($postOrderPayment) && $modelOrderPayment->validate()) {
                        $modelAddressFind = UserAddress::find()->where(['user_id' => $user_id, 'street' => $modelAddress->street, 'city' => $modelAddress->city, 'state' => $modelAddress->state, 'country' => $modelAddress->country, 'zip_code' => $modelAddress->zip_code])->one();
                        if (!empty($modelAddressFind) && $modelAddressFind instanceof UserAddress) {
                            $modelOrder->user_address_id = $modelAddressFind->id;
                        } else {
                            $modelAddress->address = $modelAddress->street . ", " . $modelAddress->city . ", " . $modelAddress->zip_code . ", " . $modelAddress->state . ", " . $modelAddress->country;
                            $modelAddress->save(false);
                            $modelOrder->user_address_id = $modelAddress->id;
                        }
                    } else {
                        return $modelOrderPayment;
                    }
                } else {
                    return $modelAddress;
                }

                //$modelAddressBillingFind = UserAddress::find()->where(['user_id' => $user_id, 'type' => UserAddress::BILLING,'street' => $modelAddress->street, 'city' => $modelAddress->city, 'state' => $modelAddress->state, 'country' => $modelAddress->country, 'zip_code' => $modelAddress->zip_code])->one();
                $modelAddressBillingFind = UserAddress::find()->where(['user_id' => $user_id, 'street' => $modelAddress->street, 'city' => $modelAddress->city, 'state' => $modelAddress->state, 'country' => $modelAddress->country, 'zip_code' => $modelAddress->zip_code])->one();
                if (empty($modelAddressBillingFind)) {
                    $modelAddressBillingFind = $modelAddress;
                    //$modelAddressBillingFind->type = UserAddress::BILLING;
                    $modelAddressBillingFind->save(false);
                } else {
                    $modelOrder->user_address_id = $modelAddressBillingFind->id;
                }

                $productIds = $prodModel->id;
                $modelProducts = Product::find()->where(['id' => $productIds])->all();
                $productSold = 0;
                if (!empty($modelProducts)) {
                    foreach ($modelProducts as $prodKey => $modelProductRow) {
                        if (!empty($modelProductRow) && $modelProductRow instanceof Product && $modelProductRow->available_quantity <= 0 && in_array($modelProductRow->status_id, [ProductStatus::STATUS_SOLD, ProductStatus::STATUS_ARCHIVED])) {
                            $productSold++;
                        }
                    }
                }
                if ($productSold > 0) {
                    throw new HttpException(403, $productSold . ' ' . getValidationErrorMsg('product_out_of_stock_from_selected_products_exception', Yii::$app->language));
                }

//                $modelCartItems = CartItem::find()->where(['user_id' => $user_id])->andWhere(['IN', 'product_id', $productIds])->andWhere(['is_checkout' => CartItem::IS_CHECKOUT_YES])->all();
//
//                $cartTotal = CartItem::find()->where(['user_id' => $user_id])->andWhere(['IN', 'product_id', $productIds])->andWhere(['is_checkout' => CartItem::IS_CHECKOUT_YES])->sum('price');
//
//                $cartTotalShipping = CartItem::find()->where(['user_id' => $user_id])->andWhere(['IN', 'product_id', $productIds])->andWhere(['is_checkout' => CartItem::IS_CHECKOUT_YES])->sum('shipping_cost');
//
//                $cartTotalTax = CartItem::find()->where(['user_id' => $user_id])->andWhere(['IN', 'product_id', $productIds])->andWhere(['is_checkout' => CartItem::IS_CHECKOUT_YES])->sum('tax');


                $modelCartItems = CartItem::find()->where(['user_id' => $user_id])->andWhere(['IN', 'id', [$cartIdsRow]])->andWhere(['is_checkout' => CartItem::IS_CHECKOUT_YES])->all();
                //p($modelCartItems);

                $cartTotal = CartItem::find()->where(['user_id' => $user_id])->andWhere(['IN', 'id', [$cartIdsRow]])->andWhere(['is_checkout' => CartItem::IS_CHECKOUT_YES])->sum('price');

                $cartTotalShipping = CartItem::find()->where(['user_id' => $user_id])->andWhere(['IN', 'id', [$cartIdsRow]])->andWhere(['is_checkout' => CartItem::IS_CHECKOUT_YES])->sum('shipping_cost');

                $cartTotalTax = CartItem::find()->where(['user_id' => $user_id])->andWhere(['IN', 'id', [$cartIdsRow]])->andWhere(['is_checkout' => CartItem::IS_CHECKOUT_YES])->sum('tax');

                if (empty($cartTotal) && empty($cartTotalShipping)) {
//                    $cartTotal = OrderItem::find()->where(['order_id' => $modelOrder->id])->andWhere(['in', 'product_id', $productIds])->sum('price');
//                    $cartTotalShipping = OrderItem::find()->where(['order_id' => $modelOrder->id])->andWhere(['in', 'product_id', $productIds])->sum('shipping_cost');
//                    $cartTotalTax = OrderItem::find()->where(['order_id' => $modelOrder->id])->andWhere(['in', 'product_id', $productIds])->sum('tax');


                    $cartTotal = OrderItem::find()->where(['order_id' => $modelOrder->id])->andWhere(['in', 'product_id', [$productIds]])->sum('price');
                    $cartTotalShipping = OrderItem::find()->where(['order_id' => $modelOrder->id])->andWhere(['in', 'product_id', [$productIds]])->sum('shipping_cost');
                    $cartTotalTax = OrderItem::find()->where(['order_id' => $modelOrder->id])->andWhere(['in', 'product_id', [$productIds]])->sum('tax');
                }

                $subTotal = (!empty($cartTotal)) ? $cartTotal : 0.00;

                // (product price + tax + shipping cost)
                $modelOrder->total_amount = (!empty($cartTotal)) ? ($cartTotal + $cartTotalShipping + $cartTotalTax) : 0.00;

                $modelOrder->status = Order::STATUS_ORDER_PENDING;

                $modelOrder->save(false);

                $grandTotal = $modelOrder->total_amount;

                if (!empty($modelCartItems)) {
//                    p($modelCartItems,0);
                    foreach ($modelCartItems as $key => $modelCartItemRow) {
                        if (!empty($modelCartItemRow) && $modelCartItemRow instanceof CartItem) {

                            $modelOrderItem = new OrderItem();
                            $modelOrderItem->order_id = $modelOrder->id;
                            $modelOrderItem->product_id = $modelCartItemRow->product_id;
                            $modelOrderItem->quantity = $modelCartItemRow->quantity;
                            $modelOrderItem->color = $modelCartItemRow->color;
                            $modelOrderItem->size_id = $modelCartItemRow->size_id;
                            $modelOrderItem->size = $modelCartItemRow->size;
                            $modelOrderItem->price = $modelCartItemRow->price;
                            $modelOrderItem->tax = $modelCartItemRow->tax;
                            $modelOrderItem->shipping_cost = $modelCartItemRow->shipping_cost;

                            $modelOrderItem->product_name = $modelCartItemRow->product_name;
                            $modelOrderItem->category_name = $modelCartItemRow->category_name;
                            $modelOrderItem->subcategory_name = $modelCartItemRow->subcategory_name;
                            $modelOrderItem->seller_id = $modelCartItemRow->seller_id;

                            $modelOrderItem->save(false);

//                            $modelProductPriceUpdate = Product::find()->where(['id' => $modelCartItemRow->product_id])->one();
//                            if (!empty($modelProductPriceUpdate) && $modelProductPriceUpdate instanceof Product) {
//                                $modelProductPriceUpdate->price = $modelCartItemRow->price;
//                                $modelProductPriceUpdate->save(false);
//                            }
                        }
                    }

                    // Send Email notification to buyer for order placed start
                    //$modelOrder
                    $getUsersArr = [];
                    if (!empty($getUsersArr)) {
                        unset($getUsersArr);
                    }
                    $getUsersArr[] = $modelOrder->user;
                    if (!empty($getUsersArr)) {
                        foreach ($getUsersArr as $getUsersArrROW) {
                            if ($getUsersArrROW instanceof User && ($user_id == $getUsersArrROW->id)) {

                                if (!empty($getUsersArrROW->email) && $getUsersArrROW->is_order_placed_email_notification_on == User::IS_NOTIFICATION_ON) {
                                    $messageString = "Thank you for create an order.\nYour order placed.";

                                    if (!empty($getUsersArrROW->email)) {
                                        try {
                                            Yii::$app->mailer->compose('api/addNewOrder', ['receiver' => $getUsersArrROW, 'message' => $messageString])
                                                ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
                                                ->setTo($getUsersArrROW->email)
                                                ->setSubject('Order placed')
                                                ->send();
                                        } catch (HttpException $e) {
                                            //echo "Error: " . $e->getMessage();
                                            echo "Error: ";

                                        }
                                    }
                                }
                            }
                        }
                    }
                    //Send Email notification to buyer for order placed end


                    $cardType = OrderPayment::CARD_TYPE_VISA;
                    if (!empty($modelOrderPayment->card_number)) {
                        if ($modelOrderPayment->card_number[0] == OrderPayment::CARD_TYPE_VISA_NUMBER) {
                            $cardType = OrderPayment::CARD_TYPE_VISA;
                        } else if (in_array($modelOrderPayment->card_number[0], [OrderPayment::CARD_TYPE_MASTER_NUMBER_ONE, OrderPayment::CARD_TYPE_MASTER_NUMBER_TWO])) {
                            $cardType = OrderPayment::CARD_TYPE_MASTER;
                        } else if ($modelOrderPayment->card_number[0] == OrderPayment::CARD_TYPE_AMEX_NUMBER) {
                            $cardType = OrderPayment::CARD_TYPE_AMEX;
                        } else if ($modelOrderPayment->card_number[0] == OrderPayment::CARD_TYPE_DISCOVER_NUMBER) {
                            $cardType = OrderPayment::CARD_TYPE_DISCOVER;
                        }
                    }

                    $expMontYear = explode("/", $modelOrderPayment->expiry_month_year);
                    $cardHoderName = explode(" ", $modelOrderPayment->card_holder_name);

                    $sellerDetail = '';
                    if (!empty($modelOrder->orderItems)) {
                        $orderItms = $modelOrder->orderItems;
                        foreach ($orderItms as $itmKey => $orderitemRow) {
                            if (!empty($orderitemRow) && $orderitemRow instanceof OrderItem) {
                                if (!empty($orderitemRow) && !empty($orderitemRow->product) && !empty($orderitemRow->product->user)) {
                                    if ($orderitemRow->product->user instanceof User) {
                                        $sellerDetail = $orderitemRow->product->user;
                                    }
                                }
                            }
                        }
                    }

                    $paymentRequestData = [
                        'total' => $grandTotal,
                        'user_id' => $user_id,
                        'order_id' => $modelOrder->id,
                        'card_type' => $cardType,
                        'card_exp_month' => $expMontYear[0],
                        'card_exp_year' => (!empty($expMontYear[1])) ? $expMontYear[1] : date('Y'),
                        'card_first_name' => $cardHoderName[0],
                        'card_last_name' => (!empty($cardHoderName[1])) ? $cardHoderName[1] : "User",
                        'sub_total' => $subTotal,
                        'user' => Yii::$app->user->identity,
                        'user_address' => $modelAddress,
                        'user_address_billing' => $modelAddressBillingFind,
                        'destination_id' => (!empty($sellerDetail) && $sellerDetail instanceof User && !empty($sellerDetail->stripe_account_connect_id)) ? $sellerDetail->stripe_account_connect_id : "",
                        'seller_id' => (!empty($sellerDetail) && $sellerDetail instanceof User && !empty($sellerDetail->id)) ? $sellerDetail->id : "",
                    ];

                    $modelOrderPayment->order_id = $modelOrder->id;
                    $modelOrderPayment->card_type = $cardType;
                    $modelOrderPayment->save(false);

                    $response = $this->makePayment(array_merge($post, $paymentRequestData));
                    //$response = $this->makePayment_bkp(array_merge($post, $paymentRequestData));
                    //p($response);
                    if (!empty($response)) {

                        $modelBCToSellerPayment = "";

                        if (!empty($response->status) && $response->status == 'succeeded') {
                            $getUsersPaymentDoneArr = [];
                            // Send Email notification to buyer for order payment done start
                            //$modelOrder
                            if (!empty($getUsersPaymentDoneArr)) {
                                unset($getUsersPaymentDoneArr);
                            }
                            $getUsersPaymentDoneArr[] = $modelOrder->user;
                            if (!empty($getUsersPaymentDoneArr)) {
                                foreach ($getUsersPaymentDoneArr as $getUsersPaymentDoneArrROW) {
                                    if ($getUsersPaymentDoneArrROW instanceof User && ($user_id == $getUsersPaymentDoneArrROW->id)) {

                                        if (!empty($getUsersPaymentDoneArrROW->email) && $getUsersPaymentDoneArrROW->is_payment_done_email_notification_on == User::IS_NOTIFICATION_ON) {
                                            $messageStringPaymentDone = "Thank you for create payment for your order.\n Your payment done for order ID:" . $modelOrder->id;

                                            if (!empty($getUsersPaymentDoneArrROW->email)) {
                                                try {
                                                    Yii::$app->mailer->compose('api/orderPayment', ['receiver' => $getUsersPaymentDoneArrROW, 'message' => $messageStringPaymentDone])
                                                        ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
                                                        ->setTo($getUsersArrROW->email)
                                                        ->setSubject('Order Payment Done')
                                                        ->send();
                                                } catch (HttpException $e) {
                                                    //echo "Error: " . $e->getMessage();
                                                    echo "Error: ";

                                                }

                                            }
                                        }
                                    }
                                }
                            }
                            // Send Email notification to buyer for order payment done end


                            if (!empty($modelOrder->orderItems)) {
                                foreach ($modelOrder->orderItems as $keys => $orderItemRow) {
                                    if (!empty($orderItemRow) && $orderItemRow instanceof OrderItem) {
                                        $remainQty = ($orderItemRow->product->available_quantity - $orderItemRow->quantity);
                                        $orderItemRow->product->available_quantity = (!empty($remainQty) && $remainQty > 0) ? $remainQty : 0;
                                        $productModel = $orderItemRow->product;
                                        if ($remainQty <= 0) {
                                            $productModel->status_id = ProductStatus::STATUS_SOLD;
                                        } else {
                                            $productModel->status_id = ProductStatus::STATUS_IN_STOCK;
                                        }
                                        $productModel->available_quantity = $remainQty;
                                        $productModel->price = $orderItemRow->price;
                                        $productModel->save(false);

                                        if ($orderItemRow->product->type == Product::PRODUCT_TYPE_USED) {
                                            $modelProductTracking = new ProductTracking();
                                            if (!empty($orderItemRow->product->product_tracking_id)) {
                                                $modelProductTracking->parent_id = $orderItemRow->product->product_tracking_id;
                                            }
                                            $modelProductTracking->product_id = $orderItemRow->product_id;
                                            $modelProductTracking->user_id = $orderItemRow->product->user_id;
                                            $modelProductTracking->order_id = $orderItemRow->order_id;
                                            $modelProductTracking->location = (!empty($orderItemRow->product->address) && !empty($orderItemRow->product->address->city)) ? $orderItemRow->product->address->city : '';
                                            $modelProductTracking->price = $orderItemRow->product->getReferPrice();
                                            $modelProductTracking->resale_date = date('Y-m-d H:i:s');
                                            $modelProductTracking->created_at = date('Y-m-d H:i:s');
                                            $modelProductTracking->updated_at = date('Y-m-d H:i:s');

                                            $modelProductTracking->save(false);

                                            if (!empty($modelProductTracking) && !empty($modelProductTracking->id) && empty($orderItemRow->product->product_tracking_id)) {
                                                $orderItemRow->product->product_tracking_id = $modelProductTracking->id;
                                            }
                                        }

                                        // Generate pdf of order invoice
                                        $generateInvoice = $this->generateInvoice($orderItemRow->id);

                                        // Track for Pending payment from bridecycle to seller start
                                        $modelBridecycleToSellerPayment = new BridecycleToSellerPayments();
                                        $modelBridecycleToSellerPayment->order_id = $modelOrder->id;
                                        $modelBridecycleToSellerPayment->order_item_id = $orderItemRow->id;
                                        $modelBridecycleToSellerPayment->product_id = $orderItemRow->product->id;
                                        $modelBridecycleToSellerPayment->seller_id = $orderItemRow->product->user->id;
                                        $modelBridecycleToSellerPayment->amount = (double)($orderItemRow->product->getReferPrice() + $orderItemRow->shipping_cost);
                                        $modelBridecycleToSellerPayment->product_price = (double)($orderItemRow->product->getReferPrice() - $orderItemRow->tax);
                                        $modelBridecycleToSellerPayment->tax = (double)($orderItemRow->tax);
                                        $modelBridecycleToSellerPayment->status = BridecycleToSellerPayments::STATUS_PENDING;
                                        $modelBridecycleToSellerPayment->save(false);
                                        $modelBCToSellerPayment = $modelBridecycleToSellerPayment;
                                        // Track for Pending payment from bridecycle to seller end

                                        //$orderItemRow->product->price = $productActualPrice;
                                        //$orderItemRow->product->price = $orderItemRow->price;
                                        //$orderItemRow->product->save(false);

                                        // Send Push notification start for seller
                                        $getUsers = [];
                                        if (!empty($getUsers)) {
                                            unset($getUsers);
                                        }
                                        $getUsers[] = $orderItemRow->product->user;
                                        if (!empty($getUsers)) {
                                            foreach ($getUsers as $userROW) {
                                                if ($userROW instanceof User && ($user_id != $userROW->id)) {
                                                    if ($userROW->is_order_placed_notification_on == User::IS_NOTIFICATION_ON && !empty($userROW->userDevice)) {
                                                        $userDevice = $userROW->userDevice;

                                                        // Insert into notification.
                                                        $notificationText = $modelOrder->user->first_name . " " . $modelOrder->user->last_name . " Place a new order for your product " . $orderItemRow->product->name;
                                                        $modelNotification = new Notification();
                                                        $modelNotification->owner_id = $user_id;
                                                        $modelNotification->notification_receiver_id = $userROW->id;
                                                        $modelNotification->ref_id = $modelOrder->id;
                                                        $modelNotification->notification_text = $notificationText;
                                                        $modelNotification->action = "Add";
                                                        $modelNotification->ref_type = "Order";
                                                        $modelNotification->product_id = $orderItemRow->product->id;
                                                        $modelNotification->save(false);

                                                        $badge = Notification::find()->where(['notification_receiver_id' => $userROW->id, 'is_read' => Notification::NOTIFICATION_IS_READ_NO])->count();
                                                        if ($userDevice->device_platform == 'android') {
                                                            $notificationToken = array($userDevice->notification_token);
                                                            $senderName = $modelOrder->user->first_name . " " . $modelOrder->user->last_name;
                                                            $modelNotification->sendPushNotificationAndroid($modelNotification->ref_id, $modelNotification->ref_type, $notificationToken, $notificationText, $senderName, $modelNotification);
                                                        } else {
                                                            $note = Yii::$app->fcm->createNotification(Yii::$app->name, $notificationText);
                                                            $note->setBadge($badge);
                                                            $note->setSound('default');
                                                            $message = Yii::$app->fcm->createMessage();
                                                            $message->addRecipient(new \paragraph1\phpFCM\Recipient\Device($userDevice->notification_token));
                                                            $message->setNotification($note)
                                                                ->setData([
                                                                    'id' => $modelNotification->ref_id,
                                                                    'type' => $modelNotification->ref_type,
                                                                    'message' => $notificationText,
                                                                    'action' => (!empty($modelNotification) && !empty($modelNotification->action)) ? $modelNotification->action : "",
                                                                ]);
                                                            $notificationResponse = Yii::$app->fcm->send($message);
                                                        }
                                                    }

                                                    if (!empty($userROW->email) && $userROW->is_order_placed_email_notification_on == User::IS_NOTIFICATION_ON) {
                                                        $message = $modelOrder->user->first_name . " " . $modelOrder->user->last_name . " Place a new order for your product " . $orderItemRow->product->name;

                                                        if (!empty($userROW->email)) {
                                                            try {
                                                                Yii::$app->mailer->compose('api/addNewOrder', ['receiver' => $userROW, 'message' => $message])
                                                                    ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
                                                                    ->setTo($userROW->email)
                                                                    ->setSubject('New order place for your product!')
                                                                    ->send();
                                                            } catch (HttpException $e) {
                                                                //echo "Error: " . $e->getMessage();
                                                                echo "Error: ";

                                                            }

                                                        }
                                                    }

                                                }
                                            }
                                        }
                                        // Send Push notification end for seller
                                    }
                                }
                            }

                            if (!empty($response) && !empty($response->status) && $response->status == 'succeeded') {
                                //$modelOrder->status = Order::STATUS_ORDER_PENDING;
                                $modelOrder->status = Order::STATUS_ORDER_INPROGRESS;

                                //$modelCartItems = CartItem::find()->where(['user_id' => $user_id])->andWhere(['in', 'product_id', $productIds])->andWhere(['is_checkout' => CartItem::IS_CHECKOUT_YES])->all();
                                $modelCartItems = CartItem::find()->where(['user_id' => $user_id])->andWhere(['in', 'id', [$cartIdsRow]])->andWhere(['is_checkout' => CartItem::IS_CHECKOUT_YES])->all();

                                foreach ($modelCartItems as $key => $modelCartItemRow) {
                                    if (!empty($modelCartItemRow) && $modelCartItemRow instanceof CartItem) {
                                        // Delete from cart
                                        $modelCartItemRow->delete();
                                    }
                                }

                                if (!empty($modelBCToSellerPayment) && $modelBCToSellerPayment instanceof BridecycleToSellerPayments) {
                                    //$modelBCToSellerPayment->status = BridecycleToSellerPayments::STATUS_COMPLETE;
                                    $modelBCToSellerPayment->status = BridecycleToSellerPayments::STATUS_PENDING;
                                    $modelBCToSellerPayment->save(false);
                                }
                            }
                            $modelOrder->save(false);
                        }
                        $modelOrderPayment->payment_response = (!empty($response) && !empty($response->status) && $response->status == 'succeeded') ? $response : "";
                        $modelOrderPayment->payment_status = (!empty($response->status)) ? $response->status : 'failed';
                        $modelOrderPayment->payment_id = (!empty($response->id)) ? $response->id : "";
                        $modelOrderPayment->save(false);
                        //return $modelOrderPayment;
                    }
                } else {
                    throw new NotFoundHttpException(getValidationErrorMsg('cart_item_not_exist', Yii::$app->language));
                }

                $modeOrders[] = $modelOrder;
            }
        }
        return $modeOrders;
    }

    /**
     * @param $request
     * @return string|\Stripe\Charge
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function makePayment($request)
    {

        $stripe = new \Stripe\StripeClient(
            Yii::$app->params['stripe_secret_key']
        );

        $resultCust = $stripe->customers->create([
            'email' => Yii::$app->user->identity->email,
            'name' => Yii::$app->user->identity->first_name . " " . Yii::$app->user->identity->last_name,
        ]);

        $chargeResult = "";
        try {
            $expiryMonthAndYear = explode("/", $request['expiry_month_year']);
            $cardToken = $stripe->tokens->create([
                'card' => [
                    'number' => $request['card_number'],
                    'exp_month' => $expiryMonthAndYear[0],
                    'exp_year' => $expiryMonthAndYear[1],
                    'cvc' => $request['cvv'],
                ],
            ]);
            $cardRequest = $stripe->customers->createSource(
                $resultCust->id,
                ['source' => $cardToken->id]
            );

            //$transactionFee = ($request['total'] * Yii::$app->params['payment_fee'] / 100);
            $brideCycleEarning = ($request['total'] * Yii::$app->params['bridecycle_product_order_charge_percentage'] / 100);

            //$sellerAmount = $request['total'] - ($brideCycleEarning + $transactionFee);
            $sellerAmount = $request['total'] - ($brideCycleEarning);

            //p("Total".$request['total'],0);
            //p("BCE".$brideCycleEarning,0);

            $chargeResult = $stripe->charges->create([
                'amount' => (is_integer($request['total']) && !is_float($request['total'])) ? $request['total'] . '00' : round($request['total']) . "00",
                'currency' => 'eur',
                'customer' => $resultCust->id,
                'source' => $cardRequest->id,
                'capture' => true,
                'description' => 'Payment for Order id: ' . $request['order_id'],
//                'transfer_data' => [
//                    'destination' => $request['destination_id'],
//                    'amount' => (is_integer($sellerAmount) && !is_float($sellerAmount)) ? $sellerAmount . '00' : round($sellerAmount) . "00"
//                ]
            ]);

            if (!empty($chargeResult) && !empty($chargeResult->balance_transaction)) {
                $balanceTransactionResult = $stripe->balanceTransactions->retrieve(
                    $chargeResult->balance_transaction,
                    []
                );
                if (!empty($balanceTransactionResult) && !empty($balanceTransactionResult->net)) {
                    $netAmount = ($balanceTransactionResult->net / 100);
                    $sellerAmount = ($netAmount - ($brideCycleEarning));
                }
            }
            //$sellerAmount = floor($sellerAmount);
            $sellerAmount = round($sellerAmount);

            $modelPaymentTransferDetail = new PaymentTransferDetails();
            $modelPaymentTransferDetail->order_id = $request['order_id'];
            $modelPaymentTransferDetail->seller_id = $request['seller_id'];
            $modelPaymentTransferDetail->source_id = $chargeResult->id; // Charge ID
            $modelPaymentTransferDetail->destination_id = $request['destination_id'];
            $modelPaymentTransferDetail->transfer_amount = $sellerAmount;
            $modelPaymentTransferDetail->is_transferred = PaymentTransferDetails::IS_TRANSFFERED_NO;
            $modelPaymentTransferDetail->save(false);

//            $transferResult = $stripe->transfers->create([
//                'amount' => $sellerAmount,
//                'currency' => 'eur',
//                'destination' => $request['destination_id'],
//                //'transfer_group' => 'Payment  transfer for Order id: ' . $request['order_id'],
//            ]);
//            p($transferResult);

        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
        return $chargeResult;
    }

    /**
     * @param null $is_success
     * @param null $owner_id
     * @param null $order_id
     */
    public function actionPaypalPaymentResponse($is_success = null, $owner_id = null, $order_id = null)
    {
        $order = Order::find($order_id);
        if (!empty($order)) {
            if ($is_success) {
                //$order->status = Order::STATUS_ORDER_DELIVERED;
                $order->status = Order::STATUS_ORDER_INPROGRESS;
            } else {
                $order->status = Order::STATUS_ORDER_CANCEL;
            }
            $order->save(false);
        }
        return $is_success;
    }

    /**
     * @param $order_item_id
     * @return string
     */
    public function generateInvoice($order_item_id)
    {
        $this->layout = "";
        $modelOrderItem = OrderItem::find()->where(['id' => $order_item_id])->one();

        $modelProduct = '';
        $modelseller = '';
        $modelsellerDetail = '';
        $modelOrder = $modelOrderItem->order;

        if (!empty($modelOrderItem) && $modelOrderItem instanceof OrderItem) {
            $modelProduct = $modelOrderItem->product;

            $modelProduct->price = $modelOrderItem->price;
            //$modelProduct->price = ($modelProduct->getReferPrice() - $modelProduct->option_price);
            $modelProduct->save(false);


            $modelseller = $modelOrderItem->product->user;
            if ((!empty($modelOrderItem->product->user) && $modelOrderItem->product->user->is_shop_owner == '1' && $modelOrderItem->product->type == 'n' && !empty($modelOrderItem->product->user->shopDetail))) {
                $modelsellerDetail = $modelOrderItem->product->user->shopDetail;
            } else {
                $modelsellerDetail = $modelOrderItem->product->user;
            }

            $buyerUser = User::findOne($modelOrder->user_id);
            //$buyerUserAddress = UserAddress::find()->where(['user_id' => $modelOrder->user_id])->one();
            $buyerUserAddress = UserAddress::find()->where(['id' => $modelOrder->user_address_id])->one();
            //$sellerAddress = UserAddress::find()->where(['user_id' => $modelseller->id])->one();
            $sellerAddress = UserAddress::find()->where(['id' => $modelOrderItem->product->address_id])->one();
            $currentDate = date('d-m-Y H:i');

            // Start - Generate Ordre Tracking Number
            $uniqueNumber = substr(str_shuffle("0123456789abcdefghijklmnopqrstvwxyz"), 6, 12);
            $existTrackingId = OrderItem::find()->where(['order_tracking_id' => $uniqueNumber])->one();
            if ($existTrackingId instanceof OrderItem) {
                $uniqueNumber = substr(str_shuffle("0123456789abcdefghijklmnopqrstvwxyz"), 6, 12);
            }
            $modelOrderItem->order_tracking_id = $uniqueNumber;
            // End - Generate Ordre Tracking Number


            // Start - Generate Ordre Unique Number
            $uniqueOrderNumber = 'ODR' . substr(str_shuffle("0123456789abcdefghijklmnopqrstvwxyz"), 6, 12);
            $existOrderUniqueId = Order::find()->where(['unique_id' => $uniqueOrderNumber])->one();
            if ($existOrderUniqueId instanceof Order) {
                $uniqueOrderNumber = 'ODR' . substr(str_shuffle("0123456789abcdefghijklmnopqrstvwxyz"), 6, 12);
            }
            $modelOrder->unique_id = $uniqueOrderNumber;
            $modelOrder->save(false);
            // End - Generate Ordre Unique Number


            //$transactionFees = Setting::find()->where(['option_key' => 'transaction_fees'])->one();
            $transactionFeesAmount = 0;
            // if ($transactionFees instanceof Setting) {
            //     $transactionFeesAmount = $transactionFees->option_value;
            // }

            if (!empty($modelProduct) && $modelProduct instanceof Product && !empty($modelProduct->option_price)) {
                $transactionFeesAmount = $modelProduct->option_price;
            }

            $html = $this->renderPartial('/order/invoice', ['model' => $modelOrderItem, 'order' => $modelOrder, 'product' => $modelProduct, 'seller' => $modelseller, 'sellerDetail' => $modelsellerDetail, 'sellerAddress' => $sellerAddress, 'currentDate' => $currentDate, 'buyerUser' => $buyerUser, 'buyerUserAddress' => $buyerUserAddress, 'transactionFeesAmount' => $transactionFeesAmount]);

            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4');
            $dompdf->render();
            $output = $dompdf->output();
            //$fileName = "order-" . time() . "-" . $modelOrder->id . ".pdf";
            $fileName = "order-" . time() . "-" . $modelOrderItem->id . "_" . $modelOrder->id . ".pdf";

            file_put_contents(Yii::getAlias('@orderInvoiceRelativePath') . '/' . $fileName, $output);
            $modelOrderItem->invoice = $fileName;
            $modelOrderItem->save(false);

            return Yii::getAlias('@orderInvoiceRelativePath') . "/" . $fileName;
        }
    }

    /**
     *
     * @return array|\yii\db\ActiveRecord[]
     * @throws BadRequestHttpException
     *
     */
    public function actionAddProductToCheckout()
    {
        $post = Yii::$app->request->post();
//        if (empty($post) || empty($post['product_id'])) {
//            throw new BadRequestHttpException(getValidationErrorMsg('product_id_required', Yii::$app->language));
//        }

        $cartIds = [];

        $productId = [];
        if (empty($post) || empty($post['cart_id'])) {
            throw new BadRequestHttpException(getValidationErrorMsg('cart_id_required', Yii::$app->language));
        } else {
            $cartIds = explode(",", $post['cart_id']);
            $productId = CartItem::find()->select('product_id')->where(['in', 'id', $cartIds])->column();
        }

        //$productIds = explode(",", $productId);
        $productIds = $productId;

        $modelProducts = Product::find()->where(['IN', 'id', $productIds])->all();
        $productSold = 0;
        if (!empty($modelProducts)) {
            foreach ($modelProducts as $prodKey => $modelProductRow) {
                if (!empty($modelProductRow) && $modelProductRow instanceof Product && $modelProductRow->available_quantity <= 0 || in_array($modelProductRow->status_id, [ProductStatus::STATUS_PENDING_APPROVAL, ProductStatus::STATUS_SOLD, ProductStatus::STATUS_ARCHIVED])) {
                    $productSold++;
                }
            }
        }
        if ($productSold > 0) {
            throw new HttpException(403, $productSold . ' ' . getValidationErrorMsg('product_out_of_stock_from_selected_products_exception', Yii::$app->language));
        }

        $user_id = Yii::$app->user->identity->id;
        if (!empty($productIds)) {
            $readyToCheckout = 1;
            $notReadyToCheckoutProducts = "";

            $modelsProduct = Product::find()->where(['IN', 'id', $productIds])->all();
            if (!empty($modelsProduct)) {
                foreach ($modelsProduct as $keyStud => $modelProduct) {
                    if (!empty($modelProduct) && $modelProduct instanceof Product) {
                        if (empty($modelProduct->available_quantity) || $modelProduct->available_quantity == '0' && $keyStud == 0) {
                            $readyToCheckout = 0;
                            $notReadyToCheckoutProducts .= $modelProduct->name;
                        } elseif (empty($modelProduct->available_quantity) || $modelProduct->available_quantity == '0' && $keyStud > 0) {
                            $readyToCheckout = 0;
                            $notReadyToCheckoutProducts .= ", " . $modelProduct->name;
                        }
                    }
                }
            } else {
                throw new HttpException(404, getValidationErrorMsg('data_not_found_exception', Yii::$app->language));
            }

            if ($readyToCheckout != 1 && $readyToCheckout == 0) {
                throw new HttpException(403, $notReadyToCheckoutProducts . getValidationErrorMsg('product_sold_exception', Yii::$app->language));
            }
        }

        //$modelCartItems = CartItem::find()->where(['user_id' => $user_id])->andWhere(['in', 'product_id', $productIds])->andWhere(['is_checkout' => CartItem::IS_CHECKOUT_NO])->all();
        $modelCartItems = CartItem::find()->where(['user_id' => $user_id])->andWhere(['in', 'id', $cartIds])->andWhere(['in', 'product_id', $productIds])->andWhere(['is_checkout' => CartItem::IS_CHECKOUT_NO])->all();
        if (!empty($modelCartItems)) {
            foreach ($modelCartItems as $key => $modelCartItemRow) {
                if (!empty($modelCartItemRow) && $modelCartItemRow instanceof CartItem) {
                    $modelCartItemRow->is_checkout = CartItem::IS_CHECKOUT_YES;
                    $modelCartItemRow->save(false);
                }
            }
        }
        return $modelCartItems;
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     * @throws BadRequestHttpException
     * @throws HttpException
     */
    public function actionAddProductToCheckout_bkp()
    {
        $post = Yii::$app->request->post();
        if (empty($post) || empty($post['product_id'])) {
            throw new BadRequestHttpException(getValidationErrorMsg('product_id_required', Yii::$app->language));
        }

        $productIds = explode(",", $post['product_id']);

        $modelProducts = Product::find()->where(['IN', 'id', $productIds])->all();
        $productSold = 0;
        if (!empty($modelProducts)) {
            foreach ($modelProducts as $prodKey => $modelProductRow) {
                if (!empty($modelProductRow) && $modelProductRow instanceof Product && $modelProductRow->available_quantity <= 0 || in_array($modelProductRow->status_id, [ProductStatus::STATUS_PENDING_APPROVAL, ProductStatus::STATUS_SOLD, ProductStatus::STATUS_ARCHIVED])) {
                    $productSold++;
                }
            }
        }
        if ($productSold > 0) {
            throw new HttpException(403, $productSold . ' ' . getValidationErrorMsg('product_out_of_stock_from_selected_products_exception', Yii::$app->language));
        }

        $user_id = Yii::$app->user->identity->id;
        if (!empty($productIds)) {
            $readyToCheckout = 1;
            $notReadyToCheckoutProducts = "";

            $modelsProduct = Product::find()->where(['IN', 'id', $productIds])->all();
            if (!empty($modelsProduct)) {
                foreach ($modelsProduct as $keyStud => $modelProduct) {
                    if (!empty($modelProduct) && $modelProduct instanceof Product) {
                        if (empty($modelProduct->available_quantity) || $modelProduct->available_quantity == '0' && $keyStud == 0) {
                            $readyToCheckout = 0;
                            $notReadyToCheckoutProducts .= $modelProduct->name;
                        } elseif (empty($modelProduct->available_quantity) || $modelProduct->available_quantity == '0' && $keyStud > 0) {
                            $readyToCheckout = 0;
                            $notReadyToCheckoutProducts .= ", " . $modelProduct->name;
                        }
                    }
                }
            } else {
                throw new HttpException(404, getValidationErrorMsg('data_not_found_exception', Yii::$app->language));
            }

            if ($readyToCheckout != 1 && $readyToCheckout == 0) {
                throw new HttpException(403, $notReadyToCheckoutProducts . getValidationErrorMsg('product_sold_exception', Yii::$app->language));
            }
        }

        $modelCartItems = CartItem::find()->where(['user_id' => $user_id])->andWhere(['in', 'product_id', $productIds])->andWhere(['is_checkout' => CartItem::IS_CHECKOUT_NO])->all();
        if (!empty($modelCartItems)) {
            foreach ($modelCartItems as $key => $modelCartItemRow) {
                if (!empty($modelCartItemRow) && $modelCartItemRow instanceof CartItem) {
                    $modelCartItemRow->is_checkout = CartItem::IS_CHECKOUT_YES;
                    $modelCartItemRow->save(false);
                }
            }
        }
        return $modelCartItems;
    }
}