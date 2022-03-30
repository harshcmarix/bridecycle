<?php


namespace app\modules\admin\controllers;

use app\models\Order;
use app\models\Product;
use app\models\ProductStatus;
use app\modules\api\v1\models\User;
use yii\filters\AccessControl;
use yii\helpers\Html;
use yii\web\Controller;
use Yii;
use yii2tech\spreadsheet\Spreadsheet;

class ReportController extends Controller
{

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['sales', 'customers', 'export-orders-report', 'export-products-report', 'export-customers-report'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['sales', 'customers', 'export-orders-report', 'export-products-report', 'export-customers-report'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param string $p
     * @return string
     * @throws \Exception
     */
    public function actionSales($p = 'w')
    {
        $periodType = Yii::$app->request->get('p');
        $orders = $products = [];

        $date = date('YmdHis');
        $ts = strtotime($date);

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
        $monthWiseOrders = [];
        $monthWiseProducts = [];

        $totalOrders = $totalProducts = 0;

        if ($periodType == 'y') {
            $isLeapYear = $this->yearCheckIsLeap(date('Y'));
            $thirtyOneDays = [0, 2, 4, 6, 7, 9, 11];
            $thirtyDays = [3, 5, 8, 10];

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
                $orderCount = Order::find()->where(['between', 'updated_at', $monthStartDate, $monthEndDate])->andWhere(['status' => Order::STATUS_ORDER_DELIVERED])->count();
                $productCount = Product::find()->where(['between', 'created_at', $monthStartDate, $monthEndDate])->andWhere(['IN', 'status_id', [ProductStatus::STATUS_APPROVED, ProductStatus::STATUS_IN_STOCK]])->count();

                $monthWiseOrders[$month[$i]] = $orderCount;
                $monthWiseProducts[$month[$i]] = $productCount;

                $totalOrders += $orderCount;
                $totalProducts += $productCount;
            }
        } elseif ($periodType == 'm') {
            $rangeStartDate = date('Y-m-01'); // hard-coded '01' for first day;
            $rangeEndDate = date('Y-m-t'); // last day of month
        } else {
            $start = (date('w', $ts) == 0) ? $ts : strtotime('last sunday', $ts);
            $range = array(date('Y-m-d', $start), date('Y-m-d', strtotime('next saturday', $start)));
            $rangeStartDate = $range[0];
            $rangeEndDate = $range[1];
        }

        if ($periodType == 'w' || $periodType == 'm') {

            $period = new \DatePeriod(new \DateTime($rangeStartDate), new \DateInterval('P1D'), new \DateTime($rangeEndDate));
            $dates = [];
            foreach ($period as $key => $value) {
                $dates[] = $value->format('Y-m-d');
            }

            if ($periodType == 'w') {
                $dates[] = $rangeEndDate;
            }

            foreach ($dates as $key => $date) {
                $orderCount = Order::find()->where(['between', 'updated_at', $date . " 00:00:01", $date . " 23:23:59"])->andWhere(['status' => Order::STATUS_ORDER_DELIVERED])->count();
                $productCount = Product::find()->where(['between', 'created_at', $date . " 00:00:01", $date . " 23:23:59"])->andWhere(['IN', 'status_id', [ProductStatus::STATUS_APPROVED, ProductStatus::STATUS_IN_STOCK]])->count();

                $orders[$date] = $orderCount;
                $products[$date] = $productCount;

                $totalOrders += $orderCount;
                $totalProducts += $productCount;
            }
        } else {
            $orders = $monthWiseOrders;
            $products = $monthWiseProducts;
        }
        return $this->render('sales', ['orders' => $orders, 'products' => $products, 'totalOrders' => $totalOrders, 'totalProducts' => $totalProducts]);
    }

    /**
     * @param string $p
     * @throws \Exception
     */
    public function actionExportOrdersReport($p = 'w')
    {
        $periodType = Yii::$app->request->get('p');
        $orders = [];
        $date = date('YmdHis');
        $ts = strtotime($date);
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
        $monthWiseOrders = [];
        $totalOrders = 0;
        if ($periodType == 'y') {
            $isLeapYear = $this->yearCheckIsLeap(date('Y'));
            $thirtyOneDays = [0, 2, 4, 6, 7, 9, 11];
            $thirtyDays = [3, 5, 8, 10];
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
                $orderCount = Order::find()->where(['between', 'updated_at', $monthStartDate, $monthEndDate])->andWhere(['status' => Order::STATUS_ORDER_DELIVERED])->count();
                $monthWiseOrders[$month[$i]] = $orderCount;
                $totalOrders += $orderCount;
            }
        } elseif ($periodType == 'm') {
            $rangeStartDate = date('Y-m-01'); // hard-coded '01' for first day;
            $rangeEndDate = date('Y-m-t'); // last day of month
        } else {
            $start = (date('w', $ts) == 0) ? $ts : strtotime('last sunday', $ts);
            $range = array(date('Y-m-d', $start), date('Y-m-d', strtotime('next saturday', $start)));
            $rangeStartDate = $range[0];
            $rangeEndDate = $range[1];
        }

        if ($periodType == 'w' || $periodType == 'm') {
            $period = new \DatePeriod(new \DateTime($rangeStartDate), new \DateInterval('P1D'), new \DateTime($rangeEndDate));
            $dates = [];
            foreach ($period as $key => $value) {
                $dates[] = $value->format('Y-m-d');
            }
            if ($periodType == 'w') {
                $dates[] = $rangeEndDate;
            }
            foreach ($dates as $key => $date) {
                $orderCount = Order::find()->where(['between', 'updated_at', $date . " 00:00:01", $date . " 23:23:59"])->andWhere(['status' => Order::STATUS_ORDER_DELIVERED])->count();
                $orders[$date] = $orderCount;
                $totalOrders += $orderCount;
            }
        } else {
            $orders = $monthWiseOrders;
        }

        $requestParam = Yii::$app->request->get('p');

        $reportType = "";
        if ($periodType == 'w') {
            $reportType = 'weekly';
        } elseif ($periodType == 'm') {
            $reportType = 'monthly';
        } elseif ($periodType == 'y') {
            $reportType = 'yearly';
        }

        if ($periodType == 'w' || $periodType == 'm') {
            $colType = "Date";
        } else {
            $colType = "Month";
        }

        $fileName = 'order_' . $reportType . "_" . time() . ".xls";

        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=" . $fileName);

        echo '<table border="1" width="100%">
            <thead>
                <tr>
                    <th>' . $colType . '</th>
                    <th>Orders</th>                    
                </tr>
            </thead>';
        foreach ($orders as $key => $data) {
            echo '
                <tr>
                    <td>' . Html::encode($key) . '</td>
                    <td>' . Html::encode($data) . '</td>                    
                </tr>
            ';
        }
        echo '
                <tr>
                    <td><strong>Total Orders</strong></td>
                    <td><strong>' . Html::encode($totalOrders) . '</strong></td>                    
                </tr>
            ';
        echo '</table>';
    }

