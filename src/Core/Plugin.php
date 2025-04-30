<?php
/**
 * Clase principal del plugin Interactive Calculators
 *
 * @package Interactive_Calculators
 */

namespace InteractiveCalculators\Core;

/**
 * Clase principal del plugin
 */
class Plugin
{
	/**
	 * Instancia única de esta clase (patrón Singleton)
	 * @var Plugin
	 */
	private static $instance = null;

	/**
	 * Instancia de la clase de base de datos
	 * @var Database
	 */
	private $database;

	/**
	 * Constructor de la clase
	 */
	private function __construct()
	{
		// Inicializar la base de datos
		$this->database = new Database();

		// Hacer la instancia de la bases de datos disponible globalmente
		global $ic_database;
		$ic_database = $this->database;

		// Hooks de activación y desactivación
		register_activation_hook(IC_PLUGIN_FILE, [$this,  'activate']);
		register_deactivation_hook(IC_PLUGIN_FILE, [$this, 'deactivate']);

		// Inicializar el plugin
		add_action('plugins_loaded', [$this, 'init']);
	}

	/**
	 * Obtener la instancia única de la clase (Singleton)
	 * @return Plugin
	 */
	public static function get_instance(): Plugin
	{
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Inicializar el plugin
	 */
	public function init()
	{
		// Cargar traducciones
		load_plugin_textdomain('interactive-calculators', false, dirname(plugin_basename(IC_PLUGIN_FILE)) . '/languages');

		// Actualizar base de datos si es necesario
		$this->database->update_database();

		// Registrar menús de administración
		add_action('admin_menu', array($this, 'register_admin_menu'));

		// Registrar assets
		add_action('admin_enqueue_scripts', array($this, 'register_admin_assets'));

		// Registrar shortcode para mostrar calculadoras
		add_shortcode('interactive_calculator', array($this, 'render_calculator_shortcode'));
	}

	/**
	 * Registrar los menús de administración
	 */
	public function register_admin_menu()
	{
		// Menú principal
		add_menu_page(
			'Interactive Calculators',
			'Calculators',
			'manage_options',
			'interactive-calculators',
			array($this, 'render_admin_page'),
			'dashicons-calculator',
			30
		);

		// Submenú para la lista de calculadoras
		add_submenu_page(
			'interactive-calculators',
			'All Calculators',
			'All Calculators',
			'manage_options',
			'interactive-calculators',
			array($this, 'render_calculators_list_page')
		);

		// Submenú para añadir nueva calculadora
		add_submenu_page(
			'interactive-calculators',
			'Add New Calculator',
			'Add New',
			'manage_options',
			'interactive-calculators-new',
			array($this, 'render_calculator_edit_page')
		);
	}

	/**
	 * Renderizar la página de administración
	 */
	public function render_admin_page()
	{
?>
		<div class="wrap">
			<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
			<div class="card">
				<h2>Interactive Calculators</h2>
				<p>Welcome to the Interactive Calculators admin panel.</p>
				<p>This plugin will allow you to create interactive calculators for lead generation.</p>
			</div>
		</div>
<?php
	}

	/**
	 * Renderizar la página de lista de calculadoras
	 */
	public function render_calculators_list_page()
	{
		// Verificar si hay alguna acción a realizar
		$this->process_calculator_actions();

		// Mostrar mensajes de éxito/error según parámetros de URL
		if (isset($_GET['message'])) {
			$message = sanitize_text_field($_GET['message']);

			switch ($message) {
				case 'created':
					add_settings_error(
						'interactive-calculators',
						'calculator-created',
						__('Calculator created successfully.', 'interactive-calculators'),
						'success'
					);
					break;

				case 'updated':
					add_settings_error(
						'interactive-calculators',
						'calculator-updated',
						__('Calculator updated successfully.', 'interactive-calculators'),
						'success'
					);
					break;
			}
		}

		// Obtener calculadoras del la base de datos
		$per_page = 20;
		$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
		$search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

		// Obtener parámetros de ordenación
		$orderby = isset($_GET['orderby']) ? sanitize_sql_orderby($_GET['orderby']) : 'id';
		$order = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'ASC' : 'DESC';

		// Obtener calculadoras
		$calculators = $this->database->get_calculators($per_page, $current_page, $search, $orderby, $order);
		$total_calculators = $this->database->count_calculators($search);

		// Calcular total de páginas
		$total_pages = ceil($total_calculators / $per_page);

		// Incluir la vista HTML
		include IC_PLUGIN_DIR . 'admin/views/calculators-list.php';
	}

	/**
	 * Renderizar la página de edición/creación de calculadoras
	 */
	public function render_calculator_edit_page()
	{
		// Verificar si estamos editando una calculadora existente o creando una nueva
		$calculator_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		$is_edit = $calculator_id > 0;

		// Obtener datos de la calculadora si estamos editando
		$calculator = [];
		if ($is_edit) {
			$calculator = $this->database->get_calculator($calculator_id);
			if (!$calculator) {
				wp_redirect(admin_url('admin.php?page=interactive-calculators'));
				exit;
			}
		}

		// Mensaje de redirección
		$redirect_url = '';

		// Procesar el envío del formulario si corresponde
		$errors = [];
		if (isset($_POST['save_calculator']) && isset($_POST['_wpnonce'])) {
			$errors = $this->process_calculator_form($calculator_id);

			// Si no hay errores, preparar redirección mediante JavaScript
			if (empty($errors)) {
				$message = $is_edit ? 'updated' : 'created';
				$redirect_url = admin_url('admin.php?page=interactive-calculators&message=' . $message);
			}
		}

		// Incluir la vista del formulario
		include IC_PLUGIN_DIR . 'admin/views/calculator-edit.php';

		// Si hay una URL de redirección, ejecutar JavaScript para redirigir
		if (!empty($redirect_url)) {
			echo '<script>window.location.href = "' . esc_url($redirect_url) . '";</script>';
		}
	}

	/**
	 * Renderiza el shortcode de calculadora
	 *
	 * @param array $atts Atributos del shortcode
	 * @return string HTML de la calculadora
	 */
	public function render_calculator_shortcode($atts)
	{
		$atts = shortcode_atts(
			array(
				'id' => 0,
			),
			$atts,
			'interactive_calculator'
		);

		$calculator_id = intval($atts['id']);
		if ($calculator_id <= 0) {
			return '<p class="calculator-error">' . __('Error: ID de calculadora no válido.', 'interactive-calculators') . '</p>';
		}

		// Obtener datos de la calculadora
		$calculator = $this->database->get_calculator($calculator_id);
		if (!$calculator) {
			return '<p class="calculator-error">' . __('Error: Calculadora no encontrada.', 'interactive-calculators') . '</p>';
		}

		// Obtener campos y configuración
		$fields = json_decode($calculator['fields'], true);
		$settings = json_decode($calculator['settings'], true);

		if (!is_array($fields) || !is_array($settings)) {
			return '<p class="calculator-error">' . __('Error: Datos de calculadora inválidos.', 'interactive-calculators') . '</p>';
		}

		// Iniciar el buffer de salida
		ob_start();

		// Incluir la vista de la calculadora en el frontend
		include IC_PLUGIN_DIR . 'public/views/calculator.php';

		// Devolver el contenido del buffer
		return ob_get_clean();
	}

	/**
	 * Registrar scripts y estilos para administración
	 */
	public function register_admin_assets($hook)
	{
		// Solo cargar en páginas de administración de nuestro plugin
		if (strpos($hook, 'interactive-calculators') === false) {
			return;
		}

		wp_enqueue_style('ic-admin-css', IC_PLUGIN_URL . 'assets/css/admin.css', array(), IC_PLUGIN_VERSION);
		wp_enqueue_script('ic-admin-js', IC_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), IC_PLUGIN_VERSION, true);
	}

