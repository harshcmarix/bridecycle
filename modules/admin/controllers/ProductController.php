<?php

namespace app\modules\admin\controllers;

use app\models\Brand;
use app\models\ProductCategory;
use app\models\search\ProductSearch;
use Yii;
use app\models\Product;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * ProductsController implements the CRUD actions for Products model.
 */
class ProductController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Products models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $categories = ArrayHelper::map(ProductCategory::find()->where(['parent_category_id' => null])->all(), 'id', function ($data) {
            return $data['name'];
        });

        $subCategories = ArrayHelper::map(ProductCategory::find()->where(['IS NOT', 'parent_category_id', null])->all(), 'id', function ($data) {
            return $data['name'];
        });

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'categories' => $categories,
            'subCategories' => $subCategories,
        ]);
    }

    /**
     * Displays a single Products model.
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
     * Creates a new Products model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Product();
        $category = ArrayHelper::map(ProductCategory::find()->where(['parent_category_id' => null])->all(), 'id', 'name');
        $subcategory = [];
        $brand = ArrayHelper::map(Brand::find()->all(), 'id', 'name');

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'category' => $category,
            'subcategory' => $subcategory,
            'brand' => $brand
        ]);
    }

    /**
     * Updates an existing Products model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);


        $category = ArrayHelper::map(ProductCategory::find()->where(['parent_category_id' => null])->all(), 'id', 'name');
        $subcategory = ArrayHelper::map(ProductCategory::find()->where(['parent_category_id' => $model->category_id])->all(), 'id', 'name');;
        $brand = ArrayHelper::map(Brand::find()->all(), 'id', 'name');


        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'category' => $category,
            'subcategory' => $subcategory,
            'brand' => $brand
        ]);
    }

    /**
     * Deletes an existing Products model.
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
     * Finds the Products model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Product the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Product::findOne($id)) !== null) {
            return $model;
        }
        throw NotFoundHttpException('The requested page does not exist.');
    }

    public function actionGetSubCategoryList($category_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $subCategoryList = [];
        if (!empty($category_id)) {
            $subCategory = ArrayHelper::map(ProductCategory::find()->where(['parent_category_id' => $category_id])->all(), 'id', 'name');
            if (!empty($subCategory)) {
                foreach ($subCategory as $key => $subCategoryRow) {
                    $subCategoryList[] = "<option value='" . $key . "'>" . $subCategoryRow . "</option>";
                }
            }
        }
        return ['success' => true, 'dataList' => $subCategoryList];

    }
}
