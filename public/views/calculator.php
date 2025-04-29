<?php

/**
 * Vista de calculadora interactiva con captación de email antes de mostrar resultados
 *
 * @package Interactive_Calculators
 */

// Si se accede directamente, abortar
if (!defined('ABSPATH')) {
	exit;
}

// Variables disponibles
$calculator_id = intval($calculator['id']);
$calculator_name = esc_html($calculator['name']);
$formula = isset($settings['formula']) ? $settings['formula'] : '';
$result_prefix = isset($settings['resultPrefix']) ? $settings['resultPrefix'] : '';
$result_suffix = isset($settings['resultSuffix']) ? $settings['resultSuffix'] : '';
$decimal_places = isset($settings['decimalPlaces']) ? intval($settings['decimalPlaces']) : 2;
$call_to_action = isset($settings['callToAction']) ? $settings['callToAction'] : '¡Déjanos tu email para ver el resultado!';
$success_message = isset($settings['successMessage']) ? $settings['successMessage'] : 'Gracias por tu interés. Hemos enviado más información a tu correo.';

// Generar ID único para esta instancia
$unique_id = 'calculator-' . $calculator_id . '-' . uniqid();

// Determinar el tipo de calculadora para aplicar el color
$calculator_type = isset($calculator['type']) ? $calculator['type'] : 'standard';
$type_colors = [
	'productivity' => '#4CAF50',  // Verde
	'bottleneck' => '#F44336',    // Rojo
	'sales' => '#2196F3',         // Azul
	'capital' => '#FFC107',       // Ámbar
	'culture' => '#9C27B0',       // Púrpura
	'time' => '#FF5722',          // Naranja
	'roi' => '#3F51B5',           // Índigo
	'standard' => '#607D8B'       // Azul grisáceo
];
$calc_color = isset($type_colors[$calculator_type]) ? $type_colors[$calculator_type] : $type_colors['standard'];
?>

