<?php

namespace app\modules\api\v2\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataFilter;
use yii\data\ActiveDataProvider;
use app\models\Notification;

/**
 * NotificationSearch represents the model behind the search form of `app\models\Notification`.
 */
class NotificationSearch extends Notification
{
    /**
     * @var $hiddenFields Array of hidden fields which not needed in APIs
     */
    protected $hiddenFields = [];

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'owner_id', 'notification_receiver_id', 'ref_id'], 'integer'],
            [['notification_text', 'action', 'ref_type', 'created_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($requestParams)
    {

        /* ########## Prepare Request Filter Start ######### */
        if (!empty($requestParams['filter'])) {
            foreach ($requestParams['filter'] as $key => $val) {
                if ($key === 'dropdown') {
                    if (is_array($val) && !empty($val)) {
                        foreach ($val as $k => $v) {
                            if (!empty($v['like'])) {
                                $requestParams['filter'][$key][$k]['like'] = trim(urldecode($v['like']));
                            } else {
                                if (isset($v) && !is_array($v) && $v != '') {
                                    $requestParams['filter'][$key][$k] = trim(urldecode($v));
                                }
                            }
                        }
                    }
                } else {
                    if (!empty($val['like'])) {
                        $requestParams['filter'][$key]['like'] = trim(urldecode($val['like']));
                    } else {
                        if (isset($val) && !is_array($val) && $val != '') {
                            $requestParams['filter'][$key] = trim(urldecode($val));
                        }
                    }
                }
            }
        }
        /* ########## Prepare Request Filter End ######### */

        /* ########## Active Data Filter Start ######### */
        $activeDataFilter = new ActiveDataFilter();
        $activeDataFilter->setSearchModel($this);
        $filter = null;
        if (isset($requestParams['filter']['dropdown'])) {
            unset($requestParams['filter']['dropdown']);
        }

        if ($activeDataFilter !== null) {
            if ($activeDataFilter->load($requestParams)) {
                $filter = $activeDataFilter->build();

                if ($filter === false) {
                    return $activeDataFilter;
                }
            }
        }
        /* ########## Active Data Filter End ######### */

        /* ########## Prepare Query With Default Filter Start ######### */

        // Notification is read update
        $queryUpdate = self::find()->where(['notification_receiver_id' => Yii::$app->user->identity->id, 'is_read' => Notification::NOTIFICATION_IS_READ_NO])->all();
        if (!empty($queryUpdate)) {
            foreach ($queryUpdate as $key => $queryUpdateRow) {
                if (!empty($queryUpdateRow) && $queryUpdateRow instanceof Notification) {
                    $queryUpdateRow->is_read = '1';
                    $queryUpdateRow->save(false);
                }
            }
        }

        $query = self::find()->where(['notification_receiver_id' => Yii::$app->user->identity->id]);
        $fields = $this->hiddenFields;
        if (!empty($requestParams['fields'])) {
            $fieldsData = $requestParams['fields'];
            $select = array_diff(explode(',', $fieldsData), $fields);
        } else {
            $select = ['notification.*',];
        }

        $query->select($select);
        if (!empty($filter)) {
            $query->andWhere($filter);
        }
        /* ########## Prepare Query With Default Filter End ######### */

        $query->orderBy(['id' => SORT_DESC]);
        $query->groupBy('notification.id');

        $activeDataProvider = Yii::createObject([
            'class' => ActiveDataProvider::class,
            'query' => $query,
            'pagination' => [
                'params' => $requestParams,
                //'pageSize' => isset($requestParams['pageSize']) ? $requestParams['pageSize'] : Yii::$app->params['default_page_size'], //set page size here
                'pageSize' => isset($requestParams['pageSize']) ? $requestParams['pageSize'] : 550, //set page size here
            ],
            'sort' => [
                'params' => $requestParams,
            ],
        ]);

        $notificationsModelData = $activeDataProvider->getModels();
        $activeDataProvider->setModels($notificationsModelData);
        return $activeDataProvider;
    }
}
