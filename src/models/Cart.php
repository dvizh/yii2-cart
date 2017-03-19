<?php
namespace dvizh\cart\models;

use yii;

class Cart extends \yii\db\ActiveRecord implements \dvizh\app\interfaces\entities\Cart
{
    public function getElements()
    {
        return $this->hasMany(CartElement::className(), ['cart_id' => 'id']);
    }

    public function getId()
    {
        return $this->id;
    }

    public function setUpdateTime($time)
    {
        $this->updated_time = $time;
    }

    public function saveData()
    {
        return $this->save();
    }

    public static function my()
    {
        $query = new tools\CartQuery(get_called_class());

        return $query->my();
    }

    public function rules()
    {
        return [
            [['created_time', 'user_id'], 'required', 'on' => 'create'],
            [['updated_time', 'created_time'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => yii::t('cart', 'ID'),
            'user_id' => yii::t('cart', 'User ID'),
            'created_time' => yii::t('cart', 'Created Time'),
            'updated_time' => yii::t('cart', 'Updated Time'),
        ];
    }
    
    public static function tableName()
    {
        return '{{%cart}}';
    }
    
    public function beforeDelete()
    {
        foreach ($this->elements as $elem) {
            $elem->delete();
        }
        
        return true;
    }

    public function truncate()
    {
        foreach ($this->elements as $elem) {
            $elem->delete();
        }

        return true;
    }
}
