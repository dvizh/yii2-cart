<?php
namespace dvizh\cart\interfaces;

interface Cart
{
    public function my();
    
    public function put(Element $model);
    
    public function getElements();
    
    public function getElement(CartElement $model, $options);
    
    public function getCost();
    
    public function getCount();
    
    public function getElementById($id);
    
    public function getElementsByModel(CartElement $model);
    
    public function truncate();
}
