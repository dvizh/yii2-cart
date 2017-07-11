<?php
namespace dvizh\cart\widgets;

use yii\helpers\Url;
use yii\helpers\Html;
use yii;

class ChangeOptions extends \yii\base\Widget
{
    const TYPE_SELECT = 'select';
    const TYPE_RADIO = 'radio';

    public $model = NULL;
    public $type = NULL;
    public $cssClass = '';
    public $defaultValues = [];

    public function init()
    {
        if ($this->type == NULL) {
            $this->type = self::TYPE_SELECT;
        }

        parent::init();

        \dvizh\cart\assets\WidgetAsset::register($this->getView());

        return true;
    }

    public function run()
    {
        if ($this->model instanceof \dvizh\cart\interfaces\CartElement) {
            $optionsList = $this->model->getCartOptions();
            $changerCssClass = 'dvizh-option-values-before';
            $id = $this->model->getCartId();
        } else {
            $optionsList = $this->model->getModel()->getCartOptions();
            $this->defaultValues = $this->model->getOptions();
            $id = $this->model->getId();
            $changerCssClass = 'dvizh-option-values';
        }

        if (!empty($optionsList)) {
            $i = 1;
            foreach ($optionsList as $optionId => $optionData) {
                if (!is_array($optionData)) {
                    $optionData = [];
                }
                
                $cssClass = "{$changerCssClass} dvizh-cart-option{$id} ";

                $optionsArray = ['' => $optionData['name']];
                if (isset($optionData['variants'])) {
                    foreach ($optionData['variants'] as $variantId => $value) {
                        $optionsArray[$variantId] = $value;
                    }
                }

                if ($this->type == 'select') {

                    $list = Html::dropDownList('cart_options' . $id . '-' . $i,
                        $this->_defaultValue($optionId),
                        $optionsArray,
                        ['data-href' => Url::toRoute(["/cart/element/update"]), 'data-filter-id' => $optionId, 'data-name' => Html::encode($optionData['name']), 'data-id' => $id, 'class' => "form-control $cssClass"]
                    );
                } else {
                    $list = Html::tag('div', Html::tag('strong', $optionData['name']), ['class' => 'dvizh-option-heading']);
                    $list .= Html::radioList('cart_options' . $id . '-' . $i,
                        $this->_defaultValue($optionId),
                        $optionsArray,
                        ['itemOptions' => ['data-href' => Url::toRoute(["/cart/element/update"]), 'data-filter-id' => $optionId, 'data-name' => Html::encode($optionData['name']), 'data-id' => $id, 'class' => $cssClass]]
                    );
                }

                $options[] = Html::tag('div', $list, ['class' => "dvizh-option"]);
                $i++;
            }
        } else {
            return null;
        }

        return Html::tag('div', implode('', $options), ['class' => 'dvizh-change-options ' . $this->cssClass]);
    }

    private function _defaultValue($option)
    {
        if (isset($this->defaultValues[$option])) {
            return $this->defaultValues[$option];
        }

        return false;
    }
}
