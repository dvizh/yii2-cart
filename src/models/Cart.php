<?php
namespace dvizh\cart\models;

use yii;

class Cart extends \yii\db\ActiveRecord implements \dvizh\dic\interfaces\cart\Cart
{
    public function my()
    {
        $query = new tools\CartQuery(get_called_class());

        return $query->my();
    }

    public function put(\dvizh\dic\interfaces\cart\CartElement $elementModel)
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
        return $this->my()->hasMany(CartElement::className(), ['cart_id' => 'id']);
    }
    
    public function hasElement(\dvizh\dic\interfaces\cart\CartElement $model, $options = [])
    {
        return $this->getElements()->where(['hash' => $this->_generateHash(get_class($model), $model->getPrice(), $options), 'item_id' => $model->getId()])->one();
    }
    
    public function getElementsByModel($model)
    {
        return $this->getElements()->andWhere(['model' => get_class($model), 'item_id' => $model->getId()])->all();
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
    
    private static function _generateHash($modelName, $price, $options = [])
    {
        return md5($modelName.$price.serialize($options));
    }
}