<div class="interactive-calculator calculator-<?php echo $calculator_id; ?>" id="<?php echo $unique_id; ?>">
	<div class="calculator-header">
		<div class="calculator-icon-wrapper" style="background-color: <?php echo $calc_color; ?>">
			<span class="calculator-icon dashicons
                <?php
				switch ($calculator_type) {
					case 'productivity':
						echo 'dashicons-chart-line';
						break;
					case 'bottleneck':
						echo 'dashicons-filter';
						break;
					case 'sales':
						echo 'dashicons-chart-bar';
						break;
					case 'capital':
						echo 'dashicons-money-alt';
						break;
					case 'culture':
						echo 'dashicons-groups';
						break;
					case 'time':
						echo 'dashicons-clock';
						break;
					case 'roi':
						echo 'dashicons-performance';
						break;
					default:
						echo 'dashicons-calculator';
				}
				?>
            "></span>
		</div>
		<div class="calculator-header-content">
			<h2 class="calculator-title"><?php echo $calculator_name; ?></h2>
			<?php if (!empty($calculator['description'])): ?>
				<div class="calculator-description"><?php echo wp_kses_post($calculator['description']); ?></div>
			<?php endif; ?>
		</div>
	</div>

	<div class="calculator-body">
		<!-- Paso 1: Formulario de datos -->
		<div class="calculator-step step-1 active">
			<form class="calculator-form" method="post">
				<div class="calculator-fields">
					<?php foreach ($fields as $field):
						$field_id = $unique_id . '-' . sanitize_key($field['name']);
						$field_name = sanitize_key($field['name']);
						$field_label = esc_html($field['label']);
						$field_type = esc_attr($field['type']);
						$field_required = !empty($field['required']);
						$field_placeholder = esc_attr($field['placeholder'] ?? '');
						$field_default = esc_attr($field['default'] ?? '');
					?>
						<div class="calculator-field field-type-<?php echo $field_type; ?>">
							<label for="<?php echo $field_id; ?>" class="field-label">
								<?php echo $field_label; ?>
								<?php if ($field_required): ?>
									<span class="required">*</span>
								<?php endif; ?>
							</label>

							<div class="input-wrapper">
								<?php switch ($field_type):
									case 'number': ?>
										<input type="number"
											id="<?php echo $field_id; ?>"
											name="<?php echo $field_name; ?>"
											class="calculator-input"
											value="<?php echo $field_default; ?>"
											placeholder="<?php echo $field_placeholder; ?>"
											<?php echo $field_required ? 'required' : ''; ?>>
									<?php break;

									case 'text': ?>
										<input type="text"
											id="<?php echo $field_id; ?>"
											name="<?php echo $field_name; ?>"
											class="calculator-input"
											value="<?php echo $field_default; ?>"
											placeholder="<?php echo $field_placeholder; ?>"
											<?php echo $field_required ? 'required' : ''; ?>>
									<?php break;

									case 'select':
										$options = $field['options'] ?? [];
									?>
										<div class="select-wrapper">
											<select
												id="<?php echo $field_id; ?>"
												name="<?php echo $field_name; ?>"
												class="calculator-input"
												<?php echo $field_required ? 'required' : ''; ?>>
												<option value=""><?php _e('Seleccione una opción', 'interactive-calculators'); ?></option>
												<?php foreach ($options as $option): ?>
													<option value="<?php echo esc_attr($option['value']); ?>" <?php selected($field_default, $option['value']); ?>>
														<?php echo esc_html($option['label']); ?>
													</option>
												<?php endforeach; ?>
											</select>
										</div>
									<?php break;

									case 'checkbox': ?>
										<label class="checkbox-container">
											<input type="checkbox"
												id="<?php echo $field_id; ?>"
												name="<?php echo $field_name; ?>"
												class="calculator-input"
												value="1"
												<?php checked($field_default, '1'); ?>
												<?php echo $field_required ? 'required' : ''; ?>>
											<span class="checkmark"></span>
										</label>
									<?php break;

									default: ?>
										<input type="text"
											id="<?php echo $field_id; ?>"
											name="<?php echo $field_name; ?>"
											class="calculator-input"
											value="<?php echo $field_default; ?>"
											placeholder="<?php echo $field_placeholder; ?>"
											<?php echo $field_required ? 'required' : ''; ?>>
								<?php endswitch; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

				<div class="calculator-actions">
					<button type="button" class="calculate-button" style="--calc-color: <?php echo $calc_color; ?>">
						<span class="button-icon dashicons dashicons-calculator"></span>
						<span class="button-text"><?php _e('Calcular', 'interactive-calculators'); ?></span>
					</button>
				</div>
			</form>
		</div>

		<!-- Paso 2: Formulario de email -->
		<div class="calculator-step step-2">
			<div class="email-capture-container">
				<div class="email-capture-header">
					<div class="capture-icon" style="--calc-color: <?php echo $calc_color; ?>">
						<span class="dashicons dashicons-email-alt"></span>
					</div>
					<h3><?php echo esc_html($call_to_action); ?></h3>
					<p class="email-subtitle"><?php _e('Estamos procesando tu resultado...', 'interactive-calculators'); ?></p>
				</div>

				<form class="email-form">
					<input type="hidden" name="calculator_id" value="<?php echo $calculator_id; ?>">
					<input type="hidden" name="calculator_data" class="calculator-data" value="">
					<input type="hidden" name="calculated_result" class="calculated-result" value="">

					<div class="form-group email-input-group">
						<div class="input-with-icon">
							<span class="input-icon dashicons dashicons-email-alt"></span>
							<input type="email" name="lead_email" placeholder="<?php _e('Tu correo electrónico', 'interactive-calculators'); ?>" required>
						</div>
					</div>

					<div class="privacy-notice">
						<?php _e('No compartiremos tu email con terceros.', 'interactive-calculators'); ?>
					</div>

					<div class="form-actions">
						<button type="submit" class="submit-email" style="--calc-color: <?php echo $calc_color; ?>">
							<span class="button-text"><?php _e('Ver resultado', 'interactive-calculators'); ?></span>
							<span class="button-icon dashicons dashicons-arrow-right-alt"></span>
						</button>
					</div>
				</form>
			</div>
		</div>

		<!-- Paso 3: Resultado -->
		<div class="calculator-step step-3">
			<div class="result-container">
				<div class="result-header">
					<div class="result-icon-wrapper" style="--calc-color: <?php echo $calc_color; ?>">
						<span class="result-icon dashicons dashicons-yes-alt"></span>
					</div>
					<h3 class="result-title"><?php _e('¡Aquí está tu resultado!', 'interactive-calculators'); ?></h3>
				</div>

				<div class="result-value-container">
					<div class="result-label"><?php _e('Resultado:', 'interactive-calculators'); ?></div>
					<div class="result-value" style="--calc-color: <?php echo $calc_color; ?>">
						<span class="result-prefix"><?php echo $result_prefix; ?></span>
						<span class="result-number">0</span>
						<span class="result-suffix"><?php echo $result_suffix; ?></span>
					</div>
				</div>

				<div class="result-message">
					<?php _e('Gracias por usar nuestra calculadora. Hemos enviado más información a tu correo electrónico.', 'interactive-calculators'); ?>
				</div>

				<div class="result-actions">
					<button type="button" class="restart-calculator" style="--calc-color: <?php echo $calc_color; ?>">
						<span class="button-icon dashicons dashicons-controls-repeat"></span>
						<span class="button-text"><?php _e('Realizar otro cálculo', 'interactive-calculators'); ?></span>
					</button>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	(function() {
		document.addEventListener('DOMContentLoaded', function() {
			var calculatorEl = document.getElementById('<?php echo $unique_id; ?>');
			if (!calculatorEl) return;

			// Elementos
			var form = calculatorEl.querySelector('.calculator-form');
			var calculateBtn = calculatorEl.querySelector('.calculate-button');
			var emailForm = calculatorEl.querySelector('.email-form');
			var submitEmailBtn = calculatorEl.querySelector('.submit-email');
			var resultValueEl = calculatorEl.querySelector('.result-number');
			var resultCalculatedEl = calculatorEl.querySelector('.calculated-result');
			var calculatorDataEl = calculatorEl.querySelector('.calculator-data');
			var restartBtn = calculatorEl.querySelector('.restart-calculator');

			// Pasos
			var step1 = calculatorEl.querySelector('.step-1');
			var step2 = calculatorEl.querySelector('.step-2');
			var step3 = calculatorEl.querySelector('.step-3');

			// Variables para almacenar el resultado
			var calculationResult = 0;
			var calculationData = {};

			// Añadir animación al cargar la calculadora
			calculatorEl.classList.add('loaded');

			// Botón calcular (Paso 1 -> Paso 2)
			calculateBtn.addEventListener('click', function() {
				var isValid = true;
				var fields = form.querySelectorAll('.calculator-input');
				var fieldValues = {};

				// Validar formulario y recoger valores
				fields.forEach(function(field) {
					if (field.required && !field.value) {
						isValid = false;
						field.classList.add('error');
						field.closest('.calculator-field').classList.add('has-error');
					} else {
						field.classList.remove('error');
						field.closest('.calculator-field').classList.remove('has-error');

						// Guardar valor del campo
						var name = field.name;
						var value = field.type === 'checkbox' ? (field.checked ? 1 : 0) : field.value;

						// Convertir a número si es posible
						if (!isNaN(value) && value !== '') {
							value = parseFloat(value);
						}

						fieldValues[name] = value;
					}
				});

				if (!isValid) {
					alert('<?php _e('Por favor, complete todos los campos requeridos.', 'interactive-calculators'); ?>');
					return;
				}

				// Mostrar efecto de carga
				calculateBtn.classList.add('loading');

				// Pequeño retardo para efecto visual
				setTimeout(function() {
					// Calcular resultado usando la fórmula
					try {
						var formula = '<?php echo esc_js($formula); ?>';

						// Reemplazar nombres de campos en la fórmula con valores
						for (var fieldName in fieldValues) {
							var regex = new RegExp(fieldName, 'g');
							formula = formula.replace(regex, fieldValues[fieldName]);
						}

						// Evaluar fórmula (con seguridad)
						var result = Function('"use strict"; return (' + formula + ')')();

						// Formatear resultado
						calculationResult = parseFloat(result).toFixed(<?php echo $decimal_places; ?>);
						calculationData = fieldValues;

						// Almacenar datos para formulario de email
						if (calculatorDataEl) {
							calculatorDataEl.value = JSON.stringify(fieldValues);
						}

						if (resultCalculatedEl) {
							resultCalculatedEl.value = calculationResult;
						}

						// Pasar al paso 2 (formulario de email)
						step1.classList.remove('active');
						step2.classList.add('active');

						// Quitar efecto de carga
						calculateBtn.classList.remove('loading');

					} catch (e) {
						console.error('Error de cálculo:', e);
						alert('<?php _e('Error al realizar el cálculo. Por favor, intente nuevamente.', 'interactive-calculators'); ?>');
						calculateBtn.classList.remove('loading');
					}
				}, 800);
			});

			// Formulario de email (Paso 2 -> Paso 3)
			if (emailForm) {
				emailForm.addEventListener('submit', function(e) {
					e.preventDefault();

					// Validar email
					var emailInput = this.querySelector('input[type="email"]');
					if (!emailInput.value) {
						emailInput.classList.add('error');
						return;
					}

					// Efecto de carga
					submitEmailBtn.classList.add('loading');

					// Aquí normalmente se enviaría el email al servidor
					// Por ahora solo simulamos y mostramos el resultado
					setTimeout(function() {
						// Actualizar el resultado en el paso 3
						resultValueEl.textContent = calculationResult;

						// Mostrar el paso 3
						step2.classList.remove('active');
						step3.classList.add('active');

						// Animación de conteo
						var currentValue = 0;
						var duration = 1500; // ms
						var startTime = null;

						function animateValue(timestamp) {
							if (!startTime) startTime = timestamp;
							var progress = timestamp - startTime;
							var percentage = Math.min(progress / duration, 1);

							// Función de easing
							var easing = function(t) {
								return t < .5 ? 4 * t * t * t : (t - 1) * (2 * t - 2) * (2 * t - 2) + 1;
							};

							currentValue = easing(percentage) * parseFloat(calculationResult);
							resultValueEl.textContent = parseFloat(currentValue).toFixed(<?php echo $decimal_places; ?>);

							if (percentage < 1) {
								requestAnimationFrame(animateValue);
							} else {
								resultValueEl.textContent = calculationResult;
							}
						}

						// Comenzar animación
						requestAnimationFrame(animateValue);

						// Quitar efecto de carga
						submitEmailBtn.classList.remove('loading');
					}, 1500);
				});
			}

			// Botón reiniciar (Paso 3 -> Paso 1)
			if (restartBtn) {
				restartBtn.addEventListener('click', function() {
					// Restablecer formulario
					form.reset();

					// Volver al paso 1
					step3.classList.remove('active');
					step1.classList.add('active');

					// Scroll al inicio de la calculadora
					calculatorEl.scrollIntoView({
						behavior: 'smooth',
						block: 'start'
					});
				});
			}
		});
	})();
