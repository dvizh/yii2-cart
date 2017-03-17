<?php
namespace dvizh\cart\models\tools;

use yii\web\Session;
use yii;

class CartQuery extends \yii\db\ActiveQuery
{
    public function my()
    {
        $session = yii::$app->session;

        if(!$userId = yii::$app->user->id) {
            if (!$userId = $session->get('tmp_user_id')) {
                $userId = md5(time() . '-' . yii::$app->request->userIP . Yii::$app->request->absoluteUrl);
                $session->set('tmp_user_id', $userId);
            }
        }

        $one = $this->andWhere(['user_id' => $userId])->one();
        
        if (!$one) {
            $one = yii::createObject('\dvizh\cart\models\Cart');
            
            $one->created_time = time();
            $one->updated_time = time();
            $one->user_id = $userId;
            $one->save();
        }

        return $one;
    }
}
