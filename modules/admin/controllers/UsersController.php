<?php

namespace app\modules\admin\controllers;

use app\modules\admin\models\User;
use Yii;
use app\modules\admin\models\Users;
use app\modules\admin\models\UsersSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\widgets\ActiveForm;

/**
 * UsersController implements the CRUD actions for Users model.
 */
class UsersController extends Controller
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
     * Lists all Users models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UsersSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Users model.
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
     * Creates a new Users model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Users();
        $model->scenario = Users::SCENARIO_CREATE_NORMAL_USER;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return \yii\widgets\ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) { //&& $model->validate()

            $password = $model->password_hash;
            $model->password_hash = password_hash($model->password_hash, PASSWORD_DEFAULT);

            $model->user_type = Users::USER_TYPE_NORMAL_USER;

            $newShopLogoFile = UploadedFile::getInstance($model, 'shop_logo');
            if (isset($newShopLogoFile) && isset($model->is_shop_owner)) {
                $shop_logo_picture = time() . rand(99999, 88888) . '.' . $newShopLogoFile->extension;
//                if (!empty($oldShopLogoFile) && file_exists(Yii::getAlias('@shopLogoRelativePath') . "/" . $oldShopLogoFile)) {
//                    unlink(Yii::getAlias('@shopLogoRelativePath') . "/" . $oldShopLogoFile);
//                }
                $newShopLogoFile->saveAs(Yii::getAlias('@shopLogoRelativePath') . "/" . $shop_logo_picture);
                $model->shop_logo = $shop_logo_picture;
            }

            if ($model->save(false)) {
                \Yii::$app->getSession()->setFlash('success', 'You have successfully created User!');

                Yii::$app->mailer->compose('admin/userRegistration-html', ['model' => $model, 'pwd' => $password])
                    ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
                    ->setTo($model->email)
                    ->setSubject('Thank you for Registration!')
                    ->send();

                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Users model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = Users::SCENARIO_UPDATE_NORMAL_USER;

        // Old file and Password
        $oldProfileFile = $model->profile_picture;
        $oldShopLogoFile = $model->shop_logo;
        $oldpwd = $model->password_hash;
        $model->password_hash = '';

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        $postData = Yii::$app->request->post('Users');

        if ($model->load(Yii::$app->request->post())) { // && $model->save()

            // Update new password
            $new_password = $model->password_hash;
            if (empty($new_password)) {
                $model->password_hash = $oldpwd;
            } else {
                $model->password_hash = Yii::$app->getSecurity()->generatePasswordHash($new_password);
            }

            $model->profile_picture = $oldProfileFile;

            $newShopLogoFile = UploadedFile::getInstance($model, 'shop_logo');
            if (isset($newShopLogoFile) && isset($postData['is_shop_owner'])) {
                $shop_logo_picture = time() . rand(99999, 88888) . '.' . $newShopLogoFile->extension;
                if (!empty($oldShopLogoFile) && file_exists(Yii::getAlias('@shopLogoRelativePath') . "/" . $oldShopLogoFile)) {
                    unlink(Yii::getAlias('@shopLogoRelativePath') . "/" . $oldShopLogoFile);
                }
                $newShopLogoFile->saveAs(Yii::getAlias('@shopLogoRelativePath') . "/" . $shop_logo_picture);
                $model->shop_logo = $shop_logo_picture;
            } else if (isset($postData['is_shop_owner']) && empty($newShopLogoFile)) {
                $model->shop_logo = $oldShopLogoFile;
            } else {
                $model->shop_logo = "";
            }

            if (isset($postData['is_shop_owner'])) {
                $model->is_shop_owner = Users::IS_SHOP_OWNER_YES;
                $model->shop_name = $postData['shop_name'];
                $model->shop_email = $postData['shop_email'];
                $model->shop_phone_number = $postData['shop_phone_number'];
                $model->shop_address = $postData['shop_address'];
            } else {
                $model->is_shop_owner = Users::IS_SHOP_OWNER_NO;
                $model->shop_name = $model->shop_email = $model->shop_phone_number = $model->shop_address = "";
            }

            $model->user_type = Users::USER_TYPE_NORMAL_USER;
            $model->updated_at = date('Y-m-d H:i:s');

            if ($model->save()) {
                \Yii::$app->getSession()->setFlash('success', 'You have successfully updated User!');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Users model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        $this->findModel($id)->delete();

        if (!empty($model)) {
            $oldProfileFile = $model->profile_picture;
            $oldShopLogoFile = $model->shop_logo;

            if (!empty($oldProfileFile) && file_exists(Yii::getAlias('@profilePictureRelativePath') . "/" . $oldProfileFile)) {
                unlink(Yii::getAlias('@profilePictureRelativePath') . "/" . $oldProfileFile);
            }

            if (!empty($oldShopLogoFile) && file_exists(Yii::getAlias('@shopLogoRelativePath') . "/" . $oldShopLogoFile)) {
                unlink(Yii::getAlias('@shopLogoRelativePath') . "/" . $oldShopLogoFile);
            }
        }
        \Yii::$app->getSession()->setFlash('success', 'You have successfully deleted User!');
        return $this->redirect(['index']);
    }

    /**
     * Finds the Users model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Users the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Users::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
