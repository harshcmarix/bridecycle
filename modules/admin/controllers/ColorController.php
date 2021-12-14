<?php

namespace app\modules\admin\controllers;

use app\models\Notification;
use app\models\Product;
use app\modules\api\v2\models\User;
use kartik\growl\Growl;
use Yii;
use app\models\Color;
use app\models\search\ColorSearch;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

/**
 * ColorController implements the CRUD actions for Color model.
 */
class ColorController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'view', 'create', 'update', 'delete'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'create', 'update', 'delete'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Color models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ColorSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $modelColor = new Color();
        $arrStatus = $modelColor->arrStatus;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'arrStatus' => $arrStatus,
        ]);
    }

    /**
     * Displays a single Color model.
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
     * Creates a new Color model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Color();
        $model->scenario = Color::SCENARIO_ADD_COLOR;
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            \Yii::$app->getSession()->setFlash(Growl::TYPE_SUCCESS, 'Color created successfully.');
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Color model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = Color::SCENARIO_UPDATE_COLOR;
        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            if (in_array($model->status, [Color::STATUS_APPROVE, Color::STATUS_DECLINE])) {

                $colorIds = [$model->id];
                $query = Product::find();
                if (!empty($colorIds)) {
                    foreach ($colorIds as $keyColor => $colorRow) {
                        if ($keyColor > 0) {
                            $query->orFilterWhere([
                                'or',
                                ['OR LIKE', 'products.option_color', $colorRow, false],
                            ]);
                        } else {
                            $query->andFilterWhere([
                                'or',
                                ['LIKE', 'products.option_color', $colorRow, false],
                            ]);
                        }
                    }
                }
                $modelProductsBasedOnColor = $query->all();

                if (!empty($modelProductsBasedOnColor)) {
                    foreach ($modelProductsBasedOnColor as $keyProd => $modelProductsBasedOnColorRow) {
                        if (!empty($modelProductsBasedOnColorRow) && $modelProductsBasedOnColorRow instanceof Product) {
                            $userModel = $modelProductsBasedOnColorRow->user;

                            $actionStatus = ($model->status == Color::STATUS_APPROVE) ? 'approve' : 'decline';

                            // Send push notification Start
                            $getUsers[] = $userModel;
                            if (!empty($getUsers)) {
                                foreach ($getUsers as $userROW) {
                                    if ($userROW instanceof User && (Yii::$app->user->identity->id != $userROW->id)) {
                                        $notificationText = "";
                                        if (!empty($userROW->userDevice)) {
                                            $userDevice = $userROW->userDevice;

                                            if (!empty($userDevice) && !empty($userDevice->notification_token)) {
                                                // Insert into notification.
                                                $notificationText = "Color has been " . $actionStatus . "d, Which you have selected for your product.";
                                                $modelNotification = new Notification();
                                                $modelNotification->owner_id = Yii::$app->user->identity->id;
                                                $modelNotification->notification_receiver_id = $userROW->id;
                                                $modelNotification->ref_id = $modelProductsBasedOnColorRow->id;
                                                $modelNotification->notification_text = $notificationText;
                                                $modelNotification->action = "product_color_" . $actionStatus;
                                                $modelNotification->ref_type = "products";
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
                            }
                            // Send push notification End

                            // Send Email notification Start
                            if (!empty($userModel) && $userModel instanceof User && !empty($userModel->email)) {
                                try {
                                    $message = "Color has been " . $actionStatus . "d, that has been added by you.";
                                    Yii::$app->mailer->compose('admin/general-info-send-to-user-html', ['userModel' => $userModel, 'message' => $message])
                                        ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
                                        ->setTo($userModel->email)
                                        ->setSubject('Color ' . $actionStatus . 'd!')
                                        ->send();
                                } catch (HttpException $e) {
                                    echo "Error: " . $e->getMessage();
                                }
                            }
                            // Send Email notification End
                        }
                    }
                }
            }

            \Yii::$app->getSession()->setFlash(Growl::TYPE_SUCCESS, 'Color updated successfully.');
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Color model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        \Yii::$app->getSession()->setFlash(Growl::TYPE_SUCCESS, 'Color deleted successfully.');
        return $this->redirect(['index']);
    }

    /**
     * Finds the Color model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Color the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Color::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
