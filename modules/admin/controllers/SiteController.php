<?php

namespace app\modules\admin\controllers;

use app\models\{
    Order,
    Product
};


use yii\filters\{
    VerbFilter,
    AccessControl
};
use app\modules\admin\models\{
    LoginForm,
    ForgotPasswordForm,
    ResetPasswordForm,
    User
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
                'only' => ['logout', 'index', 'forgot-password', 'error'],
                'rules' => [
                    [
                        'actions' => ['logout', 'index', 'error'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['forgot-password', 'error'],
                        'allow' => true,
                        'roles' => ['?'],
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
        $modelTotalCustomer = User::find()->where(['user_type' => User::USER_TYPE_NORMAL_USER])->count();

        $todayFrom = date('Y-m-d 00:00:01');
        $todayTo = date('Y-m-d 23:59:59');
        $modelTotalCustomerToday = User::find()->where(['user_type' => User::USER_TYPE_NORMAL_USER])->andWhere(['between', 'created_at', $todayFrom, $todayTo])->count();

        $modelTotalProduct = Product::find()->count();
        $modelTotalOrder = Order::find()->count();
        $modelTotalOrderDelivered = Order::find()->where(['status' => Order::STATUS_ORDER_COMPLETED])->count();
        $modelTotalOrderPending = Order::find()->where(['status' => Order::STATUS_ORDER_PENDING])->count();

        return $this->render('index', [
            'totalCustomer' => $modelTotalCustomer,
            'totalCustomerToday' => $modelTotalCustomerToday,
            'totalProduct' => $modelTotalProduct,
            'totalOrder' => $modelTotalOrder,
            'totalOrderDeliveredAndCompleted' => $modelTotalOrderDelivered,
            'totalOrderPending' => $modelTotalOrderPending,
        ]);

    }

    /**
     * Login
     * @return string|Response
     */
    public function actionLogin()
    {
        $this->layout = 'login';
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
        return $this->redirect(['/admin/site/login']);
    }

    /**
     * Forgot password
     * @return string|Response
     */
    public function actionForgotPassword()
    {
        $model = new ForgotPasswordForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            if ($model->sendEmail($model->email)) {
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

    public function actionError()
    {

        $this->layout = 'error';
        $exception = Yii::$app->errorHandler->exception;
        if ($exception !== null) {
            return $this->render('error', ['exception' => $exception]);
        }
    }
}
