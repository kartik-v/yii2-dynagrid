<?php

/**
 * @package   yii2-dynagrid
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2015 - 2017
 * @version   1.4.6
 */

namespace kartik\dynagrid;

use kartik\base\AssetBundle;

/**
 * Asset bundle for [[DynaGridDetail]] widget
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.2.0
 */
class DynaGridDetailAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->setSourcePath(__DIR__ . '/assets');
        $this->setupAssets('js', ['js/kv-dynagrid-detail']);
        parent::init();
    }
}