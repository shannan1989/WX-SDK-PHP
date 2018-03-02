<?php

namespace Shannan;

class Wx {

	static private $loaded = false;
	static private $modules = array();

	static function load() {
		if (self::$loaded !== true) {
			include_once __DIR__ . '/wx/src/wx_base.php';
			self::$loaded = true;
		}
	}

	static function module($module_name) {
		self::load();
		if (!in_array($module_name, self::$modules)) {
			$path = __DIR__ . '/wx/src/wx_' . $module_name . '.php';
			if (file_exists($path)) {
				include_once $path;
			} else {
				throw new \InvalidArgumentException('The module "' . $module_name . '" does not exist');
			}
		}
	}

}
