<?php
/**
 * Vista de calculadora interactiva en el frontend
 *
 * @package Interactive_Calculators
 */

// Si se accede directamente, abortar
if (!defined('ABSPATH')) {
    exit;
}

// Variables disponibles:
// $calculator - Datos de la calculadora
// $fields - Campos de la calculadora (array)
// $settings - Configuración de la calculadora (array)

$calculator_id = intval($calculator['id']);
$calculator_name = esc_html($calculator['name']);
$formula = isset($settings['formula']) ? $settings['formula'] : '';
$result_prefix = isset($settings['resultPrefix']) ? $settings['resultPrefix'] : '';
$result_suffix = isset($settings['resultSuffix']) ? $settings['resultSuffix'] : '';
$decimal_places = isset($settings['decimalPlaces']) ? intval($settings['decimalPlaces']) : 2;
$call_to_action = isset($settings['callToAction']) ? $settings['callToAction'] : '';
$success_message = isset($settings['successMessage']) ? $settings['successMessage'] : '';

// Generar ID único para esta instancia
$unique_id = 'calculator-' . $calculator_id . '-' . uniqid();
?>

<div class="interactive-calculator calculator-<?php echo $calculator_id; ?>" id="<?php echo $unique_id; ?>">
    <div class="calculator-header">
        <h2 class="calculator-title"><?php echo $calculator_name; ?></h2>
        <?php if (!empty($calculator['description'])): ?>
            <div class="calculator-description"><?php echo wp_kses_post($calculator['description']); ?></div>
        <?php endif; ?>
    </div>

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
                            // Las opciones deberían estar definidas en el campo
                            $options = $field['options'] ?? [];
                            ?>
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
                            <?php break;

                        case 'checkbox': ?>
                            <input type="checkbox"
                                id="<?php echo $field_id; ?>"
                                name="<?php echo $field_name; ?>"
                                class="calculator-input"
                                value="1"
                                <?php checked($field_default, '1'); ?>
                                <?php echo $field_required ? 'required' : ''; ?>>
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
            <?php endforeach; ?>
        </div>

        <div class="calculator-actions">
            <button type="button" class="button calculate-button">
                <?php _e('Calcular', 'interactive-calculators'); ?>
            </button>
        </div>
    </form>

    <div class="calculator-result" style="display: none;">
        <div class="result-container">
            <h3><?php _e('Resultado:', 'interactive-calculators'); ?></h3>
            <div class="result-value">
                <span class="result-prefix"><?php echo $result_prefix; ?></span>
                <span class="result-number">0</span>
                <span class="result-suffix"><?php echo $result_suffix; ?></span>
            </div>
        </div>

        <?php if (!empty($call_to_action)): ?>
            <div class="lead-capture-form">
                <div class="call-to-action"><?php echo esc_html($call_to_action); ?></div>

                <form class="lead-form" method="post">
                    <input type="hidden" name="calculator_id" value="<?php echo $calculator_id; ?>">
                    <input type="hidden" name="result" class="lead-result" value="">
                    <input type="hidden" name="calculator_data" class="calculator-data" value="">

                    <div class="form-group">
                        <input type="email" name="lead_email" placeholder="<?php _e('Email', 'interactive-calculators'); ?>" required>
                    </div>

                    <button type="submit" class="button submit-lead">
                        <?php _e('Enviar', 'interactive-calculators'); ?>
                    </button>
                </form>

                <div class="success-message" style="display: none;">
                    <?php echo esc_html($success_message); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
(function() {
    document.addEventListener('DOMContentLoaded', function() {
        var calculatorEl = document.getElementById('<?php echo $unique_id; ?>');
        if (!calculatorEl) return;

        var form = calculatorEl.querySelector('.calculator-form');
        var calculateBtn = calculatorEl.querySelector('.calculate-button');
        var resultContainer = calculatorEl.querySelector('.calculator-result');
        var resultValueEl = calculatorEl.querySelector('.result-number');
        var leadForm = calculatorEl.querySelector('.lead-form');
        var leadResultEl = calculatorEl.querySelector('.lead-result');
        var calculatorDataEl = calculatorEl.querySelector('.calculator-data');
        var successMsgEl = calculatorEl.querySelector('.success-message');

        // Botón calcular
        calculateBtn.addEventListener('click', function() {
            var isValid = true;
            var fields = form.querySelectorAll('.calculator-input');
            var fieldValues = {};

            // Validar formulario y recoger valores
            fields.forEach(function(field) {
                if (field.required && !field.value) {
                    isValid = false;
                    field.classList.add('error');
                } else {
                    field.classList.remove('error');

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
                var formattedResult = parseFloat(result).toFixed(<?php echo $decimal_places; ?>);

                // Mostrar resultado
                resultValueEl.textContent = formattedResult;
                resultContainer.style.display = 'block';

                // Actualizar valor oculto para lead
                if (leadResultEl) {
                    leadResultEl.value = formattedResult;
                }

                // Guardar datos de calculadora
                if (calculatorDataEl) {
                    calculatorDataEl.value = JSON.stringify(fieldValues);
                }
            } catch (e) {
                console.error('Error de cálculo:', e);
                alert('<?php _e('Error al realizar el cálculo. Por favor, intente nuevamente.', 'interactive-calculators'); ?>');
            }
        });

        // Envío de lead
        if (leadForm) {
            leadForm.addEventListener('submit', function(e) {
                e.preventDefault();

                var formData = new FormData(leadForm);
                formData.append('action', 'ic_submit_lead');

                // Enviar datos mediante AJAX
                fetch(ajaxurl, {
                    method: 'POST',
                    body: formData
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (data.success) {
                        leadForm.style.display = 'none';
                        successMsgEl.style.display = 'block';
                    } else {
                        alert(data.data || '<?php _e('Error al enviar. Por favor, intente nuevamente.', 'interactive-calculators'); ?>');
                    }
                })
                .catch(function(error) {
                    console.error('Error:', error);
                    alert('<?php _e('Error al enviar. Por favor, intente nuevamente.', 'interactive-calculators'); ?>');
                });
            });
        }
    });
})();
</script>

<style>
.interactive-calculator {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.calculator-title {
    font-size: 24px;
    margin-bottom: 15px;
}

.calculator-description {
    margin-bottom: 20px;
    color: #666;
}

.calculator-fields {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.calculator-field {
    margin-bottom: 15px;
}

.field-label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.calculator-input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.calculator-input.error {
    border-color: #dc3232;
}

.calculator-actions {
    margin-bottom: 20px;
}

.calculate-button {
    background-color: #0073aa;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
}

.calculator-result {
    margin-top: 30px;
    padding: 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.result-container {
    text-align: center;
    margin-bottom: 20px;
}

.result-value {
    font-size: 36px;
    font-weight: bold;
    color: #0073aa;
}

.lead-capture-form {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #eee;
    text-align: center;
}

.call-to-action {
    margin-bottom: 15px;
    font-weight: bold;
}

.lead-form {
    max-width: 400px;
    margin: 0 auto;
}

.form-group {
    margin-bottom: 15px;
}

.submit-lead {
    background-color: #46b450;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
}

.success-message {
    color: #46b450;
    font-weight: bold;
    padding: 10px;
}
</style>
