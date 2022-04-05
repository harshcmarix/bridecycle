<?php

namespace app\modules\admin\controllers;

use app\modules\api\v2\models\User;
use kartik\growl\Growl;
use Yii;
use app\models\BridecycleToSellerPayments;
use app\modules\admin\models\search\BridecycleToSellerPaymentsSearch;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * BridecycleToSellerPaymentsController implements the CRUD actions for BridecycleToSellerPayments model.
 */
class BridecycleToSellerPaymentsController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'create', 'update', 'view', 'delete'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'create', 'update', 'view', 'delete'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all BridecycleToSellerPayments models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BridecycleToSellerPaymentsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $modelUpdate = new BridecycleToSellerPayments();


        $totalEarn = 0.00;
        $data = $dataProvider->getModels();
        if (!empty($data)) {
            foreach ($data as $key => $dataRow) {
                if (!empty($dataRow) && $dataRow instanceof BridecycleToSellerPayments) {
                    $totalEarn += $dataRow->amount;
                }
            }
        }
        if ($totalEarn > 0) {
            $totalEarn = ($totalEarn * Yii::$app->params['bridecycle_product_order_charge_percentage'] / 100);
        }


        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'modelUpdate' => $modelUpdate,
            'totalEarn' => $totalEarn
        ]);
    }

    /**
     * Displays a single BridecycleToSellerPayments model.
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
     * Creates a new BridecycleToSellerPayments model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
//    public function actionCreate()
//    {
//        $model = new BridecycleToSellerPayments();
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
     * Updates an existing BridecycleToSellerPayments model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
//    public function actionUpdate($id)
//    {
//        $model = $this->findModel($id);
//
//        Yii::$app->response->format = Response::FORMAT_JSON;
//        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
//            $model->status = BridecycleToSellerPayments::STATUS_COMPLETE;
//            if ($model->save()) {
//                \Yii::$app->getSession()->setFlash(Growl::TYPE_SUCCESS, 'Seller payment updated successfully.');
//
//                $sellerModel = $model->seller;
//                $productModel = $model->product;
//
//                if (!empty($sellerModel) && $sellerModel instanceof User && (!empty($sellerModel->is_payment_done_email_notification_on) || $sellerModel->is_payment_done_email_notification_on == User::IS_EMAIL_NOTIFICATION_ON)) {
//                    if (!empty($sellerModel->email)) {
//                        try {
//                            Yii::$app->mailer->compose('admin/BCtoSellerPaymentDone-html', ['sellerModel' => $sellerModel, 'productModel' => $productModel, 'model' => $model])
//                                ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
//                                ->setTo($sellerModel->email)
//                                ->setSubject('Bride Cycle Payment!')
//                                ->send();
//                        } catch (HttpException $e) {
//                            echo "Error: " . $e->getMessage();
//                        }
//                    }
//                }
//                $response = ['success' => true];
//            } else {
//                \Yii::$app->getSession()->setFlash(Growl::TYPE_DANGER, 'Seller payment not updated, Please try again!');
//                $response = ['success' => false];
//            }
//        } else {
//            \Yii::$app->getSession()->setFlash(Growl::TYPE_DANGER, 'Something went wrong, Please try again!');
//            $response = ['success' => false];
//        }
//        return $response;
//    }

    /**
     * Deletes an existing BridecycleToSellerPayments model.
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
     * Finds the BridecycleToSellerPayments model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return BridecycleToSellerPayments the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = BridecycleToSellerPayments::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
