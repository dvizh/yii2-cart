<?php

use yii\db\Migration;

/**
 * Class m170920_074737_add_column_comment_to_cart_element_table
 */
class m270920_074737_add_column_comment_to_cart_element_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%cart_element}}', 'comment', $this->string(255)->null());

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%cart_element}}', 'comment');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m170920_074737_add_column_comment_to_cart_element_table cannot be reverted.\n";

        return false;
    }
    */
}
