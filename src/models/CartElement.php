<?php
namespace dvizh\cart\models;

use dvizh\cart\models\Cart;
use dvizh\cart\events\CartElement as CartElementEvent;
use dvizh\cart\events\Cart as CartEvent;
use dvizh\cart\interfaces\Element;
use yii;

class CartElement extends \yii\db\ActiveRecord implements Element
{
    const EVENT_ELEMENT_UPDATE = 'element_count';
    const EVENT_ELEMENT_DELETE = 'element_delete';

    public function getId()
    {
        return $this->id;
    }

    public function getCount()
    {
        return $this->count;
    }

    public function getName()
    {
        return $this->getModel()->getCartName();
    }

    public function getItemId()
    {
        return $this->item_id;
    }

    public function getComment()
    {
        return $this->comment;
    }

    public function getModel($withCartElementModel = true)
    {
        if(!$withCartElementModel) {
            return $this->model;
        }

        $model = '\\'.$this->model;
        if(is_string($this->model) && class_exists($this->model)) {
            $productModel = new $model();
            if ($productModel = $productModel::findOne($this->item_id)) {
                $model = $productModel;
            } else {
                yii::$app->cart->truncate();
                throw new \yii\base\Exception('Element model not found');
            }
        } else {
            throw new \yii\base\Exception('Unknow element model');
        }

        return $model;
    }

    public function getModelName()
    {
        return $this->model;
    }

    public function getOptions()
    {
        if(empty($this->options)) {
            return [];
        }

        return json_decode($this->options, true);
    }

    public function setItemId($itemId)
    {
        $this->item_id = $itemId;
    }

    public function setCount($count, $andSave = false)
    {
        $this->count = $count;

        if($andSave) {
            if ($this->save()) {
                $elementEvent = new CartEvent([
                    'cart' => yii::$app->cart->getElements(),
                    'cost' => yii::$app->cart->getCost(),
                    'count' => yii::$app->cart->getCount(),
                ]);

                $cartComponent = yii::$app->cart;
                $cartComponent->trigger($cartComponent::EVENT_CART_UPDATE, $elementEvent);
            }
        }
    }

    public function countIncrement($count)
    {
        $this->count = $this->count+$count;

        return $this->save();
    }

    public function getPrice($withTriggers = true)
    {
        $price = $this->price;

        $cart = yii::$app->cart;

        if($withTriggers) {
            $elementEvent = new CartElementEvent(['element' => $this, 'cost' => $price]);
            $cart->trigger($cart::EVENT_ELEMENT_PRICE, $elementEvent);
            $price = $elementEvent->cost;
        }

        $elementEvent = new CartElementEvent(['element' => $this, 'cost' => $price]);
        $cart->trigger($cart::EVENT_ELEMENT_ROUNDING, $elementEvent);
        $price = $elementEvent->cost;

        return $price;
    }

    public function setPrice($price)
    {
        $this->price = $price;
    }

    public function setModel($model)
    {
        $this->model = $model;
    }

    public function setOptions($options, $andSave = false)
    {
        if(is_array($options)) {
            $this->options = json_encode($options);
        } else {
            $this->options = $options;
        }

        if($andSave) {
            $this->save();
        }
    }

    public function setComment($comment, $andSave = false)
    {
        $this->comment = $comment;

        if($andSave) {
            $this->save();
        }
    }

    public static function tableName()
    {
        return '{{%cart_element}}';
    }

    public function getCost($withTriggers = true)
    {
        $cost = 0;
        $costProduct = $this->getPrice($withTriggers);
        $cart = \Yii::$app->cart;

        for($i = 0; $i < $this->count; $i++) {
            $currentCostProduct = $costProduct;
            if($withTriggers) {
                $elementEvent = new CartElementEvent(['element' => $this, 'cost' => $currentCostProduct]);
                $cart->trigger($cart::EVENT_ELEMENT_COST_CALCULATE, $elementEvent);
                $currentCostProduct = $elementEvent->cost;
            }
            $cost = $cost+$currentCostProduct;
        }

        if($withTriggers) {
            $elementEvent = new CartElementEvent(['element' => $this, 'cost' => $cost]);
            $cart->trigger($cart::EVENT_ELEMENT_COST, $elementEvent);
            $cost = $elementEvent->cost;
        }

        return $cost;
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
            [['hash', 'options', 'comment'], 'string'],
            [['price'], 'double'],
            [['item_id', 'count', 'parent_id'], 'integer'],
        ];
    }

    public function validateModel($attribute, $param)
    {
        $model = $this->model;
        if (class_exists($model)) {
            $elementModel = new $model();
            if (!$elementModel instanceof \dvizh\cart\interfaces\CartElement) {
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
            'comment' => yii::t('cart', 'Comment'),
        ];
    }

    public function beforeSave($insert)
    {
        $cart = yii::$app->cart;

        $cart->cart->updated_time = time();
        $cart->cart->save();

        $elementEvent = new CartElementEvent(['element' => $this]);

        $this->trigger(self::EVENT_ELEMENT_UPDATE, $elementEvent);

        if($elementEvent->stop) {
            return false;
        } else {
            return true;
        }
    }

    public function beforeDelete()
    {
        $elementEvent = new CartElementEvent(['element' => $this]);

        $this->trigger(self::EVENT_ELEMENT_DELETE, $elementEvent);

        if($elementEvent->stop) {
            return false;
        } else {
            return true;
        }
    }
}
