<?php

namespace app\modules\api\v1\controllers;

use app\models\Notification;
use app\models\Product;
use app\modules\api\v1\models\User;
use Yii;
use app\models\Trial;
use yii\base\BaseObject;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\auth\{
    HttpBasicAuth,
    CompositeAuth,
    HttpBearerAuth,
    QueryParamAuth
};
use yii\filters\Cors;
use yii\rest\ActiveController;

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
    public $searchModelClass = 'app\modules\api\v1\models\search\TrialSearch';


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
            //'delete' => ['POST', 'DELETE'],
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
            'only' => ['index', 'view', 'create', 'update', 'get-trial-list-seller', 'get-trial-list-user'], //, 'delete'
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

        $post = Yii::$app->request->post();
        $postData['Trial'] = Yii::$app->request->post();

        $modelProduct = Product::findOne($postData['Trial']['product_id']);

        $postData['Trial']['sender_id'] = Yii::$app->user->identity->id;
        $postData['Trial']['receiver_id'] = (!empty($modelProduct) && !empty($modelProduct->user_id)) ? $modelProduct->user_id : "";

        if ($model->load($postData) && $model->validate()) {
            if ($model->save()) {

                // Send Push notification and email notification start
                $getUsers[] = $model->receiver;
                if (!empty($getUsers)) {
                    foreach ($getUsers as $userROW) {
                        if ($userROW instanceof User) {
                            if ($userROW->is_click_and_try_notification_on == User::IS_NOTIFICATION_ON && !empty($userROW->userDevice)) {
                                $userDevice = $userROW->userDevice;

                                // Insert into notification.
                                $notificationText = $model->name . "has create a request for trial of " . $modelProduct->name . " on date" . $model->date . " at " . $model->time;
                                $modelNotification = new Notification();
                                $modelNotification->owner_id = Yii::$app->user->identity->id;
                                $modelNotification->notification_receiver_id = $userROW->id;
                                $modelNotification->ref_id = $model->id;
                                $modelNotification->notification_text = $notificationText;
                                $modelNotification->action = "Add";
                                $modelNotification->ref_type = "trial_book";
                                $modelNotification->save(false);

                                $badge = Notification::find()->where(['notification_receiver_id' => $userROW->id, 'is_read' => Notification::NOTIFICATION_IS_READ_NO])->count();
                                if ($userDevice->device_platform == 'android') {
                                    $notificationToken = array($userDevice->notification_token);
                                    $senderName = Yii::$app->user->identity->first_name . " " . Yii::$app->user->identity->last_name;
                                    $modelNotification->sendPushNotificationAndroid($modelNotification->ref_id, $modelNotification->ref_type, $notificationToken, $notificationText, $senderName);
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
                                        ]);
                                    $response = Yii::$app->fcm->send($message);
                                }
                            }

                            if ($userROW->is_click_and_try_email_notification_on == User::IS_NOTIFICATION_ON) {
                                $message = $model->name . "has create a request for trial of " . $modelProduct->name . " on date" . $model->date . " at " . $model->time;

                                if (!empty($userROW->email)) {
                                    Yii::$app->mailer->compose('api/addNewTrialBooking', ['sender' => $userROW, 'receiver' => $model->receiver, 'product' => $modelProduct, 'message' => $message, 'model' => $model])
                                        ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
                                        ->setTo($userROW->email)
                                        ->setSubject('Request for trial of your product')
                                        ->send();
                                }


                            }
                        }
                    }
                }
                // Send Push notification and email notification start

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

        $model->scenario = Trial::SCENARIO_ACCEPT_REJECT;

        if (!$model instanceof Trial) {
            throw new NotFoundHttpException('Trial doesn\'t exist.');
        }

        $postData = Yii::$app->request->post();
        $trialPostData['Trial'] = $postData;

        $modelProduct = Product::findOne($model->product_id);

        if ($model->load($trialPostData) && $model->validate()) {
            if ($model->save(false)) {

                if ($model->status == Trial::STATUS_ACCEPT) {
                    $action = "accept_trial";
                    $notificationText = $modelProduct->name . "'s" . " seller has accepted your request for trial of " . $modelProduct->name . " on date" . $model->date . " at " . $model->time;
                } elseif ($model->status == Trial::STATUS_REJECT) {
                    $notificationText = $modelProduct->name . "'s" . " seller has rejected your request for trial of " . $modelProduct->name . " on date" . $model->date . " at " . $model->time;
                    $action = "reject_trial";
                } else {
                    $notificationText = $action = "";
                }
                // Send Push notification and email notification start
                $getUsers[] = $model->sender;

                if (!empty($getUsers) && !empty($notificationText)) {
                    foreach ($getUsers as $userROW) {
                        if ($userROW instanceof User) {
                            if ($userROW->is_click_and_try_notification_on == User::IS_NOTIFICATION_ON && !empty($userROW->userDevice)) {
                                $userDevice = $userROW->userDevice;

                                // Insert into notification.
                                $modelNotification = new Notification();
                                $modelNotification->owner_id = Yii::$app->user->identity->id;
                                $modelNotification->notification_receiver_id = $userROW->id;
                                $modelNotification->ref_id = $model->id;
                                $modelNotification->notification_text = $notificationText;
                                $modelNotification->action = $action;
                                $modelNotification->ref_type = "trial_book";
                                $modelNotification->save(false);

                                $badge = Notification::find()->where(['notification_receiver_id' => $userROW->id, 'is_read' => Notification::NOTIFICATION_IS_READ_NO])->count();
                                if ($userDevice->device_platform == 'android') {
                                    $notificationToken = array($userDevice->notification_token);
                                    $senderName = Yii::$app->user->identity->first_name . " " . Yii::$app->user->identity->last_name;
                                    $modelNotification->sendPushNotificationAndroid($modelNotification->ref_id, $modelNotification->ref_type, $notificationToken, $notificationText, $senderName);
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
                                        ]);
                                    $response = Yii::$app->fcm->send($message);
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
                                    Yii::$app->mailer->compose('api/addNewTrialBooking', ['sender' => $userROW, 'receiver' => $model->sender, 'product' => $modelProduct, 'message' => $message, 'model' => $model])
                                        ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
                                        ->setTo($userROW->email)
                                        ->setSubject('Request for trial has ' . $isAccept)
                                        ->send();
                                }
                            }
                        }
                    }
                }
                // Send Push notification and email notification start
            }
        }
        $model = Trial::findOne($id);
        return $model;
    }

    /**
     * Deletes an existing Trial model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
//    public function actionDelete($id)
//    {
//        $this->findModel($id)->delete();
//
//        return $this->redirect(['index']);
//    }

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

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     * @throws BadRequestHttpException
     */
    public function actionGetTrialListSeller()
    {
        $postData = Yii::$app->request->post();

        if (empty($postData) || empty($postData['receiver_id'])) {
            throw new BadRequestHttpException('Invalid parameter passed. Request must required parameter "receiver_id"');
        }

        $models = Trial::find()->where(['receiver_id' => $postData['receiver_id']])->all();
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
            throw new BadRequestHttpException('Invalid parameter passed. Request must required parameter "sender_id"');
        }

        $models = Trial::find()->where(['sender_id' => $postData['sender_id']])->all();
        return $models;
    }
}
