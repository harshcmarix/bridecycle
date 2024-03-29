<?php

namespace app\modules\api\v2\controllers;

use Yii;
use app\models\Tailor;
use yii\web\NotFoundHttpException;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;
use yii\filters\Cors;

/**
 * TailorController implements the CRUD actions for Tailor model.
 */
class TailorController extends ActiveController
{
    /**
     * @var string
     */
    public $modelClass = 'app\models\Tailor';

    /**
     * @var string
     */
    public $searchModelClass = 'app\modules\api\v2\models\search\TailorSearch';

    /**
     * @return array
     */
    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD', 'OPTIONS'],
            'view' => ['GET', 'HEAD', 'OPTIONS'],
            'create' => ['POST', 'OPTIONS'],
            'update' => ['PUT', 'PATCH'],
            'delete' => ['POST', 'DELETE'],
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $auth = $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'only' => ['index', 'view', 'create', 'update', 'delete'],
            'authMethods' => [
                HttpBasicAuth::class,
                HttpBearerAuth::class,
                QueryParamAuth::class,
            ]
        ];

        unset($behaviors['authenticator']);
        // re-add authentication filter
        $behaviors['authenticator'] = $auth;

        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = ['options'];

        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                'Access-Control-Expose-Headers' => ['X-Pagination-Per-Page', 'X-Pagination-Current-Page', 'X-Pagination-Total-Count ', 'X-Pagination-Page-Count'],
            ],
        ];

        return $behaviors;
    }

    /**
     * @return array
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        unset($actions['update']);
        unset($actions['view']);
        unset($actions['delete']);
        unset($actions['create']);

        return $actions;
    }

    /**
     * Lists all Tailor models.
     * @return mixed
     */
    public function actionIndex()
    {
        $model = new $this->searchModelClass;
        $requestParams = Yii::$app->getRequest()->getBodyParams();

        if (empty($requestParams)) {
            $requestParams = Yii::$app->getRequest()->getQueryParams();
        }
        return $model->search($requestParams);
    }

    /**
     * Displays a single Tailor model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = Tailor::findOne($id);
        if (!$model instanceof Tailor) {
            throw new NotFoundHttpException('Tailor doesn\'t exist.');
        }

        $tailor_shop_image = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
        if (!empty($model->shop_image) && file_exists(Yii::getAlias('@tailorShopImageRelativePath') . '/' . $model->shop_image)) {
            $tailor_shop_image = Yii::$app->request->getHostInfo() . Yii::getAlias('@tailorShopImageAbsolutePath') . '/' . $model->shop_image;
        }
        $model->shop_image = $tailor_shop_image;

        $tailor_voucher_image = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
        if (!empty($model->voucher) && file_exists(Yii::getAlias('@tailorVoucherImageRelativePath') . '/' . $model->voucher)) {
            $tailor_voucher_image = Yii::$app->request->getHostInfo() . Yii::getAlias('@tailorVoucherImageAbsolutePath') . '/' . $model->voucher;
        }

        $model->zip_code = (string)$model->zip_code;
        $model->voucher = $tailor_voucher_image;

        return $model;
    }

}