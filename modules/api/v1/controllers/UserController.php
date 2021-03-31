<?php

namespace app\modules\api\v1\controllers;

use Yii;
use yii\helpers\Url;
use yii\filters\Cors;
use yii\rest\ActiveController;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UploadedFile;
use app\modules\api\v1\models\Login;
use app\modules\api\v1\models\User;
use app\modules\api\v1\models\ResetPassword;
use app\modules\api\v1\models\ForgotPassword;
use app\modules\api\v1\models\ChangePassword;
use app\modules\api\v1\models\UserAddress;
use yii\imagine\Image;

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
            'only' => ['index', 'view', 'update', 'logout', 'change-password'],
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
        // unset($actions['view']);
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
     *
     */
    public function actionCreate()
    {
        $image_url = '';
        $model = new User();
        $postData = \Yii::$app->request->post();
        $data['User'] = $postData;
        $data1['UserAddress'] = $postData;
        $model->scenario = User::SCENARIO_USER_CREATE;
        if (!empty($postData['is_shop_owner']) && $postData['is_shop_owner'] == User::SHOP_OWNER_YES) {
            $model->scenario = User::SCENARIO_SHOP_OWNER;
        }
        if ($model->load($data) && $model->validate()) {
            //file upload
            $profile_image = UploadedFile::getInstanceByName('profile_picture');
            if (is_object($profile_image)) {
                $model->profile_picture = $profile_image;
                $filename =  time() . '.' . $model->profile_picture->extension;
                $model->profile_picture->saveAs('uploads/profile_images/image/' . $filename);
                $model->profile_picture = $filename;
                $thumbnail_path = 'uploads/profile_images/thumbnail/' . $filename;
                $image_path = 'uploads/profile_images/image/' . $filename;
                // Generate a thumbnail image
                Image::thumbnail($image_path, 200, 200)->save($thumbnail_path, ['quality' => 80]);
                if (file_exists($thumbnail_path)) {
                    $image_url = Url::base('http') . '/uploads/profile_images/thumbnail/' . $filename;
                }
            }
            //file upload end
            $model->user_type = (string)User::USER_TYPE_NORMAL;
            $model->password_hash = \Yii::$app->security->generatePasswordHash($model->password);
            $model->created_at = date('Y-m-d H:i:s');
            if ($model->save()) {
                if ($model->is_shop_owner == User::SHOP_OWNER_YES) {
                    $userAddressModel = new UserAddress();
                    if ($userAddressModel->load($data1)) {
                        $userAddressModel->user_id = $model->id;
                        $userAddressModel->created_at = date('Y-m-d H:i:s');
                        $userAddressModel->save(false);
                    }
                }
                if (!empty($model->profile_picture)) {
                    $model->profile_picture = $image_url;
                }
            }
        }

        return $model;
    }

    /**
     *
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
                        $userAddressModel =  UserAddress::findOne($get_address_id->id);
                        if ($userAddressModel->load($data1)) {
                            $userAddressModel->updated_at = date('Y-m-d H:i:s');
                            $userAddressModel->save(false);
                        }
                    }
                }
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