	/**
	 * Procesar acciones sobre calculadoras (eliminar, etc.)
	 */
	private function process_calculator_actions()
	{
		// Verificar si hay alguna acción y un ID válido
		if (isset($_GET['action'], $_GET['calculator_id'], $_GET['_wpnonce'])) {
			$action = sanitize_text_field($_GET['action']);
			$calculator_id = intval($_GET['calculator_id']);
			$nonce = sanitize_text_field($_GET['_wpnonce']);

			// Verificar nonce de seguridad
			if (!wp_verify_nonce($nonce, 'calculator_' . $action . '_' . $calculator_id)) {
				wp_die('Security check failed');
			}

			$message = '';
			$message_type = 'success';

			// Procesar la acción
			switch ($action) {
				case 'delete':
					if ($this->database->delete_calculator($calculator_id)) {
						$message = __('Calculator deleted successfully.', 'interactive-calculators');
					} else {
						$message = __('Error deleting calculator.', 'interactive-calculators');
						$message_type = 'error';
					}
					break;
			}

			// Guardar mensaje en una opción transitoria
			if (!empty($message)) {
				set_transient('ic_admin_message', [
					'message' => $message,
					'type' => $message_type
				], 30);
			}

			// Redireccionar sin modificar headers
			echo '<script>window.location.href = "' . admin_url('admin.php?page=interactive-calculators') . '";</script>';
			exit;
		}

		// Verificar si hay un mensaje transitorio para mostrar
		$transient_message = get_transient('ic_admin_message');
		if ($transient_message) {
			delete_transient('ic_admin_message');

			// Añadir mensaje de éxito/error
			add_settings_error(
				'interactive-calculators',
				'calculator-' . $transient_message['type'],
				$transient_message['message'],
				$transient_message['type']
			);
		}
	}