    /**
     * @param string $p
     * @throws \Exception
     */
    public function actionExportProductsReport($p = 'w')
    {
        $periodType = Yii::$app->request->get('p');
        $products = [];
        $date = date('YmdHis');
        $ts = strtotime($date);
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

        $monthWiseProducts = [];
        $totalProducts = 0;

        if ($periodType == 'y') {
            $isLeapYear = $this->yearCheckIsLeap(date('Y'));
            $thirtyOneDays = [0, 2, 4, 6, 7, 9, 11];
            $thirtyDays = [3, 5, 8, 10];

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

                $productCount = Product::find()->where(['between', 'created_at', $monthStartDate, $monthEndDate])->andWhere(['IN', 'status_id', [ProductStatus::STATUS_APPROVED, ProductStatus::STATUS_IN_STOCK]])->count();
                $monthWiseProducts[$month[$i]] = $productCount;
                $totalProducts += $productCount;
            }
        } elseif ($periodType == 'm') {
            $rangeStartDate = date('Y-m-01'); // hard-coded '01' for first day;
            $rangeEndDate = date('Y-m-t'); // last day of month
        } else {
            $start = (date('w', $ts) == 0) ? $ts : strtotime('last sunday', $ts);
            $range = array(date('Y-m-d', $start), date('Y-m-d', strtotime('next saturday', $start)));
            $rangeStartDate = $range[0];
            $rangeEndDate = $range[1];
        }