</script>

<style>
	/* Variables personalizadas */
	:root {
		--calc-color: <?php echo $calc_color; ?>;
		--calc-color-light: color-mix(in srgb, var(--calc-color) 30%, white);
		--calc-color-dark: color-mix(in srgb, var(--calc-color) 70%, black);
		--background: #ffffff;
		--card-bg: #f8f9fa;
		--text-primary: #333333;
		--text-secondary: #555555;
		--text-muted: #6c757d;
		--border-radius: 16px;
		--shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.05);
		--shadow-md: 0 5px 15px rgba(0, 0, 0, 0.08);
		--shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.12);
		--transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
	}

	/* Estilos base */
	.interactive-calculator {
		max-width: 850px;
		margin: 0 auto 50px;
		background: var(--background);
		border-radius: var(--border-radius);
		box-shadow: var(--shadow-lg);
		overflow: hidden;
		transition: var(--transition);
		opacity: 0;
		transform: translateY(20px);
		font-family: 'Poppins', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;
	}

	.interactive-calculator.loaded {
		opacity: 1;
		transform: translateY(0);
	}

	/* Header */
	.calculator-header {
		padding: 26px 32px;
		background: linear-gradient(to right, color-mix(in srgb, var(--calc-color) 90%, white), color-mix(in srgb, var(--calc-color) 70%, white));
		position: relative;
		display: flex;
		align-items: center;
		border-bottom: none;
		color: white;
	}

	.calculator-header:after {
		content: '';
		position: absolute;
		bottom: 0;
		left: 10%;
		width: 80%;
		height: 3px;
		background: white;
		border-radius: 3px;
		opacity: 0.3;
	}

	.calculator-icon-wrapper {
		width: 64px;
		height: 64px;
		border-radius: 50%;
		background: rgba(255, 255, 255, 0.9);
		display: flex;
		align-items: center;
		justify-content: center;
		margin-right: 20px;
		flex-shrink: 0;
		box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
		position: relative;
		overflow: hidden;
	}

	.calculator-icon-wrapper:after {
		content: '';
		position: absolute;
		width: 100%;
		height: 100%;
		top: 0;
		left: 0;
		background: radial-gradient(circle, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 0.4) 100%);
	}

	.calculator-icon {
		color: var(--calc-color);
		font-size: 28px;
		position: relative;
		z-index: 2;
	}

	.calculator-header-content {
		flex-grow: 1;
	}

	.calculator-title {
		font-size: 26px;
		font-weight: 700;
		margin: 0 0 5px;
		color: white;
		line-height: 1.3;
		text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
	}

	.calculator-description {
		color: rgba(255, 255, 255, 0.9);
		font-size: 15px;
		line-height: 1.5;
		font-weight: 300;
	}

	/* Body */
	.calculator-body {
		padding: 35px;
		position: relative;
		background: linear-gradient(135deg, white, var(--card-bg));
	}

	/* Pasos */
	.calculator-step {
		display: none;
		opacity: 0;
		transform: translateY(20px);
		transition: var(--transition);
	}

	.calculator-step.active {
		display: block;
		animation: fadeIn 0.5s forwards;
	}

	@keyframes fadeIn {
		from {
			opacity: 0;
			transform: translateY(20px);
		}

		to {
			opacity: 1;
			transform: translateY(0);
		}
	}

	/* Campos */
	.calculator-fields {
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
		gap: 26px;
		margin-bottom: 35px;
	}

	.calculator-field {
		position: relative;
		transition: var(--transition);
	}

	.calculator-field.has-error {
		animation: shake 0.5s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
	}

	@keyframes shake {

		10%,
		90% {
			transform: translateX(-1px);
		}

		20%,
		80% {
			transform: translateX(2px);
		}

		30%,
		50%,
		70% {
			transform: translateX(-3px);
		}

		40%,
		60% {
			transform: translateX(3px);
		}
	}

	.field-label {
		display: block;
		margin-bottom: 10px;
		font-weight: 500;
		font-size: 15px;
		color: var(--text-secondary);
		transition: var(--transition);
	}

	.required {
		color: #ff5252;
		margin-left: 3px;
	}

	.input-wrapper {
		position: relative;
	}

	.calculator-input {
		width: 100%;
		padding: 14px 18px;
		border: 2px solid #e0e0e0;
		border-radius: 12px;
		font-size: 15px;
		transition: var(--transition);
		box-shadow: var(--shadow-sm);
		background-color: white;
	}

	.calculator-input:focus {
		border-color: var(--calc-color);
		box-shadow: 0 0 0 4px var(--calc-color-light);
		outline: none;
	}

	.calculator-input.error {
		border-color: #ff5252;
		box-shadow: 0 0 0 3px rgba(255, 82, 82, 0.1);
	}

	/* Select personalizado */
	.select-wrapper {
		position: relative;
	}

	.select-wrapper:after {
		content: '\f140';
		font-family: dashicons;
		position: absolute;
		right: 18px;
		top: 50%;
		transform: translateY(-50%);
		pointer-events: none;
		color: #777;
		transition: var(--transition);
	}

	.select-wrapper select {
		appearance: none;
		padding-right: 40px;
		cursor: pointer;
	}

	.select-wrapper:hover:after {
		color: var(--calc-color);
	}

	/* Checkbox personalizado */
	.checkbox-container {
		display: block;
		position: relative;
		padding-left: 35px;
		cursor: pointer;
		user-select: none;
		height: 25px;
		line-height: 25px;
	}

	.checkbox-container input {
		position: absolute;
		opacity: 0;
		cursor: pointer;
		height: 0;
		width: 0;
	}

	.checkmark {
		position: absolute;
		top: 0;
		left: 0;
		height: 25px;
		width: 25px;
		background-color: #f5f5f5;
		border: 2px solid #e0e0e0;
		border-radius: 6px;
		transition: var(--transition);
	}

	.checkbox-container:hover input~.checkmark {
		background-color: #f0f0f0;
		border-color: #d0d0d0;
	}

	.checkbox-container input:checked~.checkmark {
		background-color: var(--calc-color);
		border-color: var(--calc-color);
	}

	.checkmark:after {
		content: "";
		position: absolute;
		display: none;
	}

	.checkbox-container input:checked~.checkmark:after {
		display: block;
	}

	.checkbox-container .checkmark:after {
		left: 9px;
		top: 5px;
		width: 5px;
		height: 10px;
		border: solid white;
		border-width: 0 2px 2px 0;
		transform: rotate(45deg);
	}

	/* Botones */
	.calculator-actions,
	.form-actions {
		margin: 20px 0 10px;
		text-align: center;
	}

	.calculate-button,
	.submit-email,
	.restart-calculator {
		background: linear-gradient(135deg, var(--calc-color), var(--calc-color-dark));
		color: white;
		border: none;
		border-radius: 12px;
		padding: 16px 32px;
		font-size: 16px;
		font-weight: 600;
		cursor: pointer;
		transition: var(--transition);
		display: inline-flex;
		align-items: center;
		justify-content: center;
		box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15), 0 1px 3px rgba(0, 0, 0, 0.05);
		position: relative;
		overflow: hidden;
		letter-spacing: 0.5px;
	}

	.calculate-button:before,
	.submit-email:before,
	.restart-calculator:before {
		content: '';
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		background: linear-gradient(to bottom, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 100%);
	}

	.calculate-button:hover,
	.submit-email:hover,
	.restart-calculator:hover {
		transform: translateY(-3px);
		box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2), 0 4px 6px rgba(0, 0, 0, 0.1);
		background: linear-gradient(135deg, var(--calc-color-dark), var(--calc-color));
	}

	.calculate-button:active,
	.submit-email:active,
	.restart-calculator:active {
		transform: translateY(0);
		box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
	}

	.button-icon {
		margin-right: 12px;
		font-size: 18px;
	}

	.submit-email .button-icon {
		margin-right: 0;
		margin-left: 12px;
	}

	.calculate-button.loading,
	.submit-email.loading {
		padding-right: 50px;
	}

	.calculate-button.loading:after,
	.submit-email.loading:after {
		content: '';
		position: absolute;
		right: 18px;
		top: 50%;
		transform: translateY(-50%);
		width: 20px;
		height: 20px;
		border: 2px solid rgba(255, 255, 255, 0.3);
		border-top: 2px solid #fff;
		border-radius: 50%;
		animation: spin 0.8s linear infinite;
	}

	@keyframes spin {
		0% {
			transform: translateY(-50%) rotate(0deg);
		}

		100% {
			transform: translateY(-50%) rotate(360deg);
		}
	}

	/* Email Capture (Paso 2) */
	.email-capture-container {
		max-width: 500px;
		margin: 0 auto;
		text-align: center;
		padding: 30px;
		background: white;
		border-radius: var(--border-radius);
		box-shadow: var(--shadow-md);
		position: relative;
		overflow: hidden;
	}

	.email-capture-container:before {
		content: '';
		position: absolute;
		top: 0;
		left: 0;
		right: 0;
		height: 5px;
		background: linear-gradient(to right, var(--calc-color), var(--calc-color-dark));
	}

	.email-capture-header {
		margin-bottom: 30px;
	}

	.capture-icon {
		width: 80px;
		height: 80px;
		border-radius: 50%;
		background: linear-gradient(135deg, var(--calc-color), var(--calc-color-dark));
		display: flex;
		align-items: center;
		justify-content: center;
		margin: 0 auto 20px;
		box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
		position: relative;
		overflow: hidden;
	}

	.capture-icon .dashicons {
		font-size: 40px !important;
		width: fit-content;
		height: fit-content;
	}

	.capture-icon:after {
		content: '';
		position: absolute;
		width: 80px;
		height: 80px;
		top: 0;
		left: 0;
		background: radial-gradient(circle, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 0.2) 100%);
	}

	.capture-icon .dashicons {
		color: white;
		font-size: 34px;
		position: relative;
		z-index: 2;
	}

	.email-capture-header h3 {
		font-size: 22px;
		font-weight: 700;
		margin: 0 0 12px;
		color: var(--text-primary);
	}

	.email-subtitle {
		color: var(--text-muted);
		font-size: 15px;
		margin: 0 0 20px;
	}

	.email-input-group {
		margin-bottom: 20px;
	}

	.input-with-icon {
		position: relative;
	}

	.input-icon {
		position: absolute;
		left: 16px;
		top: 50%;
		transform: translate(-0%, -60%);
		color: #aaa;
		font-size: 18px;
		transition: var(--transition);
	}

	.input-with-icon input {
		padding: 8px 8px 8px 43px !important;
		width: 100% !important;
		border: 2px solid #e0e0e0 !important;
		border-radius: 12px !important;
		font-size: 15px !important;
		transition: var(--transition) !important;
		background-color: #f9f9f9 !important;
	}

	.input-with-icon input:focus {
		border-color: var(--calc-color);
		box-shadow: 0 0 0 4px var(--calc-color-light);
		background-color: white;
		outline: none;
	}

	.input-with-icon input:focus+.input-icon {
		color: var(--calc-color);
	}

	.privacy-notice {
		font-size: 13px;
		color: var(--text-muted);
		margin-bottom: 25px;
	}

	/* Resultados (Paso 3) */
	.result-container {
		text-align: center;
		max-width: 500px;
		margin: 0 auto;
		padding: 30px;
		background: white;
		border-radius: var(--border-radius);
		box-shadow: var(--shadow-md);
		position: relative;
		overflow: hidden;
	}

	.result-container:before {
		content: '';
		position: absolute;
		top: 0;
		left: 0;
		right: 0;
		height: 5px;
		background: linear-gradient(to right, var(--calc-color), var(--calc-color-dark));
	}

	.result-header {
		margin-bottom: 30px;
	}

	.result-icon-wrapper {
		width: 80px;
		height: 80px;
		border-radius: 50%;
		background: linear-gradient(135deg, var(--calc-color), var(--calc-color-dark));
		display: flex;
		align-items: center;
		justify-content: center;
		margin: 0 auto 20px;
		box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
		position: relative;
		overflow: hidden;
	}

	.result-icon-wrapper .dashicons {
		font-size: 40px !important;
		width: fit-content;
		height: fit-content;
	}

	.result-icon-wrapper:after {
		content: '';
		position: absolute;
		width: 100%;
		height: 100%;
		top: 0;
		left: 0;
		background: radial-gradient(circle, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 0.2) 100%);
	}

	.result-icon {
		color: white;
		font-size: 34px;
		position: relative;
		z-index: 2;
	}

	.result-title {
		font-size: 22px;
		font-weight: 700;
		margin: 0 0 12px;
		color: var(--text-primary);
	}

	.result-value-container {
		margin-bottom: 30px;
		padding: 25px;
		background: linear-gradient(135deg, #f8f9fa, white);
		border-radius: var(--border-radius);
		box-shadow: var(--shadow-sm);
		border: 1px solid rgba(0, 0, 0, 0.05);
	}

	.result-label {
		font-size: 16px;
		color: var(--text-secondary);
		margin-bottom: 15px;
		font-weight: 500;
	}

	.result-value {
		font-size: 48px;
		font-weight: 800;
		background: linear-gradient(135deg, var(--calc-color), var(--calc-color-dark));
		-webkit-background-clip: text;
		-webkit-text-fill-color: transparent;
		text-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
		line-height: 1.2;
		position: relative;
		display: inline-block;
	}

	.result-value:after {
		content: '';
		position: absolute;
		bottom: -10px;
		left: 50%;
		transform: translateX(-50%);
		width: 40%;
		height: 3px;
		background: linear-gradient(to right, transparent, var(--calc-color-light), transparent);
		border-radius: 3px;
	}

	.result-message {
		color: var(--text-secondary);
		font-size: 16px;
		margin-bottom: 30px;
		line-height: 1.6;
	}

	.result-actions {
		margin-top: 30px;
	}

	/* Responsive */
	@media (max-width: 767px) {
		.calculator-header {
			padding: 20px 25px;
		}

		.calculator-body {
			padding: 25px 20px;
		}

		.calculator-fields {
			display: block;
		}

		.calculator-field {
			margin-bottom: 20px;
		}

		.result-value {
			font-size: 38px;
		}

		.email-capture-container,
		.result-container {
			padding: 25px 20px;
		}
	}

	/* Añadir tipografía Poppins */
	@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');
</style>
