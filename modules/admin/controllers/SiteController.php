<?php

namespace app\modules\admin\controllers;

use app\models\ProductStatus;
use app\models\Order;
use app\models\Product;
use app\models\Ads;
use app\models\Brand;
use app\models\Tailor;
use app\modules\admin\models\ForgotPasswordForm;
use app\modules\admin\models\LoginForm;
use app\modules\admin\models\ResetPasswordForm;
use app\modules\admin\models\User;
use Yii;
use yii\base\InvalidParamException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;

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
                    'logout' => ['post', 'get'],
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
        $todayFrom = date('Y-m-d 00:00:01');
        $todayTo = date('Y-m-d 23:59:59');

        $modelTotalCustomer = User::find()->where(['user_type' => User::USER_TYPE_NORMAL_USER, 'is_shop_owner' => '0'])->count();

        $totalShopOwnerCustomer = User::find()->where(['user_type' => User::USER_TYPE_NORMAL_USER, 'is_shop_owner' => '1'])->count();

        $totalShopOwnerCustomerToday = User::find()->where(['user_type' => User::USER_TYPE_NORMAL_USER, 'is_shop_owner' => '1'])->andWhere(['between', 'created_at', $todayFrom, $todayTo])->count();

        $totSubAdmin = User::find()->where(['user_type' => User::USER_TYPE_SUB_ADMIN])->count();

        $modelTotalCustomerToday = User::find()->where(['user_type' => User::USER_TYPE_NORMAL_USER])->andWhere(['between', 'created_at', $todayFrom, $todayTo])->count();

        $totalAds = Ads::find()->count();

        $totalBrand = Brand::find()->count();

        $totalNewBrandToday = Brand::find()->Where(['between', 'created_at', $todayFrom, $todayTo])->count();

        $modelTotalProduct = Product::find()->count();

        $modelTotalProductPendingApproval = Product::find()->where(['status_id' => ProductStatus::STATUS_PENDING_APPROVAL])->andWhere(['between', 'created_at', $todayFrom, $todayTo])->count();

        $modelTotalOrder = Order::find()->count();

        $modelTotalOrderLast30Days = Order::find()->where(['between', 'created_at', date('Y-m-d 23:59:59', strtotime('-30 days')), date('Y-m-d 00:00:01')])->count();
        $modelTotalOrderLast7Days = Order::find()->where(['between', 'created_at', date('Y-m-d 23:59:59', strtotime('-7 days')), date('Y-m-d 00:00:01')])->count();
        $modelTotalOrderToday = Order::find()->where(['between', 'created_at', $todayFrom, $todayTo])->count();

        $modelTotalOrderDelivered = Order::find()->where(['status' => Order::STATUS_ORDER_COMPLETED])->count();

        $modelTotalOrderInprogress = Order::find()->where(['status' => Order::STATUS_ORDER_INPROGRESS])->count();

        $modelTotalIncome = Order::find()->where(['status' => Order::STATUS_ORDER_COMPLETED])->sum('total_amount');
        $totalTailor = Tailor::find()->count();

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
        $monthWiseIncomes = [];
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
            $monthIncome = Order::find()->where(['between', 'created_at', $monthStartDate, $monthEndDate])->andWhere(['status' => Order::STATUS_ORDER_COMPLETED])->sum('total_amount');
            $monthWiseIncomes[] = (!empty($monthIncome)) ? $monthIncome : 0;
        }

        $min = (!empty($monthWiseOrders)) ? min($monthWiseOrders) : 0;
        $max = (!empty($monthWiseOrders)) ? max($monthWiseOrders) : 0;
        $monthWiseOrders = [(double)$monthWiseOrders[0], (double)$monthWiseOrders[1], (double)$monthWiseOrders[2], (double)$monthWiseOrders[3], (double)$monthWiseOrders[4], (double)$monthWiseOrders[5], (double)$monthWiseOrders[6], (double)$monthWiseOrders[7], (double)$monthWiseOrders[8], (double)$monthWiseOrders[9], (double)$monthWiseOrders[10], (double)$monthWiseOrders[11]];

        $minIncome = (!empty($monthWiseIncomes)) ? min($monthWiseIncomes) : 0;
        $maxIncome = (!empty($monthWiseIncomes)) ? max($monthWiseIncomes) : 0;
        $monthWiseIncomes = [(double)$monthWiseIncomes[0], (double)$monthWiseIncomes[1], (double)$monthWiseIncomes[2], (double)$monthWiseIncomes[3], (double)$monthWiseIncomes[4], (double)$monthWiseIncomes[5], (double)$monthWiseIncomes[6], (double)$monthWiseIncomes[7], (double)$monthWiseIncomes[8], (double)$monthWiseIncomes[9], (double)$monthWiseIncomes[10], (double)$monthWiseIncomes[11]];

        return $this->render('index', [
            'totalCustomer' => $modelTotalCustomer,
            'totalShopOwnerCustomer' => $totalShopOwnerCustomer,
            'totalShopOwnerCustomerToday' => $totalShopOwnerCustomerToday,
            'totSubAdmin' => $totSubAdmin,
            'totalCustomerToday' => $modelTotalCustomerToday,
            'totalActiveAds' => $totalAds,
            'totalBrand' => $totalBrand,
            'totalNewBrandToday' => $totalNewBrandToday,
            'totalProduct' => $modelTotalProduct,
            'totalProductPendingApproval' => $modelTotalProductPendingApproval,
            'totalOrder' => $modelTotalOrder,
            'totalOrderLastMonth' => $modelTotalOrderLast30Days,
            'totalOrderLastWeek' => $modelTotalOrderLast7Days,
            'totalOrderToday' => $modelTotalOrderToday,
            'totalOrderDeliveredAndCompleted' => $modelTotalOrderDelivered,
            'totalOrderPending' => $modelTotalOrderInprogress,
            'totalIncome' => (!empty($modelTotalIncome)) ? $modelTotalIncome : 0,
            'totalTailor' => $totalTailor,
            'month' => $month,
            'monthWiseOrders' => $monthWiseOrders,
            'min' => $min,
            'max' => $max + 1,
            'monthWiseIncomes' => $monthWiseIncomes,
            'minIncome' => $minIncome,
            'maxIncome' => $maxIncome + 1,
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

    /**
     * @return string|void
     */
    public function actionError()
    {
        $this->layout = 'error';
        $exception = Yii::$app->errorHandler->exception;
        if ($exception !== null) {
            return $this->render('error', ['exception' => $exception]);
        }
    }

    /**
     * @param $my_year
     * @return bool
     */
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

    /**
     * @return false|string
     */
    public function actionCurrentYearOrders()
    {
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
            $monthWiseOrders[] = Order::find()->where(['between', 'created_at', $monthStartDate, $monthEndDate])->andWhere(['status' => Order::STATUS_ORDER_COMPLETED])->count();
        }

        $monthWiseOrders = [(double)$monthWiseOrders[0], (double)$monthWiseOrders[1], (double)$monthWiseOrders[2], (double)$monthWiseOrders[3], (double)$monthWiseOrders[4], (double)$monthWiseOrders[5], (double)$monthWiseOrders[6], (double)$monthWiseOrders[7], (double)$monthWiseOrders[8], (double)$monthWiseOrders[9], (double)$monthWiseOrders[10], (double)$monthWiseOrders[11]];
        $monthWiseOrders = [
            ['name' => 'jan', 'y' => $monthWiseOrders[0]],
            ['name' => 'feb', 'y' => $monthWiseOrders[1]],
            ['name' => 'mar', 'y' => $monthWiseOrders[2]],
            ['name' => 'apr', 'y' => $monthWiseOrders[3]],
            ['name' => 'may', 'y' => $monthWiseOrders[4]],
            ['name' => 'jun', 'y' => $monthWiseOrders[5]],
            ['name' => 'jul', 'y' => $monthWiseOrders[6]],
            ['name' => 'aug', 'y' => $monthWiseOrders[7]],
            ['name' => 'sep', 'y' => $monthWiseOrders[8]],
            ['name' => 'oct', 'y' => $monthWiseOrders[9]],
            ['name' => 'nov', 'y' => $monthWiseOrders[10]],
            ['name' => 'dec', 'y' => $monthWiseOrders[11]],
        ];

        return json_encode((array)$monthWiseOrders);
    }

    /**
     * @return false|string
     */
    public function actionCurrentMonthOrders()
    {
        $currentMonthOrders = [];
        for ($i = 1; $i <= 31; $i++) {
            $startDate = date('Y') . '-' . date('m') . '-' . $i . ' 00:00:01';
            $endDate = date('Y') . '-' . date('m') . '-' . $i . ' 23:23:59';
            $tmparr = [
                'name' => (string)$i,
                'y' => (double)Order::find()->where(['between', 'created_at', $startDate, $endDate])->andWhere(['status' => Order::STATUS_ORDER_COMPLETED])->count()
            ];
            array_push($currentMonthOrders, $tmparr);
        }

        return json_encode($currentMonthOrders);
    }

    /**
     * @return false|string
     */
    public function actionCurrentWeekOrders()
    {
        $currentWeekOrders = [];
        $weekStart = date('d', strtotime('monday this week'));
        $weekEnd = date('d', strtotime('sunday this week'));
        for ($i = $weekStart; $i <= $weekEnd; $i++) {
            $startDate = date('Y') . '-' . date('m') . '-' . $i . ' 00:00:01';
            $endDate = date('Y') . '-' . date('m') . '-' . $i . ' 23:23:59';
            $datetime = \DateTime::createFromFormat('Ymd', date('Y') . date('m') . $i);
            $dayName = $datetime->format('D');
            $tmparr = [
                'name' => $dayName,
                'y' => (double)Order::find()->where(['between', 'created_at', $startDate, $endDate])->andWhere(['status' => Order::STATUS_ORDER_COMPLETED])->count()
            ];
            array_push($currentWeekOrders, $tmparr);
        }
        return json_encode($currentWeekOrders);
    }

    /**
     * @return false|string
     */
    public function actionCurrentDayOrders()
    {
        $todayOrders = [];
        $tmparr = [
            'name' => 'Today',
            'y' => (double)Order::find()->where('DATE(`created_at`) = CURRENT_DATE')->andWhere(['status' => Order::STATUS_ORDER_COMPLETED])->count()
        ];
        array_push($todayOrders, $tmparr);
        return json_encode($todayOrders);
    }

    /**
     * @return false|string
     */
    public function actionCurrentYearIncome()
    {
        $isLeapYear = $this->yearCheckIsLeap(date('Y'));
        $thirtyOneDays = [0, 2, 4, 6, 7, 9, 11];
        $thirtyDays = [3, 5, 8, 10];

        $monthWiseIncome = [];
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
            $monthWiseIncome[] = Order::find()->where(['between', 'created_at', $monthStartDate, $monthEndDate])->andWhere(['status' => Order::STATUS_ORDER_COMPLETED])->sum('total_amount');
        }

        $monthWiseIncome = [(double)$monthWiseIncome[0], (double)$monthWiseIncome[1], (double)$monthWiseIncome[2], (double)$monthWiseIncome[3], (double)$monthWiseIncome[4], (double)$monthWiseIncome[5], (double)$monthWiseIncome[6], (double)$monthWiseIncome[7], (double)$monthWiseIncome[8], (double)$monthWiseIncome[9], (double)$monthWiseIncome[10], (double)$monthWiseIncome[11]];
        $monthWiseIncome = [
            ['name' => 'jan', 'y' => $monthWiseIncome[0]],
            ['name' => 'feb', 'y' => $monthWiseIncome[1]],
            ['name' => 'mar', 'y' => $monthWiseIncome[2]],
            ['name' => 'apr', 'y' => $monthWiseIncome[3]],
            ['name' => 'may', 'y' => $monthWiseIncome[4]],
            ['name' => 'jun', 'y' => $monthWiseIncome[5]],
            ['name' => 'jul', 'y' => $monthWiseIncome[6]],
            ['name' => 'aug', 'y' => $monthWiseIncome[7]],
            ['name' => 'sep', 'y' => $monthWiseIncome[8]],
            ['name' => 'oct', 'y' => $monthWiseIncome[9]],
            ['name' => 'nov', 'y' => $monthWiseIncome[10]],
            ['name' => 'dec', 'y' => $monthWiseIncome[11]],
        ];

        return json_encode((array)$monthWiseIncome);
    }

    /**
     * @return false|string
     */
    public function actionCurrentMonthIncome()
    {
        $currentMonthIncome = [];
        for ($i = 1; $i <= 31; $i++) {
            $startDate = date('Y') . '-' . date('m') . '-' . $i . ' 00:00:01';
            $endDate = date('Y') . '-' . date('m') . '-' . $i . ' 23:23:59';
            $tmparr = [
                'name' => (string)$i,
                'y' => (double)Order::find()->where(['between', 'created_at', $startDate, $endDate])->andWhere(['status' => Order::STATUS_ORDER_COMPLETED])->sum('total_amount')
            ];
            array_push($currentMonthIncome, $tmparr);
        }
        return json_encode($currentMonthIncome);
    }

    /**
     * @return false|string
     */
    public function actionCurrentWeekIncome()
    {
        $currentWeekIncome = [];
        $weekStart = date('d', strtotime('monday this week'));
        $weekEnd = date('d', strtotime('sunday this week'));
        for ($i = $weekStart; $i <= $weekEnd; $i++) {
            $startDate = date('Y') . '-' . date('m') . '-' . $i . ' 00:00:01';
            $endDate = date('Y') . '-' . date('m') . '-' . $i . ' 23:23:59';
            $datetime = \DateTime::createFromFormat('Ymd', date('Y') . date('m') . $i);
            $dayName = $datetime->format('D');
            $tmparr = [
                'name' => $dayName,
                'y' => (double)Order::find()->where(['between', 'created_at', $startDate, $endDate])->andWhere(['status' => Order::STATUS_ORDER_COMPLETED])->sum('total_amount')
            ];
            array_push($currentWeekIncome, $tmparr);
        }
        return json_encode($currentWeekIncome);
    }

    /**
     * @return false|string
     */
    public function actionCurrentDayIncome()
    {
        $todayIncome = [];
        $tmparr = [
            'name' => 'Today',
            'y' => (double)Order::find()->where('DATE(`created_at`) = CURRENT_DATE')->andWhere(['status' => Order::STATUS_ORDER_COMPLETED])->sum('total_amount')
        ];
        array_push($todayIncome, $tmparr);
        return json_encode($todayIncome);
    }
}
