<?php

/**
 * @package   yii2-dynagrid
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2015 - 2022
 * @version   1.5.3
 */

namespace kartik\dynagrid;

use Yii;

/**
 * Trait for dynagrid widgets
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.0
 */
trait DynaGridTrait
{
    /**
     * Gets the category translated description
     *
     * @param  string  $cat  the category 'grid', 'filter', or 'sort'
     * @param  boolean  $initCap  whether to capitalize first letter.
     *
     * @return string
     */
    public static function getCat($cat, $initCap = false)
    {
        if ($initCap) {
            return ucfirst(static::getCat($cat, false));
        }
        switch ($cat) {
            case DynaGridStore::STORE_GRID:
                return Yii::t('kvdynagrid', 'grid');
            case DynaGridStore::STORE_SORT:
                return Yii::t('kvdynagrid', 'sort');
            case DynaGridStore::STORE_FILTER:
                return Yii::t('kvdynagrid', 'filter');
            default:
                return Yii::t('kvdynagrid', $cat);
        }
    }
}