        if ($periodType == 'w' || $periodType == 'm') {
            $period = new \DatePeriod(new \DateTime($rangeStartDate), new \DateInterval('P1D'), new \DateTime($rangeEndDate));
            $dates = [];
            foreach ($period as $key => $value) {
                $dates[] = $value->format('Y-m-d');
            }
            if ($periodType == 'w') {
                $dates[] = $rangeEndDate;
            }
            foreach ($dates as $key => $date) {
                $productCount = Product::find()->where(['between', 'created_at', $date . " 00:00:01", $date . " 23:23:59"])->andWhere(['IN', 'status_id', [ProductStatus::STATUS_APPROVED, ProductStatus::STATUS_IN_STOCK]])->count();
                $products[$date] = $productCount;
                $totalProducts += $productCount;
            }
        } else {
            $products = $monthWiseProducts;
        }
        $reportType = "";
        if ($periodType == 'w') {
            $reportType = 'weekly';
        } elseif ($periodType == 'm') {
            $reportType = 'monthly';
        } elseif ($periodType == 'y') {
            $reportType = 'yearly';
        }

        if ($periodType == 'w' || $periodType == 'm') {
            $colType = "Date";
        } else {
            $colType = "Month";
        }

        $fileName = 'product_' . $reportType . "_" . time() . ".xls";

        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=" . $fileName);

