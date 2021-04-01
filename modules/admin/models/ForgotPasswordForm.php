<?php
 
namespace app\modules\admin\models;

use Yii;
use yii\base\Model;
use app\modules\admin\models\User;

/**
 * Password reset request form
 */
class ForgotPasswordForm extends Model
{
    public $email;
 
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'exist',
                'targetClass' => 'app\modules\admin\models\User',
                // 'filter' => ['status' => User::STATUS_ACTIVE],
                'filter' => ['user_type' => User::USER_TYPE_ADMIN],
                'message' => 'There is no user with such email.'
            ],
        ];
    }
 
    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return bool whether the email was send
     */
    public function sendEmail()
    {
        /* @var $user User */
        $user = User::findOne([
            'user_type' => User::USER_TYPE_ADMIN,
            'email' => $this->email,
        ]);
 
        if (!$user) {
            return false;
        }
 
        if (!User::isPasswordResetTokenValid($user->password_reset_token)) {
            $user->generatePasswordResetToken();
            if (!$user->save()) {
                return false;
            }
        }
 
        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'admin/passwordResetToken-html', 'text' => 'admin/passwordResetToken-text'],
                ['user' => $user]
            )
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
            ->setTo($this->email)
            ->setSubject('Password reset for ' . Yii::$app->name)
            ->send();
    }
}
