<?php
namespace dvizh\cart\interfaces;

interface CartElement
{
    public function getCartId();

    public function getCartName();

    public function getCartPrice();
    
    public function getCartOptions();
}
