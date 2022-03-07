<?php

namespace app\modules\api\v2\controllers;


use app\models\BridecycleToSellerPayments;
use app\models\CartItem;
use app\models\Notification;
use app\models\Order;
use app\models\OrderItem;
use app\models\OrderPayment;
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

            $cartIteamAlreadyAdded = CartItem::find()->where(['product_id' => $model->product_id, 'user_id' => $model->user_id, 'is_checkout' => CartItem::IS_CHECKOUT_NO, 'color' => $model->color, 'size' => $model->size])->one();
            if (!empty($cartIteamAlreadyAdded) && $cartIteamAlreadyAdded instanceof CartItem) {
                throw new BadRequestHttpException(getValidationErrorMsg('product_already_added_to_cart_exception', Yii::$app->language));
            }

            $productData = Product::find()->where(['id' => $model->product_id])->one();

            if (!empty($productData) && $productData instanceof Product && in_array($productData->status_id, [ProductStatus::STATUS_ARCHIVED, ProductStatus::STATUS_PENDING_APPROVAL])) {
                throw new Exception(getValidationErrorMsg('product_not_available_choose_other_exception', Yii::$app->language));
            }

            //$basePrice = (!empty($productData) && $productData instanceof Product && !empty($productData->price)) ? $productData->price * $model->quantity : 0;
            $basePrice = (!empty($productData) && $productData instanceof Product && !empty($productData->getReferPrice())) ? $productData->getReferPrice() * $model->quantity : 0;
            $taxPrice = (!empty($productData) && $productData instanceof Product && !empty($productData->option_price)) ? $productData->option_price * $model->quantity : 0;
            $model->price = ($basePrice + $taxPrice);
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
        $model = CartItem::findOne($id);
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
            $model->price = ($basePrice + $taxPrice);
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
        if (empty($post) || empty($post['product_id'])) {
            throw new BadRequestHttpException(getValidationErrorMsg('product_id_required', Yii::$app->language));
        }

        $user_id = Yii::$app->user->identity->id;

        $modelOrder = new Order();
        $modelOrder->user_id = $user_id;
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

        $productIds = explode(",", $post['product_id']);
        $modelProducts = Product::find()->where(['IN', 'id', $productIds])->all();
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

        $modelCartItems = CartItem::find()->where(['user_id' => $user_id])->andWhere(['IN', 'product_id', $productIds])->andWhere(['is_checkout' => CartItem::IS_CHECKOUT_YES])->all();

        $cartTotal = CartItem::find()->where(['user_id' => $user_id])->andWhere(['IN', 'product_id', $productIds])->andWhere(['is_checkout' => CartItem::IS_CHECKOUT_YES])->sum('price');

        $cartTotalShipping = CartItem::find()->where(['user_id' => $user_id])->andWhere(['IN', 'product_id', $productIds])->andWhere(['is_checkout' => CartItem::IS_CHECKOUT_YES])->sum('shipping_cost');

        if (empty($cartTotal) && empty($cartTotalShipping)) {
            $cartTotal = OrderItem::find()->where(['order_id' => $modelOrder->id])->andWhere(['in', 'product_id', $productIds])->sum('price');
            $cartTotalShipping = OrderItem::find()->where(['order_id' => $modelOrder->id])->andWhere(['in', 'product_id', $productIds])->sum('shipping_cost');
        }

        $subTotal = (!empty($cartTotal)) ? $cartTotal : 0.00;

        // (product price + tax + shipping cost)
        $modelOrder->total_amount = (!empty($cartTotal)) ? ($cartTotal + $cartTotalShipping) : 0.00;

        $modelOrder->status = Order::STATUS_ORDER_PENDING;
        $modelOrder->save(false);

        $grandTotal = $modelOrder->total_amount;

        if (!empty($modelCartItems)) {
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
                }
            }

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

            $paymentRequestData = [
                'total' => $grandTotal,
                'user_id' => $user_id,
                'order_id' => $modelOrder->id,
                'card_type' => $cardType,
                'card_exp_month' => $expMontYear[0],
                'card_exp_year' => $expMontYear[1],
                'card_first_name' => $cardHoderName[0],
                'card_last_name' => (!empty($cardHoderName[1])) ? $cardHoderName[1] : "User",
                'sub_total' => $subTotal,
                'user' => Yii::$app->user->identity,
                'user_address' => $modelAddress,
                'user_address_billing' => $modelAddressBillingFind,
            ];

            $modelOrderPayment->order_id = $modelOrder->id;
            $modelOrderPayment->card_type = $cardType;
            $modelOrderPayment->save(false);

            $response = $this->makePayment(array_merge($post, $paymentRequestData));
            //p($response);
            if (!empty($response)) {

                if (!empty($response->getState()) && $response->getState() == 'created') {

                    if (!empty($modelOrder->orderItems)) {
                        foreach ($modelOrder->orderItems as $keys => $orderItemRow) {
                            if (!empty($orderItemRow) && $orderItemRow instanceof OrderItem) {
                                $remainQty = ($orderItemRow->product->available_quantity - $orderItemRow->quantity);
                                $orderItemRow->product->available_quantity = (!empty($remainQty) && $remainQty > 0) ? $remainQty : 0;
                                if ($remainQty <= 0) {
                                    $orderItemRow->product->status_id = ProductStatus::STATUS_SOLD;
                                } else {
                                    $orderItemRow->product->status_id = ProductStatus::STATUS_IN_STOCK;
                                }
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

                                $orderItemRow->product->save(false);

                                // Generate pdf of order invoice
                                $generateInvoice = $this->generateInvoice($orderItemRow->id);

                                // Track for Pending payment from bridecycle to seller start
                                $modelBridecycleToSellerPayment = new BridecycleToSellerPayments();
                                $modelBridecycleToSellerPayment->order_id = $modelOrder->id;
                                $modelBridecycleToSellerPayment->order_item_id = $orderItemRow->id;
                                $modelBridecycleToSellerPayment->product_id = $orderItemRow->product->id;
                                $modelBridecycleToSellerPayment->seller_id = $orderItemRow->product->user->id;
                                $modelBridecycleToSellerPayment->amount = (double)($orderItemRow->product->getReferPrice() + $orderItemRow->shipping_cost);
                                $modelBridecycleToSellerPayment->product_price = (double)($orderItemRow->price - $orderItemRow->tax);
                                $modelBridecycleToSellerPayment->tax = (double)($orderItemRow->tax);
                                $modelBridecycleToSellerPayment->status = BridecycleToSellerPayments::STATUS_PENDING;
                                $modelBridecycleToSellerPayment->save(false);
                                // Track for Pending payment from bridecycle to seller end

                                // Send Push notification start
                                $getUsers[] = $orderItemRow->product->user;
                                if (!empty($getUsers)) {
                                    foreach ($getUsers as $userROW) {
                                        if ($userROW instanceof User && ($user_id != $userROW->id)) {
                                            if ($userROW->is_order_placed_notification_on == User::IS_NOTIFICATION_ON && !empty($userROW->userDevice)) {
                                                $userDevice = $userROW->userDevice;

                                                // Insert into notification.
                                                $notificationText = $modelOrder->user->first_name . " " . $modelOrder->user->last_name . " Place a new order";
                                                $modelNotification = new Notification();
                                                $modelNotification->owner_id = $user_id;
                                                $modelNotification->notification_receiver_id = $userROW->id;
                                                $modelNotification->ref_id = $modelOrder->id;
                                                $modelNotification->notification_text = $notificationText;
                                                $modelNotification->action = "Add";
                                                $modelNotification->ref_type = "Order";
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

                                            if ($userROW->is_order_placed_email_notification_on == User::IS_NOTIFICATION_ON) {
                                                $message = $modelOrder->user->first_name . " " . $modelOrder->user->last_name . " Place a new order";
                                            }
                                        }
                                    }
                                }
                                // Send Push notification end
                            }
                        }
                    }

                    if (!empty($response) && !empty($response->getState()) && $response->getState() == 'created') {
                        $modelOrder->status = Order::STATUS_ORDER_INPROGRESS;

                        $modelCartItems = CartItem::find()->where(['user_id' => $user_id])->andWhere(['in', 'product_id', $productIds])->andWhere(['is_checkout' => CartItem::IS_CHECKOUT_YES])->all();

                        foreach ($modelCartItems as $key => $modelCartItemRow) {
                            if (!empty($modelCartItemRow) && $modelCartItemRow instanceof CartItem) {
                                // Delete from cart
                                $modelCartItemRow->delete();
                            }
                        }
                    }
                    $modelOrder->save(false);
                }
                $modelOrderPayment->payment_response = (!empty($response) && !empty($response->getState()) && $response->getState() == 'created') ? $response : "";
                $modelOrderPayment->payment_status = (!empty($response->getState())) ? $response->getState() : 'failed';
                $modelOrderPayment->payment_id = (!empty($response->getId())) ? $response->getId() : "";
                $modelOrderPayment->save(false);
                return $modelOrderPayment;
            }
        } else {
            throw new NotFoundHttpException(getValidationErrorMsg('cart_item_not_exist', Yii::$app->language));
        }
        return $modelOrder;
    }

    /**
     * @param $request
     * @return \Exception|Payment|PayPalConnectionException
     */
    public function makePayment($request)
    {
        $apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                Yii::$app->params['paypal_client_id'], // ClientID
                Yii::$app->params['paypal_client_secret'] // ClientSecret
            )
        );

        // or whatever yours is called

        $cardType = $request['card_type'];
        $cardNumber = $request['card_number'];
        $cardExpMonth = $request['card_exp_month'];
        $cardExpYear = $request['card_exp_year'];
        $cardCvv = $request['cvv'];
        $cardFirstname = $request['card_first_name'];
        $cardLastname = $request['card_last_name'];
        $user = $request['user'];
        $subTotal = !empty($request['sub_total']) ? $request['sub_total'] : 0.0;
        $tax = !empty($request['tax']) ? $request['tax'] : 0.0;
        $shippingCharge = !empty($request['shipping_charge']) ? $request['shipping_charge'] : 0.0;
        $total = (($subTotal + $tax + $shippingCharge) > 0) ? ($subTotal + $tax + $shippingCharge) : 1.00;

        $billAddress = $request['user_address_billing'];
        $addressLine = !empty($billAddress) && !empty($billAddress->street) ? $billAddress->street : '52 N Main St';
        $addressCity = !empty($billAddress) && !empty($billAddress->city) ? $billAddress->city : 'Johnstown';
        $addressPostCode = !empty($billAddress) && !empty($billAddress->zip_code) ? $billAddress->zip_code : '43210';
        $addressState = !empty($billAddress) && !empty($billAddress->state) ? $billAddress->state : 'OH';
        $addressCountry = !empty($billAddress) && !empty($billAddress->country) ? $billAddress->country : 'US';
        $addressPhone = !empty($user) && !empty($user->mobile) ? $user->mobile : '408-334-8890';

        // set billing address
        $addr = new Address();
        $addr->setLine1($addressLine);
        $addr->setCity($addressCity);
        $addr->setCountryCode($addressCountry);
        $addr->setPostalCode($addressPostCode);
        $addr->setState($addressState);
        $addr->setPhone($addressPhone);

        $shippingAddress = $request['user_address'];

        $addressShippingLine = !empty($shippingAddress) && !empty($shippingAddress->street) ? $shippingAddress->street : '52 N Main St';
        $addressShippingCity = !empty($shippingAddress) && !empty($shippingAddress->city) ? $shippingAddress->city : 'Johnstown';
        $addressShippingCountry = !empty($shippingAddress) && !empty($shippingAddress->country) ? $shippingAddress->country : 'US';
        $addressShippingPostCode = !empty($shippingAddress) && !empty($shippingAddress->zip_code) ? $shippingAddress->zip_code : '43210';
        $addressShippingState = !empty($shippingAddress) && !empty($shippingAddress->state) ? $shippingAddress->state : 'OH';
        $addressShippingPhone = !empty($user) && !empty($user->mobile) ? $user->mobile : '408-334-8890';

        // set shipping address
        $addrShip = new Address();
        $addrShip->setLine1($addressShippingLine);
        $addrShip->setCity($addressShippingCity);
        $addrShip->setCountryCode($addressShippingCountry);
        $addrShip->setPostalCode($addressShippingPostCode);
        $addrShip->setState($addressShippingState);
        $addrShip->setPhone($addressShippingPhone);

        // set credit card information
        $card = new CreditCard();
        $card->setNumber($cardNumber);
        $card->setType($cardType);
        $card->setExpireMonth($cardExpMonth);
        $card->setExpireYear($cardExpYear);
        $card->setCvv2($cardCvv);
        $card->setFirstName($cardFirstname);
        $card->setLastName($cardLastname);
        $card->setBillingAddress($addr);

        $fi = new FundingInstrument();
        $fi->setCreditCard($card);

        $payer = new Payer();
        $payer->setPaymentMethod("paypal");

        // Specify the payment amount.
        $amountDetails = new Details();
        $amountDetails->setSubtotal($subTotal);
        $amountDetails->setTax($tax);
        $amountDetails->setShipping($shippingCharge);

        $amount = new Amount();
        $amount->setCurrency(Yii::$app->params['paypal_payment_currency']);
        $amount->setTotal($total);
        $amount->setDetails($amountDetails);

        // ###Transaction
        // payment - what is the payment for and who
        // is fulfilling it. Transaction is created with
        // a `Payee` and `Amount` types
        $transaction = new Transaction();
        $transaction->setAmount($amount);
        $transaction->setDescription('This is the order payment transaction.');
        $transaction->setPaymentOptions(array('allowed_payment_method' => 'INSTANT_FUNDING_SOURCE'));

        $returnUrl = Url::to(['/cart-item/paypal-payment-response', 'is_success' => true, 'owner_id' => $request['user_id'], 'order_id' => $request['order_id']], true);
        $cancelUrl = Url::to(['/cart-item/paypal-payment-response', 'is_success' => false, 'owner_id' => $request['user_id'], 'order_id' => $request['order_id']], true);
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($returnUrl);
        $redirectUrls->setCancelUrl($cancelUrl);

        $payment = new Payment();
        $payment->setRedirectUrls($redirectUrls);
        $payment->setIntent("sale");
        $payment->setPayer($payer);
        $payment->setTransactions(array($transaction));

        try {
            $response = $payment->create($apiContext);
            $payment = Payment::get($response->getId(), $apiContext);
            return $payment;
        } catch (PayPalConnectionException $pce) {
            // Don't spit out errors or use "exit" like this in production code
            echo "Error: " . $pce->getMessage();
        }
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
                //$order->status = Order::STATUS_ORDER_COMPLETED;
                $order->status = Order::STATUS_ORDER_INPROGRESS;
            } else {
                $order->status = Order::STATUS_ORDER_CANCELLED;
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
        $modelOrderItem = OrderItem::findOne($order_item_id);

        $modelProduct = '';
        $modelseller = '';
        $modelsellerDetail = '';
        $modelOrder = $modelOrderItem->order;

        if (!empty($modelOrderItem) && $modelOrderItem instanceof OrderItem) {
            $modelProduct = $modelOrderItem->product;
            $modelseller = $modelOrderItem->product->user;
            if ((!empty($modelOrderItem->product->user) && $modelOrderItem->product->user->is_shop_owner == '1' && $modelOrderItem->product->type == 'n' && !empty($modelOrderItem->product->user->ShopDetails))) {
                $modelsellerDetail = $modelOrderItem->product->user->ShopDetails;
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