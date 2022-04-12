<?php

namespace app\modules\api\v2\controllers;

use app\models\BridecycleToSellerPayments;
use app\models\Notification;
use app\models\Order;
use app\models\OrderPayment;
use app\models\OrderPaymentRefund;
use app\models\PaymentTransferDetails;
use app\models\ProductStatus;
use app\modules\admin\models\Product;
use app\modules\api\v2\models\User;
use Stripe\Refund;
use Yii;
use app\models\OrderReturn;
use yii\db\Exception;
use yii\rest\ActiveController;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\Cors;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\imagine\Image;

/**
 * OrderReturnController implements the CRUD actions for OrderReturn model.
 */
class OrderReturnController extends ActiveController
{
    /**
     * @var string
     */
    public $modelClass = 'app\models\OrderReturn';

    /**
     * @var string
     */
    public $searchModelClass = 'app\modules\api\v2\models\search\OrderReturnSearch';

    /**
     * @return array
     */
    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD', 'OPTIONS'],
            'index-buyer' => ['GET', 'HEAD', 'OPTIONS'],
            'index-seller' => ['GET', 'HEAD', 'OPTIONS'],
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
            'only' => ['index', 'index-buyer', 'index-seller', 'view', 'create', 'update', 'delete'],
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
        unset($actions['index-seller']);
        unset($actions['index-buyer']);
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
     * Lists all Order models.
     * @return mixed
     */
    public function actionIndexSeller()
    {
        $model = new $this->searchModelClass;
        $requestParams = Yii::$app->getRequest()->getBodyParams();

        if (empty($requestParams)) {
            $requestParams = Yii::$app->getRequest()->getQueryParams();
        }

        return $model->searchSeller($requestParams, Yii::$app->user->identity->id);
    }

    /**
     * Lists all Order models.
     * @return mixed
     */
    public function actionIndexBuyer()
    {
        $model = new $this->searchModelClass;
        $requestParams = Yii::$app->getRequest()->getBodyParams();

        if (empty($requestParams)) {
            $requestParams = Yii::$app->getRequest()->getQueryParams();
        }

        return $model->searchBuyer($requestParams, Yii::$app->user->identity->id);
    }

