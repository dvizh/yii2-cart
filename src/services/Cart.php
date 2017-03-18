<?php
namespace dvizh\cart\services;

use dvizh\cart\events\Cart as CartEvent;
use dvizh\cart\events\CartElement as CartElementEvent;
use dvizh\cart\events\CartGroupModels;
use yii;

class Cart extends \yii\base\Component implements \dvizh\dic\interfaces\services\Cart
{
    const EVENT_CART_INIT = 'cart_init';
    const EVENT_CART_TRUNCATE = 'cart_truncate';
    const EVENT_CART_COST = 'cart_cost';
    const EVENT_CART_COUNT = 'cart_count';
    const EVENT_CART_PUT = 'cart_put';
    const EVENT_CART_COST_ROUNDING = 'cart_cost_rounding';
    const EVENT_MODELS_ROUNDING = 'cart_models_rounding';
    const EVENT_ELEMENT_COST = 'element_cost';
    const EVENT_ELEMENT_COST_ROUNDING = 'element_cost_rounding';

    public $currency = NULL;
    public $currencyPosition = 'after';
    public $priceFormat = [2, '.', ''];

    public function init()
    {
        $this->trigger(self::EVENT_CART_INIT, new CartEvent(['cart' => $this->getByCurrentUser()]));
        
        return $this;
    }

    public function getByCurrentUser() : \dvizh\dic\interfaces\entity\Cart
    {
        return \dvizh\cart\models\Cart::my();
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function getCurrencyPosition()
    {
        return $this->currencyPosition;
    }

    public function getPriceFormat() : array
    {
        return $this->priceFormat;
    }

    public function putElement(\dvizh\dic\interfaces\entity\SoldGoods $product, $count = 1, $price = null, $options = [])
    {
        $elementModel = new \dvizh\cart\models\CartElement;
        $elementModel->setCount((int)$count);
        $elementModel->setPrice($product->getPrice());
        $elementModel->setItemId($product->getItemId());
        $elementModel->setModelName($product->getModelName());
        $elementModel->setOptions($product->getOptions());
        $elementModel->setHash(self::_generateHash($product::className(), $price, $options));
        $elementModel->setCart($this->getByCurrentUser());

        if ($elementModel->saveData()) {
            $elementEvent = new CartElementEvent(['element' => $elementModel]);
            $this->trigger(self::EVENT_CART_PUT, $elementEvent);

            return $elementModel;
        } else {
            throw new \Exception('Data error');
        }
    }

    public function hasElement(\dvizh\dic\interfaces\entity\SoldGoods $product, $price, $options = [])
    {
        return $this->getElements()->where(['hash' => self::_generateHash($product::className(), $price, $options), 'item_id' => $product->getItemId()])->one();
    }

    public function getElements()
    {
        return $this->getByCurrentUser()->elements;
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
        $count = 0;

        foreach($this->elements as $element) {
            $count += $element->count;
        }

        $cartEvent = new CartEvent(['cart' => $this->getByCurrentUser(), 'count' => $count]);
        $this->trigger(self::EVENT_CART_COUNT, $cartEvent);
        $count = $cartEvent->count;

        return $count;
    }

    public function getBaseCost()
    {
        $cost = 0;
        foreach($this->elements as $element) {
            $cost += $element->getBaseCost();
        }

        return $cost;
    }

    public function getCost()
    {
        $elements = $this->getByCurrentUser()->elements;
        $pricesByModels = [];

        foreach($elements as $element) {
            $price = $element->getCost();
            
            $elementEvent = new CartElementEvent(['element' => $element, 'cost' => $price]);
            
            $cart->trigger(self::EVENT_ELEMENT_COST_ROUNDING, $elementEvent);
            $cart->trigger(self::EVENT_ELEMENT_COST, $elementEvent);

            if (!isset($pricesByModels[$element->model])) {
                $pricesByModels[$element->model] = 0;
            }
            
            $pricesByModels[$element->model] += $elementEvent->cost;
        }

        $cost = 0;
        foreach($pricesByModels as $model => $price) {
            $cartGroupModels = new CartGroupModels(['cart' => $this->getByCurrentUser(), 'cost' => $price, 'model' => $model]);
            $this->trigger(self::EVENT_MODELS_ROUNDING, $cartGroupModels);
            $cost += $cartGroupModels->cost;
        }

        $cartEvent = new CartEvent(['cart' => $this->getByCurrentUser(), 'cost' => $cost]);

        $this->trigger(self::EVENT_CART_COST, $cartEvent);
        $this->trigger(self::EVENT_CART_COST_ROUNDING, $cartEvent);

        return $cartEvent->cost;
    }

    public function getCostFormatted()
    {
        $price = number_format($this->getCost(), $this->priceFormat[0], $this->priceFormat[1], $this->priceFormat[2]);
        if ($this->currencyPosition == 'after') {
            return "<span>$price</span>{$this->currency}";
        } else {
            return "<span>{$this->currency}</span>$price";
        }
    }

    public function truncate()
    {
        $this->trigger(self::EVENT_CART_TRUNCATE, new CartEvent(['cart' => $this->getByCurrentUser()]));
        $truncate = $this->getByCurrentUser()->truncate();
        $this->update();

        return $truncate;
    }

    public function updateNow()
    {
        $cart = $this->getByCurrentUser();
        $cart->setUpdateTime(time());

        return $cart->saveData();
    }

    private static function _generateHash($modelName, $price, $options = [])
    {
        return md5($modelName.$price.serialize($options));
    }
}
