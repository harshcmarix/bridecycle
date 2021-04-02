<?php

namespace app\modules\api\v1\controllers;

use yii\rest\ActiveController;

/**
 * Class SiteController
 * @package app\modules\api\v1\controllers
 */
class SiteController extends ActiveController
{
    /**
     * @var string
     */
    public $modelClass = 'app\modules\api\models\User';

    /**
     * @var string
     */
    public $searchModelClass = 'app\modules\api\models\search\UserSearch';

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
            'delete' => ['DELETE']
        ];
    }

    /**
     * @return array
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['view']);
        unset($actions['delete']);
        return $actions;
    }

    /**
     * @return string[]
     */
    public function actionIndex()
    {
        return [
            'message' => "You may customize this page by editing the following file:" . __FILE__,
        ];
    }
}
