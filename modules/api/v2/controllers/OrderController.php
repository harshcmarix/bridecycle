<?php

namespace app\modules\api\v2\controllers;

use app\models\BridecycleToSellerPayments;
use app\models\Notification;
use app\models\OrderPayment;
use app\models\OrderPaymentRefund;
use app\models\PaymentTransferDetails;
use app\models\Product;
use app\models\ProductStatus;
use app\modules\api\v2\models\User;
use Stripe\Refund;
use Yii;
use app\models\Order;
use yii\db\Exception;
use yii\web\NotFoundHttpException;
use yii\rest\ActiveController;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\Cors;
use yii\web\HttpException;

/**
 * OrderController implements the CRUD actions for Order model.
 */
class OrderController extends ActiveController
{
    /**
     * @var string
     */
    public $modelClass = 'app\models\Order';

    /**
     * @var string
     */
    public $searchModelClass = 'app\modules\api\v2\models\search\OrderSearch';

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
        unset($actions['update']);

        return $actions;
    }

    /**
     * Lists all Order models.
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
     * Displays a single Order model.
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
     * Creates a new Order model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Order();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Order model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = Order::find()->where(['id' => $id])->one();

        if (!$model instanceof Order) {
            throw new NotFoundHttpException(getValidationErrorMsg('order_not_exist', Yii::$app->language));
        }
        $postData = Yii::$app->request->post();
        $orderData['Order'] = $postData;

        if ($model->load($orderData) && $model->validate()) {

            if (in_array($orderData['Order']['status'], [Order::STATUS_ORDER_IN_TRANSIT, Order::STATUS_ORDER_DELIVERED, Order::STATUS_ORDER_CANCEL])) {

                $modelProduct = (!empty($model->orderItems) && !empty($model->orderItems[0]) && !empty($model->orderItems[0]->product)) ? $model->orderItems[0]->product : "";
                $modelOrderItem = (!empty($model->orderItems) && !empty($model->orderItems[0]))  ? $model->orderItems[0] : "";

                $sender = User::find()->where(['id' => Yii::$app->user->identity->id])->one();

                $getUsers = [];
                if (Yii::$app->user->identity->id != $model->user_id) {
                    $getUsers[] = User::find()->where(['id' => $model->user_id])->one();
                } else {
                    if (!empty($modelProduct) && $modelProduct instanceof Product) {
                        $getUsers[] = User::find()->where(['id' => $modelProduct->user_id])->one();
                    }
                }

                if ($orderData['Order']['status'] == Order::STATUS_ORDER_IN_TRANSIT) {
                    if (!empty($getUsers)) {
                        foreach ($getUsers as $keys => $userROW) {
                            if ($userROW instanceof User && (Yii::$app->user->identity->id != $userROW->id)) {
                                if (!empty($userROW->userDevice)) {
                                    $userDevice = $userROW->userDevice;

                                    // Insert into notification.
                                    $notificationText = "Your order will be delivered soon, Order ID:" . $model->unique_id;
                                    if (!empty($orderData['Order']['transit_detail'])) {
                                        $notificationText .= "\n Your shipment detail as below: \n" . $orderData['Order']['transit_detail'];
                                    }
                                    $action = "Edit";
                                    $modelNotification = new Notification();
                                    $modelNotification->owner_id = $sender->id;
                                    $modelNotification->notification_receiver_id = $userROW->id;
                                    $modelNotification->ref_id = $model->id;
                                    $modelNotification->notification_text = $notificationText;
                                    $modelNotification->action = $action;
                                    $modelNotification->ref_type = "Order";
                                    $modelNotification->product_id = $modelProduct->id;
                                    $modelNotification->save(false);

                                    $badge = Notification::find()->where(['notification_receiver_id' => $userROW->id, 'is_read' => Notification::NOTIFICATION_IS_READ_NO])->count();
                                    if ($userDevice->device_platform == 'android') {
                                        $notificationToken = array($userDevice->notification_token);
                                        $senderName = $sender->first_name . " " . $sender->last_name;
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
                                        $response = Yii::$app->fcm->send($message);
                                    }
                                }

                                if (!empty($userROW->email)) {
                                    $message = "Your order will be delivered soon, Order ID:" . $model->unique_id;
                                    if (!empty($orderData['Order']['transit_detail'])) {
                                        $message .= "\n Your shipment detail as below: \n" . $orderData['Order']['transit_detail'];
                                    }

                                    $subject = "Your order will be delivered soon";
                                    if (!empty($userROW->email)) {
                                        try {
                                            Yii::$app->mailer->compose('api/orderIntransit', ['sender' => $sender, 'receiver' => $userROW, 'message' => $message])
                                                ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
                                                ->setTo($userROW->email)
                                                ->setSubject($subject)
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
                }

                if ($orderData['Order']['status'] == Order::STATUS_ORDER_DELIVERED) {

                    if (!empty($getUsers)) {
                        foreach ($getUsers as $keys => $userROW) {
                            if ($userROW instanceof User && (Yii::$app->user->identity->id != $userROW->id)) {
                                if ($userROW->is_order_delivered_notification_on == User::IS_NOTIFICATION_ON && !empty($userROW->userDevice)) {
                                    $userDevice = $userROW->userDevice;

                                    // Insert into notification.
                                    $notificationText = "Your order " . $model->unique_id . " has been delivered. Thank you for shopping with " . Yii::$app->name;

                                    $action = "Edit";
                                    $modelNotification = new Notification();
                                    $modelNotification->owner_id = $sender->id;
                                    $modelNotification->notification_receiver_id = $userROW->id;
                                    $modelNotification->ref_id = $model->id;
                                    $modelNotification->notification_text = $notificationText;
                                    $modelNotification->action = $action;
                                    $modelNotification->ref_type = "Order";
                                    $modelNotification->product_id = $modelProduct->id;
                                    $modelNotification->save(false);

                                    $badge = Notification::find()->where(['notification_receiver_id' => $userROW->id, 'is_read' => Notification::NOTIFICATION_IS_READ_NO])->count();
                                    if ($userDevice->device_platform == 'android') {
                                        $notificationToken = array($userDevice->notification_token);
                                        $senderName = $sender->first_name . " " . $sender->last_name;
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
                                        $response = Yii::$app->fcm->send($message);
                                    }
                                }

                                if (!empty($userROW->email) && $userROW->is_order_delivered_email_notification_on == User::IS_NOTIFICATION_ON) {
                                    $message = "Your order " . $model->unique_id . " has been delivered. Thank you for shopping with " . Yii::$app->name;
                                    $subject = "Your order has been delivered";
                                    if (!empty($userROW->email)) {
                                        try {
                                            Yii::$app->mailer->compose('api/orderDelivered', ['sender' => $sender, 'receiver' => $userROW, 'message' => $message])
                                                ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
                                                ->setTo($userROW->email)
                                                ->setSubject($subject)
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

                }

                if ($orderData['Order']['status'] == Order::STATUS_ORDER_CANCEL) {

                    $model->orderPayment;
                    $chargeId = "";
                    $transferId = "";
                    if (!empty($model->orderPayment) && $model->orderPayment instanceof OrderPayment && !empty($model->orderPayment->payment_id)) {
                        $chargeId = $model->orderPayment->payment_id;
                    }

                    if (!empty($model->paymentTransferDetail) && $model->paymentTransferDetail instanceof PaymentTransferDetails && !empty($model->paymentTransferDetail->transfer_id)) {
                        $transferId = $model->paymentTransferDetail->transfer_id;
                    }

                    $stripe = new \Stripe\StripeClient(
                        Yii::$app->params['stripe_secret_key']
                    );

                    $isReverseTransfer = false;
                    if (!empty($transferId) && $transferId != "") {
                        $isReverseTransfer = true;
                    }

                    if (!empty($chargeId) || $chargeId != "") {
                        $refundResult = "";
                        try {
                            $refund = $stripe->refunds->create([
                                //'charge' => 'ch_3KayxHAvFy5NACFp0BIFRGfB',
                                'charge' => $chargeId,
                                'reverse_transfer' => $isReverseTransfer,
                                //'source_transfer_reversal' => false,
                                'refund_application_fee' => $isReverseTransfer,
                                'reason' => 'requested_by_customer',
                                'metadata' => ['description' => 'Refund the payment for order cancel, Order id:' . $model->id . "_" . $model->unique_id]
                            ]);
                            $refundResult = $refund;

                        } catch (Exception $e) {
                            echo "Error :" . $e->getMessage();
                        }

                        $refundId = "";
                        $refundAmount = "";
                        if (!empty($refund) && $refund instanceof Refund && !empty($refund->status) && $refund->status == Refund::STATUS_SUCCEEDED) {
                            $model->is_payment_refunded = Order::IS_PAYMENT_REFUNDED_YES;
                            $refundId = $refund->id;
                            $refundAmount = $refund->amount;
                            if (!empty($modelProduct) && $modelProduct instanceof Product) {
                                $modelProduct->status_id = ProductStatus::STATUS_IN_STOCK;
                                $modelProduct->available_quantity = ($modelProduct->available_quantity + 1);
                                //$modelProduct->price = ($modelProduct->getReferPrice() - $modelProduct->option_price);
                                $modelProduct->price = $modelOrderItem->price;
                                $modelProduct->save(false);
                            }
                        } elseif (!empty($refund) && $refund instanceof Refund && !empty($refund->status) && in_array($refund->status, [Refund::STATUS_FAILED, Refund::STATUS_PENDING, Refund::STATUS_CANCELED])) {
                            $model->is_payment_refunded = Order::IS_PAYMENT_REFUNDED_NO;
                        } else {
                            $model->is_payment_refunded = Order::IS_PAYMENT_REFUNDED_NO;
                        }

                        // Insert into OrderPaumentRefund start
                        $modelOrerPaymentRefund = new OrderPaymentRefund();
                        $modelOrerPaymentRefund->order_id = $model->id;
                        $modelOrerPaymentRefund->payment_refund_id = $refundId;
                        $modelOrerPaymentRefund->amount = $refundAmount;
                        $modelOrerPaymentRefund->refund_status = (!empty($refundResult) && $refundResult instanceof Refund && !empty($refundResult->status)) ? $refundResult->status : Refund::STATUS_FAILED;
                        $modelOrerPaymentRefund->refund_response = $refundResult;
                        $modelOrerPaymentRefund->save(false);
                        // Insert into OrderPaymentRefund end

                        // Cancel Order notification Start

                        if (!empty($getUsers)) {
                            foreach ($getUsers as $keys => $userROW) {
                                if ($userROW instanceof User && (Yii::$app->user->identity->id != $userROW->id)) {
                                    if (!empty($userROW->userDevice)) {
                                        $userDevice = $userROW->userDevice;

                                        // Insert into notification.

                                        if (Yii::$app->user->identity->id != $model->user_id) { // is a seller cancel
                                            $notificationText = "Your order " . $model->unique_id . " has been cancelled by " . $sender->first_name . " " . $sender->last_name . ".";
                                            $cancelBy = "_by_seller";
                                        } else { // is a buyer cancel
                                            $notificationText = "Your order " . $model->unique_id . " has been cancelled by " . $sender->first_name . " " . $sender->last_name . ".";
                                            $cancelBy = "_by_buyer";
                                        }

                                        $action = "cancel_order" . $cancelBy;
                                        $modelNotification = new Notification();
                                        $modelNotification->owner_id = $sender->id;
                                        $modelNotification->notification_receiver_id = $userROW->id;
                                        $modelNotification->ref_id = $model->id;
                                        $modelNotification->notification_text = $notificationText;
                                        $modelNotification->action = $action;
                                        $modelNotification->ref_type = "Order";
                                        $modelNotification->product_id = $modelProduct->id;
                                        $modelNotification->save(false);

                                        $badge = Notification::find()->where(['notification_receiver_id' => $userROW->id, 'is_read' => Notification::NOTIFICATION_IS_READ_NO])->count();
                                        if ($userDevice->device_platform == 'android') {
                                            $notificationToken = array($userDevice->notification_token);
                                            $senderName = $sender->first_name . " " . $sender->last_name;
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
                                            $response = Yii::$app->fcm->send($message);
                                        }
                                    }

                                    if (!empty($userROW->email)) {
                                        if (Yii::$app->user->identity->id != $model->user_id) { // is a seller cancel
                                            $message = "Your order " . $model->unique_id . " has been cancelled by " . $sender->first_name . " " . $sender->last_name . ".";
                                        } else { // is a buyer cancel
                                            $message = "Your order " . $model->unique_id . " has been cancelled by " . $sender->first_name . " " . $sender->last_name . ".";
                                        }
                                        $subject = "Your order has been cancelled";
                                        if (!empty($userROW->email)) {
                                            try {
                                                Yii::$app->mailer->compose('api/orderCancelled', ['sender' => $sender, 'receiver' => $userROW, 'message' => $message])
                                                    ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
                                                    ->setTo($userROW->email)
                                                    ->setSubject($subject)
                                                    ->send();
                                            } catch (HttpException $e) {
                                                echo "Error: " . $e->getMessage();
                                                //echo "Error: ";
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        // Cancel Order notification End


                        // Refund Order Notification Start
                        if ($model->is_payment_refunded == Order::IS_PAYMENT_REFUNDED_YES) {
                            $getUsersData[] = User::find()->where(['id' => $model->user_id])->one();
                            if (!empty($getUsersData)) {
                                foreach ($getUsersData as $keys1 => $userROWS) {
                                    if ($userROWS instanceof User && (Yii::$app->user->identity->id != $userROWS->id)) {
                                        if (!empty($userROWS->userDevice)) {
                                            $userDevice = $userROWS->userDevice;

                                            // Refunded Start
                                            // Insert into notification.
//                                        $notificationText = "Your order id:" . $modelOrder->id . " payment refund by " . Yii::$app->name;
////
////                                        $action = "Edit";
////                                        $modelNotification = new Notification();
////                                        $modelNotification->owner_id = $sender->id;
////                                        $modelNotification->notification_receiver_id = $userROWS->id;
////                                        $modelNotification->ref_id = $model->id;
////                                        $modelNotification->notification_text = $notificationText;
////                                        $modelNotification->action = $action;
////                                        $modelNotification->ref_type = "Order";
////                                        $modelNotification->product_id = $modelProduct->id;
////                                        $modelNotification->save(false);
////
////                                        $badge = Notification::find()->where(['notification_receiver_id' => $userROWS->id, 'is_read' => Notification::NOTIFICATION_IS_READ_NO])->count();
////                                        if ($userDevice->device_platform == 'android') {
////                                            $notificationToken = array($userDevice->notification_token);
////                                            $senderName = $sender->first_name . " " . $sender->last_name;
////                                            $modelNotification->sendPushNotificationAndroid($modelNotification->ref_id, $modelNotification->ref_type, $notificationToken, $notificationText, $senderName, $modelNotification);
////                                        } else {
////                                            $note = Yii::$app->fcm->createNotification(Yii::$app->name, $notificationText);
////                                            $note->setBadge($badge);
////                                            $note->setSound('default');
////                                            $message = Yii::$app->fcm->createMessage();
////                                            $message->addRecipient(new \paragraph1\phpFCM\Recipient\Device($userDevice->notification_token));
////                                            $message->setNotification($note)
////                                                ->setData([
////                                                    'id' => $modelNotification->ref_id,
////                                                    'type' => $modelNotification->ref_type,
////                                                    'message' => $notificationText,
////                                                    'action' => (!empty($modelNotification) && !empty($modelNotification->action)) ? $modelNotification->action : "",
////                                                ]);
////                                            $response = Yii::$app->fcm->send($message);
////                                        }

                                            // Refund Notification end

                                        }

                                        if (!empty($userROWS->email)) {
                                            $message = "Your order id:" . $model->unique_id . " payment refund by " . Yii::$app->name;
                                            $subject = "Your order payment refund";
                                            if (!empty($userROWS->email)) {
                                                try {
                                                    Yii::$app->mailer->compose('api/orderPayment', ['sender' => $sender, 'receiver' => $userROWS, 'message' => $message])
                                                        ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
                                                        ->setTo($userROWS->email)
                                                        ->setSubject($subject)
                                                        ->send();
                                                } catch (HttpException $e) {
                                                    echo "Error: " . $e->getMessage();
                                                    //echo "Error: ";
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        // Refund Order Notification End
                    }

                    if (!empty($modelProduct) && $modelProduct instanceof Product) {
                        //$modelProduct->price = ($modelProduct->getReferPrice() - $modelProduct->option_price);
                        $modelProduct->price = $modelOrderItem->price;
                        $modelProduct->save(false);
                    }
                }
            }

            if (Yii::$app->user->identity->id != $model->user_id && $orderData['Order']['status'] == Order::STATUS_ORDER_CANCEL) {
                $model->status = Order::STATUS_ORDER_CANCEL_BY_SELLER;
            }

            $model->save(false);

            if (in_array($orderData['Order']['status'], [Order::STATUS_ORDER_CANCEL_BY_SELLER, Order::STATUS_ORDER_CANCEL]) && $model->is_payment_refunded == Order::IS_PAYMENT_REFUNDED_YES) {
                $modelBridecycleToSellerPayment = BridecycleToSellerPayments::find()->where(['order_id' => $model->id])->one();
                if (!empty($modelBridecycleToSellerPayment) && $modelBridecycleToSellerPayment instanceof BridecycleToSellerPayments) {
                    $modelBridecycleToSellerPayment->delete();
                }
            }
        }

        return $model;
    }

    /**
     * Deletes an existing Order model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Order model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Order the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Order::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(getValidationErrorMsg('order_not_exist', Yii::$app->language));
    }

}