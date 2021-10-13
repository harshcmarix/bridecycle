<?php

namespace app\modules\admin\controllers;

use app\models\UserPurchasedSubscriptions;
use app\modules\admin\models\search\UserPurchasedSubscriptionsSearch;
use app\modules\api\v2\models\User;
use kartik\growl\Growl;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * UserPurchasedSubscriptionsController implements the CRUD actions for UserPurchasedSubscriptions model.
 */
class UserPurchasedSubscriptionsController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                //'only' => ['index', 'create', 'update', 'view', 'delete'],
                'only' => ['index', 'view', 'delete'],
                'rules' => [
                    [
                        'allow' => true,
                        //'actions' => ['index', 'create', 'update', 'view', 'delete'],
                        'actions' => ['index', 'view', 'delete'],
                        'roles' => ['@'], // Allow only for login user
                    ],
                ],
            ],
        ];
    }


    /**
     * Lists all UserPurchasedSubscriptions models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserPurchasedSubscriptionsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $users = ArrayHelper::map(User::find()->where(['IN', 'user_type', [User::USER_TYPE_NORMAL]])->all(), 'id', function ($data) {
            return $data['first_name'] . " " . $data['last_name'] . " (" . $data['email'] . ")";
        });

        $totalEarn = 0.00;
        $data = $dataProvider->getModels();
        if (!empty($data)) {
            foreach ($data as $key => $dataRow) {
                if (!empty($dataRow) && $dataRow instanceof UserPurchasedSubscriptions) {
                    $totalEarn += $dataRow->amount;
                }
            }
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'users' => $users,
            'totalEarn' => $totalEarn
        ]);
    }

    /**
     * Displays a single UserPurchasedSubscriptions model.
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
     * Creates a new UserPurchasedSubscriptions model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
//    public function actionCreate()
//    {
//        $model = new UserPurchasedSubscriptions();
//
//        if ($model->load(Yii::$app->request->post()) && $model->save()) {
//            return $this->redirect(['view', 'id' => $model->id]);
//        }
//
//        return $this->render('create', [
//            'model' => $model,
//        ]);
//    }

    /**
     * Updates an existing UserPurchasedSubscriptions model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
//    public function actionUpdate($id)
//    {
//        $model = $this->findModel($id);
//
//        if ($model->load(Yii::$app->request->post()) && $model->save()) {
//            return $this->redirect(['view', 'id' => $model->id]);
//        }
//
//        return $this->render('update', [
//            'model' => $model,
//        ]);
//    }

    /**
     * Deletes an existing UserPurchasedSubscriptions model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->delete()) {
            Yii::$app->session->setFlash(Growl::TYPE_SUCCESS, "User subscription deleted successfully.");
        } else {
            Yii::$app->session->setFlash(Growl::TYPE_DANGER, "Error while deleting User subscription.");
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the UserPurchasedSubscriptions model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return UserPurchasedSubscriptions the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = UserPurchasedSubscriptions::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
