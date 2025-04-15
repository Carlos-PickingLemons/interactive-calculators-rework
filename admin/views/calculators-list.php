<?php
/**
 * Vista para mostrar la lista de calculadoras
 *
 * @package Interactive_Calculators
 */

// Si se accede directamente, abortar
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Interactive Calculators', 'interactive-calculators'); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=interactive-calculators-new'); ?>" class="page-title-action"><?php _e('Add New', 'interactive-calculators'); ?></a>

    <hr class="wp-header-end">

    <?php
    // Mostrar mensajes de error/éxito
    settings_errors('interactive-calculators');
    ?>

    <form method="get">
        <input type="hidden" name="page" value="interactive-calculators">

        <p class="search-box">
            <label class="screen-reader-text" for="calculator-search-input"><?php _e('Search Calculators:', 'interactive-calculators'); ?></label>
            <input type="search" id="calculator-search-input" name="s" value="<?php echo esc_attr($search); ?>">
            <input type="submit" id="search-submit" class="button" value="<?php _e('Search Calculators', 'interactive-calculators'); ?>">
        </p>

        <div class="tablenav top">
            <div class="alignleft actions bulkactions">
                <!-- Aquí podrían ir acciones en lote en el futuro -->
            </div>

            <?php if ($total_pages > 1) : ?>
            <div class="tablenav-pages">
                <span class="displaying-num">
                    <?php
                    printf(
                        _n('%s item', '%s items', $total_calculators, 'interactive-calculators'),
                        number_format_i18n($total_calculators)
                    );
                    ?>
                </span>

                <span class="pagination-links">
                    <?php
                    // Primera página
                    if ($current_page > 1) {
                        printf(
                            '<a class="first-page button" href="%s"><span class="screen-reader-text">%s</span><span aria-hidden="true">«</span></a>',
                            esc_url(add_query_arg('paged', 1)),
                            __('First page', 'interactive-calculators')
                        );
                    } else {
                        echo '<span class="first-page button disabled" aria-hidden="true">«</span>';
                    }

                    // Página anterior
                    if ($current_page > 1) {
                        printf(
                            '<a class="prev-page button" href="%s"><span class="screen-reader-text">%s</span><span aria-hidden="true">‹</span></a>',
                            esc_url(add_query_arg('paged', max(1, $current_page - 1))),
                            __('Previous page', 'interactive-calculators')
                        );
                    } else {
                        echo '<span class="prev-page button disabled" aria-hidden="true">‹</span>';
                    }

                    // Selector de página actual
                    printf(
                        '<span class="paging-input">%s of <span class="total-pages">%s</span></span>',
                        '<input class="current-page" type="text" name="paged" value="' . esc_attr($current_page) . '" size="1" aria-describedby="table-paging">',
                        number_format_i18n($total_pages)
                    );

                    // Página siguiente
                    if ($current_page < $total_pages) {
                        printf(
                            '<a class="next-page button" href="%s"><span class="screen-reader-text">%s</span><span aria-hidden="true">›</span></a>',
                            esc_url(add_query_arg('paged', min($total_pages, $current_page + 1))),
                            __('Next page', 'interactive-calculators')
                        );
                    } else {
                        echo '<span class="next-page button disabled" aria-hidden="true">›</span>';
                    }

                    // Última página
                    if ($current_page < $total_pages) {
                        printf(
                            '<a class="last-page button" href="%s"><span class="screen-reader-text">%s</span><span aria-hidden="true">»</span></a>',
                            esc_url(add_query_arg('paged', $total_pages)),
                            __('Last page', 'interactive-calculators')
                        );
                    } else {
                        echo '<span class="last-page button disabled" aria-hidden="true">»</span>';
                    }
                    ?>
                </span>
            </div>
            <?php endif; ?>
        </div>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <?php
                    // Columnas de la tabla con enlaces de ordenación
                    $sortable_columns = array(
                        'name' => 'name',
                        'type' => 'type',
                        'shortcode' => 'shortcode',
                        'created_at' => 'created_at'
                    );

                    // Función para crear el enlace de ordenación
                    $create_sort_link = function($column_title, $column_key) use ($orderby, $order, $sortable_columns) {
                        if (!isset($sortable_columns[$column_key])) {
                            return $column_title;
                        }

                        $current_orderby = $orderby;
                        $current_order = $order;

                        // Si ya estamos ordenando por esta columna, invertir la dirección
                        if ($current_orderby === $column_key) {
                            $current_order = $current_order === 'ASC' ? 'DESC' : 'ASC';
                        } else {
                            $current_order = 'ASC';
                        }

                        $link = add_query_arg(array(
                            'orderby' => $column_key,
                            'order' => strtolower($current_order)
                        ));

                        $class = '';
                        if ($current_orderby === $column_key) {
                            $class = ' sorted ' . strtolower($order);
                        } else {
                            $class = ' sortable ' . strtolower($current_order) . '-sort';
                        }

                        return '<a href="' . esc_url($link) . '"><span>' . $column_title . '</span><span class="sorting-indicator"></span></a>';
                    };
                    ?>

                    <th scope="col" class="manage-column column-name column-primary sorted <?php echo strtolower($orderby === 'name' ? $order : ''); ?>">
                        <?php echo $create_sort_link(__('Name', 'interactive-calculators'), 'name'); ?>
                    </th>
                    <th scope="col" class="manage-column column-type <?php echo $orderby === 'type' ? 'sorted ' . strtolower($order) : 'sortable ' . strtolower($order) . '-sort'; ?>">
                        <?php echo $create_sort_link(__('Type', 'interactive-calculators'), 'type'); ?>
                    </th>
                    <th scope="col" class="manage-column column-shortcode <?php echo $orderby === 'shortcode' ? 'sorted ' . strtolower($order) : 'sortable ' . strtolower($order) . '-sort'; ?>">
                        <?php echo $create_sort_link(__('Shortcode', 'interactive-calculators'), 'shortcode'); ?>
                    </th>
                    <th scope="col" class="manage-column column-created <?php echo $orderby === 'created_at' ? 'sorted ' . strtolower($order) : 'sortable ' . strtolower($order) . '-sort'; ?>">
                        <?php echo $create_sort_link(__('Created', 'interactive-calculators'), 'created_at'); ?>
                    </th>
                </tr>
            </thead>

            <tbody id="the-list">
                <?php
                if (empty($calculators)) {
                    echo '<tr class="no-items"><td class="colspanchange" colspan="4">' . __('No calculators found.', 'interactive-calculators') . '</td></tr>';
                } else {
                    foreach ($calculators as $calculator) {
                        // Preparar URLs de acciones
                        $edit_url = admin_url('admin.php?page=interactive-calculators-new&id=' . $calculator['id']);
                        $delete_url = wp_nonce_url(
                            admin_url('admin.php?page=interactive-calculators&action=delete&calculator_id=' . $calculator['id']),
                            'calculator_delete_' . $calculator['id']
                        );

                        // Obtener el nombre del tipo
                        $calculator_types = array(
                            'productivity' => __('Productivity Detector', 'interactive-calculators'),
                            'bottleneck' => __('Bottleneck Locator', 'interactive-calculators'),
                            'sales' => __('Sales Booster', 'interactive-calculators'),
                            'capital' => __('Trapped Capital Liberator', 'interactive-calculators'),
                            'culture' => __('Lean Culture Test', 'interactive-calculators'),
                            'time' => __('Wasted Time Counter', 'interactive-calculators'),
                            'roi' => __('Express ROI Simulator', 'interactive-calculators')
                        );
                        $type_name = isset($calculator_types[$calculator['type']]) ? $calculator_types[$calculator['type']] : $calculator['type'];

                        // Shortcode para mostrar
                        $shortcode = '[interactive_calculator id="' . $calculator['id'] . '"]';

                        // Formatear fecha
                        $created_date = date_i18n(get_option('date_format'), strtotime($calculator['created_at']));
                        ?>
                        <tr id="calculator-<?php echo $calculator['id']; ?>">
                            <td class="name column-name has-row-actions column-primary">
                                <strong><a href="<?php echo esc_url($edit_url); ?>" class="row-title"><?php echo esc_html($calculator['name']); ?></a></strong>
                                <div class="row-actions">
                                    <span class="edit"><a href="<?php echo esc_url($edit_url); ?>"><?php _e('Edit', 'interactive-calculators'); ?></a> | </span>
                                    <span class="trash"><a href="<?php echo esc_url($delete_url); ?>" class="submitdelete" onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete this calculator?', 'interactive-calculators')); ?>');"><?php _e('Delete', 'interactive-calculators'); ?></a></span>
                                </div>
                                <button type="button" class="toggle-row"><span class="screen-reader-text"><?php _e('Show more details', 'interactive-calculators'); ?></span></button>
                            </td>
                            <td class="type column-type" data-colname="<?php _e('Type', 'interactive-calculators'); ?>">
                                <?php echo esc_html($type_name); ?>
                            </td>
                            <td class="shortcode column-shortcode" data-colname="<?php _e('Shortcode', 'interactive-calculators'); ?>">
                                <code><?php echo esc_html($shortcode); ?></code>
                                <button type="button" class="copy-shortcode button-link" data-clipboard-text="<?php echo esc_attr($shortcode); ?>" title="<?php _e('Copy to clipboard', 'interactive-calculators'); ?>">
                                    <span class="dashicons dashicons-clipboard"></span>
                                </button>
                            </td>
                            <td class="created column-created" data-colname="<?php _e('Created', 'interactive-calculators'); ?>">
                                <?php echo esc_html($created_date); ?>
                            </td>
                        </tr>
                        <?php
                    }
                }
                ?>
            </tbody>

            <tfoot>
                <tr>
                    <th scope="col" class="manage-column column-name column-primary sorted <?php echo strtolower($orderby === 'name' ? $order : ''); ?>">
                        <?php echo $create_sort_link(__('Name', 'interactive-calculators'), 'name'); ?>
                    </th>
                    <th scope="col" class="manage-column column-type <?php echo $orderby === 'type' ? 'sorted ' . strtolower($order) : 'sortable ' . strtolower($order) . '-sort'; ?>">
                        <?php echo $create_sort_link(__('Type', 'interactive-calculators'), 'type'); ?>
                    </th>
                    <th scope="col" class="manage-column column-shortcode <?php echo $orderby === 'shortcode' ? 'sorted ' . strtolower($order) : 'sortable ' . strtolower($order) . '-sort'; ?>">
                        <?php echo $create_sort_link(__('Shortcode', 'interactive-calculators'), 'shortcode'); ?>
                    </th>
                    <th scope="col" class="manage-column column-created <?php echo $orderby === 'created_at' ? 'sorted ' . strtolower($order) : 'sortable ' . strtolower($order) . '-sort'; ?>">
                        <?php echo $create_sort_link(__('Created', 'interactive-calculators'), 'created_at'); ?>
                    </th>
                </tr>
            </tfoot>
        </table>

        <div class="tablenav bottom">
            <?php if ($total_pages > 1) : ?>
            <div class="tablenav-pages">
                <span class="displaying-num">
                    <?php
                    printf(
                        _n('%s item', '%s items', $total_calculators, 'interactive-calculators'),
                        number_format_i18n($total_calculators)
                    );
                    ?>
                </span>

                <span class="pagination-links">
                    <?php
                    // Primera página
                    if ($current_page > 1) {
                        printf(
                            '<a class="first-page button" href="%s"><span class="screen-reader-text">%s</span><span aria-hidden="true">«</span></a>',
                            esc_url(add_query_arg('paged', 1)),
                            __('First page', 'interactive-calculators')
                        );
                    } else {
                        echo '<span class="first-page button disabled" aria-hidden="true">«</span>';
                    }

                    // Página anterior
                    if ($current_page > 1) {
                        printf(
                            '<a class="prev-page button" href="%s"><span class="screen-reader-text">%s</span><span aria-hidden="true">‹</span></a>',
                            esc_url(add_query_arg('paged', max(1, $current_page - 1))),
                            __('Previous page', 'interactive-calculators')
                        );
                    } else {
                        echo '<span class="prev-page button disabled" aria-hidden="true">‹</span>';
                    }

                    // Selector de página actual
                    printf(
                        '<span class="paging-input">%s of <span class="total-pages">%s</span></span>',
                        '<input class="current-page" type="text" name="paged" value="' . esc_attr($current_page) . '" size="1" aria-describedby="table-paging">',
                        number_format_i18n($total_pages)
                    );

                    // Página siguiente
                    if ($current_page < $total_pages) {
                        printf(
                            '<a class="next-page button" href="%s"><span class="screen-reader-text">%s</span><span aria-hidden="true">›</span></a>',
                            esc_url(add_query_arg('paged', min($total_pages, $current_page + 1))),
                            __('Next page', 'interactive-calculators')
                        );
                    } else {
                        echo '<span class="next-page button disabled" aria-hidden="true">›</span>';
                    }

                    // Última página
                    if ($current_page < $total_pages) {
                        printf(
                            '<a class="last-page button" href="%s"><span class="screen-reader-text">%s</span><span aria-hidden="true">»</span></a>',
                            esc_url(add_query_arg('paged', $total_pages)),
                            __('Last page', 'interactive-calculators')
                        );
                    } else {
                        echo '<span class="last-page button disabled" aria-hidden="true">»</span>';
                    }
                    ?>
                </span>
            </div>
            <?php endif; ?>
        </div>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Funcionalidad para copiar shortcode al portapapeles
    $('.copy-shortcode').click(function() {
        var tempTextarea = $('<textarea>');
        $('body').append(tempTextarea);
        tempTextarea.val($(this).data('clipboard-text')).select();
        document.execCommand('copy');
        tempTextarea.remove();

        // Mostrar mensaje de éxito
        var $button = $(this);
        var originalTitle = $button.attr('title');
        $button.attr('title', '<?php _e('Copied!', 'interactive-calculators'); ?>');
        setTimeout(function() {
            $button.attr('title', originalTitle);
        }, 2000);
    });
});
</script>
