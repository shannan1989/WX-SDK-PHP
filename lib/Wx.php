<?php

namespace Shannan;

class Wx {

	/**
	 * Register
	 * @return void
	 */
	public static function register() {
		if (version_compare(PHP_VERSION, '5.1.2', '>=')) {
			//SPL autoloading was introduced in PHP 5.1.2
			if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
				spl_autoload_register(array(new self, 'autoload'), true, true);
			} else {
				spl_autoload_register(array(new self, 'autoload'));
			}
		} else {

			/**
			 * Fall back to traditional autoload for old PHP versions
			 * @param string $classname The name of the class to load
			 */
			function __autoload($classname) {
				Wx::autoload($classname);
			}

		}
	}

	/**
	 * Autoload
	 * @param string $class 类名
	 */
	public static function autoload($class) {
		$filename = strtolower(substr(preg_replace('/([A-Z]+)/', '_$1', $class), 1));
		//Can't use __DIR__ as it's only in PHP 5.3+ , use dirname(__FILE__) instead.
		$file = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'wx' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $filename . '.php');
		if (file_exists($file) && is_readable($file)) {
			/** @noinspection PhpIncludeInspection Dynamic includes */
			require_once $file;
		}
	}

}
