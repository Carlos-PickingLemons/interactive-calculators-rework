<?php
/**
 * Archivo de inicialización del plugin
 */

 namespace InteractiveCalculators\Core;

class Bootstrap {
	/**
	 * Inicializa el plugin
	 */
	public static function init() {
		self::loadAutoloader();
		self::defineConstants();

		$plugin = Plugin::get_instance();
		return $plugin;
	}

	/**
	 * Carga el autoloader
	 */
	public static function loadAutoloader() {
		require_once IC_PLUGIN_DIR . 'src/Core/Autoloader.php';
		Autoloader::register();
	}

	/**
	 * Carga las constantes del plugin
	 */
	public static function defineConstants() {
		require_once IC_PLUGIN_DIR . 'src/Core/constants.php';
	}
}
