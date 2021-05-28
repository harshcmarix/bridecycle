<?php

namespace app\modules\api\v1\controllers;

use app\models\Product;
use Yii;
use app\models\Trial;
use yii\web\NotFoundHttpException;
use yii\filters\auth\{
    HttpBasicAuth,
    CompositeAuth,
    HttpBearerAuth,
    QueryParamAuth
};
use yii\filters\Cors;
use yii\rest\ActiveController;

/**
 * TrialController implements the CRUD actions for Trial model.
 */
class TrialController extends ActiveController
{
    /**
     * @var string
     */
    public $modelClass = 'app\models\Trial';

    /**
     * @var string
     */
    public $searchModelClass = 'app\modules\api\v1\models\search\TrialSearch';


    /**
     * @return array
     */
    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD', 'OPTIONS'],
            'view' => ['GET', 'HEAD', 'OPTIONS'],
            'create' => ['POST', 'OPTIONS'],
            'update' => ['PUT', 'PATCH'],
            //'delete' => ['POST', 'DELETE'],
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
            'only' => ['index', 'view', 'create', 'update'], //, 'delete'
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
        //unset($actions['delete']);
        unset($actions['view']);
        return $actions;
    }

    /**
     * Lists all Brand models.
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
     * Displays a single Trial model.
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
     * Creates a new Trial model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Trial();

        $post = Yii::$app->request->post();
        $postData['Trial'] = Yii::$app->request->post();

        $modelProduct = Product::findOne($postData['Trial']['product_id']);

        $postData['Trial']['sender_id'] = Yii::$app->user->identity->id;
        $postData['Trial']['receiver_id'] = (!empty($modelProduct) && !empty($modelProduct->user_id)) ? $modelProduct->user_id : "";

        if ($model->load($postData) && $model->validate()) {
            $model->save();
        }

        return $model;
    }

    /**
     * Updates an existing Trial model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = Trial::findOne($id);

        $model->scenario = Trial::SCENARIO_ACCEPT_REJECT;

        if (!$model instanceof Trial) {
            throw new NotFoundHttpException('Trial doesn\'t exist.');
        }
        //$oldStatus = $model->status;
        $postData = Yii::$app->request->post();
        $trialPostData['Trial'] = $postData;
        if ($model->load($trialPostData) && $model->validate()) {
            $model->save(false);
        }
        $model = Trial::findOne($id);
        return $model;
    }

    /**
     * Deletes an existing Trial model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
//    public function actionDelete($id)
//    {
//        $this->findModel($id)->delete();
//
//        return $this->redirect(['index']);
//    }

    /**
     * Finds the Trial model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Trial the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Trial::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
