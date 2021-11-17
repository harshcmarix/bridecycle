<?php

namespace app\modules\admin\controllers;

use app\models\UserBankDetails;
use app\models\ShopDetail;
use app\models\UserAddress;
use app\modules\admin\models\search\UserSearch;
use app\modules\admin\models\User;
use Imagine\Image\Box;
use kartik\growl\Growl;
use Yii;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\imagine\Image;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\widgets\ActiveForm;
use yii2tech\csvgrid\CsvGrid;

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
                'only' => ['index', 'create', 'update', 'view', 'delete', 'index-new-customer', 'new-customer-create', 'new-customer-update', 'new-customer-view', 'new-customer-delete', 'index-new-shop-owner-customer', 'new-shop-owner-customer-create', 'new-shop-owner-customer-update', 'new-shop-owner-customer-view', 'new-shop-owner-customer-delete'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'create', 'update', 'view', 'delete', 'index-new-customer', 'new-customer-create', 'new-customer-update', 'new-customer-view', 'new-customer-delete', 'index-new-shop-owner-customer', 'new-shop-owner-customer-create', 'new-shop-owner-customer-update', 'new-shop-owner-customer-view', 'new-shop-owner-customer-delete'],
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
        $userTypes = [User::USER_TYPE_ADMIN => 'Admin', User::USER_TYPE_SUB_ADMIN => 'Sub Admin', User::USER_TYPE_NORMAL_USER => "Normal User"];

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'userTypes' => $userTypes,
            'isShopOwner' => $searchModel->isShopOwner
        ]);
    }

    /**
     * Lists all Users models.
     * @return mixed
     */
    public function actionIndexNewCustomer()
    {
        $searchModel = new UserSearch();

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $userTypes = [User::USER_TYPE_ADMIN => 'Admin', User::USER_TYPE_SUB_ADMIN => 'Sub Admin', User::USER_TYPE_NORMAL_USER => "Normal User"];

        return $this->render('index-new-customer', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'userTypes' => $userTypes,
            'isShopOwner' => $searchModel->isShopOwner
        ]);
    }

    /**
     * @return string
     */
    public function actionIndexNewShopOwnerCustomer()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $userTypes = [User::USER_TYPE_ADMIN => 'Admin', User::USER_TYPE_SUB_ADMIN => 'Sub Admin', User::USER_TYPE_NORMAL_USER => "Normal User"];

        return $this->render('index-new-shop-owner-customer', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'userTypes' => $userTypes,
            'isShopOwner' => $searchModel->isShopOwner
        ]);
    }

    /**
     * Displays a single Users model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id, $pageId = '', $pageType = '')
    {
        $userShopAddress = UserAddress::find()->where(['user_id' => $id, 'type' => UserAddress::TYPE_SHOP])->one();
        $bankDetails = UserBankDetails::find()->where(['user_id' => $id])->one();

        return $this->render('view', [
            'model' => $this->findModel($id),
            'shopAddress' => $userShopAddress,
            'bankDetails' => $bankDetails,
            'pageId' => $pageId,
            'pageType' => $pageType,
        ]);
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionNewCustomerView($id)
    {
        $userShopAddress = UserAddress::find()->where(['user_id' => $id, 'type' => UserAddress::TYPE_SHOP])->one();
        $bankDetails = UserBankDetails::find()->where(['user_id' => $id])->one();
        return $this->render('view_new_customer', [
            'model' => $this->findModel($id),
            'shopAddress' => $userShopAddress,
            'bankDetails' => $bankDetails,
        ]);
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionNewShopOwnerCustomerView($id, $pageId = '', $pageType = '')
    {
        $bankDetails = "";
        $userShopAddress = UserAddress::find()->where(['user_id' => $id, 'type' => UserAddress::TYPE_SHOP])->one();
        return $this->render('view_new_shop_owner_customer', [
            'model' => $this->findModel($id),
            'shopAddress' => $userShopAddress,
            'bankDetails' => $bankDetails,
            'pageId' => $pageId,
            'pageType' => $pageType,
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

            $password = $model->password;
            $model->password_hash = password_hash($model->password, PASSWORD_DEFAULT);

            $model->user_type = User::USER_TYPE_NORMAL_USER;

            $profilePicture = UploadedFile::getInstance($model, 'profile_picture');
            if (isset($profilePicture)) {

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

                $ext = $profilePicture->extension;
                $fileName = pathinfo($profilePicture->name, PATHINFO_FILENAME);
                $fileName = time() . rand(99999, 88888) . '.' . $ext;
                // Upload profile picture
                $profilePicture->saveAs($uploadDirPath . '/' . $fileName);
                // Create thumb of profile picture
                $actualImagePath = $uploadDirPath . '/' . $fileName;
                $thumbImagePath = $uploadThumbDirPath . '/' . $fileName;

                Image::getImagine()->open($actualImagePath)->thumbnail(new Box(Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height']))->save($thumbImagePath, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
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

                        Image::getImagine()->open(Yii::getAlias('@shopLogoRelativePath') . "/" . $shop_logo_picture)->thumbnail(new Box(Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height']))->save(Yii::getAlias('@shopLogoThumbRelativePath') . "/" . $shop_logo_picture, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);

                        $modelUserShopDetail->shop_logo = $shop_logo_picture;
                    }
                    $modelUserShopDetail->save(false);
                }

                \Yii::$app->getSession()->setFlash(Growl::TYPE_SUCCESS, 'Customer created successfully.');

                Yii::$app->mailer->compose('admin/userRegistration-html', ['model' => $model, 'pwd' => $password])
                    ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
                    ->setTo($model->email)
                    ->setSubject('Thank you for Registration!')
                    ->send();

                return $this->redirect(['index']);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * @return array|string|Response
     */
    public function actionNewCustomerCreate()
    {
        $model = new User();
        $model->scenario = User::SCENARIO_CREATE_NORMAL_USER;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return \yii\widgets\ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) { //&& $model->validate()

            $password = $model->password;
            $model->password_hash = password_hash($model->password, PASSWORD_DEFAULT);

            $model->user_type = User::USER_TYPE_NORMAL_USER;

            $profilePicture = UploadedFile::getInstance($model, 'profile_picture');
            if (isset($profilePicture)) {

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

                $ext = $profilePicture->extension;
                $fileName = pathinfo($profilePicture->name, PATHINFO_FILENAME);
                $fileName = time() . rand(99999, 88888) . '.' . $ext;
                // Upload profile picture
                $profilePicture->saveAs($uploadDirPath . '/' . $fileName);
                // Create thumb of profile picture
                $actualImagePath = $uploadDirPath . '/' . $fileName;
                $thumbImagePath = $uploadThumbDirPath . '/' . $fileName;

                Image::getImagine()->open($actualImagePath)->thumbnail(new Box(Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height']))->save($thumbImagePath, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
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

                        Image::getImagine()->open(Yii::getAlias('@shopLogoRelativePath') . "/" . $shop_logo_picture)->thumbnail(new Box(Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height']))->save(Yii::getAlias('@shopLogoThumbRelativePath') . "/" . $shop_logo_picture, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);

                        $modelUserShopDetail->shop_logo = $shop_logo_picture;
                    }
                    $modelUserShopDetail->save(false);
                }

                \Yii::$app->getSession()->setFlash(Growl::TYPE_SUCCESS, 'New Customer created successfully.');

                Yii::$app->mailer->compose('admin/userRegistration-html', ['model' => $model, 'pwd' => $password])
                    ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
                    ->setTo($model->email)
                    ->setSubject('Thank you for Registration!')
                    ->send();

                return $this->redirect(['index-new-customer']);
            }
        }

        return $this->render('create_new_customer', [
            'model' => $model,
        ]);
    }

    /**
     * @return array|string|Response
     */
    public function actionNewShopOwnerCustomerCreate()
    {
        $model = new User();
        $model->scenario = User::SCENARIO_CREATE_NORMAL_USER;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return \yii\widgets\ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) { //&& $model->validate()

            $password = $model->password;
            $model->password_hash = password_hash($model->password, PASSWORD_DEFAULT);

            $model->user_type = User::USER_TYPE_NORMAL_USER;

            $profilePicture = UploadedFile::getInstance($model, 'profile_picture');
            if (isset($profilePicture)) {

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

                $ext = $profilePicture->extension;
                $fileName = pathinfo($profilePicture->name, PATHINFO_FILENAME);
                $fileName = time() . rand(99999, 88888) . '.' . $ext;
                // Upload profile picture
                $profilePicture->saveAs($uploadDirPath . '/' . $fileName);
                // Create thumb of profile picture
                $actualImagePath = $uploadDirPath . '/' . $fileName;
                $thumbImagePath = $uploadThumbDirPath . '/' . $fileName;

                Image::getImagine()->open($actualImagePath)->thumbnail(new Box(Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height']))->save($thumbImagePath, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
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

                        Image::getImagine()->open(Yii::getAlias('@shopLogoRelativePath') . "/" . $shop_logo_picture)->thumbnail(new Box(Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height']))->save(Yii::getAlias('@shopLogoThumbRelativePath') . "/" . $shop_logo_picture, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);

                        $modelUserShopDetail->shop_logo = $shop_logo_picture;
                    }
                    $modelUserShopDetail->save(false);
                }

                \Yii::$app->getSession()->setFlash(Growl::TYPE_SUCCESS, 'New Shop Owner created successfully.');

                Yii::$app->mailer->compose('admin/userRegistration-html', ['model' => $model, 'pwd' => $password])
                    ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
                    ->setTo($model->email)
                    ->setSubject('Thank you for Registration!')
                    ->send();

                return $this->redirect(['index-new-shop-owner-customer']);
            }
        }

        return $this->render('create_new_shop_owner_customer', [
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

            // Update user status
            if (!empty($postData['user_status']) && $postData['user_status'] == User::USER_STATUS_IN_ACTIVE) {
                $model->user_status = User::USER_STATUS_IN_ACTIVE;
            } else if (!empty(Yii::$app->request->get('f'))) {
                $model->user_status = User::USER_STATUS_ACTIVE;
            } else {
                $model->user_status = User::USER_STATUS_ACTIVE;
            }

            // Update new password
            $new_password = $model->password;
            if (empty($new_password)) {
                $model->password_hash = $oldpwd;
            } else {
                $model->password_hash = Yii::$app->getSecurity()->generatePasswordHash($new_password);
            }

            $newProfilePictureFile = UploadedFile::getInstance($model, 'profile_picture');
            if (isset($newProfilePictureFile)) {

                $profilePicture = time() . rand(99999, 88888) . '.' . $newProfilePictureFile->extension;
                if (!empty($oldProfileFile) && file_exists(Yii::getAlias('@profilePictureRelativePath') . "/" . $oldProfileFile)) {
                    unlink(Yii::getAlias('@profilePictureRelativePath') . "/" . $oldProfileFile);
                }
                if (!empty($oldProfileFile) && file_exists(Yii::getAlias('@profilePictureThumbRelativePath') . "/" . $oldProfileFile)) {
                    unlink(Yii::getAlias('@profilePictureThumbRelativePath') . "/" . $oldProfileFile);
                }

                $newProfilePictureFile->saveAs(Yii::getAlias('@profilePictureRelativePath') . "/" . $profilePicture);

                Image::getImagine()->open(Yii::getAlias('@profilePictureRelativePath') . "/" . $profilePicture)->thumbnail(new Box(Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height']))->save(Yii::getAlias('@profilePictureThumbRelativePath') . "/" . $profilePicture, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);

                $model->profile_picture = $profilePicture;
            } else if (empty($newProfilePictureFile)) {
                $model->profile_picture = $oldProfileFile;
            } else {
                $model->profile_picture = '';
            }

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

                    Image::getImagine()->open(Yii::getAlias('@shopLogoRelativePath') . "/" . $shop_logo_picture)->thumbnail(new Box(Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height']))->save(Yii::getAlias('@shopLogoThumbRelativePath') . "/" . $shop_logo_picture, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);

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
                \Yii::$app->getSession()->setFlash(Growl::TYPE_SUCCESS, 'Customer updated successfully.');
                return $this->redirect(['index']);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * @param $id
     * @return array|string|Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\base\Exception
     * @throws \yii\db\StaleObjectException
     */
    public function actionNewCustomerUpdate($id)
    {

        $model = $this->findModel($id);
        $modelShopAddress = UserAddress::find()->where(['user_id' => $id, 'type' => UserAddress::TYPE_SHOP])->one();
        $modelShopDetail = $model->shopDetail;
        $model->scenario = User::SCENARIO_UPDATE_NORMAL_USER;

        $newsLetterSubscription = $model->is_newsletter_subscription;

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

            // Update user status
            if (!empty($postData['user_status']) && $postData['user_status'] == User::USER_STATUS_IN_ACTIVE) {
                $model->user_status = User::USER_STATUS_IN_ACTIVE;
            } else if (!empty(Yii::$app->request->get('f'))) {
                $model->user_status = User::USER_STATUS_ACTIVE;
            } else {
                $model->user_status = User::USER_STATUS_ACTIVE;
            }

            // Update new password
            $new_password = $model->password;
            if (empty($new_password)) {
                $model->password_hash = $oldpwd;
            } else {
                $model->password_hash = Yii::$app->getSecurity()->generatePasswordHash($new_password);
            }

            $newProfilePictureFile = UploadedFile::getInstance($model, 'profile_picture');
            if (isset($newProfilePictureFile)) {

                $profilePicture = time() . rand(99999, 88888) . '.' . $newProfilePictureFile->extension;
                if (!empty($oldProfileFile) && file_exists(Yii::getAlias('@profilePictureRelativePath') . "/" . $oldProfileFile)) {
                    unlink(Yii::getAlias('@profilePictureRelativePath') . "/" . $oldProfileFile);
                }
                if (!empty($oldProfileFile) && file_exists(Yii::getAlias('@profilePictureThumbRelativePath') . "/" . $oldProfileFile)) {
                    unlink(Yii::getAlias('@profilePictureThumbRelativePath') . "/" . $oldProfileFile);
                }

                $newProfilePictureFile->saveAs(Yii::getAlias('@profilePictureRelativePath') . "/" . $profilePicture);

                Image::getImagine()->open(Yii::getAlias('@profilePictureRelativePath') . "/" . $profilePicture)->thumbnail(new Box(Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height']))->save(Yii::getAlias('@profilePictureThumbRelativePath') . "/" . $profilePicture, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);

                $model->profile_picture = $profilePicture;
            } else if (empty($newProfilePictureFile)) {
                $model->profile_picture = $oldProfileFile;
            } else {
                $model->profile_picture = '';
            }

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

                    Image::getImagine()->open(Yii::getAlias('@shopLogoRelativePath') . "/" . $shop_logo_picture)->thumbnail(new Box(Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height']))->save(Yii::getAlias('@shopLogoThumbRelativePath') . "/" . $shop_logo_picture, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);

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

            $model->is_newsletter_subscription = $newsLetterSubscription;

            if ($model->save()) {
                \Yii::$app->getSession()->setFlash(Growl::TYPE_SUCCESS, 'New Customer updated successfully.');
                return $this->redirect(['index-new-customer']);
            }
        }

        return $this->render('update_new_customer', [
            'model' => $model,
        ]);
    }

    /**
     * @param $id
     * @return array|string|Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\base\Exception
     * @throws \yii\db\StaleObjectException
     */
    public function actionNewShopOwnerCustomerUpdate($id)
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

            // Update user status
            if (!empty($postData['user_status']) && $postData['user_status'] == User::USER_STATUS_IN_ACTIVE) {
                $model->user_status = User::USER_STATUS_IN_ACTIVE;
            } else if (!empty(Yii::$app->request->get('f'))) {
                $model->user_status = User::USER_STATUS_ACTIVE;
            } else {
                $model->user_status = User::USER_STATUS_ACTIVE;
            }

            // Update new password
            $new_password = $model->password;
            if (empty($new_password)) {
                $model->password_hash = $oldpwd;
            } else {
                $model->password_hash = Yii::$app->getSecurity()->generatePasswordHash($new_password);
            }

            $newProfilePictureFile = UploadedFile::getInstance($model, 'profile_picture');
            if (isset($newProfilePictureFile)) {

                $profilePicture = time() . rand(99999, 88888) . '.' . $newProfilePictureFile->extension;
                if (!empty($oldProfileFile) && file_exists(Yii::getAlias('@profilePictureRelativePath') . "/" . $oldProfileFile)) {
                    unlink(Yii::getAlias('@profilePictureRelativePath') . "/" . $oldProfileFile);
                }
                if (!empty($oldProfileFile) && file_exists(Yii::getAlias('@profilePictureThumbRelativePath') . "/" . $oldProfileFile)) {
                    unlink(Yii::getAlias('@profilePictureThumbRelativePath') . "/" . $oldProfileFile);
                }

                $newProfilePictureFile->saveAs(Yii::getAlias('@profilePictureRelativePath') . "/" . $profilePicture);

                Image::getImagine()->open(Yii::getAlias('@profilePictureRelativePath') . "/" . $profilePicture)->thumbnail(new Box(Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height']))->save(Yii::getAlias('@profilePictureThumbRelativePath') . "/" . $profilePicture, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);

                $model->profile_picture = $profilePicture;
            } else if (empty($newProfilePictureFile)) {
                $model->profile_picture = $oldProfileFile;
            } else {
                $model->profile_picture = '';
            }

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

                    Image::getImagine()->open(Yii::getAlias('@shopLogoRelativePath') . "/" . $shop_logo_picture)->thumbnail(new Box(Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height']))->save(Yii::getAlias('@shopLogoThumbRelativePath') . "/" . $shop_logo_picture, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);

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
                \Yii::$app->getSession()->setFlash(Growl::TYPE_SUCCESS, 'Shop owner updated successfully.');
                return $this->redirect(['index-new-shop-owner-customer']);
            }
        }

        return $this->render('update_new_shop_owner_customer', [
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

        \Yii::$app->getSession()->setFlash(Growl::TYPE_SUCCESS, 'Customer deleted successfully.');
        return $this->redirect(['index']);
    }

    /**
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionNewCustomerDelete($id)
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

        \Yii::$app->getSession()->setFlash(Growl::TYPE_SUCCESS, 'Customer deleted successfully.');
        return $this->redirect(['index-new-customer']);
    }

    /**
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionNewShopOwnerCustomerDelete($id)
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

        \Yii::$app->getSession()->setFlash(Growl::TYPE_SUCCESS, 'Shop owner deleted successfully.');
        return $this->redirect(['index-new-shop-owner-customer']);
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
    public function actionShopLogoDelete($id)
    {
        $model = ShopDetail::findOne($id);
        $uploadDirPath = Yii::getAlias('@shopLogoRelativePath');
        $uploadThumbDirPath = Yii::getAlias('@shopLogoThumbRelativePath');
        // unlink images with thumb
        if (file_exists($uploadDirPath . '/' . $model->shop_logo) && !empty($model->shop_logo)) {
            unlink($uploadDirPath . '/' . $model->shop_logo);
        }
        if (file_exists($uploadThumbDirPath . '/' . $model->shop_logo) && !empty($model->shop_logo)) {
            unlink($uploadThumbDirPath . '/' . $model->shop_logo);
        }
        $model->shop_logo = '';
        if ($model->save(false)) {
            return Json::encode(['success' => 'image successfully deleted']);
        }
    }

    /**
     * @param $id
     * @return string|void
     */
    public function actionProfileDelete($id)
    {
        $model = User::findOne($id);
        $uploadDirPath = Yii::getAlias('@profilePictureRelativePath');
        $uploadThumbDirPath = Yii::getAlias('@profilePictureThumbRelativePath');

        // unlink images with thumb
        if (file_exists($uploadDirPath . '/' . $model->profile_picture) && !empty($model->profile_picture)) {
            unlink($uploadDirPath . '/' . $model->profile_picture);
        }
        if (file_exists($uploadThumbDirPath . '/' . $model->profile_picture) && !empty($model->profile_picture)) {
            unlink($uploadThumbDirPath . '/' . $model->profile_picture);
        }
        $model->profile_picture = '';
        if ($model->save(false)) {
            return Json::encode(['success' => 'Profile image successfully deleted']);
        }
    }

    public function actionExportCsv()
    {
        $exporter = new CsvGrid([
            'dataProvider' => new ArrayDataProvider([
                'allModels' => [
                    [
                        'first_name' => 'first name1',
                        'last_name' => 'last name1',
                        'email' => 'abc987@yopmail.com',
                        'mobile' => '1234567890',
                    ],
                    [
                        'first_name' => 'first name2',
                        'last_name' => 'last name2',
                        'email' => 'abc789@yopmail.com',
                        'mobile' => '0123456789',
                    ],
                ],
            ]),
            'columns' => [
                [
                    'attribute' => 'first_name',
                ],
                [
                    'attribute' => 'last_name',
                ],
                [
                    'attribute' => 'email',
                ],
                [
                    'attribute' => 'mobile',
                    //'format' => 'decimal',
                ],
            ],
        ]);
        $fileName = date('d_m_Y_His');
        // $exporter->export()->saveAs(Yii::getAlias('@uploadsRelativePath') . '/' . $fileName . '.csv');
        return $exporter->export()->send($fileName . '.csv');
    }
}
