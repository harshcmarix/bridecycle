<?php

namespace app\modules\api\v1\controllers;

use app\components\PaypalPayment;
use Yii;
use app\models\UserSubscription;

use yii\filters\auth\{
    CompositeAuth,
    HttpBasicAuth,
    HttpBearerAuth,
    QueryParamAuth
};
use yii\base\BaseObject;
use yii\filters\Cors;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;


/**
 * UserSubscriptionController implements the CRUD actions for UserSubscription model.
 */
class UserSubscriptionController extends ActiveController
{
    /**
     * @var string
     */
    public $modelClass = 'app\models\UserSubscription';

    /**
     * @var string
     */
    public $searchModelClass = 'app\modules\api\v1\models\search\UserSubscriptionSearch';


    /**
     * @return \string[][]
     */
    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD', 'OPTIONS'],
            'view' => ['GET', 'HEAD', 'OPTIONS'],
            'create' => ['POST', 'OPTIONS'],
            'update' => ['PUT', 'PATCH'],
            'delete' => ['POST', 'DELETE'],
            'paypal-payment-response' => ['GET', 'HEAD', 'OPTIONS'],
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
            'only' => ['index', 'view', 'create', 'update', 'delete', 'paypal-payment-response'],
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
        //unset($actions['index']);
        unset($actions['update']);
        unset($actions['create']);
        unset($actions['view']);
        return $actions;
    }

    /**
     * Lists all FavouriteProduct models.
     * @return mixed
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
     * Displays a single UserSubscription model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new UserSubscription model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new UserSubscription();
        $postData = \Yii::$app->request->post();
        $userSubscriptionData['UserSubscription'] = $postData;

        if ($model->load($userSubscriptionData) && $model->validate()) {


            $model->user_id = Yii::$app->user->identity->id;
            $modelAddress = (!empty($model->user) && !empty($model->user->userAddresses) && !empty($model->user->userAddresses[0])) ? $model->user->userAddresses[0] : '';
           // p($modelAddress);
            if ($model->save()) {
                // Paypal payment
                $subscription_package_id = $model->subscription_id;
                $price = $model->subscription->amount;
                $packageName = $model->subscription->name;
                $ownerId = $model->user_id;
                $user_subdcription_id = $model->id;
                $paypal = new PaypalPayment();
                $response = $paypal->SubscriptionCreatePayment($subscription_package_id, $price, $packageName, $ownerId, $user_subdcription_id);
                p($response);
                if (!empty($response)) {


//                    $request_params = array
//                    (
//                        'METHOD' => 'DoDirectPayment',
//                        'USER' => $api_username,
//                        'PWD' => $api_password,
//                        'SIGNATURE' => $api_signature,
//                        'VERSION' => 'v1',
//                        'PAYMENTACTION' => 'Sale',
//                        'IPADDRESS' => $_SERVER['REMOTE_ADDR'],
//                        //'CREDITCARDTYPE' => 'MasterCard',
//                        'ACCT' => (!empty($model->card_number)) ? $model->card_number : '5522340006063638',
//                        'EXPDATE' => (!empty($model->expiry_month_year)) ? str_replace("/", "", $model->expiry_month_year) : '022025',
//                        'CVV2' => (!empty($model->cvv)) ? $model->cvv : '456',
//                        'FIRSTNAME' => (!empty($model->user)) ? $model->user->first_name : 'Tester',
//                        'LASTNAME' => (!empty($model->user)) ? $model->user->last_name : 'Testerson',
//                        'STREET' => (!empty($modelAddress)) ? $modelAddress->street : '707 W. Bay Drive',
//                        'CITY' => (!empty($modelAddress)) ? $modelAddress->city : 'Largo',
//                        'STATE' => (!empty($modelAddress)) ? $modelAddress->state : 'FL',
//                        'COUNTRYCODE' => (!empty($modelAddress)) ? $modelAddress->country : 'US',
//                        'ZIP' => (!empty($modelAddress)) ? $modelAddress->zip_code : '33770',
//                        'AMT' => $price,
//                        'CURRENCYCODE' => Yii::$app->params['paypal_payment_currency'],
//                        'DESC' => 'Subscription purchase Payment.'
//                    );
//
//                    // Loop through $request_params array to generate the NVP string.
//                    $nvp_string = '';
//                    foreach ($request_params as $var => $val) {
//                        $nvp_string .= '&' . $var . '=' . urlencode($val);
//                    }

                }
            }

        }

        return $model;

    }

    /**
     * Updates an existing UserSubscription model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing UserSubscription model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }


    public function actionPaypalPaymentResponse($is_success = null, $subscription_package_id = null, $owner_id = null, $user_subdcription_id = null)
    {
        p($is_success);
    }

    /**
     * Finds the UserSubscription model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return UserSubscription the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = UserSubscription::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
