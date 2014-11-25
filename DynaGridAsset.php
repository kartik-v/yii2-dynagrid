<?php

/**
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2014
 * @package yii2-dynagrid
 * @version 1.3.0
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
	public function init()
	{
		$this->setSourcePath(__DIR__ . '/assets');
		$this->setupAssets('js', ['js/kv-dynagrid']);
		$this->setupAssets('css', ['css/kv-dynagrid']);
		parent::init();
	}

}