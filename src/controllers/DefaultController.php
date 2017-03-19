<?php
namespace dvizh\cart\controllers;

use dvizh\cart\models\Cart;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii;

class DefaultController extends \yii\web\Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'truncate' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $elements = yii::createObject('\dvizh\cart\services\UserCart')->elements;

        return $this->render('index', [
            'elements' => $elements,
        ]);
    }

    public function actionTruncate()
    {
        $json = ['result' => 'undefined', 'error' => false];

        $cart = yii::createObject('\dvizh\cart\services\UserCart');
        
        if ($cart->truncate()) {
            $json['result'] = 'success';
        } else {
            $json['result'] = 'fail';
            $json['error'] = '';
        }

        return $this->_cartJson($json);
    }

    public function actionInfo() {
        return $this->_cartJson();
    }
    
    private function _cartJson($json)
    {
        if ($cart = yii::createObject('\dvizh\cart\services\UserCart')) {
            $json['elementsHTML'] = \dvizh\cart\widgets\ElementsList::widget();
            $json['count'] = $cart->getCount();
            $json['price'] = $cart->getCost();
        } else {
            $json['count'] = 0;
            $json['price'] = 0;
        }
        return Json::encode($json);
    }
}
