<?php
namespace dvizh\cart\widgets; 

use yii\helpers\Html;
use yii\helpers\Url;
use yii;

class TruncateButton extends \yii\base\Widget
{
    public $text = NULL;
    public $cssClass = 'btn btn-danger';
    public $truncateCartUrl = '/cart/default/truncate';
 
    public function init()
    {
        parent::init();

        \dvizh\cart\assets\WidgetAsset::register($this->getView());

        if ($this->text == NULL) {
            $this->text = yii::t('cart', 'Truncate');
        }
        
        return true;
    }

    public function run()
    {
        return Html::a(Html::encode($this->text), [$this->truncateCartUrl],
            [
                'class' => 'dvizh-cart-truncate-button ' . $this->cssClass,
                'data-role' => 'truncate-cart-button',
                'data-url' => Url::toRoute($this->truncateCartUrl),
            ]);
    }
}
