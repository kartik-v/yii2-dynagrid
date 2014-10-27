<?php

/**
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2014
 * @package yii2-dynagrid
 * @version 1.2.0
 */

namespace kartik\dynagrid;

use kartik\widgets\AssetBundle;

/**
 * Asset bundle for DynaGridDetail Widget
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.0
 */
class DynaGridDetailAsset extends AssetBundle
{

	public function init()
	{
		$this->setSourcePath(__DIR__ . '/assets');
		$this->setupAssets('js', ['js/kv-dynagrid-detail']);
		parent::init();
	}

}