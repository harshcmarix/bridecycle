<?php

namespace app\modules\admin\controllers;

use app\modules\api\v2\models\User;
use kartik\growl\Growl;
use Yii;
use app\models\BlockUser;
use app\modules\admin\models\search\BlockUserSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;


/**
 * BlockUserController implements the CRUD actions for BlockUser model.
 */
class BlockUserController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'view', 'delete'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'delete'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }


    /**
     * Lists all BlockUser models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BlockUserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $users = ArrayHelper::map(User::find()->where(['NOT IN', 'user_type', [User::USER_TYPE_ADMIN, User::USER_TYPE_SUB_ADMIN]])->all(), 'id', function ($model) {
            return (!empty($model) && $model instanceof User && !empty($model->first_name)) ? $model->first_name . " " . $model->last_name . "(" . $model->email . ")" : "user" . " (" . $model->email . ")";
        });

        $sellers = ArrayHelper::map(User::find()->where(['NOT IN', 'user_type', [User::USER_TYPE_ADMIN, User::USER_TYPE_SUB_ADMIN]])->all(), 'id', function ($model) {
            return (!empty($model) && $model instanceof User && !empty($model->first_name)) ? $model->first_name . " " . $model->last_name . "(" . $model->email . ")" : "user" . " (" . $model->email . ")";
        });

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'users' => $users,
            'sellers' => $sellers
        ]);
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
        $model = new BlockUser();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing BlockUser model.
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
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $model->delete();

        \Yii::$app->session->setFlash(Growl::TYPE_SUCCESS, 'User unblock successfully.');
        return $this->redirect(['index']);

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

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
