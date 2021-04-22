<?php

namespace app\modules\admin\controllers;

use app\models\Brand;
use app\models\ProductCategory;
use app\models\ProductImage;
use app\models\ProductStatus;
use app\models\search\ProductSearch;
use Yii;
use app\models\Product;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\imagine\Image;
use yii\log\EmailTarget;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use kartik\growl\Growl;
use yii\web\UploadedFile;

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
        $status = ArrayHelper::map(ProductStatus::find()->all(), 'id', 'status');

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $images = UploadedFile::getInstances($model, 'images');
            $model->user_id = Yii::$app->user->identity->id;
            if ($model->save()) {
                if (!empty($images)) {
                    foreach ($images as $img) {
                        $modelImage = new ProductImage();

                        $uploadDirPath = Yii::getAlias('@productImageRelativePath');
                        $uploadThumbDirPath = Yii::getAlias('@productImageThumbRelativePath');
                        $thumbImagePath = '';

                        // Create product upload directory if not exist
                        if (!is_dir($uploadDirPath)) {
                            mkdir($uploadDirPath, 0777);
                        }

                        // Create product thumb upload directory if not exist
                        if (!is_dir($uploadThumbDirPath)) {
                            mkdir($uploadThumbDirPath, 0777);
                        }

                        $fileName = time() . rand(99999, 88888) . '.' . $img->extension;
                        // Upload product picture
                        $img->saveAs($uploadDirPath . '/' . $fileName);
                        // Create thumb of product picture
                        $actualImagePath = $uploadDirPath . '/' . $fileName;
                        $thumbImagePath = $uploadThumbDirPath . '/' . $fileName;

                        Image::thumbnail($actualImagePath, Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height'])->save($thumbImagePath, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
                        // Insert product picture name into database

                        $modelImage->product_id = $model->id;
                        $modelImage->name = $fileName;
                        $modelImage->created_at = date('Y-m-d H:i:s');
                        $modelImage->save(false);
                    }
                }
            }

            \Yii::$app->session->setFlash(Growl::TYPE_SUCCESS, 'You have successfully created Product!');
            //return $this->redirect(['view', 'id' => $model->id]);
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
            'category' => $category,
            'subcategory' => $subcategory,
            'brand' => $brand,
            'status' => $status
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
        $oldUserId = $model->user_id;

        $category = ArrayHelper::map(ProductCategory::find()->where(['parent_category_id' => null])->all(), 'id', 'name');
        $subcategory = ArrayHelper::map(ProductCategory::find()->where(['parent_category_id' => $model->category_id])->all(), 'id', 'name');;
        $brand = ArrayHelper::map(Brand::find()->all(), 'id', 'name');
        $status = ArrayHelper::map(ProductStatus::find()->all(), 'id', 'status');

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if (empty($oldUserId)) {
                $model->user_id = Yii::$app->user->identity->id;
            }
            $images = UploadedFile::getInstances($model, 'images');

            if ($model->save()) {
                if (!empty($images)) {

                    $oldImages = $model->productImages;
                    if (!empty($oldImages)) {
                        foreach ($oldImages as $oldImageRow) {
                            $oldImageRow->delete();
                        }
                    }

                    foreach ($images as $img) {
                        $modelImage = new ProductImage();

                        $uploadDirPath = Yii::getAlias('@productImageRelativePath');
                        $uploadThumbDirPath = Yii::getAlias('@productImageThumbRelativePath');
                        $thumbImagePath = '';

                        // Create product upload directory if not exist
                        if (!is_dir($uploadDirPath)) {
                            mkdir($uploadDirPath, 0777);
                        }

                        // Create product thumb upload directory if not exist
                        if (!is_dir($uploadThumbDirPath)) {
                            mkdir($uploadThumbDirPath, 0777);
                        }

                        $fileName = time() . rand(99999, 88888) . '.' . $img->extension;
                        // Upload product picture
                        $img->saveAs($uploadDirPath . '/' . $fileName);
                        // Create thumb of product picture
                        $actualImagePath = $uploadDirPath . '/' . $fileName;
                        $thumbImagePath = $uploadThumbDirPath . '/' . $fileName;

                        Image::thumbnail($actualImagePath, Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height'])->save($thumbImagePath, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
                        // Insert product picture name into database

                        $modelImage->product_id = $model->id;
                        $modelImage->name = $fileName;
                        $modelImage->created_at = date('Y-m-d H:i:s');
                        $modelImage->save(false);
                    }
                }
            }

            \Yii::$app->session->setFlash(Growl::TYPE_SUCCESS, 'You have successfully updated Product!');
            //return $this->redirect(['view', 'id' => $model->id]);
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
            'category' => $category,
            'subcategory' => $subcategory,
            'brand' => $brand,
            'status' => $status
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
        $model = $this->findModel($id);
        if (!empty($model->productImages)) {
            foreach ($model->productImages as $key => $imageRow) {
                if ($imageRow instanceof ProductImage) {
                    if (!empty($imageRow->name) && file_exists(Yii::getAlias('@productImageRelativePath') . "/" . $imageRow->name)) {
                        unlink(Yii::getAlias('@productImageRelativePath') . "/" . $imageRow->name);
                    }

                    if (!empty($imageRow->name) && file_exists(Yii::getAlias('@productImageThumbRelativePath') . "/" . $imageRow->name)) {
                        unlink(Yii::getAlias('@productImageThumbRelativePath') . "/" . $imageRow->name);
                    }
                    $imageRow->delete();
                }
            }
        }
        $this->findModel($id)->delete();
        \Yii::$app->getSession()->setFlash(Growl::TYPE_SUCCESS, 'You have successfully deleted Product!');
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
        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * @param $category_id
     * @return array
     */
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

    /**
     * @return bool[]|false[]
     * @throws NotFoundHttpException
     */
    public function actionUpdateTopSelling()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = Yii::$app->request->post('id');
        $is_top_selling = Yii::$app->request->post('is_top_selling');

        $response = ['success' => false];
        if (!empty($id)) {
            $model = $this->findModel($id);
            if ($model) {
                $model->is_top_selling = (!empty($is_top_selling) && $is_top_selling == 1) ? Product::IS_TOP_SELLING_YES : Product::IS_TOP_SELLING_NO;
                $model->save(false);
                \Yii::$app->getSession()->setFlash(Growl::TYPE_SUCCESS, 'You have successfully updated Product!');
                $response = ['success' => true];
            }
        }
        return $response;
    }

    /**
     * @return bool[]|false[]
     * @throws NotFoundHttpException
     */
    public function actionUpdateTopTrending()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = Yii::$app->request->post('id');
        $is_top_trending = Yii::$app->request->post('is_top_trending');

        $response = ['success' => false];
        if (!empty($id)) {
            $model = $this->findModel($id);
            if ($model) {
                $model->is_top_trending = (!empty($is_top_trending) && $is_top_trending == 1) ? Product::IS_TOP_TRENDING_YES : Product::IS_TOP_TRENDING_NO;
                $model->save(false);
                \Yii::$app->getSession()->setFlash(Growl::TYPE_SUCCESS, 'You have successfully updated Product!');
                $response = ['success' => true];
            }
        }
        return $response;
    }

    public function actionDeleteProductImage($id, $product_id)
    {
        $model = ProductImage::findOne($id);
        if (!empty($model)) {
            if (!empty($model->name) && file_exists(Yii::getAlias('@productImageRelativePath') . "/" . $model->name)) {
                unlink(Yii::getAlias('@productImageRelativePath') . "/" . $model->name);
            }

            if (!empty($model->name) && file_exists(Yii::getAlias('@productImageThumbRelativePath') . "/" . $model->name)) {
                unlink(Yii::getAlias('@productImageThumbRelativePath') . "/" . $model->name);
            }
            $model->delete();
            \Yii::$app->getSession()->setFlash(Growl::TYPE_SUCCESS, 'You have successfully Deleted Product Image!');
        }
        return $this->redirect(['update', 'id' => $product_id]);
    }

}
