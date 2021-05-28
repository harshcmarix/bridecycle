<?php

namespace app\modules\admin\models;

use Yii;
use yii\base\Model;

/**
 * Class ForgotPasswordForm
 * @package app\modules\admin\models
 */
class ForgotPasswordForm extends Model
{
    /**
     * Used for forgot password
     * @var string
     */
    public $email;

    /**
     * @return array
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
                'message' => 'User does not exist.'
            ],
        ];
    }

    /**
     * @return bool
     * @throws \yii\base\Exception
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
            ->setFrom([Yii::$app->params['support_email'] => Yii::$app->name . ' robot'])
            ->setTo($this->email)
            ->setSubject('Password Reset For ' . Yii::$app->name)
            ->send();
    }
}
