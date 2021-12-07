<?php

namespace app\modules\admin\controllers;

use app\models\ProductCategory;
use app\models\ProductSizes;
use Yii;
use app\models\Sizes;
use app\modules\admin\models\search\SizesSearch;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use kartik\growl\Growl;

/**
 * SizeController implements the CRUD actions for Sizes model.
 */
class SizeController extends Controller
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
     * Lists all Sizes models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SizesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $productCategories = ArrayHelper::map(ProductCategory::find()->where(['parent_category_id' => null])->all(), 'id', 'name');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'productCategories' => $productCategories
        ]);
    }

    /**
     * Displays a single Sizes model.
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
     * Creates a new Sizes model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Sizes();

        $productCategories = ArrayHelper::map(ProductCategory::find()->where(['parent_category_id' => null])->all(), 'id', 'name');

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            if ($model->save()) {
                Yii::$app->session->setFlash(Growl::TYPE_SUCCESS, "Size created successfully.");
            } else {
                Yii::$app->session->setFlash(Growl::TYPE_DANGER, "Error while creating Size.");
            }

            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
            'productCategories' => $productCategories
        ]);
    }

    /**
     * Updates an existing Sizes model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $productCategories = ArrayHelper::map(ProductCategory::find()->where(['parent_category_id' => null])->all(), 'id', 'name');

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            if ($model->save()) {
                Yii::$app->session->setFlash(Growl::TYPE_SUCCESS, "Size updated successfully.");
            } else {
                Yii::$app->session->setFlash(Growl::TYPE_DANGER, "Error while updating Size.");
            }

            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
            'productCategories' => $productCategories
        ]);
    }

    /**
     * Deletes an existing Sizes model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        $modelsProductSize = $model->productSizes;

        if (!empty($model) && !empty($modelsProductSize) && count($modelsProductSize) > 0) {
            Yii::$app->session->setFlash(Growl::TYPE_INFO, count($modelsProductSize) . " Product(s) are using this size, So you can not delete this size!");
        } elseif (!empty($model) && empty($modelsProductSize) && count($modelsProductSize) <= 0) {
            $model->delete();
            Yii::$app->session->setFlash(Growl::TYPE_SUCCESS, "Size deleted successfully.");
        } else {
            Yii::$app->session->setFlash(Growl::TYPE_DANGER, "Error while deleted Size.");
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the Sizes model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Sizes the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Sizes::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
