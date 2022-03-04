<?php

namespace app\modules\api\v2\controllers;

use app\models\BlockUser;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\Cors;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;


/**
 * BlockUserController implements the CRUD actions for BlockUser model.
 */
class BlockUserController extends ActiveController
{
    /**
     * @var string
     */
    public $modelClass = 'app\models\BlockUser';

    /**
     * @var string
     */
    public $searchModelClass = 'app\modules\api\v2\models\search\BlockUserSearch';

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
            'only' => ['index', 'view', 'create', 'delete'],
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
        return $model->search($requestParams, Yii::$app->user->identity->id);
    }

    /**
     * Displays a single BlockUser model.
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
     * Creates a new BlockUser model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $post = Yii::$app->request->post();
        $postData['BlockUser'] = $post;
        $postData['BlockUser']['user_id'] = Yii::$app->user->identity->id;
        $model = new BlockUser();
        if ($model->load($postData) && $model->validate()) {

            $blockedUserModel = BlockUser::find()->where(['user_id' => Yii::$app->user->identity->id])->andWhere(['seller_id' => $model->seller_id])->one();
            if (empty($blockedUserModel)) {
                $model->save();
            } else {
                $model = $blockedUserModel;
            }
        }

        return $model;
    }

    /**
     * Deletes an existing BlockUser model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = BlockUser::findOne($id);
        if (!$model instanceof BlockUser) {
            throw new NotFoundHttpException(getValidationErrorMsg('block_user_not_exist', Yii::$app->language));
        }
        $model->delete();
    }

    /**
     * Finds the BlockUser model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return BlockUser the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = BlockUser::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(getValidationErrorMsg('page_not_exist', Yii::$app->language));
    }

}
