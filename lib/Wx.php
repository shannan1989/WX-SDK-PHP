<?php

namespace Shannan;

class Wx {

	static private $loaded = false;
	static private $modules = [];

	static function load() {
		if (self::$loaded !== true) {
			require_once __DIR__ . '/wx/src/wx_base.php';
			self::$loaded = true;
		}
	}

	static function module($module_name) {
		self::load();
		if (!in_array($module_name, self::$modules)) {
			$path = __DIR__ . '/wx/src/wx_' . $module_name . '.php';
			if (file_exists($path)) {
				require_once $path;
				self::$modules[] = $module_name;
			} else {
				throw new \InvalidArgumentException('The module "' . $module_name . '" does not exist');
			}
		}
	}

}
