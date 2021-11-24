<?php

namespace app\modules\api\v2\controllers;

use app\models\ShopDetail;
use app\models\UserAddress;
use app\models\UserDevice;
use app\modules\api\v2\models\ChangePassword;
use app\modules\api\v2\models\ForgotPassword;
use app\modules\api\v2\models\Login;
use app\modules\api\v2\models\ResetPassword;
use app\modules\api\v2\models\User;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\Cors;
use yii\imagine\Image;
use yii\rest\ActiveController;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UploadedFile;

/**
 * Class UserController
 * @package app\modules\api\v2\controllers
 */
class UserController extends ActiveController
{
    /**
     * @var string
     */
    public $modelClass = 'app\modules\api\v2\models\User';

    /**
     * @var string
     */
    public $searchModelClass = 'app\modules\api\v2\models\search\UserSearch';

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
            'logout' => ['GET', 'HEAD', 'OPTIONS'],
            'forgot-password' => ['POST', 'OPTIONS'],
            'verify-reset-password' => ['POST', 'OPTIONS'],
            'reset-password' => ['POST', 'OPTIONS'],
            'change-password' => ['POST', 'OPTIONS'],
            'update-profile-picture' => ['POST', 'OPTIONS'],
            'delete-user-address' => ['POST', 'OPTIONS'],
            'verify-profile-verification-code' => ['POST', 'OPTIONS'],
            'resend-verification-code' => ['POST', 'OPTIONS'],
            'enter-size-information' => ['POST', 'OPTIONS'],
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
            'only' => ['index', 'view', 'update', 'change-password', 'update-profile-picture', 'delete-user-address', 'enter-size-information'],
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

