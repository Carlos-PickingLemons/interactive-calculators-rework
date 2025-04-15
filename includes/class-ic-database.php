<?php

/**
 * Clase para la gestión de base de datos
 *
 * @package Interactive_Calculators
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Clase de base de datos para calculadoras interactivas
 */
class IC_Database
{
	/**
	 * Nombre de la tabla de calculadoras
	 * @var string
	 */
	private $calculators_table;

	/**
	 * Nombre de la tabla de leads
	 * @var string
	 */
	private $leads_table;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		global $wpdb;
		$this->calculators_table = $wpdb->prefix . 'ic_calculators';
		$this->leads_table = $wpdb->prefix . 'ic_leads';
	}

	/**
	 * Installar tablas de base de datos
	 */
	public function install()
	{
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		// Tabla de calculadoras
		$sql_calculators = "CREATE TABLE {$this->calculators_table} (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			name varchar(100) NOT NULL,
			type varchar(50) NOT NULL,
			description text,
			fields longtext,
			settings longtext,
			shortcode varchar(50) NOT NULL,
			page_id mediumint(9) DEFAULT 0,
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		// Tabla de leads
		$sql_leads = "CREATE TABLE {$this->leads_table} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            calculator_id mediumint(9) NOT NULL,
            name varchar(100),
            email varchar(100) NOT NULL,
            phone varchar(20),
            company varchar(100),
            data longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY calculator_id (calculator_id)
        ) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql_calculators);
		dbDelta($sql_leads);
		add_option('ic_db_version', IC_PLUGIN_VERSION);
	}

	/**
	 * Actualizar la estructura de la base de datos si es necesario
	 */
	public function update_database()
	{
		$current_version = get_option('ic_db_version', '0.0.0');

		// Si ya estamos en la versión actual, no hacer nada
		if (version_compare($current_version, IC_PLUGIN_VERSION, '>=')) {
			return;
		}

		global $wpdb;

		// Si estamos actualizando desde una versión anterior a la 1.0.0
		if (version_compare($current_version, '1.0.0', '<')) {
			// Comprobar si la tabla existe
			if ($wpdb->get_var("SHOW TABLES LIKE '{$this->calculators_table}'") === $this->calculators_table) {
				// Comprobar si la columna page_id ya existe
				$columns = $wpdb->get_results("SHOW COLUMNS FROM {$this->calculators_table} LIKE 'page_id'");

				if (empty($columns)) {
					// Añadir la columna page_id
					$wpdb->query("ALTER TABLE {$this->calculators_table} ADD COLUMN page_id mediumint(9) DEFAULT 0");
				}
			}
		}

		// Actualizar la versión de la base de datos
		update_option('ic_db_version', IC_PLUGIN_VERSION);
	}

	/**
	 * Obtener una calculadora específica por ID
	 *
	 * @param int $id ID de la calculadora
	 * @return array|null Datos de la calculadora o null
	 */
	public function get_calculator($id): array|null
	{
		global $wpdb;
		return $wpdb->get_row(
			$wpdb->prepare("SELECT * FROM {$this->calculators_table} WHERE id = %d", $id),
			ARRAY_A
		);
	}

	/**
	 * Obtener todas las calculadoras
	 *
	 * @param int $per_page Calculadoras por página
	 * @param int $page_number Número de página
	 * @param string $search Término de búsqueda
	 * @param string $orderby Campo para ordenar
	 * @param string $order Dirección de orden
	 * @return array Lista de calculadoras
	 */
	public function get_calculators(
		$per_page = 20,
		$page_number = 1,
		$search = '',
		$orderby = 'id',
		$order = 'DESC'
	): array {
		global $wpdb;

		$sql = "SELECT * FROM {$this->calculators_table}";

		if (!empty($search)) {
			$sql .= $wpdb->prepare(
				" WHERE name LIKE %s OR description LIKE %s",
				'%' . $wpdb->esc_like($search) . '%',
				'%' . $wpdb->esc_like($search) . '%'
			);
		}

		$sql .= !empty($orderby)
			? " ORDER BY " . sanitize_sql_orderby("$orderby $order")
			: " ORDER BY id DESC";

		$sql .= $wpdb->prepare(" LIMIT %d OFFSET %d", $per_page, ($page_number - 1) * $per_page);

		return $wpdb->get_results($sql, ARRAY_A);
	}

	/**
	 * Contar calculadoras
	 *
	 * @param string $search Término de búsqueda
	 * @return int Total de calculadoras
	 */
	public function count_calculators($search = ''): int
	{
		global $wpdb;
		$sql = "SELECT COUNT(*) FROM {$this->calculators_table}";

		if (!empty($search)) {
			$sql .= $wpdb->prepare(
				" WHERE name LIKE %s OR description LIKE %s",
				'%' . $wpdb->esc_like($search) . '%',
				'%' . $wpdb->esc_like($search) . '%'
			);
		}

		return (int) $wpdb->get_var($sql);
	}

	/**
	 * Crear una nueva calculadora
	 *
	 * @param array $data Datos de la calculadora
	 * @return int|false ID de la calculadora o false
	 */
	public function create_calculator($data)
	{
		global $wpdb;

		if ($wpdb->get_var("SHOW TABLES LIKE '{$this->calculators_table}'") != $this->calculators_table) {
			$this->install();
			if ($wpdb->get_var("SHOW TABLES LIKE '{$this->calculators_table}'") != $this->calculators_table) {
				return false;
			}
		}

		if (empty($data['shortcode'])) {
			$data['shortcode'] = sanitize_title($data['name']);
		}

		if (empty($data['fields'])) {
			$data['fields'] = '[]';
		}

		if (empty($data['settings'])) {
			$data['settings'] = '{}';
		}

		$insert_data = [
			'name' => $data['name'],
			'type' => isset($data['type']) ? $data['type'] : 'standard',
			'description' => isset($data['description']) ? $data['description'] : '',
			'fields' => $data['fields'],
			'settings' => $data['settings'],
			'shortcode' => $data['shortcode']
		];

		$result = $wpdb->query($wpdb->prepare(
			"INSERT INTO {$this->calculators_table} (
			name, type, description, fields, settings, shortcode
			) VALUES (%s, %s, %s, %s, %s, %s)",
			$insert_data['name'],
			$insert_data['type'],
			$insert_data['description'],
			$insert_data['fields'],
			$insert_data['settings'],
			$insert_data['shortcode']
		));

		$this->create_calculator_page($wpdb->insert_id, $data);

		return $result !== false ? $wpdb->insert_id : false;
	}

	/**	 * Eliminar una calculadora
	 *
	 * @param int $id ID de la calculadora
	 * @return bool Éxito o fracaso
	 */
	public function delete_calculator($id)
	{
		global $wpdb;

		// Obtener la página asociada antes de eliminar
		$calculator = $this->get_calculator($id);

		$result = $wpdb->delete(
			$this->calculators_table,
			['id' => $id],
			['%d']
		);

		// Si la eliminación fue exitosa y había una página asociada, eliminarla
		if ($result !== false && !empty($calculator['page_id'])) {
			wp_delete_post($calculator['page_id'], true); // true para bypass papelera
		}

		return $result;
	}

	/**
	 * Actualizar una calculadora existente
	 *
	 * @param int $id ID de la calculadora
	 * @param array $data Datos a actualizar
	 * @return bool Éxito o fracaso
	 */
	/**
	 * Actualizar una calculadora existente
	 *
	 * @param int $id ID de la calculadora
	 * @param array $data Datos a actualizar
	 * @return bool Éxito o fracaso
	 */
	public function update_calculator($id, $data)
	{
		global $wpdb;

		// Preparar campos JSON
		if (isset($data['fields'])) {
			if (is_array($data['fields'])) {
				$data['fields'] = wp_json_encode($data['fields']);
			} else {
				json_decode($data['fields']);
				if (json_last_error() !== JSON_ERROR_NONE) {
					return false;
				}
			}
		}

		if (isset($data['settings'])) {
			if (is_array($data['settings'])) {
				$data['settings'] = wp_json_encode($data['settings']);
			} else {
				json_decode($data['settings']);
				if (json_last_error() !== JSON_ERROR_NONE) {
					return false;
				}
			}
		}

		// Datos a actualizar
		$update_data = [
			'name' => $data['name'],
			'type' => $data['type']
		];
		$formats = ['%s', '%s'];

		// Campos opcionales
		$optional_fields = ['description', 'fields', 'settings', 'shortcode'];
		foreach ($optional_fields as $field) {
			if (isset($data[$field])) {
				$update_data[$field] = $data[$field];
				$formats[] = '%s';
			}
		}

		// Actualizar datos
		$result = $wpdb->update(
			$this->calculators_table,
			$update_data,
			['id' => $id],
			$formats,
			['%d']
		);

		if ($result !== false) {
			// Actualizar o crear la página correspondiente
			$this->create_calculator_page($id, $data);
		}

		return $result !== false;
	}

	/**
	 * Registrar un lead de una calculadora
	 *
	 * @param array $data Datos del lead
	 * @return int|false ID del lead o false
	 */
	public function create_lead($data)
	{
		global $wpdb;

		if (isset($data['data']) && is_array($data['data'])) {
			$data['data'] = wp_json_encode($data['data']);
		}

		$result = $wpdb->insert(
			$this->leads_table,
			[
				'calculator_id' => $data['calculator_id'],
				'name' => isset($data['name']) ? $data['name'] : '',
				'email' => $data['email'],
				'phone' => isset($data['phone']) ? $data['phone'] : '',
				'company' => isset($data['company']) ? $data['company'] : '',
				'data' => isset($data['data']) ? $data['data'] : ''
			],
			['%d', '%s', '%s', '%s', '%s', '%s']
		);

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Obtener leads de una calculadora
	 *
	 * @param int $calculator_id ID de calculadora
	 * @param int $per_page Leads por página
	 * @param int $page_number Número de página
	 * @return array Lista de leads
	 */
	public function get_leads($calculator_id = 0, $per_page = 20, $page_number = 1)
	{
		global $wpdb;
		$sql = "SELECT * FROM {$this->leads_table}";

		if ($calculator_id > 0) {
			$sql .= $wpdb->prepare(" WHERE calculator_id = %d", $calculator_id);
		}

		$sql .= " ORDER BY created_at DESC";
		$sql .= $wpdb->prepare(" LIMIT %d OFFSET %d", $per_page, ($page_number - 1) * $per_page);

		return $wpdb->get_results($sql, ARRAY_A);
	}

	/**
	 * Contar leads
	 *
	 * @param int $calculator_id ID de calculadora
	 * @return int Total de leads
	 */
	public function count_leads($calculator_id = 0)
	{
		global $wpdb;
		$sql = "SELECT COUNT(*) FROM {$this->leads_table}";

		if ($calculator_id > 0) {
			$sql .= $wpdb->prepare(" WHERE calculator_id = %d", $calculator_id);
		}

		return (int) $wpdb->get_var($sql);
	}


	/**
	 * Crear o actualizar la página de una calculadora
	 *
	 * @param int $calculator_id ID de la calculadora
	 * @param array $data Datos de la calculadora
	 * @return int|false ID de la página o false en caso de error
	 */
	public function create_calculator_page($calculator_id, $data)
	{
		if (empty($data['name']) || empty($data['shortcode'])) {
			return false;
		}

		$calculator_name = sanitize_text_field($data['name']);
		$calculator_slug = sanitize_title($data['shortcode']);
		$calculator_description = isset($data['description']) ? wp_kses_post($data['description']) : '';

		// 1. Asegurarse de que existe la página padre "calculadoras"
		$parent = get_page_by_path('calculadoras', OBJECT, 'page');
		error_log(print_r($parent, true));
		if (!$parent) {
 			$parent_id = wp_insert_post([
				'post_title'    => 'Calculadoras Interactivas',
				'post_name'     => 'calculadoras',
				'post_status'   => 'publish',
				'post_type'     => 'page',
				'post_content'  => '<!-- Página principal de calculadoras interactivas -->',
			]);

			if (is_wp_error($parent_id)) {
				return false;
			}
		} else {
			$parent_id = $parent->ID;
		}

		// 2. Comprobar si ya existe una subpágina con ese slug
		$page_path = 'calculadoras/' . $calculator_slug;
		$existing_page = get_page_by_path($page_path, OBJECT, 'page');

		// Preparar el contenido de la página con el shortcode
		$shortcode = '[interactive_calculator id="' . $calculator_id . '"]';

		$content = '';
		if (!empty($calculator_description)) {
			$content .= '<div class="calculator-description">' . $calculator_description . '</div>';
		}
		$content .= '<!-- Calculadora Interactiva -->' . "\n";
		$content .= $shortcode;

		if ($existing_page) {
			// Actualizar la página existente
			$page_data = [
				'ID'            => $existing_page->ID,
				'post_title'    => $calculator_name,
				'post_content'  => $content,
				'post_status'   => 'publish',
			];

			$page_id = wp_update_post($page_data);
		} else {
			// Crear una nueva página
			$page_data = [
				'post_title'    => $calculator_name,
				'post_name'     => $calculator_slug,
				'post_content'  => $content,
				'post_status'   => 'publish',
				'post_type'     => 'page',
				'post_parent'   => $parent_id,
			];

			$page_id = wp_insert_post($page_data);
		}

		if (is_wp_error($page_id) || $page_id === 0) {
			return false;
		}

		// Actualizar la relación con la calculadora
		global $wpdb;
		$result = $wpdb->update(
			$this->calculators_table,
			['page_id' => $page_id],
			['id' => $calculator_id],
			['%d'],
			['%d']
		);

		if ($result === false) {
			// Si falla la actualización, registrar el error pero no afectar al usuario
			error_log('Error al actualizar la relación calculadora-página. Calculadora ID: ' . $calculator_id . ', Página ID: ' . $page_id);
		}

		return $page_id;
	}
}
