<?php
namespace dvizh\cart\models;

use dvizh\cart\events\CartElement as CartElementEvent;
use yii;

class CartElement extends \yii\db\ActiveRecord implements \dvizh\dic\interfaces\entity\CartElement
{
    private $cartService;

    public function init()
    {
        $this->cartService = yii::createObject('\dvizh\cart\services\Cart');

        parent::init();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getItemId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function getCount()
    {
        return $this->count;
    }

    public function getModelName()
    {
        return $this->model;
    }

    public function getOptions(): array
    {
        if ($this->options) {
            return json_encode($this->options);
        }

        return [];
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getHash()
    {
        return $this->hash;
    }

    public function setCart(\dvizh\dic\interfaces\entity\Cart $cart)
    {
        $this->link('cart', $cart);
    }

    public function setModelName($modelName)
    {
        $this->model = $modelName;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setItemId($itemId)
    {
        $this->item_id = $itemId;
    }

    public function setCount($count)
    {
        $this->count = $count;
    }

    public function setPrice($price)
    {
        $this->price = $price;
    }

    public function setOptions($options)
    {
        if ($options) {
            $this->options = json_encode($options);
        }
    }

    public function setHash($hash)
    {
        $this->hash = $hash;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function saveData()
    {
        return $this->save();
    }

    public static function tableName()
    {
        return '{{%cart_element}}';
    }

    public function getBaseCost()
    {
        return $this->getPrice();
    }

    public function getCost()
    {
        $cost = 0;
        $costProduct = $this->getPrice();

        $cart = $this->cartService;
        
        for($i = 0; $i < $this->count; $i++) {
            $currentCostProduct = $costProduct;

            $elementEvent = new CartElementEvent(['element' => $this, 'cost' => $currentCostProduct]);
            $cart->trigger($cart::EVENT_ELEMENT_COST_CALCULATE, $elementEvent);
            $currentCostProduct = $elementEvent->cost;

            $cost = $cost+$currentCostProduct;
        }
        

        $elementEvent = new CartElementEvent(['element' => $this, 'cost' => $cost]);
        $cart->trigger($cart::EVENT_ELEMENT_COST, $elementEvent);

        return $elementEvent->cost;
    }

    public function getCart()
    {
        return $this->hasOne(Cart::className(), ['id' => 'cart_id']);
    }

    public function rules()
    {
        return [
            [['cart_id', 'model', 'item_id'], 'required'],
            [['model'], 'validateModel'],
            [['hash', 'options'], 'string'],
            [['price'], 'double'],
            [['item_id', 'count', 'parent_id'], 'integer'],
        ];
    }

    public function validateModel($attribute, $param)
    {
        $model = $this->model;
        if (class_exists($model)) {
            $elementModel = new $model();
            if (!$elementModel instanceof \dvizh\dic\interfaces\entity\SoldGoods) {
                $this->addError($attribute, 'Model implement error');
            }
        } else {
            $this->addError($attribute, 'Model not exists');
        }
    }

    public function attributeLabels()
    {
        return [
            'id' => yii::t('cart', 'ID'),
            'parent_id' => yii::t('cart', 'Parent element'),
            'price' => yii::t('cart', 'Price'),
            'hash' => yii::t('cart', 'Hash'),
            'model' => yii::t('cart', 'Model name'),
            'cart_id' => yii::t('cart', 'Cart ID'),
            'item_id' => yii::t('cart', 'Item ID'),
            'count' => yii::t('cart', 'Count'),
            'options' => yii::t('cart', 'Options')
        ];
    }

    public function getProduct() : \dvizh\dic\interfaces\entity\SoldGoods
    {
        $modelStr = $this->model;
        $productModel = new $modelStr();

        return $this->hasOne($productModel::className(), ['id' => 'item_id'])->one();
    }
}
