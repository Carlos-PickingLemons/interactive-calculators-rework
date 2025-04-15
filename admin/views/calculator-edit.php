<?php

/**
 * Vista para el formulario de creación/edición de calculadoras
 *
 * @package Interactive_Calculators
 */

if (!defined('ABSPATH')) {
	exit;
}

// Establecer título según si estamos editando o creando
$page_title = $is_edit ? __('Edit Calculator', 'interactive-calculators') : __('Add New Calculator', 'interactive-calculators');

// Preparar valores predeterminados para los campos
$calculator = wp_parse_args($calculator, [
	'id' => 0,
	'name' => '',
	'type' => 'standard',
	'description' => '',
	'shortcode' => '',
	'fields' => '[]',
	'settings' => '{}'
]);

// Preparar la configuración de la calculadora
$settings = [];
if (!empty($calculator['settings'])) {
	try {
		$settings = json_decode($calculator['settings'], true);
		if (json_last_error() !== JSON_ERROR_NONE) {
			$settings = [];
		}
	} catch (Exception $e) {
		$settings = [];
	}
}

// Asegurarse de que $settings sea un array
$settings = is_array($settings) ? $settings : [];
?>

<div class="wrap">
	<h1><?php echo esc_html($page_title); ?></h1>

	<?php if (!empty($errors)) : ?>
		<div class="notice notice-error">
			<p><strong><?php _e('Please fix the following errors:', 'interactive-calculators'); ?></strong></p>
			<ul>
				<?php foreach ($errors as $error) : ?>
					<li><?php echo esc_html($error); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<form method="post" action="" id="calculator-form">
		<?php wp_nonce_field('save_calculator'); ?>

		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
				<div id="post-body-content">
					<div class="postbox">
						<h2 class="hndle"><?php _e('Calculator Details', 'interactive-calculators'); ?></h2>
						<div class="inside">
							<table class="form-table">
								<tr>
									<th scope="row">
										<label for="calculator_name"><?php _e('Name', 'interactive-calculators'); ?> <span class="required">*</span></label>
									</th>
									<td>
										<input type="text" name="calculator_name" id="calculator_name" class="regular-text" value="<?php echo esc_attr($calculator['name']); ?>" required>
										<p class="description"><?php _e('The name of your calculator.', 'interactive-calculators'); ?></p>
									</td>
								</tr>

								<tr>
									<th scope="row">
										<label for="calculator_description"><?php _e('Description', 'interactive-calculators'); ?></label>
									</th>
									<td>
										<textarea name="calculator_description" id="calculator_description" class="large-text" rows="4"><?php echo esc_textarea($calculator['description']); ?></textarea>
										<p class="description"><?php _e('A brief description of what the calculator does (optional).', 'interactive-calculators'); ?></p>
									</td>
								</tr>

								<tr>
									<th scope="row">
										<label for="calculator_shortcode"><?php _e('Custom Shortcode', 'interactive-calculators'); ?></label>
									</th>
									<td>
										<input type="text" name="calculator_shortcode" id="calculator_shortcode" class="regular-text" disabled value="<?php echo esc_attr($calculator['shortcode']); ?>">
										<p class="description"><?php _e('Custom shortcode identifier (optional). If left blank, it will be generated from the name.', 'interactive-calculators'); ?></p>
									</td>
								</tr>
							</table>
						</div>
					</div>

					<div class="postbox">
						<h2 class="hndle"><?php _e('Calculator Input Fields', 'interactive-calculators'); ?></h2>
						<div class="inside">
							<p class="description">
								<?php _e('Define the input fields that users will see in your calculator.', 'interactive-calculators'); ?>
							</p>

							<!-- Tabla de campos -->
							<table class="widefat field-table" id="calculator-fields-table">
								<thead>
									<tr>
										<th><?php _e('Field Name', 'interactive-calculators'); ?> <span class="required">*</span></th>
										<th><?php _e('Label', 'interactive-calculators'); ?> <span class="required">*</span></th>
										<th><?php _e('Type', 'interactive-calculators'); ?> <span class="required">*</span></th>
										<th><?php _e('Required', 'interactive-calculators'); ?></th>
										<th><?php _e('Placeholder', 'interactive-calculators'); ?></th>
										<th><?php _e('Default', 'interactive-calculators'); ?></th>
										<th class="field-actions"><?php _e('Actions', 'interactive-calculators'); ?></th>
									</tr>
								</thead>
								<tbody>
									<!-- Los campos se añadirán aquí dinámicamente -->
									<tr class="no-fields <?php echo !empty($calculator['fields']) && $calculator['fields'] !== '[]' ? 'hidden' : ''; ?>">
										<td colspan="7"><?php _e('No fields added yet. Click "Add Field" to begin.', 'interactive-calculators'); ?></td>
									</tr>
								</tbody>
								<tfoot>
									<tr>
										<td colspan="7">
											<button type="button" class="button add-field">
												<span class="dashicons dashicons-plus-alt2"></span> <?php _e('Add Field', 'interactive-calculators'); ?>
											</button>
										</td>
									</tr>
								</tfoot>
							</table>

							<!-- Campo oculto para almacenar el JSON generado -->
							<input type="hidden" name="calculator_fields" id="calculator_fields_json" value="<?php echo esc_attr($calculator['fields']); ?>">
						</div>
					</div>

					<div class="postbox">
						<h2 class="hndle"><?php _e('Calculator Settings', 'interactive-calculators'); ?></h2>
						<div class="inside">
							<p class="description">
								<?php _e('Define the calculation settings.', 'interactive-calculators'); ?>
							</p>

							<table class="form-table">
								<tr>
									<th scope="row">
										<label for="formula"><?php _e('Formula', 'interactive-calculators'); ?> <span class="required">*</span></label>
									</th>
									<td>
										<textarea name="formula" id="formula" class="large-text code" rows="3" required><?php echo esc_textarea(isset($settings['formula']) ? $settings['formula'] : ''); ?></textarea>
										<p class="description"><?php _e('Enter the calculation formula using field names, e.g., "field1 + field2 * 100". You can use JavaScript Math functions like Math.pow(), Math.round(), etc.', 'interactive-calculators'); ?></p>
									</td>
								</tr>

								<tr>
									<th scope="row">
										<label for="result_prefix"><?php _e('Result Prefix', 'interactive-calculators'); ?></label>
									</th>
									<td>
										<input type="text" name="result_prefix" id="result_prefix" class="regular-text" value="<?php echo esc_attr(isset($settings['resultPrefix']) ? $settings['resultPrefix'] : ''); ?>">
										<p class="description"><?php _e('Text or symbol to show before the result (e.g., "$").', 'interactive-calculators'); ?></p>
									</td>
								</tr>

								<tr>
									<th scope="row">
										<label for="result_suffix"><?php _e('Result Suffix', 'interactive-calculators'); ?></label>
									</th>
									<td>
										<input type="text" name="result_suffix" id="result_suffix" class="regular-text" value="<?php echo esc_attr(isset($settings['resultSuffix']) ? $settings['resultSuffix'] : ''); ?>">
										<p class="description"><?php _e('Text or symbol to show after the result (e.g., "€").', 'interactive-calculators'); ?></p>
									</td>
								</tr>

								<tr>
									<th scope="row">
										<label for="decimal_places"><?php _e('Decimal Places', 'interactive-calculators'); ?></label>
									</th>
									<td>
										<input type="number" name="decimal_places" id="decimal_places" class="small-text" min="0" max="10" value="<?php echo esc_attr(isset($settings['decimalPlaces']) ? $settings['decimalPlaces'] : '2'); ?>">
										<p class="description"><?php _e('Number of decimal places to show in the result.', 'interactive-calculators'); ?></p>
									</td>
								</tr>

								<tr>
									<th scope="row">
										<label for="call_to_action"><?php _e('Call to Action', 'interactive-calculators'); ?></label>
									</th>
									<td>
										<input type="text" name="call_to_action" id="call_to_action" class="large-text" value="<?php echo esc_attr(isset($settings['callToAction']) ? $settings['callToAction'] : ''); ?>">
										<p class="description"><?php _e('Message shown above the lead capture form.', 'interactive-calculators'); ?></p>
									</td>
								</tr>

								<tr>
									<th scope="row">
										<label for="success_message"><?php _e('Success Message', 'interactive-calculators'); ?></label>
									</th>
									<td>
										<input type="text" name="success_message" id="success_message" class="large-text" value="<?php echo esc_attr(isset($settings['successMessage']) ? $settings['successMessage'] : ''); ?>">
										<p class="description"><?php _e('Message shown after a user submits their email.', 'interactive-calculators'); ?></p>
									</td>
								</tr>
							</table>

							<!-- Campo oculto para almacenar la configuración JSON generada -->
							<input type="hidden" name="calculator_settings" id="calculator_settings_json" value="<?php echo esc_attr($calculator['settings']); ?>">
						</div>
					</div>
				</div>

				<div id="postbox-container-1" class="postbox-container">
					<div class="postbox">
						<h2 class="hndle"><?php _e('Actions', 'interactive-calculators'); ?></h2>
						<div class="inside">
							<div class="submitbox">
								<div id="major-publishing-actions">
									<div id="publishing-action">
										<input type="submit" name="save_calculator" id="save_calculator" class="button button-primary button-large" value="<?php echo $is_edit ? esc_attr__('Update Calculator', 'interactive-calculators') : esc_attr__('Create Calculator', 'interactive-calculators'); ?>">
									</div>
									<div class="clear"></div>
								</div>
							</div>
						</div>
					</div>

					<?php if ($is_edit) : ?>
						<div class="postbox">
							<h2 class="hndle"><?php _e('Calculator Information', 'interactive-calculators'); ?></h2>
							<div class="inside">
								<div class="misc-pub-section">
									<strong><?php _e('ID:', 'interactive-calculators'); ?></strong>
									<?php echo intval($calculator['id']); ?>
								</div>
								<?php if (!empty($calculator['created_at'])) : ?>
									<div class="misc-pub-section">
										<strong><?php _e('Created:', 'interactive-calculators'); ?></strong>
										<?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($calculator['created_at'])); ?>
									</div>
								<?php endif; ?>
								<?php if (!empty($calculator['updated_at'])) : ?>
									<div class="misc-pub-section">
										<strong><?php _e('Last Updated:', 'interactive-calculators'); ?></strong>
										<?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($calculator['updated_at'])); ?>
									</div>
								<?php endif; ?>
							</div>
						</div>

						<div class="postbox">
							<h2 class="hndle"><?php _e('Shortcode', 'interactive-calculators'); ?></h2>
							<div class="inside">
								<p><?php _e('Use this shortcode to display the calculator:', 'interactive-calculators'); ?></p>
								<div class="shortcode-container">
									<code id="calculator-shortcode">[interactive_calculator id="<?php echo intval($calculator['id']); ?>"]</code>
									<button type="button" class="button button-small copy-shortcode" data-clipboard-target="#calculator-shortcode">
										<span class="dashicons dashicons-clipboard"></span> <?php _e('Copy', 'interactive-calculators'); ?>
									</button>
								</div>
							</div>
						</div>
					<?php endif; ?>

					<div class="postbox">
						<h2 class="hndle"><?php _e('Help', 'interactive-calculators'); ?></h2>
						<div class="inside">
							<p><?php _e('Create an interactive calculator by defining its input fields and calculation settings.', 'interactive-calculators'); ?></p>

							<p><strong><?php _e('Input Fields:', 'interactive-calculators'); ?></strong></p>
							<p><?php _e('Define the fields users will see in your calculator. Each field should have:', 'interactive-calculators'); ?></p>
							<ul class="ul-disc">
								<li><code>name</code>: <?php _e('A unique identifier (used in formulas)', 'interactive-calculators'); ?></li>
								<li><code>label</code>: <?php _e('The field label shown to users', 'interactive-calculators'); ?></li>
								<li><code>type</code>: <?php _e('Field type (number, text, select, etc.)', 'interactive-calculators'); ?></li>
								<li><code>required</code>: <?php _e('Whether the field is mandatory', 'interactive-calculators'); ?></li>
							</ul>

							<p><strong><?php _e('Formula:', 'interactive-calculators'); ?></strong></p>
							<p><?php _e('Use field names in your formula. Example:', 'interactive-calculators'); ?></p>
							<ul class="ul-disc">
								<li><?php _e('If you have fields named "price" and "quantity", your formula could be:', 'interactive-calculators'); ?></li>
								<li><code>price * quantity</code></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>

