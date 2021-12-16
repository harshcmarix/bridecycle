<?php

namespace app\models;

use app\modules\api\v2\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "chat_history".
 *
 * @property int $id
 * @property int|null $product_id
 * @property int|null $from_user_id
 * @property int|null $to_user_id
 * @property string|null $message
 * @property string|null $message_type
 * @property string $chat_type
 * @property int $is_read '0'=>'no','1'=>'yes'
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property User $fromUser
 * @property User $toUser
 */
class ChatHistory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'chat_history';
    }

    /**
     * @return array[]
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => date('Y-m-d h:i:s'),
            ],
        ];
    }

    const MESSAGE_TYPE_TEXT = "text";
    const MESSAGE_TYPE_IMAGE = "image";
    const MESSAGE_TYPE_VIDEO = "video";

    const IS_UNREAD = 0;
    const IS_READ = 1;

    const CHAT_TYPE_SINGLE = "single";
    const CHAT_TYPE_GROUP = "group";

    const PAGE_LIMIT = '30';

    public $file;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [

            [['product_id', 'from_user_id', 'to_user_id', 'message_type'], 'required'],
            [['product_id', 'from_user_id', 'to_user_id', 'is_read'], 'integer'],
            [['message', 'message_type', 'chat_type'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['file'], 'file', 'maxFiles' => 1], // 'extensions' => 'png, jpg, jpeg, gif, bmp, raw, psd, webp'
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['from_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['from_user_id' => 'id']],
            [['to_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['to_user_id' => 'id']],


            [['message'], 'required', 'when' => function ($model) {
                return $model->message_type == self::MESSAGE_TYPE_TEXT;
            },
                'whenClient' => "function (attribute, value) {
                    if ($('#chathistory-message_type').val() == text) {            
                                    return $('#chathistory-message').val() == '';                                    
                                    }
                                }",],

            [['file'], 'required', 'when' => function ($model) {
                return $model->message_type == self::MESSAGE_TYPE_IMAGE;
            },
                'whenClient' => "function (attribute, value) {
                    if ($('#chathistory-message_type').val() == image) {            
                                    return $('#chathistory-file').val() == '';                                    
                                    }
                                }",],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Product ID',
            'from_user_id' => 'From User ID',
            'to_user_id' => 'To User ID',
            'message' => 'Message',
            'message_type' => 'Message Type',
            'chat_type' => 'Chat Type',
            'is_read' => 'Is Read',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return array|false
     */
    public function extraFields()
    {
        return [
            'product0' => 'product0',
            'fromUser0' => 'fromUser0',
            'toUser0' => 'toUser0',
        ];
    }

    /**
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    /**
     * Gets query for [[FromUser]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFromUser()
    {
        return $this->hasOne(User::class, ['id' => 'from_user_id']);
    }

    /**
     * Gets query for [[ToUser]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getToUser()
    {
        return $this->hasOne(User::class, ['id' => 'to_user_id']);
    }

    /////////////////////// For API use only /////////////////////////////////////

    /**
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduct0()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    /**
     * Gets query for [[FromUser]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFromUser0()
    {
        $userDetails = User::find()->where(['id' => $this->from_user_id])->one();
        if ($userDetails instanceof User) {
            $profilepicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
            if (!empty($userDetails->profile_picture) && file_exists(Yii::getAlias('@profilePictureRelativePath') . '/' . $userDetails->profile_picture)) {
                $profilepicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@profilePictureAbsolutePath') . '/' . $userDetails->profile_picture;
            }
            $userDetails->profile_picture = $profilepicture;
        }
        return $userDetails;
    }

    /**
     * Gets query for [[ToUser]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getToUser0()
    {
        $userDetails = User::find()->where(['id' => $this->to_user_id])->one();
        if ($userDetails instanceof User) {
            $profilepicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
            if (!empty($userDetails->profile_picture) && file_exists(Yii::getAlias('@profilePictureRelativePath') . '/' . $userDetails->profile_picture)) {
                $profilepicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@profilePictureAbsolutePath') . '/' . $userDetails->profile_picture;
            }
            $userDetails->profile_picture = $profilepicture;
        }
        return $userDetails;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOneToOneUnreadNotificationCount($fromId, $userID)
    {
        $count = ChatHistory::find()->where(['from_user_id' => $fromId, 'to_user_id' => $userID, 'is_read' => ChatHistory::IS_UNREAD, 'chat_type' => ChatHistory::CHAT_TYPE_SINGLE])->count();
        return $count;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOneToOneUnreadNotificationRecord($fromId, $userID)
    {
        $models = ChatHistory::find()->where(['from_user_id' => $fromId, 'to_user_id' => $userID, 'is_read' => ChatHistory::IS_UNREAD, 'chat_type' => ChatHistory::CHAT_TYPE_SINGLE])->all();
        return $models;
    }
}