	/**
	 * Procesa el formulario de calculadora
	 * @param int $calculator_id ID de la calculadora (0 para nueva)
	 * @return array Lista de errores (vacía si no hay errores)
	 */
	private function process_calculator_form($calculator_id = 0)
	{
		$errors = [];

		// Verificar nonce de seguridad
		if (!wp_verify_nonce($_POST['_wpnonce'], 'save_calculator')) {
			wp_die('Error de seguridad');
		}

		// Recoger y sanitizar datos del formulario
		$name = isset($_POST['calculator_name']) ? sanitize_text_field($_POST['calculator_name']) : '';
		$description = isset($_POST['calculator_description']) ? wp_kses_post($_POST['calculator_description']) : '';
		$shortcode = isset($_POST['calculator_shortcode']) ? sanitize_title($_POST['calculator_shortcode']) : '';
		$fields = isset($_POST['calculator_fields']) ? stripslashes($_POST['calculator_fields']) : '[]';

		// Escapar caracteres problemáticos
		$fields = str_replace("\\", "\\\\", $fields);
		$settings_json = isset($_POST['calculator_settings']) ? $_POST['calculator_settings'] : '{}';

		// Validar campos obligatorios
		if (empty($name)) {
			$errors[] = __('El nombre de la calculadora es obligatorio.', 'interactive-calculators');
			return $errors;
		}

		// Verificar JSON de settings si es necesario
		json_decode($settings_json);
		if (json_last_error() !== JSON_ERROR_NONE) {
			// Crear JSON válido con los campos individuales
			$settings_array = [
				'formula' => isset($_POST['formula']) ? sanitize_text_field($_POST['formula']) : '',
				'resultPrefix' => isset($_POST['result_prefix']) ? sanitize_text_field($_POST['result_prefix']) : '',
				'resultSuffix' => isset($_POST['result_suffix']) ? sanitize_text_field($_POST['result_suffix']) : '',
				'decimalPlaces' => isset($_POST['decimal_places']) ? intval($_POST['decimal_places']) : 2,
				'callToAction' => isset($_POST['call_to_action']) ? sanitize_text_field($_POST['call_to_action']) : '',
				'successMessage' => isset($_POST['success_message']) ? sanitize_text_field($_POST['success_message']) : ''
			];
			$settings_json = json_encode($settings_array);
		}

		// Crear o actualizar la calculadora
		$calculator_data = [
			'name' => $name,
			'type' => 'standard',
			'description' => $description,
			'fields' => $fields,
			'settings' => $settings_json
		];

		// Añadir shortcode personalizado si existe
		if (!empty($shortcode)) {
			$calculator_data['shortcode'] = $shortcode;
		}

		// Guardar en la base de datos
		if ($calculator_id > 0) {
			$result = $this->database->update_calculator($calculator_id, $calculator_data);
			if (!$result) {
				$errors[] = __('Error al actualizar la calculadora.', 'interactive-calculators');
			}
		} else {
			$result = $this->database->create_calculator($calculator_data);
			if (!$result) {
				$errors[] = __('Error al crear la calculadora.', 'interactive-calculators');
			}
		}

		return $errors;
	}

	/**
	 * Función de activación del plugin
	 */
	public function activate()
	{
		// Instalar tablas de base de datos
		$this->database->install();

		// Actualizar la estructura si es necesario
		$this->database->update_database();

		flush_rewrite_rules();
	}

	/**
	 * Función de desactivación del plugin
	 */
	public function deactivate()
	{
		flush_rewrite_rules();
	}
}
