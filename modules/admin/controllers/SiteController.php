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
        $modelTotalIncome = Order::find()->where(['status' => Order::STATUS_ORDER_COMPLETED])->sum('total_amount');

        // Chart uses

        $min = $max = 0;
        $month = [
            0 => 'Jan',
            1 => 'Feb',
            2 => 'Mar',
            3 => 'Apr',
            4 => 'May',
            5 => 'Jun',
            6 => 'Jul',
            7 => 'Aug',
            8 => 'Sep',
            9 => 'Oct',
            10 => 'Nov',
            11 => 'Dec',
        ];

        $isLeapYear = $this->yearCheckIsLeap(date('Y'));
        $thirtyOneDays = [0, 2, 4, 6, 7, 9, 11];
        $thirtyDays = [3, 5, 8, 10];

        $monthWiseOrders = [];
        for ($i = 0; $i < 12; $i++) {
            $mnt = ($i + 1);
            $year = date('Y');
            $monthStartDate = date('Y-m-d 00:00:01', strtotime($year . "-" . $mnt . "-1"));
            if (in_array($i, $thirtyOneDays)) {
                $monthEndDate = date('Y-m-d 23:23:59', strtotime($year . "-" . $mnt . "-31"));
            } elseif (in_array($i, $thirtyDays)) {
                $monthEndDate = date('Y-m-d 23:23:59', strtotime($year . "-" . $mnt . "-30"));
            } else {
                if (!$isLeapYear) {
                    $monthEndDate = date('Y-m-d 23:23:59', strtotime($year . "-" . $mnt . "-28"));
                } else {
                    $monthEndDate = date('Y-m-d 23:23:59', strtotime($year . "-" . $mnt . "-29"));
                }
            }
            $monthWiseOrders[] = Order::find()->where(['between', 'created_at', $monthStartDate, $monthEndDate])->count();
        }
        $min = (!empty($monthWiseOrders)) ? min($monthWiseOrders) : 0;
        $max = (!empty($monthWiseOrders)) ? max($monthWiseOrders) : 0;
        $monthWiseOrders = [$monthWiseOrders[0], $monthWiseOrders[1], $monthWiseOrders[2], $monthWiseOrders[3], $monthWiseOrders[4], $monthWiseOrders[5], $monthWiseOrders[6], $monthWiseOrders[7], $monthWiseOrders[8], $monthWiseOrders[9], $monthWiseOrders[10], $monthWiseOrders[11]];

        return $this->render('index', [
            'totalCustomer' => $modelTotalCustomer,
            'totalCustomerToday' => $modelTotalCustomerToday,
            'totalProduct' => $modelTotalProduct,
            'totalOrder' => $modelTotalOrder,
            'totalOrderDeliveredAndCompleted' => $modelTotalOrderDelivered,
            'totalOrderPending' => $modelTotalOrderPending,
            'totalIncome' => (!empty($modelTotalIncome)) ? $modelTotalIncome : 0,
            'month' => $month,
            'monthWiseOrders' => $monthWiseOrders,
            'min' => $min,
            'max' => $max
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

    public function yearCheckIsLeap($my_year)
    {
        $result = false;
        if ($my_year % 400 == 0)
            $result = true;
        if ($my_year % 4 == 0)
            $result = true;
        else if ($my_year % 100 == 0)
            $result = false;
        else
            $result = false;

        return $result;
    }
}
