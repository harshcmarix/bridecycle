<?php

namespace app\modules\api\v1\models\search;

use app\modules\api\v1\models\User;
use Yii;
use yii\base\BaseObject;
use yii\base\Model;
use yii\data\{
    ActiveDataFilter,
    ActiveDataProvider
};
use app\models\ChatHistory;

/**
 * ChatHistorySearch represents the model behind the search form of `app\models\ChatHistory`.
 */
class ChatHistorySearch extends ChatHistory
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
            [['id', 'from_user_id', 'to_user_id', 'is_read'], 'integer'],
            [['unread_msg_count', 'last_msg', 'message', 'message_type', 'chat_type', 'created_at', 'updated_at'], 'safe'],
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
        $userModel = User::findOne(Yii::$app->user->identity->id);
        $query = self::find()->where("(from_user_id = $userModel->id OR to_user_id = $userModel->id) AND chat_type='single' ");
        $fields = $this->hiddenFields;
        if (!empty($requestParams['fields'])) {
            $fieldsData = $requestParams['fields'];
            $select = array_diff(explode(',', $fieldsData), $fields);
        } else {
            $select = ['chat_history.*'];
        }

        $query->select($select);
        if (!empty($filter)) {
            $query->andWhere($filter);
        }
        /* ########## Prepare Query With Default Filter End ######### */

        //$query->groupBy('chat_history.id');
        $query->groupBy("from_user_id, to_user_id");

        $activeDataProvider = Yii::createObject([
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

        $chatHistoryModelData = $activeDataProvider->getModels();


        if (!empty($chatHistoryModelData)) {
            $getUserIDs = [];
            foreach ($chatHistoryModelData as $keys => $conversation) {
                if ($conversation->from_user_id == $userModel->id) {
                    if (!in_array($conversation->to_user_id, $getUserIDs)) {
                        $getUserIDs[] = $conversation->to_user_id;
                    }
                } else if ($conversation->to_user_id == $userModel->id) {
                    if (!in_array($conversation->from_user_id, $getUserIDs)) {
                        $getUserIDs[] = $conversation->from_user_id;
                    }
                }
            }

            if (!empty($getUserIDs)) {
                $data = [];
                foreach ($getUserIDs as $key => $getUserID) {

                    $userDetails = User::find()->where(["id" => $getUserID])->one();
                    $lastMessage = ChatHistory::find()->where("((from_user_id = $userModel->id AND to_user_id = $userDetails->id) OR (to_user_id = $userModel->id AND from_user_id = $userDetails->id)) AND chat_type = 'single'")->orderBy("id DESC")->one();

                    if (!empty($lastMessage) && $lastMessage instanceof ChatHistory && !empty($userDetails) && $userDetails instanceof User) {
                        $lastMessage->message_type = (!empty($lastMessage) && !empty($lastMessage->message_type)) ? $lastMessage->message_type : "";

                        if (!empty($lastMessage) && !empty($lastMessage->message_type) && in_array($lastMessage->message_type, [ChatHistory::MESSAGE_TYPE_IMAGE, ChatHistory::MESSAGE_TYPE_VIDEO])) {
                            //$lastMessage->last_message = (!empty($lastMessage) && !empty($lastMessage->message)) ? Yii::getAlias('@apiImagesRoot') . Yii::getAlias('@chatMediaAbsolutePath') . '/' . $lastMessage->message : "";
                            // $dataRow['last_message'] = (!empty($lastMessage) && !empty($lastMessage->message)) ? Yii::$app->request->getHostInfo() . Yii::getAlias('@chatMediaThumbAbsolutePath') . '/' . $lastMessage->message : "";

                            $imgFile = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                            if (!empty($lastMessage) && !empty($lastMessage->message) && file_exists(Yii::getAlias('@chatMediaThumbRelativePath') . '/' . $lastMessage->message)) {
                                $imgFile = Yii::$app->request->getHostInfo() . Yii::getAlias('@chatMediaThumbAbsolutePath') . '/' . $lastMessage->message;
                            }
                            $dataRow['last_message'] = $imgFile;
                            $lastMessage->message = $imgFile;
                        } else {
                            //$lastMessage->last_message = (!empty($lastMessage) && !empty($lastMessage->message)) ? $lastMessage->message : "";
                            $dataRow['last_message'] = (!empty($lastMessage) && !empty($lastMessage->message)) ? $lastMessage->message : "";
                        }


                        $dataRow['unread_message_count'] = $lastMessage->getOneToOneUnreadNotificationCount($userDetails->id, $userModel->id);
                        $dataRow['fromUser0'] = $lastMessage->fromUser0;
                        $dataRow['toUser0'] = $lastMessage->toUser0;


                        $data[] = array_merge($lastMessage->toArray(), $dataRow);
                    }
                }
                $chatHistoryModelData = $data;
            }
        }

        $activeDataProvider->setModels($chatHistoryModelData);

        return $activeDataProvider;
    }
}
