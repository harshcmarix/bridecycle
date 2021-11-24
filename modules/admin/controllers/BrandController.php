<?php

namespace app\modules\admin\controllers;

use app\models\Notification;
use app\models\Product;
use app\modules\api\v2\models\User;
use Imagine\Image\Box;
use Yii;
use app\models\Brand;
use app\models\search\BrandSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\web\Response;
use kartik\growl\Growl;
use Mpdf\Tag\Em;
use yii\imagine\Image;
use yii\filters\AccessControl;
use \yii\helpers\Json;


/**
 * BrandController implements the CRUD actions for Brand model.
 */
class BrandController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'new-brand', 'create', 'new-brand-create', 'update', 'new-brand-update', 'view', 'new-brand-view', 'delete', 'new-brand-delete', 'update-top-brand'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'new-brand', 'create', 'new-brand-create', 'update', 'new-brand-update', 'view', 'new-brand-view', 'delete', 'new-brand-delete', 'update-top-brand'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Brand models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BrandSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionNewBrand()
    {
        $searchModel = new BrandSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('new-brand', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Brand model.
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

    public function actionNewBrandView($id)
    {
        return $this->render('new-brand-view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Brand model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Brand();
        $model->scenario = Brand::SCENARIO_CREATE;
        $brand_image = UploadedFile::getInstance($model, 'image');

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return \yii\widgets\ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {

            if (!empty($brand_image)) {
                $uploadDirPath = Yii::getAlias('@brandImageRelativePath');
                $uploadThumbDirPath = Yii::getAlias('@brandImageThumbRelativePath');
                $thumbImagePath = '';

                // Create profile upload directory if not exist
                if (!is_dir($uploadDirPath)) {
                    mkdir($uploadDirPath, 0777);
                }

                // Create profile thumb upload directory if not exist
                if (!is_dir($uploadThumbDirPath)) {
                    mkdir($uploadThumbDirPath, 0777);
                }

                $ext = $brand_image->extension;
                $fileName = pathinfo($brand_image->name, PATHINFO_FILENAME);
                $fileName = time() . rand(99999, 88888) . '.' . $ext;
                // Upload profile picture
                $brand_image->saveAs($uploadDirPath . '/' . $fileName);
                // Create thumb of profile picture
                $actualImagePath = $uploadDirPath . '/' . $fileName;
                $thumbImagePath = $uploadThumbDirPath . '/' . $fileName;

                Image::getImagine()->open($actualImagePath)->thumbnail(new Box(Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height']))->save($thumbImagePath, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
                // Insert profile picture name into database
                $model->image = $fileName;

                $brandData = Yii::$app->request->post('Brand');
                $model->is_top_brand = (!empty($brandData['is_top_brand'])) ? $brandData['is_top_brand'] : Brand::NOT_TOP_BRAND;
                $model->status = $brandData['status'];
            }

            if ($model->save()) {
                Yii::$app->session->setFlash(Growl::TYPE_SUCCESS, "Brand created successfully.");
            } else {
                Yii::$app->session->setFlash(Growl::TYPE_DANGER, "Error while creating Brand.");
            }
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionNewBrandCreate()
    {
        $model = new Brand();
        $model->scenario = Brand::SCENARIO_CREATE;
        $brand_image = UploadedFile::getInstance($model, 'image');

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return \yii\widgets\ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {

            if (!empty($brand_image)) {
                $uploadDirPath = Yii::getAlias('@brandImageRelativePath');
                $uploadThumbDirPath = Yii::getAlias('@brandImageThumbRelativePath');
                $thumbImagePath = '';

                // Create profile upload directory if not exist
                if (!is_dir($uploadDirPath)) {
                    mkdir($uploadDirPath, 0777);
                }

                // Create profile thumb upload directory if not exist
                if (!is_dir($uploadThumbDirPath)) {
                    mkdir($uploadThumbDirPath, 0777);
                }

                $ext = $brand_image->extension;
                $fileName = pathinfo($brand_image->name, PATHINFO_FILENAME);
                $fileName = time() . rand(99999, 88888) . '.' . $ext;
                // Upload profile picture
                $brand_image->saveAs($uploadDirPath . '/' . $fileName);
                // Create thumb of profile picture
                $actualImagePath = $uploadDirPath . '/' . $fileName;
                $thumbImagePath = $uploadThumbDirPath . '/' . $fileName;

                Image::getImagine()->open($actualImagePath)->thumbnail(new Box(Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height']))->save($thumbImagePath, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
                // Insert profile picture name into database
                $model->image = $fileName;

                $brandData = Yii::$app->request->post('Brand');
                $model->is_top_brand = (!empty($brandData['is_top_brand'])) ? $brandData['is_top_brand'] : Brand::NOT_TOP_BRAND;
                $model->status = $brandData['status'];
            }

            if ($model->save()) {
                Yii::$app->session->setFlash(Growl::TYPE_SUCCESS, "New brand created successfully.");
            } else {
                Yii::$app->session->setFlash(Growl::TYPE_DANGER, "Error while creating new brand.");
            }
            return $this->redirect(['new-brand']);
        }

        return $this->render('new-brand-create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Brand model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $old_image = $model->image;

        $new_image = UploadedFile::getInstance($model, 'image');

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return \yii\widgets\ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {

            $brandData = Yii::$app->request->post('Brand');

            $model->is_top_brand = (!empty($brandData['is_top_brand'])) ? $brandData['is_top_brand'] : Brand::NOT_TOP_BRAND;
            if (!empty($brandData['status'])) {
                $model->status = $brandData['status'];
            }

            if (!empty($new_image)) {
                $uploadDirPath = Yii::getAlias('@brandImageRelativePath');
                $uploadThumbDirPath = Yii::getAlias('@brandImageThumbRelativePath');
                $thumbImagePath = '';

                // Create product image upload directory if not exist
                if (!is_dir($uploadDirPath)) {
                    mkdir($uploadDirPath, 0777);
                }
                //unlink real image if update
                if (file_exists($uploadDirPath . '/' . $old_image) && !empty($old_image)) {
                    unlink($uploadDirPath . '/' . $old_image);
                }
                // Create product image thumb upload directory if not exist
                if (!is_dir($uploadThumbDirPath)) {
                    mkdir($uploadThumbDirPath, 0777);
                }
                //unlink thumb image if update
                if (file_exists($uploadThumbDirPath . '/' . $old_image) && !empty($old_image)) {
                    unlink($uploadThumbDirPath . '/' . $old_image);
                }

                $ext = $new_image->extension;
                $fileName = pathinfo($new_image->name, PATHINFO_FILENAME);
                $fileName = time() . rand(99999, 88888) . '.' . $ext;
                // Upload profile picture
                $new_image->saveAs($uploadDirPath . '/' . $fileName);
                // Create thumb of profile picture
                $actualImagePath = $uploadDirPath . '/' . $fileName;
                $thumbImagePath = $uploadThumbDirPath . '/' . $fileName;

                Image::getImagine()->open($actualImagePath)->thumbnail(new Box(Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height']))->save($thumbImagePath, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
                // Insert profile picture name into database
                $model->image = $fileName;
            } else {
                $model->image = $old_image;
            }

            if ($model->save()) {

                if (in_array($model->status, [Brand::STATUS_APPROVE, Brand::STATUS_DECLINE])) {
                    $productsModel = Product::find()->where(['brand_id' => $model->id])->all();

                    if (!empty($productsModel)) {
                        foreach ($productsModel as $key => $productsModelRow) {
                            if (!empty($productsModelRow) && $productsModelRow instanceof Product) {

                                $actionStatus = ($model->status == Brand::STATUS_APPROVE) ? 'approve' : 'decline';

                                $userDataModel = $productsModelRow->user;

                                // Send push notification Start
                                $getUsers[] = $userDataModel;
                                if (!empty($getUsers)) {
                                    foreach ($getUsers as $userROW) {
                                        if ($userROW instanceof User && (Yii::$app->user->identity->id != $userROW->id)) {
                                            $notificationText = "";
                                            if (!empty($userROW->userDevice)) {
                                                $userDevice = $userROW->userDevice;

                                                if (!empty($userDevice) && !empty($userDevice->notification_token)) {
                                                    // Insert into notification.
                                                    $notificationText = "Brand has been " . $actionStatus . "d, Which you have selected for your product.";
                                                    $modelNotification = new Notification();
                                                    $modelNotification->owner_id = Yii::$app->user->identity->id;
                                                    $modelNotification->notification_receiver_id = $userROW->id;
                                                    $modelNotification->ref_id = $productsModelRow->id;
                                                    $modelNotification->notification_text = $notificationText;
                                                    $modelNotification->action = "product_brand_" . $actionStatus;
                                                    $modelNotification->ref_type = "products";
                                                    $modelNotification->save(false);

                                                    $badge = Notification::find()->where(['notification_receiver_id' => $userROW->id, 'is_read' => Notification::NOTIFICATION_IS_READ_NO])->count();
                                                    if ($userDevice->device_platform == 'android') {
                                                        $notificationToken = array($userDevice->notification_token);
                                                        $senderName = Yii::$app->user->identity->first_name . " " . Yii::$app->user->identity->last_name;
                                                        $modelNotification->sendPushNotificationAndroid($modelNotification->ref_id, $modelNotification->ref_type, $notificationToken, $notificationText, $senderName,$modelNotification);
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
                                if (!empty($userDataModel) && $userDataModel instanceof User && !empty($userDataModel->email)) {
                                    $message = "Brand has been " . $actionStatus . "d, Which you have selected for your product.";
                                    Yii::$app->mailer->compose('admin/general-info-send-to-user-html', ['userModel' => $userDataModel, 'message' => $message])
                                        ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
                                        ->setTo($userDataModel->email)
                                        ->setSubject('Brand ' . $actionStatus . 'd!')
                                        ->send();
                                }
                                // Send Email notification End
                            }
                        }
                    }
                }

                Yii::$app->session->setFlash(Growl::TYPE_SUCCESS, "Brand updated successfully.");
            } else {
                Yii::$app->session->setFlash(Growl::TYPE_DANGER, "Error while updating Brand.");
            }
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionNewBrandUpdate($id)
    {
        $model = $this->findModel($id);
        $old_image = $model->image;

        $new_image = UploadedFile::getInstance($model, 'image');

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return \yii\widgets\ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {

            $brandData = Yii::$app->request->post('Brand');

            $model->is_top_brand = (!empty($brandData['is_top_brand'])) ? $brandData['is_top_brand'] : Brand::NOT_TOP_BRAND;
            if (!empty($brandData['status'])) {
                $model->status = $brandData['status'];
            }

            if (!empty($new_image)) {
                $uploadDirPath = Yii::getAlias('@brandImageRelativePath');
                $uploadThumbDirPath = Yii::getAlias('@brandImageThumbRelativePath');
                $thumbImagePath = '';

                // Create product image upload directory if not exist
                if (!is_dir($uploadDirPath)) {
                    mkdir($uploadDirPath, 0777);
                }
                //unlink real image if update
                if (file_exists($uploadDirPath . '/' . $old_image) && !empty($old_image)) {
                    unlink($uploadDirPath . '/' . $old_image);
                }
                // Create product image thumb upload directory if not exist
                if (!is_dir($uploadThumbDirPath)) {
                    mkdir($uploadThumbDirPath, 0777);
                }
                //unlink thumb image if update
                if (file_exists($uploadThumbDirPath . '/' . $old_image) && !empty($old_image)) {
                    unlink($uploadThumbDirPath . '/' . $old_image);
                }

                $ext = $new_image->extension;
                $fileName = pathinfo($new_image->name, PATHINFO_FILENAME);
                $fileName = time() . rand(99999, 88888) . '.' . $ext;
                // Upload profile picture
                $new_image->saveAs($uploadDirPath . '/' . $fileName);
                // Create thumb of profile picture
                $actualImagePath = $uploadDirPath . '/' . $fileName;
                $thumbImagePath = $uploadThumbDirPath . '/' . $fileName;

                Image::getImagine()->open($actualImagePath)->thumbnail(new Box(Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height']))->save($thumbImagePath, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
                // Insert profile picture name into database
                $model->image = $fileName;
            } else {
                $model->image = $old_image;
            }

            if ($model->save()) {

                if (in_array($model->status, [Brand::STATUS_APPROVE, Brand::STATUS_DECLINE])) {
                    $productsModel = Product::find()->where(['brand_id' => $model->id])->all();

                    if (!empty($productsModel)) {
                        foreach ($productsModel as $key => $productsModelRow) {
                            if (!empty($productsModelRow) && $productsModelRow instanceof Product) {

                                $actionStatus = ($model->status == Brand::STATUS_APPROVE) ? 'approve' : 'decline';

                                $userDataModel = $productsModelRow->user;

                                // Send push notification Start
                                $getUsers[] = $userDataModel;
                                if (!empty($getUsers)) {
                                    foreach ($getUsers as $userROW) {
                                        if ($userROW instanceof User && (Yii::$app->user->identity->id != $userROW->id)) {
                                            $notificationText = "";
                                            if (!empty($userROW->userDevice)) {
                                                $userDevice = $userROW->userDevice;

                                                if (!empty($userDevice) && !empty($userDevice->notification_token)) {
                                                    // Insert into notification.
                                                    $notificationText = "Brand has been " . $actionStatus . "d, Which you have selected for your product.";
                                                    $modelNotification = new Notification();
                                                    $modelNotification->owner_id = Yii::$app->user->identity->id;
                                                    $modelNotification->notification_receiver_id = $userROW->id;
                                                    $modelNotification->ref_id = $productsModelRow->id;
                                                    $modelNotification->notification_text = $notificationText;
                                                    $modelNotification->action = "product_brand_" . $actionStatus;
                                                    $modelNotification->ref_type = "products";
                                                    $modelNotification->save(false);

                                                    $badge = Notification::find()->where(['notification_receiver_id' => $userROW->id, 'is_read' => Notification::NOTIFICATION_IS_READ_NO])->count();
                                                    if ($userDevice->device_platform == 'android') {
                                                        $notificationToken = array($userDevice->notification_token);
                                                        $senderName = Yii::$app->user->identity->first_name . " " . Yii::$app->user->identity->last_name;
                                                        $modelNotification->sendPushNotificationAndroid($modelNotification->ref_id, $modelNotification->ref_type, $notificationToken, $notificationText, $senderName,$modelNotification);
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
                                if (!empty($userDataModel) && $userDataModel instanceof User && !empty($userDataModel->email)) {
                                    $message = "Brand has been " . $actionStatus . "d, Which you have selected for your product.";
                                    Yii::$app->mailer->compose('admin/general-info-send-to-user-html', ['userModel' => $userDataModel, 'message' => $message])
                                        ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
                                        ->setTo($userDataModel->email)
                                        ->setSubject('Brand ' . $actionStatus . 'd!')
                                        ->send();
                                }
                                // Send Email notification End
                            }
                        }
                    }
                }

                Yii::$app->session->setFlash(Growl::TYPE_SUCCESS, "New brand updated successfully.");
            } else {
                Yii::$app->session->setFlash(Growl::TYPE_DANGER, "Error while updating new brand.");
            }
            return $this->redirect(['new-brand']);
        }

        return $this->render('new-brand-update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Brand model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $image = $model->image;
        $uploadDirPath = Yii::getAlias('@brandImageRelativePath');
        $uploadThumbDirPath = Yii::getAlias('@brandImageThumbRelativePath');
        // unlink images with thumb
        if (file_exists($uploadDirPath . '/' . $image) && !empty($image)) {
            unlink($uploadDirPath . '/' . $image);
        }
        if (file_exists($uploadThumbDirPath . '/' . $image) && !empty($image)) {
            unlink($uploadThumbDirPath . '/' . $image);
        }
        if ($model->delete()) {
            Yii::$app->session->setFlash(Growl::TYPE_SUCCESS, "Brand deleted successfully.");
        } else {
            Yii::$app->session->setFlash(Growl::TYPE_DANGER, "Error while deleting Brand.");
        }
        return $this->redirect(['index']);
    }

    public function actionNewBrandDelete($id)
    {
        $model = $this->findModel($id);
        $image = $model->image;
        $uploadDirPath = Yii::getAlias('@brandImageRelativePath');
        $uploadThumbDirPath = Yii::getAlias('@brandImageThumbRelativePath');
        // unlink images with thumb
        if (file_exists($uploadDirPath . '/' . $image) && !empty($image)) {
            unlink($uploadDirPath . '/' . $image);
        }
        if (file_exists($uploadThumbDirPath . '/' . $image) && !empty($image)) {
            unlink($uploadThumbDirPath . '/' . $image);
        }
        if ($model->delete()) {
            Yii::$app->session->setFlash(Growl::TYPE_SUCCESS, "New brand deleted successfully.");
        } else {
            Yii::$app->session->setFlash(Growl::TYPE_DANGER, "Error while deleting new brand.");
        }
        return $this->redirect(['new-brand']);
    }

    /**
     * Finds the Brand model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Brand the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Brand::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Deletes an existing image from perticular field.
     * If deletion is successful, success message will get in update page result.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionImageDelete($id)
    {
        $model = $this->findModel($id);

        $uploadDirPath = Yii::getAlias('@brandImageRelativePath');
        $uploadThumbDirPath = Yii::getAlias('@brandImageThumbRelativePath');
        // unlink images with thumb
        if (file_exists($uploadDirPath . '/' . $model->image) && !empty($model->image)) {
            unlink($uploadDirPath . '/' . $model->image);
        }
        if (file_exists($uploadThumbDirPath . '/' . $model->image) && !empty($model->image)) {
            unlink($uploadThumbDirPath . '/' . $model->image);
        }
        $model->image = null;
        if ($model->save()) {
            return Json::encode(['success' => 'image successfully deleted']);
        }
    }

    /**
     * @return bool[]|false[]
     * @throws NotFoundHttpException
     */
    public function actionUpdateTopBrand()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = Yii::$app->request->post('id');
        $is_top_brand = Yii::$app->request->post('is_top_brand');

        $response = ['success' => false];
        if (!empty($id)) {
            $model = Brand::findOne($id);
            if ($model) {
                $model->is_top_brand = (!empty($is_top_brand) && $is_top_brand == 1) ? Brand::TOP_BRAND : Brand::NOT_TOP_BRAND;
                $model->save(false);
                \Yii::$app->getSession()->setFlash(Growl::TYPE_SUCCESS, 'Brand updated successfully.');
                $response = ['success' => true];
            }
        }
        return $response;
    }
}