<style>
	.field-table {
		margin-bottom: 20px;
		border-collapse: collapse;
	}
	.field-table th {
		text-align: left;
		padding: 8px;
	}
	.field-table td {
		padding: 8px;
		vertical-align: middle;
	}
	.field-table .field-actions {
		width: 50px;
		text-align: center;
	}
	.field-table input[type="text"] {
		width: 100%;
	}
	.field-table .remove-field {
		color: #b32d2e;
	}
	.field-table .remove-field:hover {
		color: #dc3232;
	}
	.field-table .no-fields {
		text-align: center;
		color: #777;
		font-style: italic;
	}
	.field-table .no-fields.hidden {
		display: none;
	}
	.required {
		color: #d63638;
	}
	.shortcode-container {
		display: flex;
		align-items: center;
		background: #f9f9f9;
		padding: 10px;
		border: 1px solid #ddd;
		border-radius: 3px;
	}
	.shortcode-container code {
		flex-grow: 1;
		background: transparent;
		padding: 0;
	}
	.copy-shortcode {
		margin-left: 10px;
	}
	#formula {
		font-family: monospace;
	}
</style>

<script>
	jQuery(document).ready(function($) {
		var nextFieldId = 1;
		var fieldTable = $('#calculator-fields-table');
		var fieldsJson = $('#calculator_fields_json');
		var settingsJson = $('#calculator_settings_json');

		// Tipos de campos disponibles
		var fieldTypes = {
			'number': 'Número',
			'text': 'Texto',
			'select': 'Desplegable',
			'checkbox': 'Casilla',
			'radio': 'Botones de opción'
		};

		// Inicializar fields JSON
		function initFieldsJson() {
			try {
				if (fieldsJson.val() && fieldsJson.val() !== '[]') {
					var fields = JSON.parse(fieldsJson.val());
					if (Array.isArray(fields) && fields.length > 0) {
						$.each(fields, function(index, field) {
							addFieldRow(field);
						});
						return;
					}
				}
			} catch (e) {
				console.error('Error al analizar JSON de campos:', e);
			}

			fieldsJson.val('[]');
			addFieldRow({
				name: '',
				label: '',
				type: '',
				required: false,
				placeholder: '',
				default: ''
			});
		}

		// Inicializar settings JSON
		function initSettingsJson() {
			try {
				if (settingsJson.val() && settingsJson.val() !== '{}') {
					var settings = JSON.parse(settingsJson.val());
					if (settings.formula) $('#formula').val(settings.formula);
					if (settings.resultPrefix) $('#result_prefix').val(settings.resultPrefix);
					if (settings.resultSuffix) $('#result_suffix').val(settings.resultSuffix);
					if (settings.decimalPlaces) $('#decimal_places').val(settings.decimalPlaces);
					if (settings.callToAction) $('#call_to_action').val(settings.callToAction);
					if (settings.successMessage) $('#success_message').val(settings.successMessage);
					return;
				}
			} catch (e) {
				console.error('Error al analizar JSON de configuración:', e);
			}

			var defaultSettings = {
				formula: '',
				resultPrefix: '',
				resultSuffix: '€',
				decimalPlaces: 2,
				callToAction: 'Deja tu email para recibir más información',
				successMessage: '¡Gracias! Te contactaremos pronto.'
			};

			$('#formula').val(defaultSettings.formula);
			$('#result_prefix').val(defaultSettings.resultPrefix);
			$('#result_suffix').val(defaultSettings.resultSuffix);
			$('#decimal_places').val(defaultSettings.decimalPlaces);
			$('#call_to_action').val(defaultSettings.callToAction);
			$('#success_message').val(defaultSettings.successMessage);

			updateSettingsJson();
		}

		// Inicializar campos
		initFieldsJson();
		initSettingsJson();

		// Botón de añadir campo
		fieldTable.on('click', '.add-field', function() {
			addFieldRow();
		});

		// Botón de eliminar campo
		fieldTable.on('click', '.remove-field', function() {
			$(this).closest('tr').remove();
			updateFieldsJson();

			if (fieldTable.find('tbody tr').not('.no-fields').length === 0) {
				fieldTable.find('.no-fields').removeClass('hidden');
			}
		});

		// Actualizar JSON cuando cambia cualquier campo
		fieldTable.on('change', 'input, select', function() {
			updateFieldsJson();
		});

		// Actualizar JSON de configuración cuando cambian los campos
		$('textarea[name="formula"], input[name^="result_"], input[name^="decimal_places"], input[name^="call_to_action"], input[name^="success_message"]').on('change input', function() {
			updateSettingsJson();
		});

		// Función para añadir una fila de campo
		function addFieldRow(fieldData) {
			fieldTable.find('.no-fields').addClass('hidden');
			var fieldId = 'field_' + nextFieldId++;
			var field = fieldData || {};
			var row = $('<tr class="field-row"></tr>');

			// Nombre del campo
			row.append('<td><input type="text" class="field-name" value="' +
				(field.name || '') + '" placeholder="campo_nombre" required></td>');

			// Etiqueta
			row.append('<td><input type="text" class="field-label" value="' +
				(field.label || '') + '" placeholder="Etiqueta visible" required></td>');

			// Tipo de campo
			var typeSelect = $('<select class="field-type"></select>');
			$.each(fieldTypes, function(value, label) {
				typeSelect.append($('<option></option>').attr('value', value).text(label));
			});
			if (field.type) {
				typeSelect.val(field.type);
			}
			row.append($('<td></td>').append(typeSelect));

			// Campo obligatorio
			var isRequired = field.required || false;
			row.append('<td><input type="checkbox" class="field-required" ' +
				(isRequired ? 'checked' : '') + '></td>');

			// Placeholder
			row.append('<td><input type="text" class="field-placeholder" value="' +
				(field.placeholder || '') + '" placeholder="Texto de ayuda"></td>');

			// Valor por defecto
			row.append('<td><input type="text" class="field-default" value="' +
				(field.default || '') + '" placeholder="Valor por defecto"></td>');

			// Acciones
			row.append('<td class="field-actions"><button type="button" class="button button-small remove-field">' +
				'<span class="dashicons dashicons-trash"></span></button></td>');

			fieldTable.find('tbody').append(row);
			updateFieldsJson();
		}

		// Función para actualizar el JSON de campos
		function updateFieldsJson() {
			var fields = [];

			fieldTable.find('tbody tr.field-row').each(function() {
				var row = $(this);
				var field = {
					"name": row.find('.field-name').val() || 'campo_default',
					"label": row.find('.field-label').val() || 'Campo',
					"type": row.find('.field-type').val() || 'number',
					"required": row.find('.field-required').is(':checked'),
					"placeholder": row.find('.field-placeholder').val() || '',
					"default": row.find('.field-default').val() || ''
				};
				fields.push(field);
			});

			var jsonStr = JSON.stringify(fields);
			$('#calculator_fields_json').val(jsonStr);
		}

		// Función para actualizar el JSON de configuración
		function updateSettingsJson() {
			var settings = {
				formula: $('#formula').val() || 'campo1',
				resultPrefix: $('#result_prefix').val() || '',
				resultSuffix: $('#result_suffix').val() || '',
				decimalPlaces: parseInt($('#decimal_places').val()) || 2,
				callToAction: $('#call_to_action').val() || '',
				successMessage: $('#success_message').val() || ''
			};

			settingsJson.val(JSON.stringify(settings));
		}

		// Validar formulario
		$('#calculator-form').on('submit', function(e) {
			if (!$('#calculator_name').val()) {
				e.preventDefault();
				alert('Por favor, ingresa un nombre para la calculadora.');
				return false;
			}

			updateFieldsJson();
			updateSettingsJson();
			return true;
		});

		// Copiar shortcode al portapapeles
		$('.copy-shortcode').on('click', function() {
			var tempTextarea = $('<textarea>');
			$('body').append(tempTextarea);
			tempTextarea.val($($(this).data('clipboard-target')).text()).select();
			document.execCommand('copy');
			tempTextarea.remove();

			var $button = $(this);
			var originalText = $button.html();
			$button.html('<span class="dashicons dashicons-yes"></span> Copiado!');
			setTimeout(function() {
				$button.html(originalText);
			}, 2000);
		});
	});
</script>
