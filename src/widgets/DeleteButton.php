<?php
namespace dvizh\cart\widgets;

use yii\helpers\Html;
use yii\helpers\Url;

class DeleteButton extends \yii\base\Widget
{
    public $text = NULL;
    public $model = NULL;
    public $cssClass = 'btn btn-danger';
    public $lineSelector = 'li';  //Селектор материнского элемента, где выводится элемент
    public $deleteElementUrl = '/cart/element/delete';

    public function init()
    {
        parent::init();

        \dvizh\cart\assets\WidgetAsset::register($this->getView());

        if ($this->text == NULL) {
            $this->text = '╳';
        }

        return true;
    }

    public function run()
    {
        return Html::a($this->text, [$this->deleteElementUrl],
            [
                'data-url' => Url::toRoute($this->deleteElementUrl),
                'data-role' => 'cart-delete-button',
                'data-line-selector' => $this->lineSelector,
                'class' => 'dvizh-cart-delete-button ' . $this->cssClass,
                'data-id' => $this->model->getId()
            ]);
    }
}
