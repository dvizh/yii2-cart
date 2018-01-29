<?php
namespace dvizh\cart\models;

use dvizh\cart\interfaces\Cart as CartInterface;
use yii;

class Cart extends \yii\db\ActiveRecord implements CartInterface
{
    private $element = null;
    
    public function init()
    {
        $this->element = yii::$container->get('cartElement');
    }
    
    public function my()
    {
        $query = new tools\CartQuery(get_called_class());
        return $query->my();
    }
    
    public function put(\dvizh\cart\interfaces\Element $elementModel)
    {
        $elementModel->hash = self::_generateHash($elementModel->model, $elementModel->price, $elementModel->getOptions());

        $elementModel->link('cart', $this->my());

        if ($elementModel->validate() && $elementModel->save()) {
            return $elementModel;
        } else {
            throw new \Exception(current($elementModel->getFirstErrors()));
        }
    }
    
    public function getElements()
    {
        return $this->hasMany($this->element, ['cart_id' => 'id']);
    }
    
    public function getElement(\dvizh\cart\interfaces\CartElement $model, $options = [], $price = null)
    {
        $price = empty($price) ? $model->getCartPrice() : $price;
        return $this->getElements()->where(['hash' => $this->_generateHash(get_class($model), $price, $options), 'item_id' => $model->getCartId()])->one();
    }
    
    public function getElementsByModel(\dvizh\cart\interfaces\CartElement $model)
    {
        return $this->getElements()->andWhere(['model' => get_class($model), 'item_id' => $model->getCartId()])->all();
    }
    
    public function getElementById($id)
    {
        return $this->getElements()->andWhere(['id' => $id])->one();
    }
    
    public function getCount()
    {
        return intval($this->getElements()->sum('count'));
    }
    
    public function getCost()
    {
        return $cost = $this->getElements()->sum('price*count');
    }
    
    public function truncate()
    {
        foreach($this->elements as $element) {
            $element->delete();
        }
        
        return $this;
    }

    public function rules()
    {
        return [
            [['created_time', 'user_id'], 'required', 'on' => 'create'],
            [['tmp_user_id'], 'string'],
            [['updated_time', 'created_time'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => yii::t('cart', 'ID'),
            'user_id' => yii::t('cart', 'User ID'),
            'tmp_user_id' => yii::t('cart', 'Tmp user ID'),
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
    
    private static function _generateHash($modelName, $price, $options = [])
    {
        return md5($modelName.$price.serialize($options));
    }
}
