<?php

namespace app\modules\api\v1\controllers;

use app\models\Notification;
use app\models\ProductRating;
use app\modules\api\v1\models\User;
use Yii;
use app\models\SellerRating;
use app\modules\api\v1\models\search\SellerRatingSearch;
use yii\filters\auth\{
    HttpBasicAuth,
    CompositeAuth,
    HttpBearerAuth,
    QueryParamAuth
};
use yii\base\BaseObject;
use yii\filters\Cors;
use yii\rest\ActiveController;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;


/**
 * SellerRatingController implements the CRUD actions for SellerRating model.
 */
class SellerRatingController extends ActiveController
{
    /**
     * @var string
     */
    public $modelClass = 'app\models\SellerRating';

    /**
     * @var string
     */
    public $searchModelClass = 'app\modules\api\v1\models\search\SellerRatingSearch';

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
     * Displays a single SellerRating model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = SellerRating::find()->where(['seller_id' => $id])->all();

        $modelSeller = User::findOne($id);
        if (!$modelSeller instanceof User) {
            throw new NotFoundHttpException('Seller doesn\'t exist.');
        }

        if (empty($model)) {
            throw new NotFoundHttpException('Seller rating doesn\'t exist.');
        }

        $totalRatings = count($model);

        $ratings = [];
        $i = $j = $k = $l = $m = $sumRatings = $rating = 0;
        foreach ($model as $dataRatings) {
            if ($dataRatings->rate == 5) {
                $i++;
            }
            if ($dataRatings->rate == 4) {
                $j++;
            }
            if ($dataRatings->rate == 3) {
                $k++;
            }
            if ($dataRatings->rate == 2) {
                $l++;
            }
            if ($dataRatings->rate == 1) {
                $m++;
            }
            $sumRatings += $dataRatings->rate;
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
     * Creates a new SellerRating model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new SellerRating();
        $postData = Yii::$app->request->post();


        if (empty($postData) || empty($postData['seller_id'])) {
            throw new BadRequestHttpException('Invalid parameter passed. Request must required parameter "seller_id"');
        }

        $sellerRating['SellerRating'] = $postData;
        $sellerRating['SellerRating']['user_id'] = Yii::$app->user->identity->id;
        //$sellerRating['SellerRating']['status'] = ProductRating::STATUS_PENDING;
        $alreadyExist = SellerRating::find()->where(['seller_id' => $sellerRating['SellerRating']['seller_id'], 'user_id' => $sellerRating['SellerRating']['user_id']])->all();
        if (!empty($alreadyExist)) {
            throw new ForbiddenHttpException('You have already reviewed this seller');
        }
        if ($model->load($sellerRating) && $model->validate()) {
            $model->save();


            // Send Push notification start
            $getUsers[] = $model->seller;

            if (!empty($getUsers)) {
                foreach ($getUsers as $keys => $userROW) {
                    if ($userROW instanceof User) {
                        if ($userROW->is_new_message_notification_on == User::IS_NOTIFICATION_ON && !empty($userROW->userDevice)) {
                            $userDevice = $userROW->userDevice;

                            // Insert into notification.
                            $notificationText = $model->user->first_name . " " . $model->user->last_name . " has added rate or review for your profile";
                            $modelNotification = new Notification();
                            $modelNotification->owner_id = $model->user_id;
                            $modelNotification->notification_receiver_id = $userROW->id;
                            $modelNotification->ref_id = $model->id;
                            $modelNotification->notification_text = $notificationText;
                            $modelNotification->action = "Add";
                            $modelNotification->ref_type = "seller_ratings"; // For seller rate review
                            //$modelNotification->created_at = time();
                            $modelNotification->save(false);

                            $badge = Notification::find()->where(['notification_receiver_id' => $userROW->id, 'is_read' => Notification::NOTIFICATION_IS_READ_NO])->count();
                            if ($userDevice->device_platform == 'android') {
                                $notificationToken = array($userDevice->notification_token);
                                $senderName = $model->user->first_name . " " . $model->user->last_name;
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

                        if ($userROW->is_new_message_email_notification_on == User::IS_NOTIFICATION_ON) {
                            $message = $model->user->first_name . " " . $model->user->last_name . " has added rate or review for your profile";
                            if (!empty($userROW->email)) {
                                Yii::$app->mailer->compose('api/addNewProfileRateReview', ['sender' => $model->user, 'receiver' => $userROW, 'message' => $message])
                                    ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
                                    ->setTo($userROW->email)
                                    ->setSubject('Added rate or review for your profile!')
                                    ->send();
                            }

                        }
                    }
                }
            }
            // Send Push notification end


        }

        return $model;
    }

    /**
     * Updates an existing SellerRating model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing SellerRating model.
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
     * Finds the SellerRating model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SellerRating the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SellerRating::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
