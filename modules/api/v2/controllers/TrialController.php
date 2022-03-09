<?php

namespace app\modules\api\v2\controllers;

use app\models\Notification;
use app\models\Product;
use app\models\Timezone;
use app\models\Trial;
use app\modules\api\v2\models\User;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\Cors;
use yii\rest\ActiveController;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

/**
 * TrialController implements the CRUD actions for Trial model.
 */
class TrialController extends ActiveController
{
    /**
     * @var string
     */
    public $modelClass = 'app\models\Trial';

    /**
     * @var string
     */
    public $searchModelClass = 'app\modules\api\v2\models\search\TrialSearch';

    /**
     * @return array
     */
    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD', 'OPTIONS'],
            'view' => ['GET', 'HEAD', 'OPTIONS'],
            'create' => ['POST', 'OPTIONS'],
            'get-trial-list-seller' => ['POST', 'OPTIONS'],
            'get-trial-list-user' => ['POST', 'OPTIONS'],
            'update' => ['PUT', 'PATCH'],
            'get-timezone-list' => ['GET', 'HEAD', 'OPTIONS'],
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
            'only' => ['index', 'view', 'create', 'update', 'get-trial-list-seller', 'get-trial-list-user'], //, 'delete','get-timezone-list'
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
        unset($actions['view']);
        return $actions;
    }

    /**
     * Lists all Brand models.
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
     * Displays a single Trial model.
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
     * Creates a new Trial model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Trial();
        $postData['Trial'] = Yii::$app->request->post();

        $modelProduct = Product::findOne($postData['Trial']['product_id']);

        $postData['Trial']['sender_id'] = Yii::$app->user->identity->id;
        $postData['Trial']['receiver_id'] = (!empty($modelProduct) && !empty($modelProduct->user_id)) ? $modelProduct->user_id : "";

        if ($model->load($postData) && $model->validate()) {
            $model->status = Trial::STATUS_PENDING;

            // Check seller has accepted/rejected trial booking if no then it throw exception start.
            $createdTrialsData = Trial::find()
                ->where('trials.sender_id=' . Yii::$app->user->identity->id)
                ->andWhere('trials.receiver_id=' . $modelProduct->user_id)
                ->andWhere('trials.product_id=' . $modelProduct->id)->all();

            if (!empty($createdTrialsData) && !empty($createdTrialsData[count($createdTrialsData) - 1])) {
                $data = $createdTrialsData[count($createdTrialsData) - 1];
                if (!empty($data) && $data instanceof Trial && $data->status == Trial::STATUS_PENDING) {
                    throw new HttpException(403, getValidationErrorMsg('trial_already_made', Yii::$app->language));
                }
            }
            // Check seller has accepted/rejected trial booking if no then it throw exception end.

            $resultData = $this->getTwoTimeZoneDifference($modelProduct->user->timezone_id, $model->timezone_id, $model->time);
            if ($resultData == true) {

                if ($model->save()) {

                    $getUtcTime = $this->getUTCTimeBasedOnTimeZone($model->timezone_id, $model->time);

                    if (!empty($getUtcTime)) {
                        $model->timezone_utc_time = (string)$getUtcTime;
                    }

                    $model->save(false);

                    // Send Push notification and email notification start
                    $getUsers[] = $model->receiver;
                    if (!empty($getUsers)) {
                        foreach ($getUsers as $userROW) {
                            if ($userROW instanceof User && (Yii::$app->user->identity->id != $userROW->id)) {
                                $notificationText = "";
                                if ($userROW->is_click_and_try_notification_on == User::IS_NOTIFICATION_ON && !empty($userROW->userDevice)) {
                                    $userDevice = $userROW->userDevice;

                                    if (!empty($userDevice) && !empty($userDevice->notification_token)) {
                                        // Insert into notification.
                                        $notificationText = $model->name . " has create a request for trial of " . $modelProduct->name . " on date" . $model->date . " at " . $model->time;
                                        $modelNotification = new Notification();
                                        $modelNotification->owner_id = Yii::$app->user->identity->id;
                                        $modelNotification->notification_receiver_id = $userROW->id;
                                        $modelNotification->ref_id = $model->id;
                                        $modelNotification->notification_text = $notificationText;
                                        $modelNotification->action = "Add";
                                        $modelNotification->ref_type = "trial_book";
                                        $modelNotification->product_id = $modelProduct->id;
                                        $modelNotification->save(false);

                                        $badge = Notification::find()->where(['notification_receiver_id' => $userROW->id, 'is_read' => Notification::NOTIFICATION_IS_READ_NO])->count();
                                        if ($userDevice->device_platform == 'android') {
                                            $notificationToken = array($userDevice->notification_token);
                                            $senderName = Yii::$app->user->identity->first_name . " " . Yii::$app->user->identity->last_name;
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

                                if ($userROW->is_click_and_try_email_notification_on == User::IS_NOTIFICATION_ON) {
                                    $message = $model->name . "has create a request for trial of " . $modelProduct->name . " on date" . $model->date . " at " . $model->time;
                                    if (!empty($userROW->email)) {
                                        try {
                                            Yii::$app->mailer->compose('api/addNewTrialBooking', ['sender' => Yii::$app->user->identity, 'receiver' => $userROW, 'product' => $modelProduct, 'message' => $message, 'model' => $model])
                                                ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
                                                ->setTo($userROW->email)
                                                ->setSubject('Request for trial of your product')
                                                ->send();
                                        } catch (HttpException $e) {
                                            echo "Error: " . $e->getMessage();
                                        }
                                    }
                                }

                            }
                        }
                    }
                    // Send Push notification and email notification end
                }
            } else {
                throw new HttpException(getValidationErrorMsg('select_another_timezone', Yii::$app->language));
            }
        }

        return $model;
    }

    /**
     * Updates an existing Trial model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = Trial::findOne($id);

        if (!$model instanceof Trial) {
            throw new NotFoundHttpException(getValidationErrorMsg('trial_not_exist', Yii::$app->language));
        }

        $model->scenario = Trial::SCENARIO_ACCEPT_REJECT;

        $postData = Yii::$app->request->post();
        $trialPostData['Trial'] = $postData;

        $modelProduct = Product::findOne($model->product_id);

        if ($model->load($trialPostData) && $model->validate()) {
            if ($model->save(false)) {

                $getUtcTime = $this->getUTCTimeBasedOnTimeZone($model->timezone_id, $model->time);
                if (!empty($getUtcTime)) {
                    $model->timezone_utc_time = $getUtcTime;
                }

                if ($model->status == Trial::STATUS_ACCEPT) {
                    $action = "accept_trial";
                    $notificationText = $modelProduct->name . "'s" . " seller has accepted your request for trial of " . $modelProduct->name . " on date " . $model->date . " at " . $model->time;
                } elseif ($model->status == Trial::STATUS_REJECT) {
                    $notificationText = $modelProduct->name . "'s" . " seller has rejected your request for trial of " . $modelProduct->name . " on date " . $model->date . " at " . $model->time;
                    $action = "reject_trial";
                } else {
                    $notificationText = $action = "";
                }
                // Send Push notification and email notification start
                $getUsers[] = $model->sender;

                if (!empty($getUsers) && !empty($notificationText)) {
                    foreach ($getUsers as $userROW) {
                        if ($userROW instanceof User && (Yii::$app->user->identity->id != $userROW->id)) {
                            if ($userROW->is_click_and_try_notification_on == User::IS_NOTIFICATION_ON && !empty($userROW->userDevice)) {
                                $userDevice = $userROW->userDevice;

                                if (!empty($userDevice) && !empty($userDevice->notification_token)) {
                                    // Insert into notification.
                                    $modelNotification = new Notification();
                                    $modelNotification->owner_id = Yii::$app->user->identity->id;
                                    $modelNotification->notification_receiver_id = $userROW->id;
                                    $modelNotification->ref_id = $model->id;
                                    $modelNotification->notification_text = $notificationText;
                                    $modelNotification->action = $action;
                                    $modelNotification->ref_type = "trial_book";
                                    $modelNotification->product_id = $modelProduct->id;
                                    $modelNotification->save(false);

                                    $badge = Notification::find()->where(['notification_receiver_id' => $userROW->id, 'is_read' => Notification::NOTIFICATION_IS_READ_NO])->count();
                                    if ($userDevice->device_platform == 'android') {
                                        $notificationToken = array($userDevice->notification_token);
                                        $senderName = Yii::$app->user->identity->first_name . " " . Yii::$app->user->identity->last_name;
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

                            if ($userROW->is_click_and_try_email_notification_on == User::IS_NOTIFICATION_ON) {
                                $message = $notificationText;

                                if (($model->status == Trial::STATUS_ACCEPT)) {
                                    $isAccept = 'accepted by seller';
                                } else {
                                    $isAccept = 'rejected by seller';
                                }

                                if (!empty($userROW->email)) {
                                    try {
                                        Yii::$app->mailer->compose('api/trialBookingAcceptReject', ['sender' => Yii::$app->user->identity, 'receiver' => $userROW, 'product' => $modelProduct, 'message' => $message, 'model' => $model])
                                            ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
                                            ->setTo($userROW->email)
                                            ->setSubject('Request for trial has ' . $isAccept)
                                            ->send();
                                    } catch (httpException $e) {
                                        echo "Error: " . $e->getMessage();
                                    }
                                }
                            }
                        }
                    }
                }
                // Send Push notification and email notification end
            }
        }
        $model = Trial::findOne($id);
        return $model;
    }

    /**
     * Finds the Trial model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Trial the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Trial::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException(getValidationErrorMsg('page_not_exist', Yii::$app->language));
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     * @throws BadRequestHttpException
     */
    public function actionGetTrialListSeller()
    {
        $postData = Yii::$app->request->post();

        if (empty($postData) || empty($postData['receiver_id'])) {
            throw new BadRequestHttpException(getValidationErrorMsg('receiver_id_required', Yii::$app->language));
        }
        $models = Trial::find()->where(['receiver_id' => $postData['receiver_id']])->orderBy(['created_at' => SORT_DESC])->all();
        return $models;
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     * @throws BadRequestHttpException
     */
    public function actionGetTrialListUser()
    {
        $postData = Yii::$app->request->post();

        if (empty($postData) || empty($postData['sender_id'])) {
            throw new BadRequestHttpException(getValidationErrorMsg('sender_id_required', Yii::$app->language));
        }
        $models = Trial::find()->where(['sender_id' => $postData['sender_id']])->orderBy(['created_at' => SORT_DESC])->all();
        return $models;
    }

    /**
     * @param $timezone_id
     * @param null $time
     * @return false|int
     * @throws NotFoundHttpException
     */
    public function getUTCTimeBasedOnTimeZone($timezone_id, $time = null)
    {
        $modelTimeZone = Timezone::findOne($timezone_id);

        if (!$modelTimeZone instanceof Timezone) {
            throw new NotFoundHttpException(getValidationErrorMsg('timezone_not_exist', Yii::$app->language));
        }

        date_default_timezone_set("$modelTimeZone->time_zone");

        $utcTime = date('H:i:s', strtotime($time . ' UTC'));
        return (string)$utcTime;
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     */
    public function actionGetTimezoneList()
    {
        $models = Timezone::find()->all();
        return $models;
    }

    /**
     * return bool
     */
    public function getTwoTimeZoneDifference($seller_timezone, $buyer_selected_timezone, $buyer_selected_time = null)
    {

        $modelTimeZoneSeller = Timezone::findOne($seller_timezone);

        if (!$modelTimeZoneSeller instanceof Timezone) {
            throw new NotFoundHttpException(getValidationErrorMsg('seller_timezone_not_exist', Yii::$app->language));
        }

        date_default_timezone_set("$modelTimeZoneSeller->time_zone");
        $utcTimeFromSeller = date('Y-m-d H:i:s', strtotime($buyer_selected_time . ' UTC'));
        $modelTimeZoneSelectedFromBuyer = Timezone::findOne($buyer_selected_timezone);

        if (!$modelTimeZoneSelectedFromBuyer instanceof Timezone) {
            throw new NotFoundHttpException(getValidationErrorMsg('buyer_timezone_not_exist', Yii::$app->language));
        }

        date_default_timezone_set("$modelTimeZoneSelectedFromBuyer->time_zone");
        $utcTimeFromBuyer = date('Y-m-d H:i:s', strtotime($buyer_selected_time . ' UTC'));

        $sellerTimeString = strtotime($utcTimeFromSeller);
        $buyerTimeString = strtotime($utcTimeFromBuyer);

        if (($sellerTimeString - $buyerTimeString) >= 0) {
            return true;
        }
        return false;
    }

}
