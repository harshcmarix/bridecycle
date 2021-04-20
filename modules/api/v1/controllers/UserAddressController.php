<?php

namespace app\modules\api\v1\controllers;

use Yii;
use app\models\UserAddress;
use app\modules\api\v1\models\search\UserAddressSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\auth\{
    HttpBasicAuth,
    CompositeAuth,
    HttpBearerAuth,
    QueryParamAuth
};
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
    public $searchModelClass = 'app\modules\api\v1\models\search\UserAddressSearch';

    /**
     * @return array
     */
    protected function verbs()
    {
        return [
            'create' =>['POST','OPTIONS'],
            'update' => ['PUT', 'PATCH'],
            'view' => ['GET', 'HEAD', 'OPTIONS'],
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
            'only' => ['create','update','view','delete'],
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
        unset($actions['delete']);
        // unset($actions['view']);
        return $actions;
    }

    /**
     * Lists all UserAddress models.
     * @return mixed
     */
    public function actionIndex()
    {
        // $searchModel = new UserAddressSearch();
        // $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // return $this->render('index', [
        //     'searchModel' => $searchModel,
        //     'dataProvider' => $dataProvider,
        // ]);
    }


    /**
     * Creates a new UserAddress model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new UserAddress();
        $addressData = \Yii::$app->request->post();
        $address['UserAddress'] = $addressData;
        if ($model->load($address) && $model->validate()) {
            $model->type = UserAddress::TYPE_BILLING;
            $model->address = $model->street.' '.$model->city.' '.$model->state.' '.$model->country.' '.$model->zip_code;
            $model->save();
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
        $model = $this->findModel($id);
        $addressData = \Yii::$app->request->post();
        $address['UserAddress'] = $addressData;
    
        if ($model->load($address) && $model->validate()) {
            $model->type = UserAddress::TYPE_BILLING;
            $model->address = $model->street.' '.$model->city.' '.$model->state.' '.$model->country.' '.$model->zip_code;
            $model->save();
        }
            return $model;
    }

    /**
     * Deletes an existing UserAddress model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = UserAddress::findOne($id);
        if (!$model instanceof UserAddress) {
            throw new NotFoundHttpException('Address doesn\'t exist.');
        }
        $model->delete();
    }

    /**
     * Finds the UserAddress model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return UserAddress the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = UserAddress::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}