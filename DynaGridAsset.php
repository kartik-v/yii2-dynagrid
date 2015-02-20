<?php

/**
 * @package   yii2-dynagrid
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2015
 * @version   1.4.2
 */

namespace kartik\dynagrid;

/**
 * Asset bundle for DynaGrid Widget
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.0
 */
class DynaGridAsset extends \kartik\base\AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->setSourcePath(__DIR__ . '/assets');
        $this->setupAssets('js', ['js/kv-dynagrid']);
        $this->setupAssets('css', ['css/kv-dynagrid']);
        parent::init();
    }

}