<?php

namespace app\modules\api\v1\controllers;

use yii\filters\auth\{
    HttpBasicAuth,
    CompositeAuth,
    HttpBearerAuth,
    QueryParamAuth
};
use yii\web\{
    UploadedFile,
    ServerErrorHttpException,
    NotFoundHttpException,
    ForbiddenHttpException,
    BadRequestHttpException
};
use app\modules\api\v1\models\{
    Login,
    User,
    ResetPassword,
    ForgotPassword,
    ChangePassword,
    UserAddress
};
use Yii;
use yii\helpers\Url;
use yii\filters\Cors;
use yii\imagine\Image;
use yii\rest\ActiveController;

/**
 * Class UserController
 * @package app\modules\api\v1\controllers
 */
class UserController extends ActiveController
{
    /**
     * @var string
     */
    public $modelClass = 'app\modules\api\v1\models\User';

    /**
     * @var string
     */
    public $searchModelClass = 'app\modules\api\v1\models\search\UserSearch';

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
            'login' => ['POST', 'OPTIONS'],
            'logout' => ['GET'],
            'forgot-password' => ['POST', 'OPTIONS'],
            'verify-reset-password' => ['POST', 'OPTIONS'],
            'reset-password' => ['POST', 'OPTIONS'],
            'change-password' => ['POST', 'OPTIONS'],
            'profile-picture-update' => ['POST', 'OPTIONS'],
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
            'only' => ['index', 'view', 'update', 'logout', 'change-password','profile-picture-update'],
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
        unset($actions['delete']);
        unset($actions['view']);
        return $actions;
    }

    /**
     * @return mixed
     * @throws \yii\base\InvalidConfigException
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
     * @return User
     * @throws \yii\base\Exception
     */
    public function actionCreate()
    {
        $model = new User();
        $postData = \Yii::$app->request->post();
        $userData['User'] = $postData;

        $model->scenario = User::SCENARIO_USER_CREATE;
        if (!empty($postData['is_shop_owner']) && $postData['is_shop_owner'] == User::SHOP_OWNER_YES) {
            $model->scenario = User::SCENARIO_SHOP_OWNER;
        }

        $model->profile_picture = UploadedFile::getInstanceByName('profile_picture');
        $model->shop_logo = UploadedFile::getInstanceByName('shop_logo');
        $model->shop_cover_picture = UploadedFile::getInstanceByName('shop_cover_picture');

        if ($model->load($userData) && $model->validate()) {
            // Profile picture upload
            $uploadDirPath = Yii::getAlias('@profilePictureRelativePath');
            $uploadThumbDirPath = Yii::getAlias('@profilePictureThumbRelativePath');
            $thumbImagePath = '';
            if ($model->profile_picture instanceof UploadedFile) {
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
            $model->user_type = (string)User::USER_TYPE_NORMAL;
            $model->password_hash = \Yii::$app->security->generatePasswordHash($model->password);

            if ($model->is_shop_owner == User::SHOP_OWNER_YES) {
                //shop logo code
                    $uploadDirPathLogo = Yii::getAlias('@shopLogoRelativePath');
                    $uploadThumbDirPathLogo = Yii::getAlias('@shopLogoThumbRelativePath');
                    $thumbImagePathLogo = '';
                    if ($model->shop_logo instanceof UploadedFile) {
                        // Create Shop logo upload directory if not exist
                        if (!is_dir($uploadDirPathLogo)) {
                            mkdir($uploadDirPathLogo, 0777);
                        }

                        // Create Shop logo thumb upload directory if not exist
                        if (!is_dir($uploadThumbDirPathLogo)) {
                            mkdir($uploadThumbDirPathLogo, 0777);
                        }

                        $logoExt = $model->shop_logo->extension;
                        $shopLogoFileName = pathinfo($model->shop_logo->name, PATHINFO_FILENAME);
                        $shopLogoFileName = $shopLogoFileName . '_' . time() . '.' . $logoExt;
                        // Upload shop logo
                        $model->shop_logo->saveAs($uploadDirPathLogo . '/' . $shopLogoFileName);
                        // Create thumb of shoplogo
                        $actualImagePathLogo = $uploadDirPathLogo . '/' . $shopLogoFileName;
                        $thumbImagePathLogo = $uploadThumbDirPathLogo . '/' . $shopLogoFileName;
                        Image::thumbnail($actualImagePathLogo, Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height'])->save($thumbImagePathLogo, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
                        // Insert shop logo name into database
                        $model->shop_logo = $shopLogoFileName;
                    }
                    // shop cover picture
                    $uploadDirPathCoverPicture = Yii::getAlias('@shopCoverPictureRelativePath');
                    $uploadThumbDirPathCoverPicture = Yii::getAlias('@shopCoverPictureThumbRelativePath');
                    $thumbImagePathCoverPicture = '';
                    if ($model->shop_cover_picture instanceof UploadedFile) {
                        // Create Shop cover picture upload directory if not exist
                        if (!is_dir($uploadDirPathCoverPicture)) {
                            mkdir($uploadDirPathCoverPicture, 0777);
                        }

                        // Create Shop cover picture thumb upload directory if not exist
                        if (!is_dir($uploadThumbDirPathCoverPicture)) {
                            mkdir($uploadThumbDirPathCoverPicture, 0777);
                        }

                        $shopCoverPictureExt = $model->shop_cover_picture->extension;
                        $shopCoverPictureFileName = pathinfo($model->shop_cover_picture->name, PATHINFO_FILENAME);
                        $shopCoverPictureFileName = $shopCoverPictureFileName . '_' . time() . '.' . $shopCoverPictureExt;
                        // Upload shop cover picture
                        $model->shop_cover_picture->saveAs($uploadDirPathCoverPicture . '/' . $shopCoverPictureFileName);
                        // Create thumb of shoplogo
                        $actualImagePathCoverPicture = $uploadDirPathCoverPicture . '/' . $shopCoverPictureFileName;
                        $thumbImagePathCoverPicture = $uploadThumbDirPathCoverPicture . '/' . $shopCoverPictureFileName;
                        Image::thumbnail($actualImagePathCoverPicture, Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height'])->save($thumbImagePathCoverPicture, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
                        // Insert shop cover picture name into database
                        $model->shop_cover_picture = $shopCoverPictureFileName;
            }

            }
            if ($model->save()) {
                // Get profile picture
                $model->profile_picture = '';
                $model->shop_logo ='';
                $model->shop_cover_picture ='';
                if (!empty($thumbImagePath) && file_exists($thumbImagePath)) {
                    $model->profile_picture = Yii::$app->request->getHostInfo() . Yii::getAlias('@profilePictureThumbAbsolutePath') . '/' . $fileName;
                }
                 if (!empty($thumbImagePathLogo) && file_exists($thumbImagePathLogo)) {
                    $model->shop_logo = Yii::$app->request->getHostInfo() . Yii::getAlias('@shopLogoThumbAbsolutePath') . '/' . $shopLogoFileName;
                }
                 if (!empty($thumbImagePathCoverPicture) && file_exists($thumbImagePathCoverPicture)) {
                    $model->shop_cover_picture = Yii::$app->request->getHostInfo() . Yii::getAlias('@shopCoverPictureThumbAbsolutePath') . '/' . $shopCoverPictureFileName;
                }

                // Insert shop details
                if ($model->is_shop_owner == User::SHOP_OWNER_YES) {
                    $userAddressModel = new UserAddress();
                    $userAddressData['UserAddress'] = $postData;
                    if ($userAddressModel->load($userAddressData)) {
                        $userAddressModel->user_id = $model->id;
                        $userAddressModel->type = (string)User::USER_TYPE_NORMAL;
                        $userAddressModel->save(false);
                    }
                }
            }
        }

        return $model;
    }

    /**
     * @param $id
     * @return User
     * @throws NotFoundHttpException
     * @throws \yii\base\Exception
     */
    public function actionUpdate($id)
    {
        $model = User::findOne($id);
        if (!$model instanceof User) {
            throw new NotFoundHttpException('User doesn\'t exist.');
        }
        $postData = \Yii::$app->request->post();
        $data['User'] = $postData;
        $data1['UserAddress'] = $postData;
        $model->scenario = User::SCENARIO_USER_UPDATE;
        // if(!empty($postData['is_shop_owner']) && $postData['is_shop_owner'] == User::SHOP_OWNER_YES)
        // {
        //     $model->scenario = User::SCENARIO_SHOP_OWNER;
        // }

        if ($model->load($data) && $model->validate()) {
            $model->password_hash = \Yii::$app->security->generatePasswordHash($model->password);
            $model->updated_at = date('Y-m-d H:i:s');
            if ($model->save()) {
                if ($model->is_shop_owner == User::SHOP_OWNER_YES) {
                    $get_address_id = UserAddress::find()->where(['user_id' => $id])->one();
                    if (!empty($get_address_id->id)) {
                        $userAddressModel = UserAddress::findOne($get_address_id->id);
                        if ($userAddressModel->load($data1)) {
                            $userAddressModel->save(false);
                        }
                    }
                }
            }
        }

        return $model;
    }
    public function actionProfilePictureUpdate($id)
    {
        $model = User::findOne($id);
       
        if (!$model instanceof User) {
            throw new NotFoundHttpException('User doesn\'t exist.');
        }

        $old_image = $model->profile_picture;
        $postData = \Yii::$app->request->post();
        $data['User'] = $postData;

        $model->profile_picture = UploadedFile::getInstanceByName('profile_picture');
        
        if ($model->load($data) && $model->validate()) {
            // Profile picture upload
            $uploadDirPath = Yii::getAlias('@profilePictureRelativePath');
            $uploadThumbDirPath = Yii::getAlias('@profilePictureThumbRelativePath');
            $thumbImagePath = '';
            if ($model->profile_picture instanceof UploadedFile) {
                // Create profile upload directory if not exist
                if (!is_dir($uploadDirPath)) {
                    mkdir($uploadDirPath, 0777);
                }
                //unlink real image if update
                if (file_exists($uploadDirPath . '/' . $old_image) && !empty($old_image)) {
                    unlink($uploadDirPath . '/' . $old_image);
                }
                // Create profile thumb upload directory if not exist
                if (!is_dir($uploadThumbDirPath)) {
                    mkdir($uploadThumbDirPath, 0777);
                }
                 //unlink thumb image if update
                if (file_exists($uploadThumbDirPath . '/' . $old_image) && !empty($old_image)) {
                    unlink($uploadThumbDirPath . '/' . $old_image);
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
        }
        if($model->save()){
            $model->profile_picture ='';
            if (!empty($thumbImagePath) && file_exists($thumbImagePath)) {
                    $model->profile_picture = Yii::$app->request->getHostInfo() . Yii::getAlias('@profilePictureThumbAbsolutePath') . '/' . $fileName;
            }
        }
        return $model;
    }

    /***************************************************************************/
    /*************************** Authentication Functions **********************/
    /***************************************************************************/

    /**
     * @return Login
     * @throws ForbiddenHttpException
     */
    public function actionLogin()
    {
        $model = new Login();
        $data['Login'] = Yii::$app->request->post();

        if ($model->load($data) && $model->validate()) {
            if (!$model->login()) {
                throw new ForbiddenHttpException('Unable to process your request. Please contact administrator');
            }
        }

        return $model;
    }

    /**
     * @return string[]
     * @throws BadRequestHttpException
     */
    public function actionLogout()
    {
        $headers = \Yii::$app->getRequest()->getHeaders();
        $authorizationData = $headers->get('Authorization');
        if (!empty($authorizationData)) {
            $authorizationArray = explode(" ", $authorizationData);
            if (empty($authorizationArray[\Yii::$app->params['token_segment']])) {
                throw new BadRequestHttpException('Invalid parameter passed. Request must required parameter bearer token');
            }

            $accessToken = $authorizationArray[\Yii::$app->params['token_segment']];
            $userModel = User::findIdentityByAccessToken($accessToken);
            if ($userModel instanceof User) {
                $userModel->access_token = null;
                $userModel->access_token_expired_at = null;
                $userModel->save();

                \Yii::$app->user->logout();

                return [
                    'message' => 'Logged Out Successfully.'
                ];
            }

            return [
                'message' => 'You are already logged Out.'
            ];
        }

        throw new BadRequestHttpException('Invalid parameter passed. Request must required parameter bearer token');
    }

    /**
     * @return ForgotPassword|User|bool|object
     * @throws ServerErrorHttpException
     * @throws \yii\base\Exception
     */
    public function actionForgotPassword()
    {
        $model = new ForgotPassword();
        $data['ForgotPassword'] = \Yii::$app->request->post();
        if ($model->load($data) && $model->validate()) {
            $tmpPassword = \Yii::$app->security->generateRandomString(8);
            $userModel = $model->getUser();
            $userModel->temporary_password = $tmpPassword;
            if ($userModel->save()) {
                $mail = \Yii::$app->mailer->compose('api/forgot_password', ['model' => $model, 'user' => $userModel])
                    ->setFrom([\Yii::$app->params['from_email'] => \Yii::$app->name])
                    ->setTo($model->email)
                    ->setSubject('Forgot your password')
                    ->send();
                if (!$mail) {
                    throw new ServerErrorHttpException("Unable to send an email. Please try again later");
                }
            }
        }

        return $model;
    }

    /**
     * @return User|\yii\db\ActiveRecord
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionVerifyResetPassword()
    {
        $postData = \Yii::$app->request->post();
        if (empty($postData) || empty($postData['tmp_password'])) {
            throw new BadRequestHttpException('Invalid parameter passed. Request must required parameter "tmp_password"');
        }
        // $model = User::find()->where(['temporary_password' => $postData['tmp_password']])->one();
        $model = User::find()->where(['temporary_password' => $postData['tmp_password'], 'user_type' => User::USER_TYPE_NORMAL])->one();
        if (!$model instanceof User) {
            throw new NotFoundHttpException('Temporary password does\'t exist.');
        }

        return $model;
    }

    /**
     * @return ResetPassword|User|bool|object
     * @throws \yii\base\Exception
     */
    public function actionResetPassword()
    {
        $model = new ResetPassword();
        $data['ResetPassword'] = \Yii::$app->request->post();

        if ($model->load($data) && $model->validate()) {
            $userModel = $model->getUser();
            $userModel->setPassword($model->password);
            $userModel->temporary_password = null;
            if (!$userModel->save()) {
                throw new ForbiddenHttpException('Unable to process your request. Please contact administrator.');
            }
        }

        return $model;
    }

    /**
     * @return ChangePassword
     * @throws ForbiddenHttpException
     */
    public function actionChangePassword()
    {
        $model = new ChangePassword();
        $data['ChangePassword'] = \Yii::$app->request->post();
        if ($model->load($data) && $model->validate()) {
            $userModel = \Yii::$app->user->identity;
            $userModel->setPassword($model->password);
            if (!$userModel->save()) {
                throw new ForbiddenHttpException('Unable to process your request. Please contact administrator.');
            }
        }

        return $model;
    }
}
