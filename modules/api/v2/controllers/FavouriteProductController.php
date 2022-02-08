<?php

namespace app\modules\api\v2\controllers;

use app\models\FavouriteProduct;
use app\models\Notification;
use app\modules\api\v2\models\User;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\Cors;
use yii\rest\ActiveController;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

/**
 * FavouriteProductController implements the CRUD actions for FavouriteProduct model.
 */
class FavouriteProductController extends ActiveController
{
    /**
     * @var string
     */
    public $modelClass = 'app\models\FavouriteProduct';

    /**
     * @var string
     */
    public $searchModelClass = 'app\modules\api\v2\models\search\FavouriteProductSearch';

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
            'only' => ['index-list', 'view', 'create', 'update', 'delete'],
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
        unset($actions['update']);
        unset($actions['create']);
        unset($actions['view']);
        unset($actions['delete']);

        return $actions;
    }

    /**
     * Lists all FavouriteProduct models.
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
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIndexList()
    {
        $model = new $this->searchModelClass;
        $requestParams = Yii::$app->getRequest()->getBodyParams();

        if (empty($requestParams)) {
            $requestParams = Yii::$app->getRequest()->getQueryParams();
        }
        return $model->search($requestParams, Yii::$app->user->identity->id);
    }

    /**
     * Creates a new SearchHistory model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new FavouriteProduct();
        $postData = Yii::$app->request->post();
        $favouriteProduct['FavouriteProduct'] = $postData;
        $favouriteProduct['FavouriteProduct']['user_id'] = Yii::$app->user->identity->id;

        $modelFavourite = FavouriteProduct::find()->where(['product_id' => $favouriteProduct['FavouriteProduct']['product_id'], 'user_id' => Yii::$app->user->identity->id])->one();
        if (!empty($modelFavourite)) {
            throw new BadRequestHttpException(getValidationErrorMsg('product_already_favourited', Yii::$app->language));
        }
        if ($model->load($favouriteProduct) && $model->validate()) {

            if ($model->save()) {

                // Send Push notification start
                $getUsers[] = $model->product->user;

                if (!empty($getUsers)) {
                    foreach ($getUsers as $keys => $userROW) {
                        if ($userROW instanceof User && ($model->user_id != $userROW->id)) {
                            if (!empty($userROW->userDevice)) {
                                $userDevice = $userROW->userDevice;

                                // Insert into notification.
                                $notificationText = Yii::$app->user->identity->first_name . " " . Yii::$app->user->identity->last_name . " has liked your product";
                                $modelNotification = new Notification();
                                $modelNotification->owner_id = $model->user_id;
                                $modelNotification->notification_receiver_id = $userROW->id;
                                $modelNotification->ref_id = $model->id;
                                $modelNotification->notification_text = $notificationText;
                                $modelNotification->action = "Add";
                                $modelNotification->ref_type = "product_favourite"; // For Product favourite
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
                    }
                }
                // Send Push notification end
            }
        }

        return $model;
    }

    /**
     * @param $id
     * @return false|int
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $model = FavouriteProduct::find()->where(['id' => $id])->one();

        if (!$model instanceof FavouriteProduct) {
            throw new NotFoundHttpException(getValidationErrorMsg('favourite_product_not_exist', Yii::$app->language));
        }
        $model->delete();
    }

}