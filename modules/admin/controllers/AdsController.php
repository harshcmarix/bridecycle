<?php

namespace app\modules\admin\controllers;

use kartik\growl\Growl;
use Yii;
use app\models\Ads;
use app\models\search\AdsSearch;
use yii\base\BaseObject;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\imagine\Image;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * AdsController implements the CRUD actions for Ads model.
 */
class AdsController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'view', 'create', 'update', 'delete'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'create', 'update', 'delete'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Ads models.
     * @return mixed
     */
    public function actionIndex()
    {
        $analytics = \Yii::createObject([
            'class' => \ymaker\google\analytics\mp\Analytics::className(),
            'v' => 1,                                       // Protocol version. Default value: 1
            'tid' => 'UA-201471334-1',                            // Tracking ID / Web Property ID
            'cid' => '101515763135510528805' // Client ID. Random UUID (http://www.ietf.org/rfc/rfc4122.txt)
        ]);
        $responce = $analytics->send([
            't' => 'preview',     // Hit Type.
            'ec' => 'video',    // Event Category.
            'ea' => 'play',     // Event Action.
            'el' => 'bridecycle',  // Event label.
            'ev' => 300,        // Event value.
            'dp' => 'ads/index', // Page
            'dh'=>'203.109.113.157/bridecycle/web/admin',
        ]);
        p($responce);


        $searchModel = new AdsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Ads model.
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
     * Creates a new Ads model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Ads();
        $model->scenario = Ads::SCENARIO_CREATE;
        $ad_image = UploadedFile::getInstance($model, 'image');

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return \yii\widgets\ActiveForm::validate($model);
        }
        if ($model->load(Yii::$app->request->post())) {

            if (!empty($ad_image)) {
                $uploadDirPath = Yii::getAlias('@adsImageRelativePath');
                $uploadThumbDirPath = Yii::getAlias('@adsImageThumbRelativePath');
                $thumbImagePath = '';

                // Create profile upload directory if not exist
                if (!is_dir($uploadDirPath)) {
                    mkdir($uploadDirPath, 0777);
                }

                // Create profile thumb upload directory if not exist
                if (!is_dir($uploadThumbDirPath)) {
                    mkdir($uploadThumbDirPath, 0777);
                }

                $ext = $ad_image->extension;
                $fileName = pathinfo($ad_image->name, PATHINFO_FILENAME);
                $fileName = $fileName . '_' . time() . '.' . $ext;
                // Upload profile picture
                $ad_image->saveAs($uploadDirPath . '/' . $fileName);
                // Create thumb of profile picture
                $actualImagePath = $uploadDirPath . '/' . $fileName;
                $thumbImagePath = $uploadThumbDirPath . '/' . $fileName;
                // p($actualImagePath);
                Image::thumbnail($actualImagePath, Yii::$app->params['profile_picture_thumb_width'], Yii::$app->params['profile_picture_thumb_height'])->save($thumbImagePath, ['quality' => Yii::$app->params['profile_picture_thumb_quality']]);
                // Insert profile picture name into database
                $model->image = $fileName;
            }

            if ($model->save()) {
                Yii::$app->session->setFlash(Growl::TYPE_SUCCESS, "Ads created successfully.");
            } else {
                Yii::$app->session->setFlash(Growl::TYPE_DANGER, "Error while creating Ads.");
            }
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Ads model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $old_image = $model->image;

        $new_image = UploadedFile::getInstance($model, 'image');

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return \yii\widgets\ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {

            if (!empty($new_image)) {
                $uploadDirPath = Yii::getAlias('@adsImageRelativePath');
                $uploadThumbDirPath = Yii::getAlias('@adsImageThumbRelativePath');
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
                $model->image = $fileName;

            } else {
                $model->image = $old_image;
            }

            if ($model->save()) {
                Yii::$app->session->setFlash(Growl::TYPE_SUCCESS, "Ads updated successfully.");
            } else {
                Yii::$app->session->setFlash(Growl::TYPE_DANGER, "Error while updating Ads.");
            }
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Ads model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $image = $model->image;
        $uploadDirPath = Yii::getAlias('@adsImageRelativePath');
        $uploadThumbDirPath = Yii::getAlias('@adsImageThumbRelativePath');
        // unlink images with thumb
        if (file_exists($uploadDirPath . '/' . $image) && !empty($image)) {
            unlink($uploadDirPath . '/' . $image);
        }
        if (file_exists($uploadThumbDirPath . '/' . $image) && !empty($image)) {
            unlink($uploadThumbDirPath . '/' . $image);
        }
        if ($model->delete()) {
            Yii::$app->session->setFlash(Growl::TYPE_SUCCESS, "Ads deleted successfully.");
        } else {
            Yii::$app->session->setFlash(Growl::TYPE_DANGER, "Error while deleting Ads.");
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the Ads model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Ads the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Ads::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionImageDelete($id)
    {
        $model = $this->findModel($id);

        $uploadDirPath = Yii::getAlias('@adsImageRelativePath');
        $uploadThumbDirPath = Yii::getAlias('@adsImageThumbRelativePath');
        // unlink images with thumb
        if (file_exists($uploadDirPath . '/' . $model->image) && !empty($model->image)) {
            unlink($uploadDirPath . '/' . $model->image);
        }
        if (file_exists($uploadThumbDirPath . '/' . $model->image) && !empty($model->image)) {
            unlink($uploadThumbDirPath . '/' . $model->image);
        }
        $model->image = null;
        if ($model->save()) {
            return Json::encode(['success' => 'image successfully deleted']);
        }
    }


//
//
//    /**
//     * Initializes an Analytics Reporting API V4 service object.
//     *
//     * @return An authorized Analytics Reporting API V4 service object.
//     */
//    public function initializeAnalytics()
//    {
//
//        // Use the developers console and download your service account
//        // credentials in JSON format. Place them in this directory or
//        // change the key file location if necessary.
//        $KEY_FILE_LOCATION = __DIR__ . '/bride-cycle-cf380-18bb47b40ab1.json';
//
//        // Create and configure a new client object.
//        $client = new \Google_Client();
//        $client->setApplicationName("Hello Analytics Reporting");
//        $client->setAuthConfig($KEY_FILE_LOCATION);
//        $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
//        $analytics = new \Google_Service_AnalyticsReporting($client);
//
//        return $analytics;
//    }
//
//
//    /**
//     * Queries the Analytics Reporting API V4.
//     *
//     * @param service An authorized Analytics Reporting API V4 service object.
//     * @return The Analytics Reporting API V4 response.
//     */
//    public function getReport($analytics) {
//
//        // Replace with your view ID, for example XXXX.
//        //$VIEW_ID = "http://203.109.113.157/bridecycle/web/admin/ads/view?id=1";
//
//
//
//        $VIEW_ID = "246399535";
//
//        // Create the DateRange object.
//        $dateRange = new \Google_Service_AnalyticsReporting_DateRange();
//        $dateRange->setStartDate("7daysAgo");
//        $dateRange->setEndDate("today");
//
//        // Create the Metrics object.
//        $sessions = new \Google_Service_AnalyticsReporting_Metric();
//        $sessions->setExpression("ga:sessions");
//        $sessions->setAlias("sessions");
//
//        // Create the ReportRequest object.
//        $request = new \Google_Service_AnalyticsReporting_ReportRequest();
//
//
//
//        $request->setViewId($VIEW_ID);
//        $request->setDateRanges($dateRange);
//        $request->setMetrics(array($sessions));
//
//        $body = new \Google_Service_AnalyticsReporting_GetReportsRequest();
//        $body->setReportRequests( array( $request) );
//        p($analytics->reports->batchGet( $body ));
//        return $analytics->reports->batchGet( $body );
//    }
//
//
//    /**
//     * Parses and prints the Analytics Reporting API V4 response.
//     *
//     * @param An Analytics Reporting API V4 response.
//     */
//    public function printResults($reports) {
//        for ( $reportIndex = 0; $reportIndex < count( $reports ); $reportIndex++ ) {
//            $report = $reports[ $reportIndex ];
//            $header = $report->getColumnHeader();
//            $dimensionHeaders = $header->getDimensions();
//            $metricHeaders = $header->getMetricHeader()->getMetricHeaderEntries();
//            $rows = $report->getData()->getRows();
//
//            for ( $rowIndex = 0; $rowIndex < count($rows); $rowIndex++) {
//                $row = $rows[ $rowIndex ];
//                $dimensions = $row->getDimensions();
//                $metrics = $row->getMetrics();
//                for ($i = 0; $i < count($dimensionHeaders) && $i < count($dimensions); $i++) {
//                    print($dimensionHeaders[$i] . ": " . $dimensions[$i] . "\n");
//                }
//
//                for ($j = 0; $j < count($metrics); $j++) {
//                    $values = $metrics[$j]->getValues();
//                    for ($k = 0; $k < count($values); $k++) {
//                        $entry = $metricHeaders[$k];
//                        print($entry->getName() . ": " . $values[$k] . "\n");
//                    }
//                }
//            }
//        }
//    }


}
