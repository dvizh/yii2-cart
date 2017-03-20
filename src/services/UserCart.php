<?php
namespace dvizh\cart\services;
use dvizh\app\interfaces\services\singletons\User;
use dvizh\app\interfaces\services\Cart;
use dvizh\app\interfaces\entities\CartElement;

use yii;

class UserCart extends \yii\base\Component implements \dvizh\app\interfaces\services\singletons\UserCart
{
    protected $cost, $user, $cart, $element;

    public function __construct(User $user, Cart $cart, CartElement $element, $config = [])
    {
        $this->user = $user;
        $this->cart = $cart;
        $this->element = $element;

        parent::__construct($config);
    }

    public function getCartEntity() : \dvizh\app\interfaces\entities\Cart
    {
        return $this->cart->getUserCartEntity($this->user);
    }

    public function putElement(\dvizh\app\interfaces\entities\Goods $product, int $count = 1, $price = null, $options = []) : int
    {
        if(!$price) {
            $price = $product->getPrice();
        }

        if($elementModel = $this->hasElement($product, $price, $options)) {
            $elementModel->setCount(($elementModel->count+$count));
        } else {
            $element = $this->element;

            $elementModel = new $element;
            $elementModel->setCount((int)$count);
            $elementModel->setPrice($price);
            $elementModel->setItemId($product->getItemId());
            $elementModel->setModelName($product->getModelName());
            $elementModel->setOptions($product->getOptions());
            $elementModel->setHash(self::_generateHash($product::className(), $price, $options));
            $elementModel->setCart($this->getCartEntity());
        }

        if ($elementModel->saveData()) {
            return $elementModel->id;
        } else {
            throw new \Exception('Data error');
        }
    }

    public function hasElement(\dvizh\app\interfaces\entities\Goods $product, $price, $options = []) : ?\dvizh\app\interfaces\entities\CartElement
    {
        return $this->getElementsRelation()->where(['hash' => self::_generateHash($product::className(), $price, $options), 'item_id' => $product->getItemId()])->one();
    }

    public function getElements() : array
    {
        return $this->getCartEntity()->elements;
    }

    public function getElementsRelation()
    {
        return $this->getCartEntity()->getElements();
    }

    public function getElementsByModel($model)
    {
        return $this->getElementsRelation()->andWhere(['model' => get_class($model), 'item_id' => $model->getId()])->all();
    }

    public function getElementById($id)
    {
        return $this->getElementsRelation()->andWhere(['id' => $id])->one();
    }

    public function getCount() : int
    {
        $count = 0;
        foreach($this->elements as $element) {
            $count += $element->count;
        }

        return $count;
    }

    public function setCost(int $cost)
    {
        $this->cost = $cost;
    }

    public function getCost() : int
    {
        if($this->cost) {
            return $this->cost;
        }

        $cost = 0;
        foreach($this->elements as $element) {
            $cost += ($element->getCost()*$element->getCount());
        }

        return $cost;
    }

    public function truncate() : bool
    {
        $this->updateNow();

        return $this->getCartEntity()->truncate();
    }

    public function updateNow()
    {
        $cart = $this->getCartEntity();
        $cart->setUpdateTime(time());
        
        return $cart->saveData();
    }

    private static function _generateHash($modelName, $price, $options = [])
    {
        return md5($modelName.$price.serialize($options));
    }
}