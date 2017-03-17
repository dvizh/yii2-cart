<?php
namespace dvizh\cart\services;

class CartElement extends \yii\base\Component implements \dvizh\dic\interfaces\CartElement
{
    function getId();
    function getName();
    function getPrice($withTriggers = true);
    function getCount();
    function getModelName();
    function getItemId();
    function getOptions();
    
    function setId();
    function setName();
    function setPrice($withTriggers = true);
    function setCount();
    function setModelName();
    function setItemId();
    function setOptions();
}