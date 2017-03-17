<?php
namespace dvizh\cart\behaviors;

use yii\base\Behavior;
use dvizh\cart\Cart;

class Discount extends Behavior
{
    public $percent = 0;

    public function events()
    {
        return [
            Cart::EVENT_CART_COST => 'doDiscount'
        ];
    }

    public function doDiscount($event)
    {
        if($this->percent > 0 && $this->percent <= 100 && $event->cost > 0) {
            $event->cost = $event->cost-($event->cost*$this->percent)/100;
        }

        return $this;
    }
}
