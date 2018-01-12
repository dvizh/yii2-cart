<?php

use yii\db\Migration;

/**
 * Class m170920_074737_add_column_comment_to_cart_element_table
 */
class m270920_094737_add_default_value_to_cart_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->alterColumn('{{%cart}}', 'user_id', $this->string(55)->defaultValue(''));
        $this->alterColumn('{{%cart_element}}', 'parent_id', $this->integer(55)->defaultValue(0));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->alterColumn('{{%cart}}', 'user_id', $this->string(55)->null());
        $this->alterColumn('{{%cart_element}}', 'parent_id', $this->integer(55));
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
