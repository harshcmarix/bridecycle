<?php

namespace app\modules\api\v2\controllers;

use app\modules\api\v2\models\UserAddress;
use PayPal\Api\Address;
use PayPal\Api\Amount;
use PayPal\Api\CreditCard;
use PayPal\Api\Details;
use PayPal\Api\FundingInstrument;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Exception\PayPalConnectionException;
use Yii;
use app\models\UserSubscription;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\Cors;
use yii\helpers\Url;
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
    public $searchModelClass = 'app\modules\api\v2\models\search\UserSubscriptionSearch';

    /**
     * @return \string[][]
     */
    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD', 'OPTIONS'],
            'view' => ['GET', 'HEAD', 'OPTIONS'],
            'create' => ['POST', 'OPTIONS'],
            'make-subscription-payment' => ['POST', 'OPTIONS'],
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
            'only' => ['index', 'view', 'create', 'update', 'delete', 'paypal-payment-response', 'make-subscription-payment'],
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

            if ($model->save()) {
                // Paypal payment
                $subscription_package_id = $model->subscription_id;
                $price = $model->subscription->amount;
                $packageName = $model->subscription->name;
                $ownerId = $model->user_id;
                $user_subdcription_id = $model->id;

                $cardType = UserSubscription::CARD_TYPE_VISA;
                if (!empty($model->card_number)) {
                    if ($model->card_number[0] == UserSubscription::CARD_TYPE_VISA_NUMBER) {
                        $cardType = UserSubscription::CARD_TYPE_VISA;
                    } else if (in_array($model->card_number[0], [UserSubscription::CARD_TYPE_MASTER_NUMBER_ONE, UserSubscription::CARD_TYPE_MASTER_NUMBER_TWO])) {
                        $cardType = UserSubscription::CARD_TYPE_MASTER;
                    } else if ($model->card_number[0] == UserSubscription::CARD_TYPE_AMEX_NUMBER) {
                        $cardType = UserSubscription::CARD_TYPE_AMEX;
                    } else if ($model->card_number[0] == UserSubscription::CARD_TYPE_DISCOVER_NUMBER) {
                        $cardType = UserSubscription::CARD_TYPE_DISCOVER;
                    }
                }

                $expMontYear = explode("/", $model->expiry_month_year);
                $cardHoderName = explode(" ", $model->card_holder_name);

                $modelSubscriptionPackage = $model->subscription;
                $paymentRequestData = [
                    'subscription_package_id' => $subscription_package_id,
                    'total' => $price,
                    'package_name' => $packageName,
                    'user_id' => $ownerId,
                    'user_subscription_id' => $user_subdcription_id,
                    'card_type' => $cardType,
                    'card_exp_month' => $expMontYear[0],
                    'card_exp_year' => $expMontYear[1],
                    'card_first_name' => $cardHoderName[0],
                    'card_last_name' => (!empty($cardHoderName[1])) ? $cardHoderName[1] : "Seller",
                    'sub_total' => $modelSubscriptionPackage->amount,
                    'user' => Yii::$app->user->identity,
                    'user_Address' => $modelAddress,
                ];

                $response = $this->makeSubscriptionPayment(array_merge($postData, $paymentRequestData));

                if (!empty($response)) {
                    $model->payment_response = (!empty($response) && !empty($response->getState()) && $response->getState() == 'created') ? $response : "";
                    $model->payment_status = (!empty($response->getState())) ? $response->getState() : 'failed';
                    $model->transaction_id = (!empty($response->getId())) ? $response->getId() : "";
                    $model->save(false);
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

    /**
     * @param $request
     * @return \Exception|Payment|PayPalConnectionException
     */
    public function makeSubscriptionPayment($request)
    {
        $apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                Yii::$app->params['paypal_client_id'], // ClientID
                Yii::$app->params['paypal_client_secret'] // ClientSecret
            )
        );

        // or whatever yours is called

        $cardType = $request['card_type'];
        $cardNumber = $request['card_number'];
        $cardExpMonth = $request['card_exp_month'];
        $cardExpYear = $request['card_exp_year'];
        $cardCvv = $request['cvv'];
        $cardFirstname = $request['card_first_name'];
        $cardLastname = $request['card_last_name'];
        $user = $request['user'];
        $subTotal = !empty($request['sub_total']) ? $request['sub_total'] : 0.0;
        $tax = !empty($request['tax']) ? $request['tax'] : 0.0;
        $shippingCharge = !empty($request['shipping_charge']) ? $request['shipping_charge'] : 0.0;
        $total = (($subTotal + $tax + $shippingCharge) > 0) ? number_format(($subTotal + $tax + $shippingCharge), 2) : 1.00;

        $userAddresses = (!empty($request['user_address'])) ? $request['user_address'] : $user->userAddresses;
        $billAddress = "";
        if (!empty($userAddresses)) {
            foreach ($userAddresses as $userAddress) {
                if (!empty($userAddress) && $userAddress instanceof UserAddress) {
                    if ($userAddress->type == UserAddress::BILLING) {
                        $billAddress = $userAddress;
                    }
                }
            }
        }

        $addressLine = !empty($billAddress) && !empty($billAddress->street) ? $billAddress->street : '52 N Main St';
        $addressCity = !empty($billAddress) && !empty($billAddress->city) ? $billAddress->city : 'Johnstown';
        $addressPostCode = !empty($billAddress) && !empty($billAddress->zip_code) ? $billAddress->zip_code : '43210';
        $addressState = !empty($billAddress) && !empty($billAddress->state) ? $billAddress->state : 'OH';
        $addressCountry = !empty($billAddress) && !empty($billAddress->country) ? $billAddress->country : 'US';
        $addressPhone = !empty($user) && !empty($user->mobile) ? $user->mobile : '408-334-8890';

        // set billing address
        $addr = new Address();
        $addr->setLine1($addressLine);
        $addr->setCity($addressCity);
        $addr->setCountryCode($addressCountry);
        $addr->setPostalCode($addressPostCode);
        $addr->setState($addressState);
        $addr->setPhone($addressPhone);

        // set credit card information
        $card = new CreditCard();
        $card->setNumber($cardNumber);
        $card->setType($cardType);
        $card->setExpireMonth($cardExpMonth);
        $card->setExpireYear($cardExpYear);
        $card->setCvv2($cardCvv);
        $card->setFirstName($cardFirstname);
        $card->setLastName($cardLastname);
        $card->setBillingAddress($addr);

        $fi = new FundingInstrument();
        $fi->setCreditCard($card);

        $payer = new Payer();
        $payer->setPaymentMethod("paypal");

        // Specify the payment amount.
        $amountDetails = new Details();
        $amountDetails->setSubtotal($subTotal);
        $amountDetails->setTax($tax);
        $amountDetails->setShipping($shippingCharge);

        $amount = new Amount();
        $amount->setCurrency(Yii::$app->params['paypal_payment_currency']);
        $amount->setTotal($total);
        $amount->setDetails($amountDetails);

        // ###Transaction
        // A transaction defines the contract of a
        // payment - what is the payment for and who
        // is fulfilling it. Transaction is created with
        // a `Payee` and `Amount` types
        $transaction = new Transaction();
        $transaction->setAmount($amount);
        $transaction->setDescription('This is the subscription purchase payment transaction.');
        $transaction->setPaymentOptions(array('allowed_payment_method' => 'INSTANT_FUNDING_SOURCE'));

        $returnUrl = Url::to(['/user-subscription/paypal-payment-response', 'is_success' => true, 'subscription_package_id' => $request['subscription_package_id'], 'owner_id' => $request['user_id'], 'user_subscription_id' => $request['user_subscription_id']], true);
        $cancelUrl = Url::to(['/user-subscription/paypal-payment-response', 'is_success' => false, 'subscription_package_id' => $request['subscription_package_id'], 'owner_id' => $request['user_id'], 'user_subscription_id' => $request['user_subscription_id']], true);
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($returnUrl);
        $redirectUrls->setCancelUrl($cancelUrl);

        $payment = new Payment();
        $payment->setRedirectUrls($redirectUrls);
        $payment->setIntent("sale");
        $payment->setPayer($payer);
        $payment->setTransactions(array($transaction));

        try {
            $response = $payment->create($apiContext);
            $payment = Payment::get($response->getId(), $apiContext);
            return $payment;
        } catch (PayPalConnectionException $pce) {
//            // Don't spit out errors or use "exit" like this in production code
//            return json_decode($pce->getData());

            //echo $pce->getCode();
            //echo $pce->getData();
            //die($pce);
            //return $pce;
            echo "Error: " . $pce->getMessage();
        }
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

        throw new NotFoundHttpException(getValidationErrorMsg('page_not_exist',Yii::$app->language));
    }

}
