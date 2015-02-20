<?php
/**
 * @package   yii2-dynagrid
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2015
 * @version   1.4.2
 */

use yii\db\Schema;
use yii\db\Migration;

/**
 * @author Philipp Frenzel <philipp@frenzel.net>
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @version 1.0
 * Migrate script to allow automatic installation on module usage
 */
class m140101_100000_dynagrid extends Migration
{
    // not null specification
    const NN = ' NOT NULL';

    /**
     * @inheritdoc
     * @return bool|void
     */
    public function up()
    {
        $tableOptions = '';

        if (Yii::$app->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%dynagrid}}', [
            'id' => Schema::TYPE_STRING . '(100)' . self::NN,
            'filter_id' => Schema::TYPE_STRING . '(100)',
            'sort_id' => Schema::TYPE_STRING . '(100)',
            'data' => Schema::TYPE_TEXT . '(5000) DEFAULT NULL'
        ], $tableOptions);

        $this->addPrimaryKey('dynagrid_PK', '{{%dynagrid}}', 'id');

        $this->createTable('{{%dynagrid_dtl}}', [
            'id' => Schema::TYPE_STRING . '(100)' . self::NN,
            'category' => Schema::TYPE_STRING . '(10)' . self::NN,
            'name' => Schema::TYPE_STRING . '(150)' . self::NN,
            'data' => Schema::TYPE_STRING . '(5000) DEFAULT NULL',
            'dynagrid_id' => Schema::TYPE_STRING . '(100)' . self::NN
        ], $tableOptions);

        $this->addPrimaryKey('dynagrid_dtl_PK', '{{%dynagrid_dtl}}', 'id');
        $this->addForeignKey('dynagrid_FK1', '{{%dynagrid}}', 'filter_id', '{{%dynagrid_dtl}}', 'id');
        $this->addForeignKey('dynagrid_FK2', '{{%dynagrid}}', 'sort_id', '{{%dynagrid_dtl}}', 'id');
    }

    /**
     * @inheritdoc
     * @return bool|void
     */
    public function down()
    {
        $this->dropForeignKey('dynagrid_FK1', '{{%dynagrid}}');
        $this->dropForeignKey('dynagrid_FK2', '{{%dynagrid}}');
        $this->dropTable('{{%dynagrid}}');
        $this->dropTable('{{%dynagrid_dtl}}');
    }
}
