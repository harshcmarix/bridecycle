<?php

namespace app\modules\admin\controllers;

use yii\filters\{
    VerbFilter,
    AccessControl
};
use app\modules\admin\models\{
    LoginForm,
    ForgotPasswordForm,
    ResetPasswordForm
};
use yii\web\{
    Response,
    BadRequestHttpException,
    Controller
};
use Yii;
use yii\base\InvalidParamException;
use kartik\growl\Growl;

/**
 * Class SiteController
 * @package app\modules\admin\controllers
 */
class SiteController extends Controller
{
    /**
     * @return array[]
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login
     * @return string|Response
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            Yii::$app->session->setFlash('success', "You are successfully logged in");
            return $this->redirect(['/admin/site/index']);
        }

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        // return $this->goHome();
        return $this->redirect(['/admin']);
    }

    /**
     * Forgot password
     * @return string|Response
     */
    public function actionForgotPassword()
    {
        $model = new ForgotPasswordForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                 Yii::$app->session->setFlash('success', 'Check your email for further instructions.');
                return $this->goHome();
            }

            Yii::$app->session->setFlash('danger', 'Sorry, we are unable to reset password for email provided.');
        }

        return $this->render('forgot-password', [
            'model' => $model,
        ]);
    }

    /**
     * @param $token
     * @return string|Response
     * @throws BadRequestHttpException
     * @throws \yii\base\Exception
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'Password reset successfully.');
            return $this->goHome();
        }

        return $this->render('reset-password', [
            'model' => $model,
        ]);
    }
}
