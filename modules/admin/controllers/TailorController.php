<?php

namespace app\modules\admin\controllers;

use Yii;
use app\models\Tailor;
use app\models\search\TailorSearch;
use yii\web\{
    Controller,
    NotFoundHttpException,
    UploadedFile
};
use yii\filters\AccessControl;
use yii\imagine\Image;
use kartik\growl\Growl;
use \yii\helpers\Json;

/**
 * TailorController implements the CRUD actions for Tailor model.
 */
class TailorController extends Controller
{
     /**
     * {@inheritdoc}
     */
     public function behaviors()
    {
        return [
           'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'create', 'update','view','delete','image-delete'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'create', 'update','view','delete','image-delete'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Tailor models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new TailorSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Tailor model.
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
     * Creates a new Tailor model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Tailor();
        $model->scenario = Tailor::SCENARIO_CREATE;
        $shop_image = UploadedFile::getInstance($model, 'shop_image');
        if ($model->load(Yii::$app->request->post())) {
            
            if(!empty($shop_image)){
                $uploadDirPath = Yii::getAlias('@tailorShopImageRelativePath');
                $uploadThumbDirPath = Yii::getAlias('@tailorShopImageThumbRelativePath');
                $thumbImagePath = '';

                // Create profile upload directory if not exist
                if (!is_dir($uploadDirPath)) {
                    mkdir($uploadDirPath, 0777);
                }

                // Create profile thumb upload directory if not exist
                if (!is_dir($uploadThumbDirPath)) {
                    mkdir($uploadThumbDirPath, 0777);
                }

                    $ext = $shop_image->extension;
                    $fileName = pathinfo($shop_image->name, PATHINFO_FILENAME);
                    $fileName = $fileName . '_' . time() . '.' . $ext;
                    // Upload profile picture
                    $shop_image->saveAs($uploadDirPath . '/' . $fileName);
                    // Create thumb of profile picture
                    $actualImagePath = $uploadDirPath . '/' . $fileName;
                    $thumbImagePath = $uploadThumbDirPath . '/' . $fileName;
                    // p($actualImagePath);
                    Image::thumbnail($actualImagePath, Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height'])->save($thumbImagePath, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
                    // Insert profile picture name into database
                    $model->shop_image = $fileName;
            }

            if ($model->save()) {
                Yii::$app->session->setFlash(Growl::TYPE_SUCCESS, "Tailor created successfully.");
            } else {
                Yii::$app->session->setFlash(Growl::TYPE_DANGER, "Error while creating Tailor.");
            }
             return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Tailor model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
         $model = $this->findModel($id);

         $old_image = $model->shop_image;
         $new_image = UploadedFile::getInstance($model, 'shop_image');

        if ($model->load(Yii::$app->request->post())) {
           
            if (!empty($new_image)) {
                    $uploadDirPath = Yii::getAlias('@tailorShopImageRelativePath');
                    $uploadThumbDirPath = Yii::getAlias('@tailorShopImageThumbRelativePath');
                    $thumbImagePath = '';

                // Create product image upload directory if not exist
                if (!is_dir($uploadDirPath)) {
                    mkdir($uploadDirPath, 0777);
                }
                //unlink real image if update
                if (file_exists($uploadDirPath . '/' . $old_image) && !empty($old_image)) {
                    unlink($uploadDirPath . '/' . $old_image);
                }
                // Create product image thumb upload directory if not exist
                if (!is_dir($uploadThumbDirPath)) {
                    mkdir($uploadThumbDirPath, 0777);
                }
                //unlink thumb image if update
                if (file_exists($uploadThumbDirPath . '/' . $old_image) && !empty($old_image)) {
                    unlink($uploadThumbDirPath . '/' . $old_image);
                }

                    $ext = $new_image->extension;
                    $fileName = pathinfo($new_image->name, PATHINFO_FILENAME);
                    $fileName = $fileName . '_' . time() . '.' . $ext;
                    // Upload profile picture
                    $new_image->saveAs($uploadDirPath . '/' . $fileName);
                    // Create thumb of profile picture
                    $actualImagePath = $uploadDirPath . '/' . $fileName;
                    $thumbImagePath = $uploadThumbDirPath . '/' . $fileName;
                    // p($actualImagePath);
                    Image::thumbnail($actualImagePath, Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height'])->save($thumbImagePath, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
                    // Insert profile picture name into database
                    $model->shop_image = $fileName;

            } else {
                $model->shop_image = $old_image;
            }
            
            if ($model->save()) {
                Yii::$app->session->setFlash(Growl::TYPE_SUCCESS, "Tailor updated successfully.");
            } else {
                Yii::$app->session->setFlash(Growl::TYPE_DANGER, "Error while updating Tailor.");
            }
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Tailor model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
       $model = $this->findModel($id);
         $uploadDirPath = Yii::getAlias('@tailorShopImageRelativePath');
         $uploadThumbDirPath = Yii::getAlias('@tailorShopImageThumbRelativePath');
         // unlink images with thumb
         if(file_exists($uploadDirPath.'/'.$model->shop_image) && !empty($model->shop_image)){
                unlink($uploadDirPath.'/'.$model->shop_image);
         }
         if(file_exists($uploadThumbDirPath.'/'.$model->shop_image) && !empty($model->shop_image)){
                unlink($uploadThumbDirPath.'/'.$model->shop_image);
         }
        if ($model->delete()) {
            Yii::$app->session->setFlash(Growl::TYPE_SUCCESS, "Tailor deleted successfully.");
        } else {
            Yii::$app->session->setFlash(Growl::TYPE_DANGER, "Error while deleting Tailor.");
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the Tailor model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Tailor the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Tailor::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
     /**
     * Deletes an existing image from perticular field.
     * If deletion is successful, success message will get in update page result.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionImageDelete($id){
        $model = $this->findModel($id);
        
         $uploadDirPath = Yii::getAlias('@tailorShopImageRelativePath');
         $uploadThumbDirPath = Yii::getAlias('@tailorShopImageThumbRelativePath');
         // unlink images with thumb
         if(file_exists($uploadDirPath.'/'.$model->shop_image) && !empty($model->shop_image)){
                unlink($uploadDirPath.'/'.$model->shop_image);
         }
         if(file_exists($uploadThumbDirPath.'/'.$model->shop_image) && !empty($model->shop_image)){
                unlink($uploadThumbDirPath.'/'.$model->shop_image);
         }
         $model->shop_image = null;
        if($model->save()){
           return Json::encode(['success'=>'image successfully deleted']);
        }
    }
}
