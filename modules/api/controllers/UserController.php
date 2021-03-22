<?php

namespace app\modules\api\controllers;

use Yii;
use yii\filters\Cors;
use yii\rest\ActiveController;
use yii\filters\auth\{
    HttpBasicAuth,
    CompositeAuth,
    HttpBearerAuth,
    QueryParamAuth
};
use yii\web\{
    BadRequestHttpException,
    ForbiddenHttpException,
    NotFoundHttpException,
    ServerErrorHttpException
};
use app\modules\api\models\{
    Login,
    User,
    ResetPassword,
    ForgotPassword,
    ChangePassword
};

/**
 * Class UserController
 * @package app\modules\api\controllers
 */
class UserController extends ActiveController
{
    /**
     * @var string
     */
    public $modelClass = 'app\modules\api\models\User';

    /**
     * @var string
     */
    public $searchModelClass = 'app\modules\api\models\search\UserSearch';

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
            'only' => ['index', 'view', 'create', 'update', 'logout', 'change-password'],
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
        unset($actions['delete']);
        unset($actions['view']);
        return $actions;
    }

    /**
     * @return Login
     * @throws ForbiddenHttpException
     */
    public function actionLogin()
    {
        $model = new Login();
        $data['Login'] = \Yii::$app->request->post();

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
        $model = User::find()->where(['temporary_password' => $postData['tmp_password'],'user_type' => Yii::$app->params['normal_user']])->one();
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
}