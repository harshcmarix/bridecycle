<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use app\modules\api\v2\models\User;
use Yii;

/**
 * This is the model class for table "cart_items".
 *
 * @property int $id
 * @property int $user_id
 * @property int $product_id
 * @property string|null $product_name
 * @property string|null $category_name
 * @property string|null $subcategory_name
 * @property int|null $seller_id
 * @property int $quantity
 * @property float $price
 * @property float|null $tax
 * @property float $shipping_cost
 * @property string|null $color
 * @property int|null $size_id
 * @property string|null $size
 * @property int|null $is_checkout
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property User $user
 * @property Product $product
 */
class CartItem extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cart_items';
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

    const IS_CHECKOUT_YES = 1;
    const IS_CHECKOUT_NO = 0;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [

            [['user_id'], 'required', 'message' => getValidationErrorMsg('user_id_required', Yii::$app->language)],
            [['product_id'], 'required', 'message' => getValidationErrorMsg('product_id_required', Yii::$app->language)],
            [['quantity'], 'required', 'message' => getValidationErrorMsg('quantity_required', Yii::$app->language)],
            [['shipping_cost'], 'required', 'message' => getValidationErrorMsg('shipping_cost_required', Yii::$app->language)],
            [['color'], 'required', 'message' => getValidationErrorMsg('color_required', Yii::$app->language)],
            //[['size_id'], 'required', 'message' => getValidationErrorMsg('size_id_required', Yii::$app->language)],

            [['user_id'], 'integer', 'message' => getValidationErrorMsg('user_id_integer_validation', Yii::$app->language)],
            [['product_id'], 'integer', 'message' => getValidationErrorMsg('product_id_integer_validation', Yii::$app->language)],
            [['quantity'], 'integer', 'message' => getValidationErrorMsg('quantity_integer_validation', Yii::$app->language)],
            [['seller_id'], 'integer', 'message' => getValidationErrorMsg('seller_id_integer_validation', Yii::$app->language)],
            [['size_id'], 'integer', 'message' => getValidationErrorMsg('size_id_integer_validation', Yii::$app->language)],

            [['shipping_cost'], 'number', 'message' => getValidationErrorMsg('shipping_cost_number_validation', Yii::$app->language)],
            [['price'], 'number', 'message' => getValidationErrorMsg('price_number_validation', Yii::$app->language)],
            [['tax'], 'number', 'message' => getValidationErrorMsg('tax_number_validation', Yii::$app->language)],

            [['size', 'is_checkout', 'created_at', 'updated_at'], 'safe'],
            [['product_name', 'category_name', 'subcategory_name'], 'safe'],
            [['color'], 'string', 'max' => 100],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['seller_id'], 'exist', 'skipOnEmpty' => true, 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['seller_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['color'], 'exist', 'skipOnError' => true, 'targetClass' => Color::class, 'targetAttribute' => ['color' => 'id']],
            [['size_id'], 'exist', 'skipOnEmpty' => true, 'skipOnError' => true, 'targetClass' => Sizes::class, 'targetAttribute' => ['size_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'product_id' => 'Product ID',
            'quantity' => 'Quantity',
            'price' => 'Price',
            'color' => 'Color',
            'size' => 'Size',
            'size_id' => 'Size',
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
            'user0' => 'user0',
            'color0' => 'color0',
            'product0' => 'product',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
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
     * Gets query for [[Color]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getColor()
    {
        return $this->hasOne(Color::class, ['id' => 'color']);

    }

    //////////only for api/////////

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser0()
    {
        $userDetails = User::find()->where(['id' => $this->user_id])->one();
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
     * Gets query for [[Color]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getColor0()
    {
        $color = Color::findOne($this->color);
        if (!empty($color) && $color instanceof Color) {
            $colorName = "";
            if (\Yii::$app->language == 'en-US' || \Yii::$app->language == 'english') {
                if (!empty($color->name)) {
                    $colorName = $color->name;
                } elseif (empty($color->name) && !empty($color->german_name)) {
                    $colorName = $color->german_name;
                }
            }

            if (\Yii::$app->language == 'de-DE' || \Yii::$app->language == 'german') {
                if (!empty($color->german_name)) {
                    $colorName = $color->german_name;
                } elseif (empty($color->german_name) && !empty($color->name)) {
                    $colorName = $color->name;
                }
            }
            $color->name = $colorName;
        }

        return $color;

    }

    public function getProduct0()
    {
        $model = Product::find()->where(['id' => $this->product_id])->one();
        if (!empty($model) && $model instanceof Product) {
            $model->price = $model->getReferPrice();
        }
        return $model;
    }
}
