<?php

namespace app\modules\api\v1\controllers;

use app\models\Notification;
use app\modules\api\v1\models\User;
use Yii;
use app\models\ChatHistory;
use yii\filters\auth\{
    CompositeAuth,
    HttpBasicAuth,
    HttpBearerAuth,
    QueryParamAuth
};

use yii\base\BaseObject;
use yii\filters\Cors;
use yii\imagine\Image;
use yii\rest\ActiveController;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * ChatHistoryController implements the CRUD actions for ChatHistory model.
 */
class ChatHistoryController extends ActiveController
{
    /**
     * @var string
     */
    public $modelClass = 'app\models\ChatHistory';

    /**
     * @var string
     */
    public $searchModelClass = 'app\modules\api\v1\models\search\ChatHistorySearch';

    /**
     * @return array
     */
    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD', 'OPTIONS'],
            'create' => ['POST', 'OPTIONS'],
            'chat-detail' => ['POST', 'OPTIONS'],
//            'delete' => ['POST', 'DELETE'],
//            'update' => ['PUT', 'PATCH'],
            //'view' => ['GET', 'HEAD', 'OPTIONS'],
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
            //'only' => ['index', 'view', 'create', 'update', 'delete',],
            'only' => ['index', 'chat-detail', 'create',],
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
//        unset($actions['update']);
        //       unset($actions['view']);
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

    public function actionChatDetail()
    {
        $post = Yii::$app->request->post();
        if (empty($post) || empty($post['product_id'])) {
            throw new BadRequestHttpException('Invalid parameter passed. Request must required parameter "product_id"');
        }

        if (empty($post) || empty($post['to_user_id'])) {
            throw new BadRequestHttpException('Invalid parameter passed. Request must required parameter "to_user_id"');
        }

        $userModel = User::find()->where(["id" => Yii::$app->user->identity->id])->one();
        $toUserModel = User::find()->where(["id" => $post['to_user_id']])->one();
        if (!empty($userModel) && !empty($toUserModel)) {

            $fromOtherUserId = $toUserModel->id;
            $from_id = $userModel->id;
            $to_id = $post['to_user_id'];
            $product_id = $post['product_id'];
            $messageId = !empty($params['message_id']) ? $params['message_id'] : "";
            $data = [];
            $query = ChatHistory::find()->where("((from_user_id = $from_id AND to_user_id = $to_id) OR (to_user_id = $from_id AND from_user_id = $to_id)) AND chat_type = 'single' AND product_id = $product_id");

            $query->limit(ChatHistory::PAGE_LIMIT);
            if (!empty($params['type'])) {
                if ($params['type'] == 'new') {
                    if (!empty($messageId)) {
                        $query->andWhere("id > '$messageId'");
                        $query->orderBy("id ASC");
                    } else {
                        $query->orderBy("id DESC");
                    }
                } else if ($params['type'] == 'old' && !empty($messageId)) {
                    $query->andWhere("id < '$messageId'");
                    $query->orderBy("id DESC");
                }
            } else {
                $query->orderBy("id ASC");
            }

            $chatHistory = $query->all();

            // Messages mark as read
            $modelChatHistory = new ChatHistory();
            //$unreadMessages = ChatReadUnreadMessage::find()->where(['from_id' => $fromOtherUserId, 'to_id' => $userModel->id, 'chat_type' => ChatReadUnreadMessage::CHAT_TYPE_SINGLE, 'is_read' => ChatReadUnreadMessage::IS_UNREAD])->all();
            $unreadMessages = $modelChatHistory->getOneToOneUnreadNotificationRecord($fromOtherUserId, $userModel->id);

            //$unreadMessageCount = ChatReadUnreadMessage::find()->where(['from_id' => $fromOtherUserId, 'to_id' => $userModel->id, 'chat_type' => ChatReadUnreadMessage::CHAT_TYPE_SINGLE, 'is_read' => ChatReadUnreadMessage::IS_UNREAD])->count();
            $unreadMessageCount = $modelChatHistory->getOneToOneUnreadNotificationCount($fromOtherUserId, $userModel->id);

            if (!empty($unreadMessages) && !empty($unreadMessageCount)) {
                foreach ($unreadMessages as $unreadMessageRow) {
                    if (!empty($unreadMessageRow) && $unreadMessageRow instanceof ChatHistory) {
                        $unreadMessageRow->is_read = ChatHistory::IS_READ;
                        $unreadMessageRow->save(false);
                    }
                }
            }

            if (!empty($chatHistory)) {
                if (!empty($params['type']) && (($params['type'] == 'old' && !empty($messageId)) || ($params['type'] == 'new' && empty($messageId)))) {
                    $chatHistory = array_reverse($chatHistory);
                }
                foreach ($chatHistory as $key => $message) {

//                    if ($message->message_type == 'video') {
//                        $data[$key]['media_url'] = Yii::getAlias('@apiImagesRoot') . Yii::getAlias('@chatMediaAbsolutePath') . '/' . $message->message;
//                        $data[$key]['media_thumb_url'] = Yii::getAlias('@apiImagesRoot') . Yii::getAlias('@chatMediaThumbAbsolutePath') . '/' . $message->message . '.jpg';
//                    } else if ($message->message_type == 'image') {
//                        $data[$key]['media_url'] = Yii::getAlias('@apiImagesRoot') . Yii::getAlias('@chatMediaAbsolutePath') . '/' . $message->message;
//                        $data[$key]['media_thumb_url'] = "";
//                    } else {
//                        $data[$key]['media_url'] = "";
//                        $data[$key]['media_thumb_url'] = "";
//                    }

                    if (!empty($message) && !empty($message->message_type) && in_array($message->message_type, [ChatHistory::MESSAGE_TYPE_IMAGE, ChatHistory::MESSAGE_TYPE_VIDEO])) {
                        $chatHistory[$key]['message'] = (!empty($message) && !empty($message->message)) ? Yii::$app->request->getHostInfo() . Yii::getAlias('@chatMediaThumbAbsolutePath') . '/' . $message->message : "";
                    } else {
                        $chatHistory[$key]['message'] = (!empty($message) && !empty($message->message)) ? $message->message : "";
                    }
                }
            }
            return $chatHistory;
        } else {
            throw new \yii\web\BadRequestHttpException("User does not exist.");
        }
    }