        echo '<table border="1" width="100%">
            <thead>
                <tr>
                    <th>' . $colType . '</th>
                    <th>Orders</th>                    
                </tr>
            </thead>';
        foreach ($products as $key => $data) {
            echo '
                <tr>
                    <td>' . Html::encode($key) . '</td>
                    <td>' . Html::encode($data) . '</td>                    
                </tr>
            ';
        }
        echo '<tr>
                    <td><strong>Total Products</strong></td>
                    <td><strong>' . Html::encode($totalProducts) . '</strong></td>                    
                </tr>';
        echo '</table>';
    }

    /**
     * @param string $p
     * @return string
     * @throws \Exception
     */
    public function actionCustomers($p = 'w')
    {
        $periodType = Yii::$app->request->get('p');
        $customers = [];
        $date = date('YmdHis');
        $ts = strtotime($date);
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
        $monthWiseCustomers = [];
        $totalCustomers = 0;

        if ($periodType == 'y') {
            $isLeapYear = $this->yearCheckIsLeap(date('Y'));
            $thirtyOneDays = [0, 2, 4, 6, 7, 9, 11];
            $thirtyDays = [3, 5, 8, 10];

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
                $customerCount = User::find()->where(['between', 'created_at', $monthStartDate, $monthEndDate])->andWhere(['user_type' => User::USER_TYPE_NORMAL])->count();
                $monthWiseCustomers[$month[$i]] = $customerCount;
                $totalCustomers += $customerCount;
            }
        } elseif ($periodType == 'm') {
            $rangeStartDate = date('Y-m-01'); // hard-coded '01' for first day;
            $rangeEndDate = date('Y-m-t'); // last day of month
        } else {
            $start = (date('w', $ts) == 0) ? $ts : strtotime('last sunday', $ts);
            $range = array(date('Y-m-d', $start), date('Y-m-d', strtotime('next saturday', $start)));
            $rangeStartDate = $range[0];
            $rangeEndDate = $range[1];
        }

        if ($periodType == 'w' || $periodType == 'm') {
            $period = new \DatePeriod(new \DateTime($rangeStartDate), new \DateInterval('P1D'), new \DateTime($rangeEndDate));
            $dates = [];
            foreach ($period as $key => $value) {
                $dates[] = $value->format('Y-m-d');
            }
            if ($periodType == 'w') {
                $dates[] = $rangeEndDate;
            }
            foreach ($dates as $key => $date) {
                $customerCount = User::find()->where(['between', 'created_at', $date . " 00:00:01", $date . " 23:23:59"])->andWhere(['user_type' => User::USER_TYPE_NORMAL])->count();;
                $customers[$date] = $customerCount;
                $totalCustomers += $customerCount;
            }
        } else {
            $customers = $monthWiseCustomers;
        }
        return $this->render('customers', ['customers' => $customers, 'totalCustomers' => $totalCustomers]);
    }

    /**
     * @param string $p
     * @throws \Exception
     */
    public function actionExportCustomersReport($p = 'w')
    {
        $periodType = Yii::$app->request->get('p');
        $customers = [];
        $date = date('YmdHis');
        $ts = strtotime($date);
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
        $monthWiseCustomers = [];
        $totalCustomers = 0;

        if ($periodType == 'y') {
            $isLeapYear = $this->yearCheckIsLeap(date('Y'));
            $thirtyOneDays = [0, 2, 4, 6, 7, 9, 11];
            $thirtyDays = [3, 5, 8, 10];

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
                $customerCount = User::find()->where(['between', 'created_at', $monthStartDate, $monthEndDate])->andWhere(['user_type' => User::USER_TYPE_NORMAL])->count();
                $monthWiseCustomers[$month[$i]] = $customerCount;
                $totalCustomers += $customerCount;
            }
        } elseif ($periodType == 'm') {
            $rangeStartDate = date('Y-m-01'); // hard-coded '01' for first day;
            $rangeEndDate = date('Y-m-t'); // last day of month
        } else {
            $start = (date('w', $ts) == 0) ? $ts : strtotime('last sunday', $ts);
            $range = array(date('Y-m-d', $start), date('Y-m-d', strtotime('next saturday', $start)));
            $rangeStartDate = $range[0];
            $rangeEndDate = $range[1];
        }

        if ($periodType == 'w' || $periodType == 'm') {
            $period = new \DatePeriod(new \DateTime($rangeStartDate), new \DateInterval('P1D'), new \DateTime($rangeEndDate));
            $dates = [];
            foreach ($period as $key => $value) {
                $dates[] = $value->format('Y-m-d');
            }
            if ($periodType == 'w') {
                $dates[] = $rangeEndDate;
            }

            foreach ($dates as $key => $date) {

                $customerCount = User::find()->where(['between', 'created_at', $date . " 00:00:01", $date . " 23:23:59"])->andWhere(['user_type' => User::USER_TYPE_NORMAL])->count();;
                $customers[$date] = $customerCount;
                $totalCustomers += $customerCount;
            }
        } else {
            $customers = $monthWiseCustomers;
        }

        $reportType = "";
        if ($periodType == 'w') {
            $reportType = 'weekly';
        } elseif ($periodType == 'm') {
            $reportType = 'monthly';
        } elseif ($periodType == 'y') {
            $reportType = 'yearly';
        }

        if ($periodType == 'w' || $periodType == 'm') {
            $colType = "Date";
        } else {
            $colType = "Month";
        }

        $fileName = 'customer_' . $reportType . "_" . time() . ".xls";

        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=" . $fileName);

        echo '<table border="1" width="100%">
            
                <tr>
                    <th>' . $colType . '</th>
                    <th>Customers</th>                    
                </tr>
            ';
        foreach ($customers as $key => $data) {
            echo '
                <tr>
                    <td>' . Html::encode($key) . '</td>
                    <td>' . Html::encode($data) . '</td>                    
                </tr>
            ';
        }
        echo '<tr>
                    <td><strong>Total Customers</strong></td>
                    <td><strong>' . Html::encode($totalCustomers) . '</strong></td>
                </tr>';
        echo '</table>';
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
}