<?php

namespace app\models;

use yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $surname
 * @property string $name
 * @property string $password write-only password
 * @property string $salt
 * @property string $access_token
 * @property string $create_date
 *
 */

class User extends ActiveRecord implements IdentityInterface
{


    public static function tableName()
    {
        return 'prm_user';
    }

    public function rules()
    {
        return [
            [['username', 'name', 'surname', 'password'], 'required'],
            ['username', 'email'],
            ['username','unique'],
        ];
    }

    public function attributeLabels(){
        return [
            'id' => _('ID'),
            'name' => _('Имя'),
            'surname' => _('Фамилия'),
            'online' => _('Онлайн'),
            'password' => _('Пароль'),
            'salt' => _('Соль'),
            'access_token' => _('Ключ авторизации'),
        ];
    }

    public function beforeSave($insert){
        if(parent::beforeSave($insert)){
            if($this->getIsNewRecord() && !empty($this->password))
            {
                $this->salt = $this->saltGenerator();
            }
            if(!empty($this->password))
            {
                $this->password = $this->passWithSalt($this->password, $this->salt);
            }
            else
            {
                unset($this->password);
            }
            return true;
        }
        else
        {
            return false;
        }
    }

    public function saltGenerator(){
        return hash("sha512", uniqid('salt_', true));
    }

    public function passWithSalt($password, $salt)
    {
        return hash("sha512", $password, $salt);
    }

    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    public static function findByUsername($username)
    {
        return static::findOne(['username'=>$username]);
    }

    public function getId(){
        return $this->getPrimaryKey()["id"];
    }

    public function getAuthKey()
    {
        return $this->access_token;
    }

    public function validateAuthKey($authkey)
    {
        return $this->getAuthKey()===$authkey;
    }

    public function validatePassword($password)
    {
        return $this->password === $this->passWithSalt($password, $this->salt);
    }
}
