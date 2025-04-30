<?php

/**
 * Sistema de autoloading para el plugin
 *
 * @package Interactive_Calculators
 */

namespace InteractiveCalculators\Core;

use Exception;

class Autoloader
{
	/**
	 * Registrar el autoloader
	 */
	public static function register()
	{
		spl_autoload_register([__CLASS__, 'autoload']);
	}

	/**
	 * Metodo de autocargador
	 */
	public static function autoload(string $class)
	{
		// Namespace base
		$prefix = 'InteractiveCalculators\\';
		$len = strlen($prefix);

		// Si la clase no pertenece a nuestro namespace, salir
		if (strncmp($prefix, $class, $len) !== 0)
			return;

		// Ruta relativa
		$relative_class = substr($class, $len);

		// Construir la ruta completa
		$file_path = str_replace('\\', '/', IC_PLUGIN_DIR
			. 'src/'
			. $relative_class
			. '.php'
		);

		// Cargar el archivo
		if (file_exists($file_path)) {
			require_once $file_path;
		} else {
			throw new Exception("File not found: $file_path");
		}
	}
}
