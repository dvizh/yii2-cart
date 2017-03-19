<?php
namespace dvizh\cart\services;

class Cart extends \yii\base\Component implements \dvizh\app\interfaces\services\Cart
{
    protected $cart;

    public function getUserCartEntity(\dvizh\app\interfaces\entities\User $user) :  \dvizh\app\interfaces\entities\Cart
    {
        return \dvizh\cart\models\Cart::my();
    }
}