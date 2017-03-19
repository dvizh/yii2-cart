<?php
namespace dvizh\cart\widgets;

use yii\helpers\Html;
use yii\helpers\Url;
use yii;

class CartInformer extends \yii\base\Widget
{

    public $text = NULL;
    public $offerUrl = NULL;
    public $cssClass = NULL;
    public $htmlTag = 'span';
	public $showOldPrice = true;

	private $cart;

	public function __construct(\dvizh\app\interfaces\services\singletons\UserCart $cart, $config = [])
    {
        $this->cart = $cart;

        parent::__construct($config);
    }

    public function init()
    {
        parent::init();

        \dvizh\cart\assets\WidgetAsset::register($this->getView());

        if ($this->offerUrl == NULL) {
            $this->offerUrl = Url::toRoute(["/cart/default/index"]);
        }
        
        if ($this->text === NULL) {
            $this->text = '{c} '. Yii::t('cart', 'on').' {p}';
        }
        
        return true;
    }

    public function run()
    {
        $cart = $this->cart;

        $this->text = str_replace(['{c}', '{p}'],
            ['<span class="dvizh-cart-count">'.$cart->getCount().'</span>', '<strong class="dvizh-cart-price">'.$cart->getCost().'</strong>'],
            $this->text
        );
        
        return Html::tag($this->htmlTag, $this->text, [
                'href' => $this->offerUrl,
                'class' => "dvizh-cart-informer {$this->cssClass}",
        ]);
    }
}
