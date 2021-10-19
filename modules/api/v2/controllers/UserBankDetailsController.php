<?php

namespace app\modules\api\v2\controllers;

use app\models\UserBankDetails;
use app\modules\api\v2\models\search\UserBankDetailsSearch;
use Yii;
use yii\filters\auth\{CompositeAuth, HttpBasicAuth, HttpBearerAuth, QueryParamAuth};
use yii\filters\Cors;
use yii\rest\ActiveController;
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
        return $model->search($requestParams,Yii::$app->user->identity->id);
    }


    /**
     * Displays a single UserBankDetails model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
//    public function actionView($id)
//    {
//        return $this->render('view', [
//            'model' => $this->findModel($id),
//        ]);
//    }

    /**
     * Creates a new UserBankDetails model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new UserBankDetails();
        $model->scenario = UserBankDetails::SCENARIO_CREATE;
        $postData = \Yii::$app->request->post();
        $userBankDetailData['UserBankDetails'] = $postData;
        $model->user_id = Yii::$app->user->identity->id;

        // Delete Old record start
        $oldData = UserBankDetails::find()->where(['user_id' => Yii::$app->user->identity->id])->one();
        if (!empty($oldData) && $oldData instanceof UserBankDetails) {
            $oldData->delete();
        }
        // Delete Old record end

        if ($model->load($userBankDetailData) && $model->validate()) {
            $model->save();
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
            throw new NotFoundHttpException('User bank detail doesn\'t exist.');
        }

        $model->scenario = UserBankDetails::SCENARIO_UPDATE;

        $postData = \Yii::$app->request->post();
        $data['UserBankDetails'] = $postData;
        $model->user_id = Yii::$app->user->identity->id;

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
    public
    function actionDelete($id)
    {
        $model = UserBankDetails::find()->where(['id' => $id])->one();
        if (!$model instanceof UserBankDetails || $model->user_id != Yii::$app->user->identity->id) {
            throw new NotFoundHttpException('User bank detail doesn\'t exist.');
        }

        $model->delete();
        //return $model;
    }

    /**
     * Finds the UserBankDetails model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return UserBankDetails the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected
    function findModel($id)
    {
        if (($model = UserBankDetails::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
