<?php
namespace dvizh\cart\events;

use yii\base\Event;

class Cart extends Event
{
    public $cart;
    public $cost;
    public $count;
}