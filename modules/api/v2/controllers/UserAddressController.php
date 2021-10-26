<?php

namespace app\modules\api\v2\controllers;

use Yii;
use app\models\UserAddress;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\Cors;
use yii\rest\ActiveController;

/**
 * UserAddressController implements the CRUD actions for UserAddress model.
 */
class UserAddressController extends ActiveController
{
    /**
     * @var string
     */
    public $modelClass = 'app\models\UserAddress';

    /**
     * @var string
     */
    public $searchModelClass = 'app\modules\api\v2\models\search\UserAddressSearch';

    /**
     * @return array
     */
    protected function verbs()
    {
        return [
            'create' => ['POST', 'OPTIONS'],
            'update' => ['PUT', 'PATCH'],
            'view' => ['GET', 'HEAD', 'OPTIONS'],
            'get-primary-address' => ['POST', 'OPTIONS'],
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
            'only' => ['create', 'update', 'view', 'delete', 'get-primary-address'],
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
        return $actions;
    }

    /**
     * Creates a new UserAddress model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new UserAddress();
        $addressData = Yii::$app->request->post();
        $address['UserAddress'] = $addressData;

        $address['UserAddress']['user_id'] = Yii::$app->user->identity->id;
        $address['UserAddress']['is_primary_address'] = (!empty($address['UserAddress']['is_primary_address'])) ? '1' : '0';
        if ($model->load($address) && $model->validate()) {
            $model->type = UserAddress::TYPE_BILLING;
            $model->address = $model->street . ', ' . $model->city . ', ' . $model->state . ', ' . $model->country . ' ' . $model->zip_code;
            $model->save(false);
        }

        if ($address['UserAddress']['is_primary_address'] == UserAddress::IS_ADDRESS_PRIMARY_YES) {
            $previousAddress = UserAddress::find()->where(['is_primary_address' => UserAddress::IS_ADDRESS_PRIMARY_YES, 'user_id' => Yii::$app->user->identity->id])->andWhere('id!=' . $model->id)->all();
            if (!empty($previousAddress)) {
                foreach ($previousAddress as $keys => $previousAddressRow) {
                    if (!empty($previousAddressRow) && $previousAddressRow instanceof UserAddress) {
                        $previousAddressRow->is_primary_address = UserAddress::IS_ADDRESS_PRIMARY_NO;
                        $previousAddressRow->save(false);
                    }
                }
            }
        } else {
            $addedAddress = UserAddress::find()->where(['is_primary_address' => UserAddress::IS_ADDRESS_PRIMARY_YES, 'user_id' => Yii::$app->user->identity->id])->all();
            if (empty($addedAddress)) {
                $model->is_primary_address = UserAddress::IS_ADDRESS_PRIMARY_YES;
                $model->save(false);
            }
        }
        return $model;
    }

    /**
     * Updates an existing UserAddress model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = UserAddress::findOne($id);
        if (!$model instanceof UserAddress) {
            throw new NotFoundHttpException('Address doesn\'t exist.');
        }
        $addressData = \Yii::$app->request->post();
        $address['UserAddress'] = $addressData;
        $address['UserAddress']['user_id'] = Yii::$app->user->identity->id;

        if ($model->load($address) && $model->validate()) {

            if (!empty($address['UserAddress']['is_primary_address'])) {
                $model->is_primary_address = UserAddress::IS_ADDRESS_PRIMARY_YES;
            } else {
                $model->is_primary_address = UserAddress::IS_ADDRESS_PRIMARY_NO;
            }

            $model->type = UserAddress::TYPE_BILLING;
            $model->address = $model->street . ', ' . $model->city . ', ' . $model->state . ', ' . $model->country . ' ' . $model->zip_code;
            $model->save(false);
        }

        if ($address['UserAddress']['is_primary_address'] == UserAddress::IS_ADDRESS_PRIMARY_YES) {
            $previousAddress = UserAddress::find()->where(['is_primary_address' => UserAddress::IS_ADDRESS_PRIMARY_YES, 'user_id' => Yii::$app->user->identity->id])->andWhere('id!=' . $model->id)->all();
            if (!empty($previousAddress)) {
                foreach ($previousAddress as $keys => $previousAddressRow) {
                    if (!empty($previousAddressRow) && $previousAddressRow instanceof UserAddress) {
                        $previousAddressRow->is_primary_address = UserAddress::IS_ADDRESS_PRIMARY_NO;
                        $previousAddressRow->save(false);
                    }
                }
            }
        }
        return $model;
    }

    /**
     * Updates an existing UserAddress model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionview($id)
    {
        $model = UserAddress::findOne($id);
        if (!$model instanceof UserAddress) {
            throw new NotFoundHttpException('Address doesn\'t exist.');
        }
        $model->is_primary_address = (string)$model->is_primary_address;

        return $model;
    }

    /**
     * @return UserAddress|\yii\db\ActiveRecord
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionGetPrimaryAddress()
    {
        $postData = Yii::$app->request->post();

        if (empty($postData) || empty($postData['is_profile_address'])) {
            throw new BadRequestHttpException('Invalid parameter passed. Request must required parameter "is_profile_address"');
        }

        if (empty($postData) || empty($postData['user_id'])) {
            throw new BadRequestHttpException('Invalid parameter passed. Request must required parameter "user_id"');
        }

        $profileAddress = UserAddress::find()->where(['user_id' => $postData['user_id'], 'is_primary_address' => UserAddress::IS_ADDRESS_PRIMARY_YES])->one();
        if (!$profileAddress instanceof UserAddress) {
            throw new NotFoundHttpException('Primary address doesn\'t exist.');
        }
        $profileAddress->is_primary_address = (string)$profileAddress->is_primary_address;
        return $profileAddress;
    }

}