<?php

namespace app\modules\api\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataFilter;
use yii\data\ActiveDataProvider;
use app\modules\api\models\User;

/**
 * Class UserSearch
 * @package app\modules\api\models\search
 */
class UserSearch extends User
{
    /**
     * @var $hiddenFields Array of hidden fields which not needed in APIs
     */
    protected $hiddenFields = ['password_hash', 'access_token', 'access_token_expired_at', 'temporary_password'];

    /**
     * @return array[]
     */
    public function rules()
    {
        return [
            [['id', 'mobile', 'shop_phone_number'], 'integer'],
            [['profile_picture', 'first_name', 'last_name', 'email', 'password_hash', 'temporary_password', 'access_token', 'access_token_expired_at', 'user_type', 'is_shop_owner', 'shop_name', 'shop_email', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * @return array|array[]
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * @param $requestParams
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
        $query = self::find();
        $fields = $this->hiddenFields;
        if (!empty($requestParams['fields'])) {
            $fieldsData = $requestParams['fields'];
            $select = array_diff(explode(',', $fieldsData), $fields);
        } else {
            $select = ['id', 'email', 'first_name', 'last_name', 'mobile', 'user_type', 'is_shop_owner'];
        }

        $query->select($select);
        if (!empty($filter)) {
            $query->andWhere($filter);
        }
        /* ########## Prepare Query With Default Filter End ######### */

        $query->groupBy('users.id');
        //p($query->createCommand()->getRawSql());
        return Yii::createObject([
            'class' => ActiveDataProvider::class,
            'query' => $query,
            'pagination' => [
                'params' => $requestParams,
                'pageSize' => isset($requestParams['pageSize']) ? $requestParams['pageSize'] : Yii::$app->params['default_page_size'], //set page size here
            ],
            'sort' => [
                'params' => $requestParams,
            ],
        ]);
    }
}