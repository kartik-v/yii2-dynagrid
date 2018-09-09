/**
 * @package   yii2-dynagrid
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2015 - 2018
 * @version   1.4.1
 */
--
-- Table structure for table `tbl_dynagrid`
--
DROP TABLE IF EXISTS `tbl_dynagrid`;
CREATE TABLE `tbl_dynagrid` (
  `id` varchar(100) NOT NULL COMMENT 'Unique dynagrid setting identifier',
  `filter_id` varchar(100) COMMENT 'Filter setting identifier',
  `sort_id` varchar(100) COMMENT 'Sort setting identifier',
  `data` varchar(5000) DEFAULT NULL COMMENT 'Json encoded data for the dynagrid configuration',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Dynagrid personalization configuration settings';

--
-- Table structure for table `tbl_dynagrid_dtl`
--
DROP TABLE IF EXISTS `tbl_dynagrid_dtl`;
CREATE TABLE `tbl_dynagrid_dtl` (
  `id` varchar(100) NOT NULL COMMENT 'Unique dynagrid detail setting identifier',
  `category` varchar(10) NOT NULL COMMENT 'Dynagrid detail setting category "filter" or "sort"',
  `name` varchar(150) NOT NULL COMMENT 'Name to identify the dynagrid detail setting',
  `data` varchar(5000) DEFAULT NULL COMMENT 'Json encoded data for the dynagrid detail configuration',
  `dynagrid_id` varchar(100) NOT NULL COMMENT 'Related dynagrid identifier',
  PRIMARY KEY (`id`),
  UNIQUE KEY `tbl_dynagrid_dtl_UK1` (`name`,`category`,`dynagrid_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Dynagrid detail configuration settings';

ALTER TABLE `tbl_dynagrid`
ADD CONSTRAINT `tbl_dynagrid_FK1` 
FOREIGN KEY (`filter_id`) 
REFERENCES `tbl_dynagrid_dtl` (`id`) 
, ADD INDEX `tbl_dynagrid_FK1` (`filter_id` ASC);

ALTER TABLE `tbl_dynagrid`
ADD CONSTRAINT `tbl_dynagrid_FK2` 
FOREIGN KEY (`sort_id`) 
REFERENCES `tbl_dynagrid_dtl` (`id`) 
, ADD INDEX `tbl_dynagrid_FK2` (`sort_id` ASC);
