<?php

/**
 * Plugin Name:     Interactive Calculators Rework
 * Description:     Engagement tools for lead generation through interactive calculators
 * Author:          Carlos M. Rodríguez Santana
 * Version:         0.1.0
 *
 * @package         Interactive_Calculators
 */

use InteractiveCalculators\Core\Bootstrap;

// Protección contra el acceso directo
if (!defined('ABSPATH')) exit;

// Constantes del plugin
define('IC_PLUGIN_FILE', __FILE__);
define('IC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('IC_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once IC_PLUGIN_DIR . 'src/Core/Bootstrap.php';
$plugin = Bootstrap::init();

// Registar los hooks de activación y desactivación
register_activation_hook(IC_PLUGIN_FILE, [$plugin, 'activate']);
register_deactivation_hook(IC_PLUGIN_FILE, [$plugin, 'deactivate']);

// Inicar el plugin cuando Wordpress esté listo
add_action('plugins_loaded', [$plugin, 'init']);
