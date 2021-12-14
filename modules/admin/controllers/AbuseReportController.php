<?php

namespace app\modules\admin\controllers;

use app\modules\api\v2\models\User;
use kartik\growl\Growl;
use Yii;
use app\models\AbuseReport;
use app\modules\admin\models\search\AbuseReportSearch;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * AbuseReportController implements the CRUD actions for AbuseReport model.
 */
class AbuseReportController extends Controller
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
     * Lists all AbuseReport models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new AbuseReportSearch();
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
     * Displays a single AbuseReport model.
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
     * Deletes an existing AbuseReport model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = AbuseReport::findOne($id);
        if (!$model instanceof AbuseReport) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $user = $model->user;
        $seller = $model->seller;
        if (!empty($user) && $user instanceof User) {
            $user->user_status = User::USER_STATUS_ACTIVE;
            $user->save(false);
        }

        if (!empty($seller) && $seller instanceof User) {
            $seller->user_status = User::USER_STATUS_ACTIVE;
            $seller->save(false);
        }

        $model->delete();

        \Yii::$app->session->setFlash(Growl::TYPE_SUCCESS, 'Abuse report deleted successfully.');
        return $this->redirect(['index']);
    }

    /**
     * Finds the AbuseReport model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return AbuseReport the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AbuseReport::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
