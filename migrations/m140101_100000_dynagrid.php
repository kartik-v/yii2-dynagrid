<?php

/**
 * @author Philipp Frenzel <philipp@frenzel.net>
 * @version 1.0
 * Migrate script to allow automatic installation on module usage
 */

namespace kartik\dynagrid\migrations; 

use yii\db\Schema;
use yii\db\Migration;

class m140101_100000_dynagrid extends Migration
{
    public function up()
    {

      switch (Yii::$app->db->driverName) {
        case 'mysql':
          $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
          break;
        case 'pgsql':
          $tableOptions = null;
          break;
        default:
          throw new RuntimeException('Your database is not supported!');
      }

      $this->createTable('{{%dynagrid}}',array(
          'id'                => Schema::TYPE_TEXT. '(100) NOT NULL',
          'filter_id'         => Schema::TYPE_TEXT. '(100) NOT NULL',
          'sort_id'           => Schema::TYPE_TEXT. '(100) NOT NULL',
          'data'              => Schema::TYPE_TEXT. '(5000) DEFAULT NULL'
      ),$tableOptions);

      $this->addPrimaryKey('PK_dynagrid','{{%dynagrid}}','id');

      $this->createTable('{{%dynagrid_dtl}}',array(
          'id'                => Schema::TYPE_TEXT. '(100) NOT NULL',
          'category'          => Schema::TYPE_TEXT. '(10) NOT NULL',
          'name'              => Schema::TYPE_TEXT. '(150) NOT NULL',
          'data'              => Schema::TYPE_TEXT. '(5000) DEFAULT NULL'
          'dynagrid_id'       => Schema::TYPE_TEXT. '(100) NOT NULL',
      ),$tableOptions);

      $this->addPrimaryKey('PK_dynagrid_dtl','{{%dynagrid_dtl}}','id');
      $this->addForeignKey('tbl_dynagrid_FK1','{{%dynagrid}}','filter_id','{{%dynagrid_dtl}}','id');
      $this->addForeignKey('tbl_dynagrid_FK2','{{%dynagrid}}','sort_id','{{%dynagrid_dtl}}','id');
    }

    public function down()
    {
        $this->dropForeignKey('tbl_dynagrid_FK1','{{%dynagrid}}');
        $this->dropForeignKey('tbl_dynagrid_FK2','{{%dynagrid}}');
        $this->dropTable('{{%dynagrid}}');
        $this->dropTable('{{%dynagrid_dtl}}');
    }
}
