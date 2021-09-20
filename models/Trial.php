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
            //[['product_id', 'sender_id', 'receiver_id', 'status', 'date', 'time'], 'required'],
            [['product_id', 'name', 'date', 'time'], 'required'],
            [['status'], 'required', 'on' => self::SCENARIO_ACCEPT_REJECT],
            [['product_id', 'sender_id', 'receiver_id', 'status'], 'integer'],
            [['date', 'time', 'created_at', 'updated_at'], 'safe'],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
            [['sender_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['sender_id' => 'id']],
            [['receiver_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['receiver_id' => 'id']],
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
            //'color' => 'color',
            'subCategory0' => 'subCategory0',
            'sender0' => 'sender0',
            'receiver0' => 'receiver0',
            //'rating' => 'rating',
        ];
    }

    /**
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }

    /**
     * Gets query for [[Sender]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSender()
    {
        return $this->hasOne(User::className(), ['id' => 'sender_id']);
    }

    /**
     * Gets query for [[Receiver]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReceiver()
    {
        return $this->hasOne(User::className(), ['id' => 'receiver_id']);
    }

    ///////////////////////For api use only /////////////////////////////////////////////

    /**
     * @return User|array|mixed|\yii\db\ActiveRecord|null
     */
    public function getSender0()
    {
        //return $this->hasOne(User::className(), ['id' => 'user_id']);
        $data = User::find()->where(['id' => $this->sender_id])->one();
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
     * @return User|array|mixed|\yii\db\ActiveRecord|null
     */
    public function getReceiver0()
    {
        //return $this->hasOne(User::className(), ['id' => 'user_id']);
        $data = User::find()->where(['id' => $this->receiver_id])->one();
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
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduct0()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }

    /**
     * Gets query for [[ProductImages]] with path for api.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductImages0()
    {
        // return $this->hasMany(ProductImage::className(), ['product_id' => 'id']);
        $productImages = ProductImage::find()->where(['product_id' => $this->product_id])->all();
        if (!empty($productImages)) {
            foreach ($productImages as $key => $value) {
                if ($value instanceof ProductImage) {
                    $product_images = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                    if (!empty($value->name) && file_exists(Yii::getAlias('@productImageThumbRelativePath') . "/" . $value->name)) {
                        $product_images = Yii::$app->request->getHostInfo() . Yii::getAlias('@productImageThumbAbsolutePath') . '/' . $value->name;
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
            if (!empty($productCategory->image) && file_exists(Yii::getAlias('@productCategoryImageThumbRelativePath') . '/' . $productCategory->image)) {
                $categoryImage = Yii::$app->request->getHostInfo() . Yii::getAlias('@productCategoryImageThumbAbsolutePath') . '/' . $productCategory->image;
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
        // return $this->hasOne(ProductCategory::className(), ['id' => 'sub_category_id']);
        $productSubCategory = ProductCategory::find()->where(['id' => $this->product->sub_category_id])->one();
        if ($productSubCategory instanceof ProductCategory) {
            $subCategoryImage = Yii::$app->request->getHostInfo() . Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
            if (!empty($productSubCategory->image) && file_exists(Yii::getAlias('@productCategoryImageThumbRelativePath') . '/' . $productSubCategory->image)) {
                $subCategoryImage = Yii::$app->request->getHostInfo() . Yii::getAlias('@productCategoryImageThumbAbsolutePath') . '/' . $productSubCategory->image;
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
            if (!empty($brand->image) && file_exists(Yii::getAlias('@brandImageThumbRelativePath') . '/' . $brand->image)) {
                $brandImage = Yii::$app->request->getHostInfo() . Yii::getAlias('@brandImageThumbAbsolutePath') . '/' . $brand->image;
            }
            $brand->image = $brandImage;
        }
        return $brand;
    }
}
