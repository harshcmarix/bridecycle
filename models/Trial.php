<?php

namespace app\models;

use app\modules\api\v2\models\User;
use Yii;

/**
 * This is the model class for table "trials".
 *
 * @property int $id
 * @property int $product_id
 * @property int $sender_id
 * @property int $receiver_id
 * @property int $status '0'=>'pending','1'=>'accept','2'=>'reject'
 * @property string $date
 * @property string $name
 * @property string $time
 * @property int $timezone_id
 * @property string $timezone_utc_time
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property Product $product
 * @property User $sender
 * @property User $receiver
 */
class Trial extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'trials';
    }

    /**
     * Constants
     */
    const STATUS_PENDING = '1';
    const STATUS_ACCEPT = '2';
    const STATUS_REJECT = '3';

    public $arrTrialStatus = [
        self::STATUS_PENDING => 'pending',
        self::STATUS_ACCEPT => 'accept',
        self::STATUS_REJECT => 'reject'
    ];

    const SCENARIO_ACCEPT_REJECT = 'accept_reject_request';

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id'], 'required', 'message' => getValidationErrorMsg('product_id_required', Yii::$app->language)],
            [['name'], 'required', 'message' => getValidationErrorMsg('name_required', Yii::$app->language)],
            [['date'], 'required', 'message' => getValidationErrorMsg('date_required', Yii::$app->language)],
            [['time'], 'required', 'message' => getValidationErrorMsg('time_required', Yii::$app->language)],
            [['timezone_id'], 'required', 'message' => getValidationErrorMsg('timezone_id_required', Yii::$app->language)],

            [['status'], 'required', 'on' => self::SCENARIO_ACCEPT_REJECT, 'message' => getValidationErrorMsg('status_required', Yii::$app->language)],

            [['product_id'], 'integer', 'message' => getValidationErrorMsg('product_id_integer_validation', Yii::$app->language)],
            [['sender_id'], 'integer', 'message' => getValidationErrorMsg('sender_id_integer_validation', Yii::$app->language)],
            [['receiver_id'], 'integer', 'message' => getValidationErrorMsg('receiver_id_integer_validation', Yii::$app->language)],
            [['status'], 'integer', 'message' => getValidationErrorMsg('status_id_integer_validation', Yii::$app->language)],
            [['timezone_id'], 'integer', 'message' => getValidationErrorMsg('timezone_id_integer_validation', Yii::$app->language)],

            [['date', 'time', 'created_at', 'updated_at', 'timezone_utc_time'], 'safe'],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['sender_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['sender_id' => 'id']],
            [['receiver_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['receiver_id' => 'id']],
            [['timezone_id'], 'exist', 'skipOnEmpty' => true, 'skipOnError' => true, 'targetClass' => Timezone::class, 'targetAttribute' => ['timezone_id' => 'id']],
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
            'sender_id' => 'Sender ID',
            'receiver_id' => 'Receiver ID',
            'status' => 'Status',
            'date' => 'Date',
            'time' => 'Time',
            'timezone_id' => 'Timezone',
            'timezone_utc_time' => 'UTC Time',
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
            'productImages0' => 'productImages0',
            'category0' => 'category0',
            'brand0' => 'brand0',
            'subCategory0' => 'subCategory0',
            'sender0' => 'sender0',
            'receiver0' => 'receiver0',
            'timezone0' => 'timezone0',
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
     * Gets query for [[Sender]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSender()
    {
        return $this->hasOne(User::class, ['id' => 'sender_id']);
    }

    /**
     * Gets query for [[Receiver]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReceiver()
    {
        return $this->hasOne(User::class, ['id' => 'receiver_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTimezone()
    {
        return $this->hasOne(Timezone::class, ['id' => 'timezone_id']);
    }

    ///////////////////////For api use only /////////////////////////////////////////////

    /**
     * @return User|array|mixed|\yii\db\ActiveRecord|null
     */
    public function getSender0()
    {
        $data = User::find()->where(['id' => $this->sender_id])->one();
        if ($data instanceof User) {
            $profilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
            if (!empty($data->profile_picture) && file_exists(Yii::getAlias('@profilePictureRelativePath') . '/' . $data->profile_picture)) {
                $profilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@profilePictureAbsolutePath') . '/' . $data->profile_picture;
            }
            $data->profile_picture = $profilePicture;
        }
        return $data;
    }

    /**
     * @return User|array|mixed|\yii\db\ActiveRecord|null
     */
    public function getReceiver0()
    {
        $data = User::find()->where(['id' => $this->receiver_id])->one();
        if ($data instanceof User) {
            $profilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
            if (!empty($data->profile_picture) && file_exists(Yii::getAlias('@profilePictureRelativePath') . '/' . $data->profile_picture)) {
                $profilePicture = Yii::$app->request->getHostInfo() . Yii::getAlias('@profilePictureAbsolutePath') . '/' . $data->profile_picture;
            }
            $data->profile_picture = $profilePicture;
        }
        return $data;
    }

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
     * Gets query for [[ProductImages]] with path for api.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductImages0()
    {
        $productImages = ProductImage::find()->where(['product_id' => $this->product_id])->all();
        if (!empty($productImages)) {
            foreach ($productImages as $key => $value) {
                if ($value instanceof ProductImage) {
                    $product_images = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                    if (!empty($value->name) && file_exists(Yii::getAlias('@productImageRelativePath') . "/" . $value->name)) {
                        $product_images = Yii::$app->request->getHostInfo() . Yii::getAlias('@productImageAbsolutePath') . '/' . $value->name;
                    }
                    $value->name = $product_images;
                }
            }
        }
        return $productImages;
    }

    /**
     * Gets query for [[Category]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategory0()
    {
        $productCategory = ProductCategory::find()->where(['id' => $this->product->category_id])->one();
        if ($productCategory instanceof ProductCategory) {
            $categoryImage = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
            if (!empty($productCategory->image) && file_exists(Yii::getAlias('@productCategoryImageRelativePath') . '/' . $productCategory->image)) {
                $categoryImage = Yii::$app->request->getHostInfo() . Yii::getAlias('@productCategoryImageAbsolutePath') . '/' . $productCategory->image;
            }
            $productCategory->image = $categoryImage;
        }

        return $productCategory;
    }

    /**
     * Gets query for [[SubCategory]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubCategory0()
    {
        $productSubCategory = ProductCategory::find()->where(['id' => $this->product->sub_category_id])->one();
        if ($productSubCategory instanceof ProductCategory) {
            $subCategoryImage = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
            if (!empty($productSubCategory->image) && file_exists(Yii::getAlias('@productCategoryImageRelativePath') . '/' . $productSubCategory->image)) {
                $subCategoryImage = Yii::$app->request->getHostInfo() . Yii::getAlias('@productCategoryImageAbsolutePath') . '/' . $productSubCategory->image;
            }
            $productSubCategory->image = $subCategoryImage;
        }
        return $productSubCategory;
    }

    /**
     * Gets query for [[Brand]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBrand0()
    {
        $brand = Brand::find()->where(['id' => $this->product->brand_id])->one();
        if ($brand instanceof Brand) {
            $brandImage = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
            if (!empty($brand->image) && file_exists(Yii::getAlias('@brandImageRelativePath') . '/' . $brand->image)) {
                $brandImage = Yii::$app->request->getHostInfo() . Yii::getAlias('@brandImageAbsolutePath') . '/' . $brand->image;
            }
            $brand->image = $brandImage;

            $brandName = "";
            if (\Yii::$app->language == 'en-US' || \Yii::$app->language == 'english') {
                if (!empty($brand->name)) {
                    $brandName = $brand->name;
                } elseif (empty($brand->name) && !empty($brand->german_name)) {
                    $brandName = $brand->german_name;
                }
            }

            if (\Yii::$app->language == 'de-DE' || \Yii::$app->language == 'german') {
                if (!empty($brand->german_name)) {
                    $brandName = $brand->german_name;
                } elseif (empty($brand->german_name) && !empty($brand->name)) {
                    $brandName = $brand->name;
                }
            }
            $brand->name = $brandName;
        }
        return $brand;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTimezone0()
    {
        return $this->hasOne(Timezone::class, ['id' => 'timezone_id']);
    }
    
}
