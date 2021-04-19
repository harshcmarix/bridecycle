<?php

namespace app\modules\admin\controllers;

use app\models\UserAddress;
use app\modules\admin\models\User;
use kartik\growl\Growl;
use Yii;
use app\modules\admin\models\search\UserSearch;
use yii\base\BaseObject;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\widgets\ActiveForm;

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
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
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
            'shopAddress'=>$userShopAddress,
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

        if ($model->load(Yii::$app->request->post())) { //&& $model->validate()

            $password = $model->password_hash;
            $model->password_hash = password_hash($model->password_hash, PASSWORD_DEFAULT);

            $model->user_type = User::USER_TYPE_NORMAL_USER;

            $newShopLogoFile = UploadedFile::getInstance($model, 'shop_logo');
            if (isset($newShopLogoFile) && isset($model->is_shop_owner)) {
                $shop_logo_picture = time() . rand(99999, 88888) . '.' . $newShopLogoFile->extension;
//                if (!empty($oldShopLogoFile) && file_exists(Yii::getAlias('@shopLogoRelativePath') . "/" . $oldShopLogoFile)) {
//                    unlink(Yii::getAlias('@shopLogoRelativePath') . "/" . $oldShopLogoFile);
//                }
                $newShopLogoFile->saveAs(Yii::getAlias('@shopLogoRelativePath') . "/" . $shop_logo_picture);
                $model->shop_logo = $shop_logo_picture;
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
                }

                \Yii::$app->getSession()->setFlash(Growl::TYPE_SUCCESS, 'You have successfully created User!');

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
        $model->scenario = User::SCENARIO_UPDATE_NORMAL_USER;

        // Old file and Password
        $oldProfileFile = $model->profile_picture;
        $oldShopLogoFile = $model->shop_logo;
        $oldpwd = $model->password_hash;
        $model->password_hash = '';

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
            $new_password = $model->password_hash;
            if (empty($new_password)) {
                $model->password_hash = $oldpwd;
            } else {
                $model->password_hash = Yii::$app->getSecurity()->generatePasswordHash($new_password);
            }

            $model->profile_picture = $oldProfileFile;

            $newShopLogoFile = UploadedFile::getInstance($model, 'shop_logo');
            if (isset($newShopLogoFile) && isset($postData['is_shop_owner'])) {
                $shop_logo_picture = time() . rand(99999, 88888) . '.' . $newShopLogoFile->extension;
                if (!empty($oldShopLogoFile) && file_exists(Yii::getAlias('@shopLogoRelativePath') . "/" . $oldShopLogoFile)) {
                    unlink(Yii::getAlias('@shopLogoRelativePath') . "/" . $oldShopLogoFile);
                }
                $newShopLogoFile->saveAs(Yii::getAlias('@shopLogoRelativePath') . "/" . $shop_logo_picture);
                $model->shop_logo = $shop_logo_picture;
            } else if (isset($postData['is_shop_owner']) && empty($newShopLogoFile)) {
                $model->shop_logo = $oldShopLogoFile;
            } else {
                $model->shop_logo = "";
            }

            if (isset($postData['is_shop_owner']) && $postData['is_shop_owner'] == '1') {
                $model->is_shop_owner = User::IS_SHOP_OWNER_YES;
                $model->shop_name = $postData['shop_name'];
                $model->shop_email = $postData['shop_email'];
                $model->shop_phone_number = $postData['shop_phone_number'];

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

            } else {
                $model->is_shop_owner = User::IS_SHOP_OWNER_NO;
                $model->shop_name = $model->shop_email = $model->shop_phone_number = ""; //= $model->shop_address
            }

            $model->user_type = User::USER_TYPE_NORMAL_USER;
            $model->updated_at = date('Y-m-d H:i:s');

            if ($model->save()) {
                \Yii::$app->getSession()->setFlash(Growl::TYPE_SUCCESS, 'You have successfully updated User!');
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

        $this->findModel($id)->delete();

        if (!empty($model)) {
            $oldProfileFile = $model->profile_picture;
            $oldShopLogoFile = $model->shop_logo;

            if (!empty($oldProfileFile) && file_exists(Yii::getAlias('@profilePictureRelativePath') . "/" . $oldProfileFile)) {
                unlink(Yii::getAlias('@profilePictureRelativePath') . "/" . $oldProfileFile);
            }

            if (!empty($oldShopLogoFile) && file_exists(Yii::getAlias('@shopLogoRelativePath') . "/" . $oldShopLogoFile)) {
                unlink(Yii::getAlias('@shopLogoRelativePath') . "/" . $oldShopLogoFile);
            }
        }
        \Yii::$app->getSession()->setFlash(Growl::TYPE_SUCCESS, 'You have successfully deleted User!');
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

        throw  NotFoundHttpException('The requested page does not exist.');
    }
}