<?php

/**
 * Plugin Name:     Interactive Calculators
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     Engagement tools for lead generation through interactive calculators
 * Author:          Carlos M. Rodríguez Santana
 * Author URI:      https://picking-lemons.com
 * Text Domain:     interactive-calculators
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Interactive_Calculators
 */

// Si se accede directamente, abortar
if (!defined('ABSPATH')) {
	exit;
}

// Definir constantes del plugin
define('IC_PLUGIN_FILE', __FILE__);
define('IC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('IC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('IC_PLUGIN_VERSION', '1.0.0');

// Incluir la clase principal del plugin
require_once IC_PLUGIN_DIR . 'includes/class-ic-database.php';
require_once IC_PLUGIN_DIR . 'includes/class-main.php';

// Inicialiar el plugin
function ic_init(): Main {
	return Main::get_instance();
}

// Iniciar el plugin
$GLOBALS['interactive_calculators'] = ic_init();
