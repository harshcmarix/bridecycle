<?php

namespace app\models;

use Yii;
use app\modules\api\v1\models\User;

/**
 * This is the model class for table "notification".
 *
 * @property int $id
 * @property int|null $owner_id
 * @property int|null $notification_receiver_id
 * @property int $ref_id
 * @property string $notification_text
 * @property string $action
 * @property string $ref_type
 * @property string $is_read
 * @property string|null $created_at
 *
 * @property User $owner
 * @property User $notificationReceiver
 */
class Notification extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'notification';
    }

    const NOTIFICATION_IS_READ_NO = '0';
    const NOTIFICATION_IS_READ_YES = '1';

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['owner_id', 'notification_receiver_id', 'ref_id'], 'integer'],
            [['ref_id', 'notification_text', 'action', 'ref_type'], 'required'],
            [['is_read', 'created_at'], 'safe'],
            [['notification_text', 'action', 'ref_type'], 'string', 'max' => 255],
            [['owner_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['owner_id' => 'id']],
            [['notification_receiver_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['notification_receiver_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'owner_id' => 'Owner ID',
            'notification_receiver_id' => 'Notification Receiver ID',
            'ref_id' => 'Ref ID',
            'notification_text' => 'Notification Text',
            'action' => 'Action',
            'ref_type' => 'Ref Type',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return array|false
     */
    public function extraFields()
    {
        return [
            'owner0' => 'owner0',
            'notificationReceiver0' => 'notificationReceiver0',
        ];
    }

    /**
     * Gets query for [[Owner]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOwner()
    {
        return $this->hasOne(User::className(), ['id' => 'owner_id']);
    }

    /**
     * Gets query for [[NotificationReceiver]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNotificationReceiver()
    {
        return $this->hasOne(User::className(), ['id' => 'notification_receiver_id']);
    }

    //////////////////////////////// For API use only //////////////////////////////////////

    /**
     * @param $id
     * @param $type
     * @param $notificationToken
     * @param $messageString
     * @return bool|string
     */
    public function sendPushNotificationAndroid($id, $type, $notificationToken, $messageString)
    {
        $url = 'https://fcm.googleapis.com/fcm/send';
        $fields = array(
            'registration_ids' => $notificationToken,
            'data' => array(
                'id' => $id,
                'title' => Yii::$app->name,
                'type' => $type, //emergency, post
                'message' => $messageString
            ),
        );

        $headers = array(
            'Authorization:key=' . Yii::$app->fcm->apiKey,
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        //curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        $result = curl_exec($ch);
        if ($result === false)
            //die('Curl failed ' . curl_error());
            die('Curl failed.');

        curl_close($ch);
        //p($result);
        return $result;
    }


    /**
     * Gets query for [[Owner]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOwner0()
    {

        $data = User::find()->where(['id' => $this->owner_id])->one();
        if ($data instanceof User) {
            $profilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
            if (!empty($data->profile_picture) && file_exists(Yii::getAlias('@profilePictureThumbRelativePath') . '/' . $data->profile_picture)) {
                $profilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@profilePictureThumbAbsolutePath') . '/' . $data->profile_picture;
            }
            $data->profile_picture = $profilePicture;
        }
        return $data;
    }

    /**
     * Gets query for [[NotificationReceiver]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNotificationReceiver0()
    {

        $data = User::find()->where(['id' => $this->notification_receiver_id])->one();
        if ($data instanceof User) {
            $profilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
            if (!empty($data->profile_picture) && file_exists(Yii::getAlias('@profilePictureThumbRelativePath') . '/' . $data->profile_picture)) {
                $profilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@profilePictureThumbAbsolutePath') . '/' . $data->profile_picture;
            }
            $data->profile_picture = $profilePicture;
        }
        return $data;
    }
}
