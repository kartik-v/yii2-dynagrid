<?php
/**
 * @package   yii2-dynagrid
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2015 - 2022
 * @version   1.5.3
 */

namespace kartik\dynagrid\migrations;

use yii\db\Migration;

/**
 * @author Philipp Frenzel <philipp@frenzel.net>
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @version 1.0
 * Migrate script to allow automatic installation on module usage
 */
class m140101_100000_dynagrid extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = '';

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%dynagrid}}', [
            'id' => $this->string(100)->notNull(),
            'filter_id' => $this->string(100),
            'sort_id' => $this->string(100),
            'data' => $this->text(),
        ], $tableOptions);

        $this->addPrimaryKey('dynagrid_PK', '{{%dynagrid}}', 'id');

        $this->createTable('{{%dynagrid_dtl}}', [
            'id' => $this->string(100)->notNull(),
            'category' => $this->string(10)->notNull(),
            'name' => $this->string(150)->notNull(),
            'data' => $this->text(),
            'dynagrid_id' => $this->string(100)->notNull(),
        ], $tableOptions);

        $this->addPrimaryKey('dynagrid_dtl_PK', '{{%dynagrid_dtl}}', 'id');
        $this->addForeignKey('dynagrid_FK1', '{{%dynagrid}}', 'filter_id', '{{%dynagrid_dtl}}', 'id');
        $this->addForeignKey('dynagrid_FK2', '{{%dynagrid}}', 'sort_id', '{{%dynagrid_dtl}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('dynagrid_FK1', '{{%dynagrid}}');
        $this->dropForeignKey('dynagrid_FK2', '{{%dynagrid}}');
        $this->dropTable('{{%dynagrid}}');
        $this->dropTable('{{%dynagrid_dtl}}');
    }
}
