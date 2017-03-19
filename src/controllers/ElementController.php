<?php
namespace dvizh\cart\controllers;

use yii\helpers\Json;
use yii\filters\VerbFilter;
use yii;

class ElementController extends \yii\web\Controller
{

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'create' => ['post'],
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    public function actionDelete()
    {
        $json = ['result' => 'undefined', 'error' => false];
        $elementId = yii::$app->request->post('elementId');

        $cart = yii::createObject('\dvizh\cart\services\UserCart');

        $elementModel = $cart->getElementById($elementId);

        if($elementModel->remove()) {
            $json['result'] = 'success';
        }
        else {
            $json['result'] = 'fail';
        }

        return $this->_cartJson($json);
    }

    public function actionCreate()
    {
        $json = ['result' => 'undefined', 'error' => false];

        $cart = yii::createObject('\dvizh\cart\services\UserCart');

        $postData = yii::$app->request->post();

        $model = $postData['CartElement']['model'];
        if($model) {
            $productModel = new $model();
            if($productModel = $productModel::getById($postData['CartElement']['item_id'])) {
                $options = [];
                if(isset($postData['CartElement']['options'])) {
                    $options = $postData['CartElement']['options'];
                }

                if($postData['CartElement']['price'] && $postData['CartElement']['price'] != 'false') {
                    $elementId = $cart->putElement($productModel, $postData['CartElement']['count'], $postData['CartElement']['price'], $options);
                } else {
                    $elementId = $cart->putElement($productModel, $postData['CartElement']['count'], null, $options);
                }

                $json['elementId'] = $elementId;
                $json['result'] = 'success';
            } else {
                $json['result'] = 'fail';
                $json['error'] = 'none product';
            }
        } else {
            $json['result'] = 'fail';
            $json['error'] = 'empty model';
        }

        return $this->_cartJson($json);
    }

    public function actionUpdate()
    {
        $json = ['result' => 'undefined', 'error' => false];

        $cart = yii::createObject('\dvizh\cart\services\UserCart');
        
        $postData = yii::$app->request->post();

        $elementModel = $cart->getElementById($postData['CartElement']['id']);
        
        if(isset($postData['CartElement']['count'])) {
            $elementModel->setCount($postData['CartElement']['count'], true);
        }
        
        if(isset($postData['CartElement']['options'])) {
            $elementModel->setOptions($postData['CartElement']['options'], true);
        }
        
        $json['elementId'] = $elementModel->getId();
        $json['result'] = 'success';

        return $this->_cartJson($json);
    }

    private function _cartJson($json)
    {
        if ($cart = yii::createObject('\dvizh\cart\services\UserCart')) {
            if(!$elementsListWidgetParams = yii::$app->request->post('elementsListWidgetParams')) {
                $elementsListWidgetParams = [];
            }

            $json['elementsHTML'] = \dvizh\cart\widgets\ElementsList::widget($elementsListWidgetParams);
            $json['count'] = $cart->getCount();
            $json['clear_price'] = $cart->getCount();
            $json['price'] = $cart->getCost();
        } else {
            $json['count'] = 0;
            $json['price'] = 0;
            $json['clear_price'] = 0;
        }
        return Json::encode($json);
    }

}