        if (empty($postData['is_login_from']) && !empty($postData['is_shop_owner']) && $postData['is_shop_owner'] == User::SHOP_OWNER_YES) {
            $model->scenario = User::SCENARIO_SHOP_OWNER;
        } elseif (!empty($postData['is_login_from']) && $postData['is_login_from'] == "") {
            $model->scenario = User::SCENARIO_USER_CREATE_FROM_SOCIAL;
        } else {
            $model->scenario = User::SCENARIO_USER_CREATE;
        }

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return \yii\widgets\ActiveForm::validate($model);
        }

        if (!empty($postData['is_login_from']) && strtolower($postData['is_login_from']) == User::IS_LOGIN_FROM_FACEBOOK) {
            if (empty($postData) || empty($postData['facebook_id'])) {
                throw new BadRequestHttpException('Invalid parameter passed. Request must required parameter "facebook_id"');
            }
            $model->facebook_id = $postData['facebook_id'];
        }

        if (!empty($postData['is_login_from']) && strtolower($postData['is_login_from']) == User::IS_LOGIN_FROM_APPLE) {
            if (empty($postData) || empty($postData['apple_id'])) {
                throw new BadRequestHttpException('Invalid parameter passed. Request must required parameter "apple_id"');
            }
            $model->apple_id = $postData['apple_id'];
        }

        if (!empty($postData['is_login_from']) && strtolower($postData['is_login_from']) == User::IS_LOGIN_FROM_GOOGLE) {
            if (empty($postData) || empty($postData['google_id'])) {
                throw new BadRequestHttpException('Invalid parameter passed. Request must required parameter "google_id"');
            }
            $model->google_id = $postData['google_id'];
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
                //$fileName = pathinfo($model->profile_picture->name, PATHINFO_FILENAME);
                $fileName = time() . rand(99999, 88888) . '.' . $ext;
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

            if (empty($postData['is_login_from'])) {
                $model->verification_code = $model->getVerificationCode();
            } else {
                $model->verification_code = "";
                $model->is_verify_user = User::IS_VERIFY_USER_YES;
            }

            $model->is_subscribed_user = (integer)User::IS_VERIFY_USER_YES;

            if ($model->save()) {
                // Insert shop details
                if ($model->is_shop_owner == User::SHOP_OWNER_YES) {
                    $userAddressModel = new UserAddress();
                    $userAddressData['UserAddress'] = $postData;
                    if ($userAddressModel->load($userAddressData)) {
                        $userAddressModel->address = $postData['street'] . ', ' . $postData['city'] . ', ' . $postData['state'] . ', ' . $postData['country'] . ' ' . $postData['zip_code'];
                        $userAddressModel->user_id = $model->id;
                        $userAddressModel->type = UserAddress::TYPE_SHOP;
                        $userAddressModel->is_primary_address = UserAddress::IS_ADDRESS_PRIMARY_YES;
                        $userAddressModel->save(false);
                    }

                    // shop owner detail start
                    $shopDetailModel = new ShopDetail();
                    $shopDetailModel->shop_logo = UploadedFile::getInstanceByName('shop_logo');
                    $shopDetailModel->shop_cover_picture = UploadedFile::getInstanceByName('shop_cover_picture');
                    $shopDetails['ShopDetail'] = $postData;

                    if ($shopDetailModel->load($shopDetails)) {
                        $shopDetailModel->user_id = $model->id;
                        //shop logo code
                        $uploadDirPathLogo = Yii::getAlias('@shopLogoRelativePath');
                        $uploadThumbDirPathLogo = Yii::getAlias('@shopLogoThumbRelativePath');
                        $thumbImagePathLogo = '';

                        if ($shopDetailModel->shop_logo instanceof UploadedFile) {
                            // Create Shop logo upload directory if not exist
                            if (!is_dir($uploadDirPathLogo)) {
                                mkdir($uploadDirPathLogo, 0777);
                            }

                            // Create Shop logo thumb upload directory if not exist
                            if (!is_dir($uploadThumbDirPathLogo)) {
                                mkdir($uploadThumbDirPathLogo, 0777);
                            }

                            $logoExt = $shopDetailModel->shop_logo->extension;
                            //$shopLogoFileName = pathinfo($shopDetailModel->shop_logo->name, PATHINFO_FILENAME);
                            $shopLogoFileName = time() . rand(99999, 88888) . '.' . $logoExt;
                            // Upload shop logo
                            $shopDetailModel->shop_logo->saveAs($uploadDirPathLogo . '/' . $shopLogoFileName);
                            // Create thumb of shoplogo
                            $actualImagePathLogo = $uploadDirPathLogo . '/' . $shopLogoFileName;
                            $thumbImagePathLogo = $uploadThumbDirPathLogo . '/' . $shopLogoFileName;
                            Image::thumbnail($actualImagePathLogo, Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height'])->save($thumbImagePathLogo, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
                            // Insert shop logo name into database
                            $shopDetailModel->shop_logo = $shopLogoFileName;
                        }
                        // shop cover picture
                        $uploadDirPathCoverPicture = Yii::getAlias('@shopCoverPictureRelativePath');
                        $uploadThumbDirPathCoverPicture = Yii::getAlias('@shopCoverPictureThumbRelativePath');
                        $thumbImagePathCoverPicture = '';
                        if ($shopDetailModel->shop_cover_picture instanceof UploadedFile) {
                            // Create Shop cover picture upload directory if not exist
                            if (!is_dir($uploadDirPathCoverPicture)) {
                                mkdir($uploadDirPathCoverPicture, 0777);
                            }

                            // Create Shop cover picture thumb upload directory if not exist
                            if (!is_dir($uploadThumbDirPathCoverPicture)) {
                                mkdir($uploadThumbDirPathCoverPicture, 0777);
                            }

                            $shopCoverPictureExt = $shopDetailModel->shop_cover_picture->extension;
                            //$shopCoverPictureFileName = pathinfo($shopDetailModel->shop_cover_picture->name, PATHINFO_FILENAME);
                            $shopCoverPictureFileName = time() . rand(99999, 88888) . '.' . $shopCoverPictureExt;
                            // Upload shop cover picture
                            $shopDetailModel->shop_cover_picture->saveAs($uploadDirPathCoverPicture . '/' . $shopCoverPictureFileName);
                            // Create thumb of shoplogo
                            $actualImagePathCoverPicture = $uploadDirPathCoverPicture . '/' . $shopCoverPictureFileName;
                            $thumbImagePathCoverPicture = $uploadThumbDirPathCoverPicture . '/' . $shopCoverPictureFileName;
                            Image::thumbnail($actualImagePathCoverPicture, Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height'])->save($thumbImagePathCoverPicture, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
                            // Insert shop cover picture name into database
                            $shopDetailModel->shop_cover_picture = $shopCoverPictureFileName;
                        }
                        $shopDetailModel->save(false);
                    }
                }

                if (empty($postData['is_login_from'])) {
                    Yii::$app->mailer->compose('api/userRegistrationVerificationCode-html', ['model' => $model])
                        ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
                        ->setTo($model->email)
                        ->setSubject('Profile verification code!')
                        ->send();
                }

                // shop owner detail end
                // Get profile picture
                $showProfilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                if (!empty($model->profile_picture) && file_exists(Yii::getAlias('@profilePictureThumbRelativePath') . '/' . $model->profile_picture)) {
                    $showProfilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@profilePictureThumbAbsolutePath') . '/' . $model->profile_picture;
                }
                $model->profile_picture = $showProfilePicture;
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
        $model = User::find()->where(['id' => $id])->one();
        if (!$model instanceof User) {
            throw new NotFoundHttpException('User doesn\'t exist.');
        }
        $postData = \Yii::$app->request->post();
        $data['User'] = $postData;
        $model->scenario = User::SCENARIO_USER_UPDATE;
        if ($model->load($data) && $model->validate()) {
            $model->password_hash = \Yii::$app->security->generatePasswordHash($model->password);

            if (!empty($data['User']['email']) && $data['User']['email'] != $model->email) {
                $model->email = $data['User']['email'];
            }

            if (!empty($data['User']['country_code'])) {
                $model->country_code = $data['User']['country_code'];
            }

            if (!empty($data['User']['timezone_id'])) {
                $model->timezone_id = $data['User']['timezone_id'];
            }

            if ($model->save()) {

                if ($model->is_shop_owner == User::SHOP_OWNER_YES) {

                    $userAddressModel = new UserAddress();
                    $userAddressData['UserAddress'] = $postData;
                    $userAddressData['UserAddress']['user_id'] = $model->id;
                    $addressModel = UserAddress::find()->where(['user_id' => Yii::$app->user->identity->id, 'street' => $userAddressData['UserAddress']['street'], 'city' => $userAddressData['UserAddress']['city'], 'state' => $userAddressData['UserAddress']['state'], 'country' => $userAddressData['UserAddress']['country'], 'zip_code' => $userAddressData['UserAddress']['zip_code']])->one();

                    $userAddressModel->address = $postData['street'] . ', ' . $postData['city'] . ', ' . $postData['state'] . ', ' . $postData['country'] . ' ' . $postData['zip_code'];

                    if ($userAddressModel->load($userAddressData) && $userAddressModel->validate()) {

                        if (!empty($addressModel) && $addressModel instanceof UserAddress && $userAddressData['UserAddress']['is_primary_address'] == UserAddress::IS_ADDRESS_PRIMARY_YES) {
                            $previousAddress = UserAddress::find()->where(['is_primary_address' => UserAddress::IS_ADDRESS_PRIMARY_YES, 'user_id' => Yii::$app->user->identity->id])->andWhere('id!=' . $addressModel->id)->all();
                            if (!empty($previousAddress)) {
                                foreach ($previousAddress as $keys => $previousAddressRow) {
                                    if (!empty($previousAddressRow) && $previousAddressRow instanceof UserAddress) {
                                        $previousAddressRow->is_primary_address = UserAddress::IS_ADDRESS_PRIMARY_NO;
                                        $previousAddressRow->save(false);
                                    }
                                }
                            }
                            $addressModel->is_primary_address = UserAddress::IS_ADDRESS_PRIMARY_YES;
                            $addressModel->save(false);
                        } else if (empty($addressModel) && (!empty($userAddressData['UserAddress']['is_primary_address'])) && $userAddressData['UserAddress']['is_primary_address'] == UserAddress::IS_ADDRESS_PRIMARY_YES) {
                            $userAddressModel->type = UserAddress::TYPE_SHOP;
                            $userAddressModel->is_primary_address = UserAddress::IS_ADDRESS_PRIMARY_YES;
                            $userAddressModel->save(false);
                        } elseif (empty($addressModel) && empty($userAddressData['UserAddress']['is_primary_address'])) {
                            $userAddressModel->type = UserAddress::TYPE_SHOP;
                            $userAddressModel->is_primary_address = UserAddress::IS_ADDRESS_PRIMARY_YES;
                            $userAddressModel->save(false);
                        }
                    } else {
                        return $userAddressModel;
                    }
                }
                $uploadThumbDirPath = Yii::getAlias('@profilePictureThumbRelativePath');
                $thumbImagePath = $uploadThumbDirPath . '/' . $model->profile_picture;
                $showProfilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                if (file_exists($thumbImagePath) && !empty($model->profile_picture)) {
                    $showProfilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@profilePictureThumbAbsolutePath') . '/' . $model->profile_picture;
                }
                $model->profile_picture = $showProfilePicture;
            }
        }
        return $model;
    }

    /**
     * use to get users data
     * @return User
     * @throws NotFoundHttpException
     * @throws \yii\base\Exception
     */
    public function actionView($id)
    {
        $model = User::findOne($id);

        if (!$model instanceof User) {
            throw new NotFoundHttpException('User doesn\'t exist.');
        }

        $uploadThumbDirPath = Yii::getAlias('@profilePictureRelativePath');
        $thumbImagePath = $uploadThumbDirPath . '/' . $model->profile_picture;
        $showProfilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
        if (!empty($model->profile_picture) && file_exists($thumbImagePath)) {
            $showProfilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@profilePictureAbsolutePath') . '/' . $model->profile_picture;
        }
        $model->profile_picture = $showProfilePicture;

        $model->height = (string)$model->height;
        $model->top_size = (string)$model->top_size;
        $model->pant_size = (string)$model->pant_size;
        $model->bust_size = (string)$model->bust_size;
        $model->waist_size = (string)$model->waist_size;
        $model->hip_size = (string)$model->hip_size;

        return $model;
    }

    /**
     * use to update profile picture
     * @return User
     * @throws NotFoundHttpException
     * @throws \yii\base\Exception
     */
    public function actionUpdateProfilePicture($id)
    {
        $model = User::findOne($id);
        $model->scenario = User::PROFILE_PICTURE_UPDATE;
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
                //$fileName = pathinfo($model->profile_picture->name, PATHINFO_FILENAME);
                $fileName = time() . rand(99999, 88888) . '.' . $ext;
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

        if ($model->save()) {
            $uploadThumbDirPath = Yii::getAlias('@profilePictureThumbRelativePath');
            $thumbImagePath = $uploadThumbDirPath . '/' . $model->profile_picture;
            $showProfilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
            if (!empty($model->profile_picture) && file_exists($thumbImagePath)) {
                $showProfilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@profilePictureThumbAbsolutePath') . '/' . $model->profile_picture;
            }
            $model->profile_picture = $showProfilePicture;
        }
        return $model;
    }

    /**
     * @param $id
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDeleteUserAddress($id)
    {
        $model = UserAddress::findOne($id);
        if (!$model instanceof UserAddress) {
            throw new NotFoundHttpException('Address doesn\'t exist.');
        }
        $model->delete();
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

        $postData = Yii::$app->request->post();

        if (empty($postData) || empty($postData['notification_token'])) {
            throw new BadRequestHttpException('Invalid parameter passed. Request must required parameter "notification_token"');
        }
        if (empty($postData) || empty($postData['device_platform'])) {
            throw new BadRequestHttpException('Invalid parameter passed. Request must required parameter "device_platform"');
        }

        if (!empty($postData) && !empty($postData['is_login_from']) && strtolower($postData['is_login_from']) == User::IS_LOGIN_FROM_FACEBOOK) {
            if (empty($postData) || empty($postData['facebook_id'])) {
                throw new BadRequestHttpException('Invalid parameter passed. Request must required parameter "facebook_id"');
            }
            $modelPostData = User::find()->where(['facebook_id' => $postData['facebook_id']])->one();
            if (!empty($modelPostData) && $modelPostData instanceof User) {
                $data['Login']['email'] = $modelPostData->email;
                $model->email = $modelPostData->email;
            } else {
                throw new NotFoundHttpException('User doesn\'t exist.');
            }
        }

        if (!empty($postData) && !empty($postData['is_login_from']) && strtolower($postData['is_login_from']) == User::IS_LOGIN_FROM_APPLE) {
            if (empty($postData) || empty($postData['apple_id'])) {
                throw new BadRequestHttpException('Invalid parameter passed. Request must required parameter "apple_id"');
            }
            $modelPostData = User::find()->where(['apple_id' => $postData['apple_id']])->one();
            //p($modelPostData);
            if (!empty($modelPostData) && $modelPostData instanceof User) {
                $data['Login']['email'] = $modelPostData->email;
                $model->email = $modelPostData->email;
            } else {
                throw new NotFoundHttpException('User doesn\'t exist.');
            }
        }

        if (!empty($postData) && !empty($postData['is_login_from']) && strtolower($postData['is_login_from']) == User::IS_LOGIN_FROM_GOOGLE) {
            if (empty($postData) || empty($postData['google_id'])) {
                throw new BadRequestHttpException('Invalid parameter passed. Request must required parameter "google_id"');
            }
            $modelPostData = User::find()->where(['google_id' => $postData['google_id']])->one();
            if (!empty($modelPostData) && $modelPostData instanceof User) {
                $data['Login']['email'] = $modelPostData->email;
                $model->email = $modelPostData->email;
            } else {
                throw new NotFoundHttpException('User doesn\'t exist.');
            }
        }

        if (empty($postData['is_login_from'])) {
            $model->scenario = Login::SCENARIO_LOGIN_FROM_APP;
        }

        $modelUser = "";
        if ($model->load($data) && $model->validate()) {
            if (!$model->login()) {
                throw new ForbiddenHttpException('Unable to process your request. Please contact administrator');
            }

            //update notification token
            if (!empty($postData['notification_token']) && !empty($postData['device_platform']) && !empty(Yii::$app->user->identity->id)) {
                $loginDevice = UserDevice::find()->where(['notification_token' => $postData['notification_token'], 'device_platform' => $postData['device_platform'], 'user_id' => Yii::$app->user->identity->id])->one();
                if (empty($loginDevice)) {
                    $loginDevice = new UserDevice();
                    $loginDevice->user_id = Yii::$app->user->identity->id;
                    $loginDevice->notification_token = $postData['notification_token'];
                    $loginDevice->device_platform = $postData['device_platform'];
                    $loginDevice->save(false);
                }
            }

            if (!empty($model->user) && $model->user->is_verify_user == 0) {

                $modelUser = User::findOne($model->user->id);
                $modelUser->verification_code = $modelUser->getVerificationCode();
                $modelUser->access_token = $modelUser->generateAccessToken();
                $modelUser->save(false);

                if (!empty($modelUser->email)) {
                    Yii::$app->mailer->compose('api/userRegistrationVerificationCode-html', ['model' => $modelUser])
                        ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
                        ->setTo($modelUser->email)
                        ->setSubject('Profile verification code!')
                        ->send();
                }
            }
            $dataResponse = array_merge($model->toArray(), ['user_id' => $model->user->id, 'is_verify_user' => $model->user->is_verify_user]);
            return $dataResponse;
        } else {
            return $model;
        }
    }

    /**
     * @return string[]
     * @throws BadRequestHttpException
     */
    public function actionLogout()
    {
        $postData = Yii::$app->request->get();

        if (empty($postData) || empty($postData['notification_token'])) {
            throw new BadRequestHttpException('Invalid parameter passed. Request must required parameter "notification_token"');
        }

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

                $loginDevice = UserDevice::find()->where(["user_id" => $userModel->id, "notification_token" => $postData['notification_token']])->one();
                if (!empty($loginDevice)) {
                    $loginDevice->delete();
                }
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
            //$userModel->password_hash = $model->getUser()->password_hash;
            $userModel->temporary_password = $tmpPassword;
            if ($userModel->save(false)) {

                if (!empty($model->email)) {
                    $mail = \Yii::$app->mailer->compose('api/forgot_password', ['model' => $model, 'user' => $userModel, 'appname' => Yii::$app->name])
                        ->setFrom([\Yii::$app->params['from_email'] => \Yii::$app->name])
                        ->setTo($model->email)
                        ->setSubject('Forgot your password')
                        ->send();
                    if (!$mail) {
                        throw new ServerErrorHttpException("Unable to send an email. Please try again later");
                    }
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

        $model = User::find()->where(['temporary_password' => $postData['tmp_password'], 'user_type' => User::USER_TYPE_NORMAL])->one();
        if (!empty($model)) {
            $uploadThumbDirPath = Yii::getAlias('@profilePictureThumbRelativePath');
            $thumbImagePath = $uploadThumbDirPath . '/' . $model->profile_picture;
            $profile_picture = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
            if (!empty($model->profile_picture) && file_exists($thumbImagePath)) {
                $profile_picture = Yii::$app->request->getHostInfo() . Yii::getAlias('@profilePictureThumbAbsolutePath') . '/' . $model->profile_picture;
            }
            $model->profile_picture = $profile_picture;
        }

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
            if (!$userModel->save(false)) {
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

    /**
     * @return User|\yii\db\ActiveRecord
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionVerifyProfileVerificationCode()
    {
        $postData = \Yii::$app->request->post();
        if (empty($postData) || empty($postData['verification_code'])) {
            throw new BadRequestHttpException('Invalid parameter passed. Request must required parameter "verification_code"');
        }

        $model = User::find()->where(['verification_code' => $postData['verification_code'], 'user_type' => User::USER_TYPE_NORMAL])->one();

        if (!$model instanceof User) {
            throw new NotFoundHttpException('Verification code doesn\'t exist.');
        }

        if (!empty($model) && $model instanceof User) {
            $model->is_verify_user = User::IS_VERIFY_USER_YES;
            if ($model->is_shop_owner == 1 || $model->is_shop_owner == '1') {
                $addedAddress = UserAddress::find()->where(['is_primary_address' => UserAddress::IS_ADDRESS_PRIMARY_NO, 'user_id' => $model->id])->all();
                if (!empty($addedAddress) && !empty($addedAddress[0]) && $addedAddress[0] instanceof UserAddress) {
                    $address = $addedAddress[0];
                    $address->is_primary_address = UserAddress::IS_ADDRESS_PRIMARY_YES;
                    $address->save(false);
                }
            }
            $model->save(false);
        }

        $uploadThumbDirPath = Yii::getAlias('@profilePictureThumbRelativePath');
        $thumbImagePath = $uploadThumbDirPath . '/' . $model->profile_picture;
        $profile_picture = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
        if (!empty($model) && $model instanceof User && !empty($model->profile_picture) && file_exists($thumbImagePath)) {
            $profile_picture = Yii::$app->request->getHostInfo() . Yii::getAlias('@profilePictureThumbAbsolutePath') . '/' . $model->profile_picture;
        }

        $model->profile_picture = $profile_picture;

        return $model;
    }

    /**
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionResendVerificationCode()
    {
        $post = \Yii::$app->request->post();
        if (empty($post) || empty($post['email'])) {
            throw new BadRequestHttpException('Invalid parameter passed. Request must required parameter "email"');
        }

        $modelUser = User::findByEmail($post['email']);
        if (empty($modelUser) && !$modelUser instanceof User) {
            throw new NotFoundHttpException('Email doesn\'t exist.');
        }

        $modelUser->verification_code = $modelUser->getVerificationCode();
        $modelUser->save(false);

        if (!empty($modelUser->email)) {
            Yii::$app->mailer->compose('api/userRegistrationVerificationCode-html', ['model' => $modelUser])
                ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
                ->setTo($modelUser->email)
                ->setSubject('Profile verification code!')
                ->send();
        }
    }

    /**
     * @return User
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionEnterSizeInformation()
    {
        $postData = \Yii::$app->request->post();

        if (empty($postData) || empty($postData['user_id'])) {
            throw new BadRequestHttpException('Invalid parameter passed. Request must required parameter "user_id"');
        }

        $model = User::find()->where(['id' => $postData['user_id'], 'user_type' => User::USER_TYPE_NORMAL, 'is_shop_owner' => User::SHOP_OWNER_NO])->one();
        if (!$model instanceof User) {
            throw new NotFoundHttpException('User doesn\'t exist.');
        }
        $model->scenario = User::SCENARIO_ADD_SIZE_INFORMARION_FOR_NORMAL_USER;
        $dataPost['User'] = $postData;

        if ($model->load($dataPost) && $model->validate()) {
            if ($model->save()) {
                $uploadThumbDirPath = Yii::getAlias('@profilePictureThumbRelativePath');
                $thumbImagePath = $uploadThumbDirPath . '/' . $model->profile_picture;
                $profile_picture = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                if (!empty($model) && $model instanceof User && !empty($model->profile_picture) && file_exists($thumbImagePath)) {
                    $profile_picture = Yii::$app->request->getHostInfo() . Yii::getAlias('@profilePictureThumbAbsolutePath') . '/' . $model->profile_picture;
                }
                $model->profile_picture = $profile_picture;
            }
        }
        return $model;
    }

    /**
     * @return User|\yii\db\ActiveRecord
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionChangeNotificationSetting()
    {
        $postData = \Yii::$app->request->post();

        if (empty($postData) || empty($postData['user_id'])) {
            throw new BadRequestHttpException('Invalid parameter passed. Request must required parameter "user_id"');
        }

        $model = User::find()->where(['id' => $postData['user_id'], 'user_type' => User::USER_TYPE_NORMAL])->one();
        if (!$model instanceof User) {
            throw new NotFoundHttpException('User doesn\'t exist.');
        }

        $dataPost['User'] = $postData;
        $model->scenario = User::SCENARIO_API_NOTIFICATION_SETTING;

        if ($model->load($dataPost) && $model->validate()) {
            if ($model->save()) {
                $uploadThumbDirPath = Yii::getAlias('@profilePictureThumbRelativePath');
                $thumbImagePath = $uploadThumbDirPath . '/' . $model->profile_picture;
                $profile_picture = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                if (!empty($model) && $model instanceof User && !empty($model->profile_picture) && file_exists($thumbImagePath)) {
                    $profile_picture = Yii::$app->request->getHostInfo() . Yii::getAlias('@profilePictureThumbAbsolutePath') . '/' . $model->profile_picture;
                }
                $model->profile_picture = $profile_picture;
            }
        }
        return $model;
    }
}