    /**
     * Displays a single ChatHistory model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
//    public function actionView($id)
//    {
//        return $this->render('view', [
//            'model' => $this->findModel($id),
//        ]);
//    }

    /**
     * Creates a new ChatHistory model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ChatHistory();

        $post = Yii::$app->request->post();
        $postChatHistory['ChatHistory'] = $post;
        $postChatHistory['ChatHistory']['from_user_id'] = Yii::$app->user->identity->id;
        $imgFile = UploadedFile::getInstanceByName('file');
        $postChatHistory['ChatHistory']['file'] = $imgFile;
        if ($model->load($postChatHistory) && $model->validate()) {

            if (!empty($imgFile) && in_array($model->message_type, [ChatHistory::MESSAGE_TYPE_IMAGE, ChatHistory::MESSAGE_TYPE_VIDEO])) {
                // file upload
                $uploadDirPath = Yii::getAlias('@chatMediaRelativePath');
                $uploadThumbDirPath = Yii::getAlias('@chatMediaThumbRelativePath');

                if ($imgFile instanceof UploadedFile) {
                    // Create profile upload directory if not exist
                    if (!is_dir($uploadDirPath)) {
                        mkdir($uploadDirPath, 0777);
                    }

                    // Create profile thumb upload directory if not exist
                    if (!is_dir($uploadThumbDirPath)) {
                        mkdir($uploadThumbDirPath, 0777);
                    }
                    $ext = $imgFile->extension;
                    $fileName = pathinfo(str_replace(" ", "", $imgFile->name), PATHINFO_FILENAME);
                    $fileName = $fileName . '_' . time() . '.' . $ext;

                    // Upload file
                    $imgFile->saveAs($uploadDirPath . '/' . $fileName);
                    //chmod($uploadDirPath . '/' . $fileName, 0777);
                    // Create thumb of file
                    $actualImagePath = $uploadDirPath . '/' . $fileName;
                    $thumbImagePath = $uploadThumbDirPath . '/' . $fileName;
                    Image::thumbnail($actualImagePath, Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height'])->save($thumbImagePath, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
                    //chmod($thumbImagePath, 0777);
                    // Insert file name into database
                    $model->message = $fileName;
                }
            }

            $model->save(false);

            // Send Push notification start
            $getUsers[] = $model->toUser;
            if (!empty($getUsers)) {
                foreach ($getUsers as $userROW) {
                    if ($userROW instanceof User) {
                        if ($userROW->is_new_message_notification_on == User::IS_NOTIFICATION_ON && !empty($userROW->userDevice)) {
                            $userDevice = $userROW->userDevice;

                            // Insert into notification.
                            //$notificationText = $userROW->first_name . " " . $userROW->last_name . " has sent new message";
                            $notificationText = $model->message;
                            $modelNotification = new Notification();
                            $modelNotification->owner_id = $model->from_user_id;
                            $modelNotification->notification_receiver_id = $userROW->id;
                            $modelNotification->ref_id = $model->id;
                            $modelNotification->notification_text = $notificationText;
                            $modelNotification->action = "Add";
                            $modelNotification->ref_type = "chat_history";
                            //$modelNotification->created_at = time();
                            $modelNotification->save(false);

                            $badge = Notification::find()->where(['notification_receiver_id' => $userROW->id, 'is_read' => Notification::NOTIFICATION_IS_READ_NO])->count();
                            if ($userDevice->device_platform == 'android') {
                                $notificationToken = array($userDevice->notification_token);
                                $senderName = $model->fromUser->first_name . " " . $model->fromUser->last_name;
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
                    }
                }
            }
            // Send Push notification end


            if (!empty($model) && !empty($model->message_type) && in_array($model->message_type, [ChatHistory::MESSAGE_TYPE_IMAGE, ChatHistory::MESSAGE_TYPE_VIDEO])) {
                $imgFile = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                if (!empty($model->message) && file_exists(Yii::getAlias('@chatMediaThumbRelativePath') . '/' . $model->message)) {
                    $imgFile = Yii::$app->request->getHostInfo() . Yii::getAlias('@chatMediaThumbAbsolutePath') . '/' . $model->message;
                }
                $model->message = $imgFile;
            } else {
                $model->message = (!empty($model) && !empty($model->message)) ? $model->message : "";
            }
        }

        return $model;
    }

    /**
     * Updates an existing ChatHistory model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
//    public function actionUpdate($id)
//    {
//        $model = $this->findModel($id);
//
//        if ($model->load(Yii::$app->request->post()) && $model->save()) {
//            return $this->redirect(['view', 'id' => $model->id]);
//        }
//
//        return $this->render('update', [
//            'model' => $model,
//        ]);
//    }

    /**
     * Deletes an existing ChatHistory model.
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
     * Finds the ChatHistory model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ChatHistory the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ChatHistory::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
