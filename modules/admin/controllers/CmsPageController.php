<?php

namespace app\modules\admin\controllers;

use Yii;
use app\models\CmsPage;
use app\models\search\CmsPageSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use kartik\growl\Growl;

/**
 * CmsPageController implements the CRUD actions for CmsPage model.
 */
class CmsPageController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
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
     * Lists all CmsPage models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CmsPageSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single CmsPage model.
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
     * Creates a new CmsPage model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new CmsPage();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash(Growl::TYPE_SUCCESS, "Content created successfully.");
            } else {
                Yii::$app->session->setFlash(Growl::TYPE_DANGER, "Error while creating Content.");
            }
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing CmsPage model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash(Growl::TYPE_SUCCESS, "Content updated successfully.");
            } else {
                Yii::$app->session->setFlash(Growl::TYPE_DANGER, "Error while updating Content.");
            }
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing CmsPage model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->delete()) {
            Yii::$app->session->setFlash(Growl::TYPE_SUCCESS, "Content deleted successfully.");
        } else {
            Yii::$app->session->setFlash(Growl::TYPE_DANGER, "Error while deleting Content.");
        }
        return $this->redirect(['index']);
    }

    /**
     * Finds the CmsPage model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return CmsPage the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CmsPage::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionCkeditorImageUpload()
    {
        $funcNum = $_REQUEST['CKEditorFuncNum'];
        $message = "";
        $url = "";
        if ($_FILES['upload']) {
            if (($_FILES['upload'] == "none") or (empty($_FILES['upload']['name']))) {
                $message = Yii::t('app', "Please Upload an image.");
            } elseif ($_FILES['upload']["size"] == 0 or $_FILES['upload']["size"] > 510241024) {
                $message = Yii::t('app', "The image should not exceed 5MB.");
            } elseif (($_FILES['upload']["type"] != "image/jpg")
                and ($_FILES['upload']["type"] != "image/jpeg")
                and ($_FILES['upload']["type"] != "image/png")) {
                $message = Yii::t('app', "The file type should be JPG , JPEG , PNG.");
            } elseif (!is_uploaded_file($_FILES['upload']["tmp_name"])) {
                $message = Yii::t('app', "Upload Error, Please try again.");
            } else {
                //you need this (use yii\db\Expression;) for RAND() method
                $random = rand('0123456789', '9876543210');

                $extension = pathinfo($_FILES['upload']['name'], PATHINFO_EXTENSION);

                //Rename the image here the way you want
                $name = date("mdYhis", time()) . "" . $random . '.' . $extension;

                // Here is the folder where you will save the images
                $folder = 'uploads/ckeditor_images/';

                // upload directory if not exist
                if (!is_dir($folder)) {
                    mkdir($folder, 0777);
                }

                $url = Yii::$app->urlManager->createAbsoluteUrl($folder . $name);

                move_uploaded_file($_FILES['upload']['tmp_name'], $folder . $name);
            }

            echo '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction("' . $funcNum . '", "' . $url . '", "' . $message . '" );</script>';
        }
    }
}
