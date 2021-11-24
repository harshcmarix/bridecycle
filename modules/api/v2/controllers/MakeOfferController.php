<?php

namespace app\modules\api\v2\controllers;

use app\models\Notification;
use app\models\Product;
use app\modules\api\v2\models\User;
use Yii;
use app\models\MakeOffer;
use Exception;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\Cors;
use yii\rest\ActiveController;


/**
 * MakeOfferController implements the CRUD actions for MakeOffer model.
 */
class MakeOfferController extends ActiveController
{
    /**
     * @var string
     */
    public $modelClass = 'app\models\MakeOffer';

    /**
     * @var string
     */
    public $searchModelClass = 'app\modules\api\v2\models\search\MakeOfferSearch';

    /**
     * @return array
     */
    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD', 'OPTIONS'],
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
            'only' => ['index', 'index-seller', 'view', 'create', 'update', 'delete'],
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
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['view']);
        unset($actions['delete']);
        return $actions;
    }

    /**
     * Lists all Maked offer models for buyer.
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
     * Lists all Maked offer models for seller.
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
     * Displays a single MakeOffer model.
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
     * Creates a new MakeOffer model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new MakeOffer();

        $post = Yii::$app->request->post();
        $postData['MakeOffer'] = Yii::$app->request->post();
        if (empty($post) || empty($post['product_id'])) {
            throw new BadRequestHttpException('Invalid parameter passed. Request must required parameter "product_id"');
        }
        $modelProduct = Product::findOne($postData['MakeOffer']['product_id']);

        if (!$modelProduct instanceof Product) {
            throw new NotFoundHttpException('Requested product doesn\'t exist.');
        }

        $postData['MakeOffer']['sender_id'] = Yii::$app->user->identity->id;
        $postData['MakeOffer']['receiver_id'] = (!empty($modelProduct) && !empty($modelProduct->user_id)) ? $modelProduct->user_id : "";
        $postData['MakeOffer']['status'] = MakeOffer::STATUS_PENDING;

        $createdOffers = 0;
        if (!empty($modelProduct) && $modelProduct instanceof Product) {
            $createdOffers = MakeOffer::find()
                ->where('make_offer.sender_id=' . Yii::$app->user->identity->id)
                ->andWhere('make_offer.product_id=' . $modelProduct->id)
                ->count();
        }

        if ($createdOffers > 0 && $createdOffers >= MakeOffer::USER_ALLOWED_OFFER) {
            throw new httpException(403, "Sorry, You have exceeded the maximum limit of making an offer for this product!");
        }

        $createdOffersData = MakeOffer::find()
            ->where('make_offer.sender_id=' . Yii::$app->user->identity->id)
            ->andWhere('make_offer.product_id=' . $modelProduct->id)->all();

        if (!empty($createdOffersData) && !empty($createdOffersData[count($createdOffersData) - 1])) {
            $data = $createdOffersData[count($createdOffersData) - 1];
            if (!empty($data) && $data instanceof MakeOffer && $data->status == MakeOffer::STATUS_PENDING) {
                throw new httpException(403, "Sorry, You have already made an offer for this product, the seller will take action on it first, then you will be performed this action!");
            }
        }

        if ($model->load($postData) && $model->validate()) {

            if ($model->save()) {
                // Send Push Notification Start
                if (!empty($postData['MakeOffer']['status']) && in_array($postData['MakeOffer']['status'], [MakeOffer::STATUS_PENDING])) {
                    $getUsers[] = User::findOne($modelProduct->user_id);
                    $sender = User::findOne(Yii::$app->user->identity->id);

                    if (!empty($getUsers)) {
                        foreach ($getUsers as $keys => $userROW) {
                            if ($userROW instanceof User && (Yii::$app->user->identity->id != $userROW->id)) {
                                if ($userROW->is_offer_update_notification_on == User::IS_NOTIFICATION_ON && !empty($userROW->userDevice)) {
                                    $userDevice = $userROW->userDevice;

                                    // Insert into notification.
                                    $notificationText = $sender->first_name . " " . $sender->last_name . " has been sent you offer for your product " . ucfirst($modelProduct->name) . " at " . Yii::$app->formatter->asCurrency($model->offer_amount);
                                    $action = "Add";
                                    $modelNotification = new Notification();
                                    $modelNotification->owner_id = $sender->id;
                                    $modelNotification->notification_receiver_id = $userROW->id;
                                    $modelNotification->ref_id = $model->id;
                                    $modelNotification->notification_text = $notificationText;
                                    $modelNotification->action = $action;
                                    $modelNotification->ref_type = "make_offer";
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

                                if (!empty($userROW->email) && $userROW->is_offer_update_email_notification_on == User::IS_NOTIFICATION_ON) {
                                    $message = $sender->first_name . " " . $sender->last_name . " has been sent you offer for your product " . ucfirst($modelProduct->name) . " at " . Yii::$app->formatter->asCurrency($model->offer_amount);
                                    $subject = "Sent an offer for your product";
                                    if (!empty($userROW->email)) {
                                        try {
                                            Yii::$app->mailer->compose('api/addNewMakeOffer', ['sender' => $sender, 'receiver' => $userROW, 'message' => $message])
                                                ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
                                                ->setTo($userROW->email)
                                                ->setSubject($subject)
                                                ->send();
                                        } catch (httpException $e) {
                                            echo "Error: " . $e->getMessage();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                // Send Push Notification End

            }
            $model->save(false);
        }
        return $model;
    }

    /**
     * Updates an existing MakeOffer model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = MakeOffer::findOne($id);

        if (!$model instanceof MakeOffer || ($model->receiver_id != Yii::$app->user->identity->id)) {
            throw new NotFoundHttpException('Offer doesn\'t exist.');
        }

        $postData = Yii::$app->request->post();
        $offerData['MakeOffer'] = $postData;

        $modelProduct = Product::findOne($model->product_id);

        if (!$modelProduct instanceof Product) {
            throw new NotFoundHttpException('Product doesn\'t exist.');
        }

        if ($model->load($offerData) && $model->validate()) {
            if (!empty($offerData['MakeOffer']['status']) && $offerData['MakeOffer']['status'] == MakeOffer::STATUS_ACCEPT) {
                $model->status = MakeOffer::STATUS_ACCEPT;
            } elseif (!empty($offerData['MakeOffer']['status']) && $offerData['MakeOffer']['status'] == MakeOffer::STATUS_REJECT) {
                $model->status = MakeOffer::STATUS_REJECT;
            } else {
                $model->status = MakeOffer::STATUS_PENDING;
            }

            // Send Push Notification Start
            if (!empty($offerData['MakeOffer']['status']) && in_array($offerData['MakeOffer']['status'], [MakeOffer::STATUS_ACCEPT, MakeOffer::STATUS_REJECT])) {
                $getUsers[] = $model->sender;

                if (!empty($getUsers)) {
                    foreach ($getUsers as $keys => $userROW) {
                        if ($userROW instanceof User && ($model->sender_id != $userROW->id)) {
                            if ($userROW->is_offer_update_notification_on == User::IS_NOTIFICATION_ON && !empty($userROW->userDevice)) {
                                $userDevice = $userROW->userDevice;

                                if (!empty($userDevice) && !empty($userDevice->notification_token)) {
                                    // Insert into notification.
                                    $notificationText = "Your offer has been rejected by the seller for product " . ucfirst($modelProduct->name) . " at " . Yii::$app->formatter->asCurrency($model->offer_amount);
                                    $action = "Reject";
                                    if ($offerData['MakeOffer']['status'] == MakeOffer::STATUS_ACCEPT) {
                                        $notificationText = "Your offer has been accepted by the seller for product " . ucfirst($modelProduct->name) . " at " . Yii::$app->formatter->asCurrency($model->offer_amount);
                                        $action = "Accept";
                                    }
                                    $modelNotification = new Notification();
                                    $modelNotification->owner_id = $model->receiver_id;
                                    $modelNotification->notification_receiver_id = $userROW->id;
                                    $modelNotification->ref_id = $model->id;
                                    $modelNotification->notification_text = $notificationText;
                                    $modelNotification->action = $action;
                                    $modelNotification->ref_type = "make_offer";
                                    $modelNotification->save(false);

                                    $badge = Notification::find()->where(['notification_receiver_id' => $userROW->id, 'is_read' => Notification::NOTIFICATION_IS_READ_NO])->count();
                                    if ($userDevice->device_platform == 'android') {
                                        $notificationToken = array($userDevice->notification_token);
                                        $senderName = $model->receiver->first_name . " " . $model->receiver->last_name;
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
                            }

                            if (!empty($userROW->email) && $userROW->is_offer_update_email_notification_on == User::IS_NOTIFICATION_ON) {
                                $message = "Your offer has been rejected by the seller for product " . ucfirst($modelProduct->name) . " at " . Yii::$app->formatter->asCurrency($model->offer_amount);
                                $subject = "Your product offer rejected by seller";
                                if ($offerData['MakeOffer']['status'] == MakeOffer::STATUS_ACCEPT) {
                                    $message = "Your offer has been accepted by the seller for product " . ucfirst($modelProduct->name) . " at " . Yii::$app->formatter->asCurrency($model->offer_amount);
                                    $subject = "Your product offer accepted by seller";
                                }

                                if (!empty($userROW->email)) {
                                    try {
                                        Yii::$app->mailer->compose('api/makeOfferAcceptReject', ['sender' => $model->receiver, 'receiver' => $userROW, 'message' => $message])
                                            ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
                                            ->setTo($userROW->email)
                                            ->setSubject($subject)
                                            ->send();
                                    } catch (Exception $e) {
                                        echo "Error: " . $e->getMessage();
                                    }
                                }
                            }
                        }
                    }
                }
            }
            // Send Push Notification End

            $model->save(false);
        }
        return $model;
    }

    /**
     * Deletes an existing MakeOffer model.
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
     * Finds the MakeOffer model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return MakeOffer the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = MakeOffer::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