    /**
     * Displays a single OrderReturn model.
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
     * Creates a new OrderReturn model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new OrderReturn();

        $postData = \Yii::$app->request->post();
        $orderReturnData['OrderReturn'] = $postData;

        $images = UploadedFile::getInstancesByName('images');

        $model->images = $images;

        $orderReturnData['OrderReturn']['images'] = $images;
        $orderReturnData['OrderReturn']['buyer_id'] = Yii::$app->user->identity->id;
        $orderReturnData['OrderReturn']['status'] = OrderReturn::STATUS_PENDING;

        if ($model->load($orderReturnData) && $model->validate()) {

            $modelOrder = Order::find()->where(['id' => $model->order_id])->one();
            $modelProduct = "";
            if (!empty($modelOrder) && $modelOrder instanceof Order) {
                $modelOrderItems = $modelOrder->orderItems;
                if (!empty($modelOrderItems)) {
                    foreach ($modelOrderItems as $keys => $modelOrderItemsRow) {
                        //p($modelOrderItemsRow->product->user_id);
                        $model->seller_id = $modelOrderItemsRow->product->user_id;
                        $modelProduct = $modelOrderItemsRow->product;
                    }
                }
            }

            /* Order return Image */
            if (!empty($images)) {
                foreach ($images as $key => $img) {

                    $uploadDirPath = Yii::getAlias('@orderReturnImageRelativePath');
                    $uploadThumbDirPath = Yii::getAlias('@orderReturnImageThumbRelativePath');
                    $thumbImagePath = '';

                    // Create product upload directory if not exist
                    if (!is_dir($uploadDirPath)) {
                        mkdir($uploadDirPath, 0777);
                    }

                    // Create product thumb upload directory if not exist
                    if (!is_dir($uploadThumbDirPath)) {
                        mkdir($uploadThumbDirPath, 0777);
                    }

                    $fileName = time() . rand(99999, 88888) . '.' . $img->extension;
                    // Upload product picture
                    $img->saveAs($uploadDirPath . '/' . $fileName);
                    // Create thumb of product picture
                    $actualImagePath = $uploadDirPath . '/' . $fileName;
                    $thumbImagePath = $uploadThumbDirPath . '/' . $fileName;

                    Image::thumbnail($actualImagePath, Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height'])->save($thumbImagePath, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
                    // Insert product picture name into database

                    if ($key == 0) {
                        $model->image_one = $fileName;
                    } else {
                        $model->image_two = $fileName;
                    }
                }
            }

            $model->save(false);

            // Send Notification Start
            $sender = $model->buyer;
            if (empty($sender)) {
                $sender = User::find()->where(['id' => Yii::$app->user->identity->id])->one();
            }

            if (!empty($model->seller)) {
                $getUsers[] = $model->seller;
            } else {
                $getUsers = [];
            }

            if (!empty($getUsers)) {
                foreach ($getUsers as $keys => $userROW) {
                    if ($userROW instanceof User && (Yii::$app->user->identity->id != $userROW->id)) {
                        if (!empty($userROW->userDevice)) {
                            $userDevice = $userROW->userDevice;

                            // Insert into notification.
                            $notificationText = "Order id:" . $modelOrder->unique_id . " has been return requested for your product.";

                            $action = "Add";
                            $modelNotification = new Notification();
                            $modelNotification->owner_id = $sender->id;
                            $modelNotification->notification_receiver_id = $userROW->id;
                            $modelNotification->ref_id = $model->id;
                            $modelNotification->notification_text = $notificationText;
                            $modelNotification->action = $action;
                            $modelNotification->ref_type = "order_return";
                            $modelNotification->product_id = (!empty($modelProduct) && !empty($modelProduct->id)) ? $modelProduct->id : "";
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
                            $message = "Order id:" . $modelOrder->unique_id . " has been return requested for your product.";
                            $subject = "Order Return Requested";
                            if (!empty($userROW->email)) {
                                try {
                                    Yii::$app->mailer->compose('api/orderReturn', ['sender' => $sender, 'receiver' => $userROW, 'message' => $message])
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
            // Send Notification End
        }

        $thumbImagePath = Yii::getAlias('@orderReturnImageRelativePath');
        $thumbImagePathRelative = Yii::getAlias('@orderReturnImageThumbRelativePath');

        if (!empty($model->image_one) && file_exists($thumbImagePathRelative . "/" . $model->image_one)) {
            $model->image_one = Yii::$app->request->getHostInfo() . $thumbImagePath . '/' . $model->image_one;
        }

        if (!empty($model->image_two) && file_exists($thumbImagePathRelative . "/" . $model->image_two)) {
            $model->image_two = Yii::$app->request->getHostInfo() . $thumbImagePath . '/' . $model->image_two;
        }

        return $model;
    }

    /**
     * Updates an existing OrderReturn model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public
    function actionUpdate($id)
    {
        $model = OrderReturn::find()->where(['id' => $id])->one();

        if (!$model instanceof OrderReturn) {
            throw new NotFoundHttpException(getValidationErrorMsg('page_not_exist', Yii::$app->language));
        }

        $postData = Yii::$app->request->post();
        $OrderReturn['OrderReturn'] = $postData;
        if ($model->load($OrderReturn) && $model->validate()) {

            if (in_array($model->status, [OrderReturn::STATUS_ACCEPT, OrderReturn::STATUS_DECLINE])) {

                $sender = User::find()->where(['id' => Yii::$app->user->identity->id])->one();
                $modelOrder = $model->order;

                $modelProduct = "";

                $getUsers = [];
                if (!empty($modelOrder) && $modelOrder instanceof Order) {
                    $getUsers[] = $modelOrder->user;
                    $modelProduct = (!empty($modelOrder->orderItems) && !empty($modelOrder->orderItems[0]) && !empty($modelOrder->orderItems[0]->product) && $modelOrder->orderItems[0]->product instanceof Product) ? $modelOrder->orderItems[0]->product : "";
                }

                if ($model->status == OrderReturn::STATUS_ACCEPT) {

                    $chargeId = "";
                    $transferId = "";
                    if (!empty($modelOrder) && $modelOrder instanceof Order) {
                        if (!empty($modelOrder->orderPayment) && $modelOrder->orderPayment instanceof OrderPayment && !empty($modelOrder->orderPayment->payment_id)) {
                            $chargeId = $modelOrder->orderPayment->payment_id;
                        }

                        if (!empty($modelOrder->paymentTransferDetail) && $modelOrder->paymentTransferDetail instanceof PaymentTransferDetails && !empty($modelOrder->paymentTransferDetail->transfer_id)) {
                            $transferId = $modelOrder->paymentTransferDetail->transfer_id;
                        }
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
                                'metadata' => ['description' => 'Refund the payment for order cancel, Order id:' . $modelOrder->id . "_" . $modelOrder->unique_id]
                            ]);

                            $refundResult = $refund;

                        } catch (Exception $e) {
                            echo "Error :" . $e->getMessage();
                        }

                        $refundId = "";
                        $refundAmount = "";
                        if (!empty($refund) && $refund instanceof Refund && !empty($refund->status) && $refund->status == Refund::STATUS_SUCCEEDED) {
                            $modelOrder->is_payment_refunded = Order::IS_PAYMENT_REFUNDED_YES;
                            $refundId = $refund->id;
                            $refundAmount = $refund->amount;
                            if (!empty($modelProduct) && $modelProduct instanceof Product) {
                                $modelProduct->status_id = ProductStatus::STATUS_IN_STOCK;
                                $modelProduct->available_quantity = ($modelProduct->available_quantity + 1);
                                $modelProduct->save(false);
                            }
                        } elseif (!empty($refund) && $refund instanceof Refund && !empty($refund->status) && in_array($refund->status, [Refund::STATUS_FAILED, Refund::STATUS_PENDING, Refund::STATUS_CANCELED])) {
                            $modelOrder->is_payment_refunded = Order::IS_PAYMENT_REFUNDED_NO;
                        } else {
                            $modelOrder->is_payment_refunded = Order::IS_PAYMENT_REFUNDED_NO;
                        }
                        $modelOrder->status = Order::STATUS_ORDER_RETURN;
                        $modelOrder->save(false);

                        if (!empty($refundResult) && $refundResult instanceof Refund && !empty($refundResult->status) && $refundResult->status == Refund::STATUS_SUCCEEDED && $modelOrder->is_payment_refunded = Order::IS_PAYMENT_REFUNDED_YES) {
                            $modelBridecycleToSellerPayment = BridecycleToSellerPayments::find()->where(['order_id' => $modelOrder->id])->one();
                            if (!empty($modelBridecycleToSellerPayment) && $modelBridecycleToSellerPayment instanceof BridecycleToSellerPayments) {
                                $modelBridecycleToSellerPayment->delete();
                            }
                        }

                        // Insert into OrderPaumentRefund start
                        $modelOrerPaymentRefund = new OrderPaymentRefund();
                        $modelOrerPaymentRefund->order_id = $modelOrder->id;
                        $modelOrerPaymentRefund->payment_refund_id = $refundId;
                        $modelOrerPaymentRefund->amount = $refundAmount;
                        $modelOrerPaymentRefund->refund_status = (!empty($refundResult) && $refundResult instanceof Refund && !empty($refundResult->status)) ? $refundResult->status : Refund::STATUS_FAILED;
                        $modelOrerPaymentRefund->refund_response = $refundResult;
                        $modelOrerPaymentRefund->save(false);
                        // Insert into OrderPaumentRefund end
                    }

                    if (!empty($getUsers)) {
                        foreach ($getUsers as $keys => $userROW) {
                            if ($userROW instanceof User && (Yii::$app->user->identity->id != $userROW->id)) {
                                if (!empty($userROW->userDevice)) {
                                    $userDevice = $userROW->userDevice;

                                    // Accept Refund Start
                                    // Insert into notification.
                                    $notificationText = "Your order id:" . $modelOrder->unique_id . " return accepted by seller.";

                                    $action = "accept_return";
                                    $modelNotification = new Notification();
                                    $modelNotification->owner_id = $sender->id;
                                    $modelNotification->notification_receiver_id = $userROW->id;
                                    $modelNotification->ref_id = $model->id;
                                    $modelNotification->notification_text = $notificationText;
                                    $modelNotification->action = $action;
                                    $modelNotification->ref_type = "order_return";
                                    $modelNotification->product_id = (!empty($modelProduct) && !empty($modelProduct->id)) ? $modelProduct->id : "";
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


                                    if ($modelOrder->is_payment_refunded == Order::IS_PAYMENT_REFUNDED_YES) {
                                        // Refunded Start
                                        // Insert into notification.
//                                        $notificationText = "Your order id:" . $modelOrder->id . " payment refund by " . Yii::$app->name;
//
//                                        $action = "Edit";
//                                        $modelNotification = new Notification();
//                                        $modelNotification->owner_id = $sender->id;
//                                        $modelNotification->notification_receiver_id = $userROW->id;
//                                        $modelNotification->ref_id = $modelOrder->id;
//                                        $modelNotification->notification_text = $notificationText;
//                                        $modelNotification->action = $action;
//                                        $modelNotification->ref_type = "Order";
//                                        $modelNotification->product_id = $modelProduct->id;
//                                        $modelNotification->save(false);
//
//                                        $badge = Notification::find()->where(['notification_receiver_id' => $userROW->id, 'is_read' => Notification::NOTIFICATION_IS_READ_NO])->count();
//                                        if ($userDevice->device_platform == 'android') {
//                                            $notificationToken = array($userDevice->notification_token);
//                                            $senderName = $sender->first_name . " " . $sender->last_name;
//                                            $modelNotification->sendPushNotificationAndroid($modelNotification->ref_id, $modelNotification->ref_type, $notificationToken, $notificationText, $senderName, $modelNotification);
//                                        } else {
//                                            $note = Yii::$app->fcm->createNotification(Yii::$app->name, $notificationText);
//                                            $note->setBadge($badge);
//                                            $note->setSound('default');
//                                            $message = Yii::$app->fcm->createMessage();
//                                            $message->addRecipient(new \paragraph1\phpFCM\Recipient\Device($userDevice->notification_token));
//                                            $message->setNotification($note)
//                                                ->setData([
//                                                    'id' => $modelNotification->ref_id,
//                                                    'type' => $modelNotification->ref_type,
//                                                    'message' => $notificationText,
//                                                    'action' => (!empty($modelNotification) && !empty($modelNotification->action)) ? $modelNotification->action : "",
//                                                ]);
//                                            $response = Yii::$app->fcm->send($message);
//                                        }
                                    }

                                }

                                if (!empty($userROW->email)) {
                                    $message = "Your order id:" . $modelOrder->unique_id . " return accepted by seller.";
                                    $subject = "Your order return accepted";
                                    if (!empty($userROW->email)) {
                                        try {
                                            Yii::$app->mailer->compose('api/orderReturn', ['sender' => $sender, 'receiver' => $userROW, 'message' => $message])
                                                ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
                                                ->setTo($userROW->email)
                                                ->setSubject($subject)
                                                ->send();
                                        } catch (HttpException $e) {
                                            echo "Error: " . $e->getMessage();
                                            //echo "Error: ";
                                        }
                                    }


                                    // Refunded
                                    if ($modelOrder->is_payment_refunded == Order::IS_PAYMENT_REFUNDED_YES) {
                                        $message = "Your order id:" . $modelOrder->unique_id . " payment refund by " . Yii::$app->name;
                                        $subject = "Your order payment refund";
                                        if (!empty($userROW->email)) {
                                            try {
                                                Yii::$app->mailer->compose('api/orderPayment', ['sender' => $sender, 'receiver' => $userROW, 'message' => $message])
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
                    }

                }

                if ($model->status == OrderReturn::STATUS_DECLINE) {

                    if (!empty($getUsers)) {
                        foreach ($getUsers as $keys => $userROW) {
                            if ($userROW instanceof User && (Yii::$app->user->identity->id != $userROW->id)) {
                                if (!empty($userROW->userDevice)) {
                                    $userDevice = $userROW->userDevice;

                                    // Accept Refund Start
                                    // Insert into notification.
                                    $notificationText = "Your order id:" . $modelOrder->unique_id . " return rejected by seller.";

                                    $action = "reject_return";
                                    $modelNotification = new Notification();
                                    $modelNotification->owner_id = $sender->id;
                                    $modelNotification->notification_receiver_id = $userROW->id;
                                    $modelNotification->ref_id = $model->id;
                                    $modelNotification->notification_text = $notificationText;
                                    $modelNotification->action = $action;
                                    $modelNotification->ref_type = "order_return";
                                    $modelNotification->product_id = (!empty($modelProduct) && !empty($modelProduct->id)) ? $modelProduct->id : "";
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
                                    $message = "Your order id:" . $modelOrder->unique_id . " return rejected by seller.";
                                    $subject = "Your order return rejected";
                                    if (!empty($userROW->email)) {
                                        try {
                                            Yii::$app->mailer->compose('api/orderReturn', ['sender' => $sender, 'receiver' => $userROW, 'message' => $message])
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
                }
            }

            $model->save(false);
        }
        return $model;
    }

    /**
     * Deletes an existing OrderReturn model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public
    function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the OrderReturn model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return OrderReturn the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected
    function findModel($id)
    {
        if (($model = OrderReturn::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(getValidationErrorMsg('page_not_exist', Yii::$app->language));
    }
}
