<?php

namespace app\modules\admin\controllers;

use app\models\ShopDetail;
use app\models\UserAddress;
use app\modules\admin\models\User;
use kartik\growl\Growl;
use Yii;
use app\modules\admin\models\search\UserSearch;
use yii\base\BaseObject;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\widgets\ActiveForm;
use yii\imagine\Image;
use \yii\helpers\Json;

/**
 * UsersController implements the CRUD actions for Users model.
 */
class UserController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'create', 'update','view','delete'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'create', 'update','view','delete'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Users models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->sort = false;
        // echo "<pre>";
        // print_r($searchModel);
        // exit;
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Users model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $userShopAddress = UserAddress::find()->where(['user_id' => $id, 'type' => UserAddress::TYPE_SHOP])->one();
        return $this->render('view', [
            'model' => $this->findModel($id),
            'shopAddress' => $userShopAddress,
        ]);
    }

    /**
     * Creates a new Users model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new User();
        $model->scenario = User::SCENARIO_CREATE_NORMAL_USER;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return \yii\widgets\ActiveForm::validate($model);
        }

       // $model->password_hash = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);

        if ($model->load(Yii::$app->request->post())) { //&& $model->validate()

            //$password = $model->password_hash;
            $password = $model->password;
            $model->password_hash = password_hash($model->password, PASSWORD_DEFAULT);

            $model->user_type = User::USER_TYPE_NORMAL_USER;

            $model->profile_picture = UploadedFile::getInstanceByName('profile_picture');
            if ($model->profile_picture instanceof UploadedFile) {
                $uploadDirPath = Yii::getAlias('@profilePictureRelativePath');
                $uploadThumbDirPath = Yii::getAlias('@profilePictureThumbRelativePath');
                $thumbImagePath = '';
                // Create profile upload directory if not exist
                if (!is_dir($uploadDirPath)) {
                    mkdir($uploadDirPath, 0777);
                }

                // Create profile thumb upload directory if not exist
                if (!is_dir($uploadThumbDirPath)) {
                    mkdir($uploadThumbDirPath, 0777);
                }

                $ext = $model->profile_picture->extension;
                $fileName = pathinfo($model->profile_picture->name, PATHINFO_FILENAME);
                $fileName = $fileName . '_' . time() . '.' . $ext;
                // Upload profile picture
                $model->profile_picture->saveAs($uploadDirPath . '/' . $fileName);
                // Create thumb of profile picture
                $actualImagePath = $uploadDirPath . '/' . $fileName;
                $thumbImagePath = $uploadThumbDirPath . '/' . $fileName;
                Image::thumbnail($actualImagePath, Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height'])->save($thumbImagePath, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
                // Insert profile picture name into database
                $model->profile_picture = $fileName;
            }

            if ($model->save(false)) {

                if (!empty($model->is_shop_owner) || $model->is_shop_owner != '0' || $model->is_shop_owner != '') {
                    $modelShopAddress = new UserAddress();
                    $postData = Yii::$app->request->post('User');
                    $shopFullAddress = $postData['shop_address_street'] . ", " . $postData['shop_address_city'] . ", " . $postData['shop_address_zip_code'];
                    $modelShopAddress->user_id = $model->id;
                    $modelShopAddress->type = UserAddress::TYPE_SHOP;
                    $modelShopAddress->street = $postData['shop_address_street'];
                    $modelShopAddress->city = $postData['shop_address_city'];
                    $modelShopAddress->state = $postData['shop_address_state'];
                    $modelShopAddress->zip_code = $postData['shop_address_zip_code'];
                    $modelShopAddress->country = $postData['shop_address_country'];
                    $modelShopAddress->address = $shopFullAddress;
                    $modelShopAddress->save();

                    $modelUserShopDetail = new ShopDetail();
                    $modelUserShopDetail->user_id = $model->id;
                    $modelUserShopDetail->shop_name = $postData['shop_name'];
                    $modelUserShopDetail->shop_email = $postData['shop_email'];
                    $modelUserShopDetail->shop_phone_number = $postData['shop_phone_number'];


                    $newShopLogoFile = UploadedFile::getInstance($model, 'shop_logo');
                    if (isset($newShopLogoFile) && isset($model->is_shop_owner)) {
                        $shop_logo_picture = time() . rand(99999, 88888) . '.' . $newShopLogoFile->extension;
                        $newShopLogoFile->saveAs(Yii::getAlias('@shopLogoRelativePath') . "/" . $shop_logo_picture);
                        Image::thumbnail(Yii::getAlias('@shopLogoRelativePath') . "/" . $shop_logo_picture, Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height'])->save(Yii::getAlias('@shopLogoThumbRelativePath') . "/" . $shop_logo_picture, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
                        $modelUserShopDetail->shop_logo = $shop_logo_picture;
                    }
                    $modelUserShopDetail->save(false);

                }

                \Yii::$app->getSession()->setFlash(Growl::TYPE_SUCCESS, 'User created successfully.');

                Yii::$app->mailer->compose('admin/userRegistration-html', ['model' => $model, 'pwd' => $password])
                ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
                ->setTo($model->email)
                ->setSubject('Thank you for Registration!')
                ->send();

                //return $this->redirect(['view', 'id' => $model->id]);
                return $this->redirect(['index']);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Users model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $modelShopAddress = UserAddress::find()->where(['user_id' => $id, 'type' => UserAddress::TYPE_SHOP])->one();
        $modelShopDetail = $model->shopDetail;
        $model->scenario = User::SCENARIO_UPDATE_NORMAL_USER;

        // Old file and Password
        $oldProfileFile = $model->profile_picture;
        $oldShopLogoFile = (!empty($modelShopDetail->shop_logo)) ? $modelShopDetail->shop_logo : "";
        $oldpwd = $model->password_hash;


        $model->shop_name = (!empty($modelShopDetail->shop_name)) ? $modelShopDetail->shop_name : "";
        $model->shop_email = (!empty($modelShopDetail->shop_email)) ? $modelShopDetail->shop_email : "";
        $model->shop_phone_number = (!empty($modelShopDetail->shop_phone_number)) ? $modelShopDetail->shop_phone_number : "";
        $model->shop_logo = (!empty($modelShopDetail->shop_logo)) ? $modelShopDetail->shop_logo : "";


        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        $model->shop_address_street = $model->shop_address_city = $model->shop_address_state = $model->shop_address_country = $model->shop_address_zip_code = "";
        if (!empty($modelShopAddress) && $modelShopAddress instanceof UserAddress) {
            $model->shop_address_street = $modelShopAddress->street;
            $model->shop_address_city = $modelShopAddress->city;
            $model->shop_address_state = $modelShopAddress->state;
            $model->shop_address_country = $modelShopAddress->country;
            $model->shop_address_zip_code = $modelShopAddress->zip_code;
        }

        $postData = Yii::$app->request->post('User');

        if ($model->load(Yii::$app->request->post())) { // && $model->save()

            // Update new password
            $new_password = $model->password;
            if (empty($new_password)) {
                $model->password_hash = $oldpwd;
            } else {
                $model->password_hash = Yii::$app->getSecurity()->generatePasswordHash($new_password);
            }

            $model->profile_picture = $oldProfileFile;

            if (empty($modelShopDetail)) {
                $modelShopDetail = new ShopDetail();
            }

            if (isset($postData['is_shop_owner']) && $postData['is_shop_owner'] == '1') {
                $newShopLogoFile = UploadedFile::getInstance($model, 'shop_logo');

                if (isset($newShopLogoFile) && isset($postData['is_shop_owner'])) {

                    $shop_logo_picture = time() . rand(99999, 88888) . '.' . $newShopLogoFile->extension;
                    if (!empty($oldShopLogoFile) && file_exists(Yii::getAlias('@shopLogoRelativePath') . "/" . $oldShopLogoFile)) {
                        unlink(Yii::getAlias('@shopLogoRelativePath') . "/" . $oldShopLogoFile);
                    }
                    if (!empty($oldShopLogoFile) && file_exists(Yii::getAlias('@shopLogoThumbRelativePath') . "/" . $oldShopLogoFile)) {
                        unlink(Yii::getAlias('@shopLogoThumbRelativePath') . "/" . $oldShopLogoFile);
                    }

                    $newShopLogoFile->saveAs(Yii::getAlias('@shopLogoRelativePath') . "/" . $shop_logo_picture);
                    // $newShopLogoFile->saveAs(Yii::getAlias('@shopLogoThumbRelativePath') . "/" . $shop_logo_picture);
                    Image::thumbnail(Yii::getAlias('@shopLogoRelativePath') . "/" . $shop_logo_picture, Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height'])->save(Yii::getAlias('@shopLogoThumbRelativePath') . "/" . $shop_logo_picture, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);

                    $modelShopDetail->shop_logo = $shop_logo_picture;
                } else if (isset($postData['is_shop_owner']) && empty($newShopLogoFile)) {
                    $modelShopDetail->shop_logo = $oldShopLogoFile;
                } else {
                    $modelShopDetail->shop_logo = "";
                }
            }


            if (isset($postData['is_shop_owner']) && $postData['is_shop_owner'] == '1') {
                $model->is_shop_owner = User::IS_SHOP_OWNER_YES;


                $modelShopDetail->shop_name = $postData['shop_name'];
                $modelShopDetail->shop_email = $postData['shop_email'];
                $modelShopDetail->shop_phone_number = $postData['shop_phone_number'];

                if (empty($modelShopAddress)) {
                    $modelShopAddress = new UserAddress();
                }
                $shopFullAddress = $postData['shop_address_street'] . ", " . $postData['shop_address_city'] . ", " . $postData['shop_address_zip_code'];
                $modelShopAddress->user_id = $id;
                $modelShopAddress->type = UserAddress::TYPE_SHOP;
                $modelShopAddress->street = $postData['shop_address_street'];
                $modelShopAddress->city = $postData['shop_address_city'];
                $modelShopAddress->state = $postData['shop_address_state'];
                $modelShopAddress->zip_code = $postData['shop_address_zip_code'];
                $modelShopAddress->country = $postData['shop_address_country'];
                $modelShopAddress->address = $shopFullAddress;
                $modelShopAddress->save();

                $modelShopDetail->user_id = $id;
                $modelShopDetail->save(false);
            } else {
                $model->is_shop_owner = User::IS_SHOP_OWNER_NO;
                //$modelShopDetail->shop_name = $modelShopDetail->shop_email = $modelShopDetail->shop_phone_number = ""; //= $model->shop_address
                if (!empty($modelShopDetail)) {

                    if (!empty($modelShopDetail->shop_logo) && file_exists(Yii::getAlias('@shopLogoRelativePath') . "/" . $modelShopDetail->shop_logo)) {
                        unlink(Yii::getAlias('@shopLogoRelativePath') . "/" . $modelShopDetail->shop_logo);
                    }

                    if (!empty($modelShopDetail->shop_logo) && file_exists(Yii::getAlias('@shopLogoThumbRelativePath') . "/" . $oldShopLogoFile)) {
                        unlink(Yii::getAlias('@shopLogoThumbRelativePath') . "/" . $modelShopDetail->shop_logo);
                    }
                    $modelShopDetail->delete();

                }
                if (!empty($modelShopAddress)) {
                    $modelShopAddress->delete();
                }
            }

            if ($model->user_type == User::USER_TYPE_ADMIN) {
                $model->user_type = User::USER_TYPE_ADMIN;
            } else {
                $model->user_type = User::USER_TYPE_NORMAL_USER;
            }
            $model->updated_at = date('Y-m-d H:i:s');

            if ($model->save()) {
                \Yii::$app->getSession()->setFlash(Growl::TYPE_SUCCESS, 'User updated successfully.');
                //return $this->redirect(['view', 'id' => $model->id]);
                return $this->redirect(['index']);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Users model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $modelShopDetail = $model->shopDetail;

        $this->findModel($id)->delete();

        if (!empty($model)) {
            $oldProfileFile = $model->profile_picture;

            if (!empty($oldProfileFile) && file_exists(Yii::getAlias('@profilePictureRelativePath') . "/" . $oldProfileFile)) {
                unlink(Yii::getAlias('@profilePictureRelativePath') . "/" . $oldProfileFile);
            }

            if (!empty($oldProfileFile) && file_exists(Yii::getAlias('@profilePictureThumbRelativePath') . "/" . $oldProfileFile)) {
                unlink(Yii::getAlias('@profilePictureThumbRelativePath') . "/" . $oldProfileFile);
            }
        }

        if (!empty($modelShopDetail) && $modelShopDetail instanceof ShopDetail) {
            $oldShopLogoFile = $modelShopDetail->shop_logo;

            if (!empty($oldShopLogoFile) && file_exists(Yii::getAlias('@shopLogoRelativePath') . "/" . $oldShopLogoFile)) {
                unlink(Yii::getAlias('@shopLogoRelativePath') . "/" . $oldShopLogoFile);
            }

            if (!empty($oldShopLogoFile) && file_exists(Yii::getAlias('@shopLogoThumbRelativePath') . "/" . $oldShopLogoFile)) {
                unlink(Yii::getAlias('@shopLogoThumbRelativePath') . "/" . $oldShopLogoFile);
            }
        }

        \Yii::$app->getSession()->setFlash(Growl::TYPE_SUCCESS, 'User deleted successfully.');
        return $this->redirect(['index']);
    }

    /**
     * Finds the Users model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
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
     public function actionShopLogoDelete($id){
        $model = ShopDetail::findOne($id);
        // p($id);
        $uploadDirPath = Yii::getAlias('@shopLogoRelativePath');
        $uploadThumbDirPath = Yii::getAlias('@shopLogoThumbRelativePath');
         // unlink images with thumb
        if(file_exists($uploadDirPath.'/'.$model->shop_logo) && !empty($model->shop_logo)){
            unlink($uploadDirPath.'/'.$model->shop_logo);
        }
        if(file_exists($uploadThumbDirPath.'/'.$model->shop_logo) && !empty($model->shop_logo)){
            unlink($uploadThumbDirPath.'/'.$model->shop_logo);
        }
        $model->shop_logo = '';
        if($model->save(false)){
           return Json::encode(['success'=>'image successfully deleted']);
       }
   }
}
