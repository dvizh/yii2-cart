<?php
use yii\db\Schema;
use yii\db\Migration;
class m160506_062849_create_cart extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable(
            '{{%cart}}',
            [
                'id' => Schema::TYPE_PK,
                'user_id' => Schema::TYPE_STRING . '(55) NOT NULL',
                'created_time' => Schema::TYPE_INTEGER . '(11) NOT NULL',
                'updated_time' => Schema::TYPE_INTEGER . '(11) NOT NULL'
            ],
            $tableOptions
        );

        $this->createIndex('user_id', '{{%cart}}', 'user_id');
        
        $this->createTable(
            '{{%cart_element}}',
            [
                'id' => Schema::TYPE_PK,
                'parent_id' => Schema::TYPE_INTEGER . '(55)',
                'model' => Schema::TYPE_STRING . '(110) NOT NULL',
                'cart_id' => Schema::TYPE_INTEGER . '(11) NOT NULL',
                'item_id' => Schema::TYPE_INTEGER . '(55) NOT NULL',
                'count' => Schema::TYPE_INTEGER . '(11) NOT NULL',
                'price' => Schema::TYPE_DECIMAL . '(11, 2)',
                'hash' => Schema::TYPE_STRING . '(255) NOT NULL',
                'options' => Schema::TYPE_TEXT . ' NULL',
            ],
            $tableOptions
        );
       
        $this->createIndex('cart_id', '{{%cart_element}}', 'cart_id');
        
        $this->addForeignKey(
            'elem_to_cart', '{{%cart_element}}', 'cart_id', '{{%cart}}', 'id', 'CASCADE', 'CASCADE'
        );
    }
    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%cart_element}}');
        $this->dropTable('{{%cart}}');
    }
}
