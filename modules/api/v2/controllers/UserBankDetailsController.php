<?php

namespace app\modules\api\v2\controllers;

use app\models\UserBankDetails;
use app\modules\api\v2\models\User;
use Yii;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\Cors;
use yii\rest\ActiveController;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

/**
 * UserBankDetailsController implements the CRUD actions for UserBankDetails model.
 */
class UserBankDetailsController extends ActiveController
{
    /**
     * @var string
     */
    public $modelClass = 'app\models\UserBankDetails';

    /**
     * @var string
     */
    public $searchModelClass = 'app\modules\api\v2\models\search\UserBankDetailsSearch';

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
            'only' => ['index', 'view', 'create', 'update', 'delete'],
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
        unset($actions['view']);
        unset($actions['delete']);

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
        return $model->search($requestParams, Yii::$app->user->identity->id);
    }

    /**
     * Creates a new UserBankDetails model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new UserBankDetails();
        //$model->scenario = UserBankDetails::SCENARIO_CREATE;
        $postData = \Yii::$app->request->post();
        $userBankDetailData['UserBankDetails'] = $postData;
        $model->user_id = Yii::$app->user->identity->id;

        // Delete Old record start
        $oldData = UserBankDetails::find()->where(['user_id' => Yii::$app->user->identity->id])->one();
        if (!empty($oldData) && $oldData instanceof UserBankDetails) {
            $oldData->delete();
        }
        // Delete Old record end

        if (!empty($userBankDetailData['UserBankDetails']) && !empty($userBankDetailData['UserBankDetails']['payment_type']) && strtolower($userBankDetailData['UserBankDetails']['payment_type']) == UserBankDetails::PAYMENT_TYPE_PAYPAL) {
            $model->scenario = UserBankDetails::PAYMENT_TYPE_PAYPAL;
        } elseif (!empty($userBankDetailData['UserBankDetails']) && !empty($userBankDetailData['UserBankDetails']['payment_type']) && strtolower($userBankDetailData['UserBankDetails']['payment_type']) == UserBankDetails::PAYMENT_TYPE_BANK) {
            $model->scenario = UserBankDetails::PAYMENT_TYPE_BANK;
        }

        if ($model->load($userBankDetailData) && $model->validate()) {


            // Create stripe Account Start
            $modelUser = User::find()->where(['id' => Yii::$app->user->identity->id])->one();
            if (!empty($modelUser) && $modelUser instanceof User && !empty($modelUser->stripe_account_connect_id)) {



                $stripe = new \Stripe\StripeClient(
                    Yii::$app->params['stripe_secret_key']
                );

//                $deleteAccountResponse = $stripe->accounts->deleteExternalAccount(
//                    //$modelUser->stripe_account_connect_id,
//                    'acct_1KKNVyAvFy5NACFp',
//                    $modelUser->stripe_bank_account_id,
//                    []
//                );

//                $resultAccount = $stripe->accounts->createExternalAccount(
//                    $modelUser->stripe_account_connect_id,
//                    //'acct_1KgRvhPABUdKTa3N',
//                    [
//                        'external_account' => [
//                            'object' => 'bank_account',
//                            'country' => 'DE',
//                            'currency' => 'eur',
//                            'account_holder_name' => $model->first_name . " " . $model->last_name,
//                            'account_holder_type' => 'individual',
//                            //'routing_number' => '110000000',
//                            'account_number' => $model->iban,
////                            'usage'=>'source',
//                        ],
//                    ]
//                );
//
//                //p($resultAccount);
//
//                if (!empty($resultAccount) && !empty($resultAccount->id)) {
//                    $modelUser->stripe_bank_account_id = $resultAccount->id;
//                    $modelUser->save(false);
//                } else {
//                    throw new HttpException($resultAccount->status, $resultAccount);
//                }
            }
            // Create stripe Account End

            $model->save(false);

        }
        return $model;
    }

    /**
     * Updates an existing UserBankDetails model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = UserBankDetails::find()->where(['id' => $id])->one();
        if (!$model instanceof UserBankDetails || $model->user_id != Yii::$app->user->identity->id) {
            throw new NotFoundHttpException(getValidationErrorMsg('user_bank_detail_not_exist', Yii::$app->language));
        }

        $model->scenario = UserBankDetails::SCENARIO_UPDATE;

        $postData = \Yii::$app->request->post();
        $data['UserBankDetails'] = $postData;
        $model->user_id = Yii::$app->user->identity->id;

        if (!empty($data['UserBankDetails']) && !empty($data['UserBankDetails']['payment_type']) && strtolower($data['UserBankDetails']['payment_type']) == UserBankDetails::PAYMENT_TYPE_PAYPAL) {
            $model->scenario = UserBankDetails::PAYMENT_TYPE_PAYPAL;
        } elseif (!empty($data['UserBankDetails']) && !empty($data['UserBankDetails']['payment_type']) && strtolower($data['UserBankDetails']['payment_type']) == UserBankDetails::PAYMENT_TYPE_BANK) {
            $model->scenario = UserBankDetails::PAYMENT_TYPE_BANK;
        }

        if ($model->load($data) && $model->validate()) {
            $model->save();
        }
        return $model;
    }

    /**
     * Deletes an existing UserBankDetails model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = UserBankDetails::find()->where(['id' => $id])->one();
        if (!$model instanceof UserBankDetails || $model->user_id != Yii::$app->user->identity->id) {
            throw new NotFoundHttpException(getValidationErrorMsg('user_bank_detail_not_exist', Yii::$app->language));
        }

        $model->delete();
    }

    /**
     * Finds the UserBankDetails model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return UserBankDetails the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = UserBankDetails::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(getValidationErrorMsg('page_not_exist', Yii::$app->language));
    }

}
