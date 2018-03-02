<?php

namespace Shannan;

class Wx {

	static $loaded = false;
	static $modules = array();

	static function load() {
		if (self::$loaded !== true) {
			include_once __DIR__ . '/wx/src/wx_base.php';
			self::$loaded = true;
		}
	}

	static function module($moduleName) {
		self::load();
		if (!in_array($moduleName, self::$modules)) {
			$path = __DIR__ . '/wx/src/wx_' . $moduleName . '.php';
			if (file_exists($path)) {
				include_once $path;
			} else {
				throw new \InvalidArgumentException('The module "' . $moduleName . '" does not exist');
			}
		}
	}

}
