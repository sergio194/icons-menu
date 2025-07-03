<?php
/*
Plugin Name: Menu Icons Pro
Plugin URI: https://github.com/sergio194/icons-menu
Description: Añade iconos personalizados a los elementos del menú de WordPress con una interfaz fácil de usar.
Version: 1.1.0
Author: Sergio Meyniel Pereira
License: GPL v2 or later
Text Domain: menu-icons-pro
GitHub Plugin URI: https://github.com/sergio194/icons-menu
*/

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes del plugin
define('MENU_ICONS_PRO_VERSION', '1.1.0');
define('MENU_ICONS_PRO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MENU_ICONS_PRO_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Clase principal del plugin
class MenuIconsPro {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    public function init() {
        // Cargar archivos de idioma
        load_plugin_textdomain('menu-icons-pro', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Hooks principales
        add_action('wp_update_nav_menu_item', array($this, 'save_menu_item_custom_fields'), 10, 2);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_filter('wp_nav_menu_objects', array($this, 'add_icons_to_menu_items'), 10, 2);
        add_filter('walker_nav_menu_start_el', array($this, 'modify_menu_output'), 10, 4);
        
        // Hooks para el editor de menús (compatibilidad múltiple)
        add_action('wp_nav_menu_item_custom_fields', array($this, 'menu_item_custom_fields'), 10, 4);
        add_action('admin_head-nav-menus.php', array($this, 'add_nav_menu_meta_boxes'));
        add_action('admin_footer-nav-menus.php', array($this, 'add_menu_fields_script'));
        
        // Hook adicional para cuando no hay soporte nativo - DESHABILITADO POR ERRORES
        // add_filter('wp_edit_nav_menu_walker', array($this, 'edit_nav_menu_walker'), 10, 2);
    }
    
    /**
     * Añadir campos personalizados al editor de menús
     */
    public function menu_item_custom_fields($item_id, $item, $depth, $args) {
        $icon_type = get_post_meta($item_id, '_menu_item_icon_type', true);
        $icon_value = get_post_meta($item_id, '_menu_item_icon_value', true);
        $icon_position = get_post_meta($item_id, '_menu_item_icon_position', true) ?: 'before';
        $icon_size = get_post_meta($item_id, '_menu_item_icon_size', true) ?: '16';
        $hide_label = get_post_meta($item_id, '_menu_item_hide_label', true);
        $icon_tooltip = get_post_meta($item_id, '_menu_item_icon_tooltip', true);
        ?>
        <div class="menu-icons-pro-settings" style="margin: 10px 0; padding: 10px; border: 1px solid #ddd; background: #f9f9f9;">
            <h4><?php _e('Configuración de Icono', 'menu-icons-pro'); ?></h4>
            
            <p class="field-icon-type">
                <label for="edit-menu-item-icon-type-<?php echo $item_id; ?>">
                    <?php _e('Tipo de Icono:', 'menu-icons-pro'); ?>
                </label>
                <select id="edit-menu-item-icon-type-<?php echo $item_id; ?>" 
                        name="menu-item-icon-type[<?php echo $item_id; ?>]" 
                        class="menu-icon-type-select">
                    <option value=""><?php _e('Sin icono', 'menu-icons-pro'); ?></option>
                    <option value="fontawesome" <?php selected($icon_type, 'fontawesome'); ?>>
                        <?php _e('Font Awesome', 'menu-icons-pro'); ?>
                    </option>
                    <option value="custom" <?php selected($icon_type, 'custom'); ?>>
                        <?php _e('Icono personalizado', 'menu-icons-pro'); ?>
                    </option>
                </select>
            </p>
            
            <p class="field-icon-value">
                <label for="edit-menu-item-icon-value-<?php echo $item_id; ?>">
                    <?php _e('Clase/URL del Icono:', 'menu-icons-pro'); ?>
                </label>
                <div style="display: flex; gap: 5px; align-items: center;">
                    <input type="text" 
                           id="edit-menu-item-icon-value-<?php echo $item_id; ?>" 
                           name="menu-item-icon-value[<?php echo $item_id; ?>]" 
                           value="<?php echo esc_attr($icon_value); ?>" 
                           class="widefat icon-input" 
                           placeholder="<?php _e('ej: fas fa-home o URL de imagen', 'menu-icons-pro'); ?>"
                           style="flex: 1;">
                    <button type="button" 
                            class="button icon-picker-btn" 
                            data-item-id="<?php echo $item_id; ?>"
                            style="white-space: nowrap;">
                        <?php _e('Elegir Icono', 'menu-icons-pro'); ?>
                    </button>
                </div>
                <small class="description">
                    <?php _e('Para Font Awesome: fas fa-home | Para personalizado: URL de la imagen', 'menu-icons-pro'); ?>
                </small>
            </p>
            
            <p class="field-hide-label">
                <label>
                    <input type="checkbox" 
                           id="edit-menu-item-hide-label-<?php echo $item_id; ?>" 
                           name="menu-item-hide-label[<?php echo $item_id; ?>]" 
                           value="1" 
                           <?php checked($hide_label, '1'); ?>>
                    <?php _e('Ocultar etiqueta de navegación (solo mostrar icono)', 'menu-icons-pro'); ?>
                </label>
            </p>
            
            <p class="field-icon-position">
                <label for="edit-menu-item-icon-position-<?php echo $item_id; ?>">
                    <?php _e('Posición del Icono:', 'menu-icons-pro'); ?>
                </label>
                <select id="edit-menu-item-icon-position-<?php echo $item_id; ?>" 
                        name="menu-item-icon-position[<?php echo $item_id; ?>]">
                    <option value="before" <?php selected($icon_position, 'before'); ?>>
                        <?php _e('Antes del texto', 'menu-icons-pro'); ?>
                    </option>
                    <option value="after" <?php selected($icon_position, 'after'); ?>>
                        <?php _e('Después del texto', 'menu-icons-pro'); ?>
                    </option>
                    <option value="only" <?php selected($icon_position, 'only'); ?>>
                        <?php _e('Solo icono (sin texto)', 'menu-icons-pro'); ?>
                    </option>
                </select>
            </p>
            
            <p class="field-icon-size">
                <label for="edit-menu-item-icon-size-<?php echo $item_id; ?>">
                    <?php _e('Tamaño del Icono (px):', 'menu-icons-pro'); ?>
                </label>
                <input type="number" 
                       id="edit-menu-item-icon-size-<?php echo $item_id; ?>" 
                       name="menu-item-icon-size[<?php echo $item_id; ?>]" 
                       value="<?php echo esc_attr($icon_size); ?>" 
                       min="8" 
                       max="64" 
                       style="width: 80px;">
            </p>
            
            <p class="field-icon-tooltip">
                <label for="edit-menu-item-icon-tooltip-<?php echo $item_id; ?>">
                    <?php _e('Texto del tooltip:', 'menu-icons-pro'); ?>
                </label>
                <input type="text"
                       id="edit-menu-item-icon-tooltip-<?php echo $item_id; ?>"
                       name="menu-item-icon-tooltip[<?php echo $item_id; ?>]"
                       value="<?php echo esc_attr($icon_tooltip); ?>"
                       class="widefat"
                       placeholder="<?php _e('Texto que aparecerá al pasar el ratón sobre el icono', 'menu-icons-pro'); ?>">
            </p>
        </div>
        <?php
    }
    
    /**
     * Guardar campos personalizados del menú
     */
    public function save_menu_item_custom_fields($menu_id, $menu_item_db_id) {
        // Validar tipo de icono
        $allowed_types = array('fontawesome', 'custom', '');
        if (isset($_POST['menu-item-icon-type'][$menu_item_db_id])) {
            $type = sanitize_text_field($_POST['menu-item-icon-type'][$menu_item_db_id]);
            if (!in_array($type, $allowed_types, true)) {
                $type = '';
            }
            update_post_meta($menu_item_db_id, '_menu_item_icon_type', $type);
        }
        // Validar valor del icono
        if (isset($_POST['menu-item-icon-value'][$menu_item_db_id])) {
            update_post_meta($menu_item_db_id, '_menu_item_icon_value', sanitize_text_field($_POST['menu-item-icon-value'][$menu_item_db_id]));
        }
        // Validar posición del icono
        $allowed_positions = array('before', 'after', 'only');
        if (isset($_POST['menu-item-icon-position'][$menu_item_db_id])) {
            $position = sanitize_text_field($_POST['menu-item-icon-position'][$menu_item_db_id]);
            if (!in_array($position, $allowed_positions, true)) {
                $position = 'before';
            }
            update_post_meta($menu_item_db_id, '_menu_item_icon_position', $position);
        }
        // Validar tamaño
        if (isset($_POST['menu-item-icon-size'][$menu_item_db_id])) {
            $size = intval($_POST['menu-item-icon-size'][$menu_item_db_id]);
            if ($size < 8 || $size > 64) {
                $size = 16;
            }
            update_post_meta($menu_item_db_id, '_menu_item_icon_size', $size);
        }
        if (isset($_POST['menu-item-hide-label'][$menu_item_db_id])) {
            update_post_meta($menu_item_db_id, '_menu_item_hide_label', '1');
        } else {
            delete_post_meta($menu_item_db_id, '_menu_item_hide_label');
        }
        if (isset($_POST['menu-item-icon-tooltip'][$menu_item_db_id])) {
            update_post_meta($menu_item_db_id, '_menu_item_icon_tooltip', sanitize_text_field($_POST['menu-item-icon-tooltip'][$menu_item_db_id]));
        } else {
            delete_post_meta($menu_item_db_id, '_menu_item_icon_tooltip');
        }
    }
    
    /**
     * Cargar scripts del frontend
     */
    public function enqueue_frontend_scripts() {
        wp_enqueue_style('menu-icons-pro-frontend', 
            MENU_ICONS_PRO_PLUGIN_URL . 'assets/css/frontend.css', 
            array(), MENU_ICONS_PRO_VERSION);
        // Cargar Font Awesome solo si no está registrada
        if (!wp_style_is('font-awesome', 'registered') && !wp_style_is('font-awesome', 'enqueued')) {
            wp_enqueue_style('font-awesome', 
                'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css', 
                array(), '6.0.0');
        }
        // Tooltip CSS inline
        add_action('wp_head', function() {
            echo '<style>.menu-icons-pro-tooltip{visibility:hidden;opacity:0;transition:opacity 0.15s;position:absolute;z-index:9999;bottom:120%;left:50%;transform:translateX(-50%);background:#222;color:#fff;padding:5px 10px;border-radius:4px;font-size:13px;white-space:nowrap;pointer-events:none;box-shadow:0 2px 8px rgba(0,0,0,0.15);} .menu-icons-pro-icon-tooltip-wrapper:hover .menu-icons-pro-tooltip{visibility:visible;opacity:1;pointer-events:auto;} .menu-icons-pro-icon-tooltip-wrapper .menu-icons-pro-tooltip::after{content:"";position:absolute;top:100%;left:50%;transform:translateX(-50%);border-width:6px;border-style:solid;border-color:#222 transparent transparent transparent;}</style>';
        });
    }
    
    /**
     * Cargar scripts del admin
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook == 'nav-menus.php') {
            // Cargar Font Awesome solo si no está registrada
            if (!wp_style_is('font-awesome', 'registered') && !wp_style_is('font-awesome', 'enqueued')) {
                wp_enqueue_style('font-awesome', 
                    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css', 
                    array(), '6.0.0');
            }
            wp_enqueue_style('menu-icons-pro-admin', 
                MENU_ICONS_PRO_PLUGIN_URL . 'assets/css/admin.css', 
                array('font-awesome'), MENU_ICONS_PRO_VERSION);
            
            // Cargar jQuery si no está cargado
            wp_enqueue_script('jquery');

            // Encolar el array de iconos y keywords como JS global antes del script inline
            wp_enqueue_script(
                'menu-icons-pro-fontawesome-icons',
                MENU_ICONS_PRO_PLUGIN_URL . 'assets/js/fontawesome-icons.js',
                array(),
                MENU_ICONS_PRO_VERSION,
                true
            );
            wp_enqueue_script(
                'menu-icons-pro-fontawesome-keywords',
                MENU_ICONS_PRO_PLUGIN_URL . 'assets/js/fontawesome-keywords.js',
                array(),
                MENU_ICONS_PRO_VERSION,
                true
            );
            
            // Localizar textos para JS
            wp_localize_script('menu-icons-pro-fontawesome-icons', 'MenuIconsProL10n', array(
                'select_icon_title' => __('Seleccionar Icono Font Awesome', 'menu-icons-pro'),
                'close' => __('Cerrar', 'menu-icons-pro'),
                'search_placeholder' => __('Buscar iconos... (ej: home, facebook, bluesky, x, instagram, mastodon, threads)', 'menu-icons-pro'),
                'search_hint' => __('Escribe para buscar entre %s iconos disponibles - Incluye todas las redes sociales modernas', 'menu-icons-pro'),
            ));
            
            // Añadir JavaScript inline para asegurar que funcione
            add_action('admin_footer', array($this, 'add_admin_inline_script'));
        }
    }
    
    /**
     * Añadir JavaScript inline para el selector de iconos
     */
    public function add_admin_inline_script() {
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            'use strict';
            
            // Función para mostrar el selector de iconos
            function showIconPicker(itemId) {
                // Usar el array global importado
                var fontAwesome = window.fontAwesomeIcons || [];
                
                // Crear el picker
                var pickerId = 'icon-picker-' + itemId;
                $('#' + pickerId).remove(); // Remover si existe
                
                var pickerHtml = '<div id="' + pickerId + '" class="menu-icon-picker-overlay" style="' +
                    'position: fixed !important; ' +
                    'top: 0 !important; ' +
                    'left: 0 !important; ' +
                    'width: 100vw !important; ' +
                    'height: 100vh !important; ' +
                    'background: rgba(0,0,0,0.7) !important; ' +
                    'z-index: 999999 !important; ' +
                    'display: flex !important; ' +
                    'align-items: center !important; ' +
                    'justify-content: center !important; ' +
                    'margin: 0 !important; ' +
                    'padding: 0 !important; ' +
                    'box-sizing: border-box !important; ' +
                    'visibility: visible !important; ' +
                    'opacity: 1 !important;' +
                '">';
                
                pickerHtml += '<div class="menu-icon-picker-content" style="' +
                    'background: white !important; ' +
                    'border: 2px solid #0073aa !important; ' +
                    'border-radius: 12px !important; ' +
                    'padding: 25px !important; ' +
                    'box-shadow: 0 10px 50px rgba(0,0,0,0.5) !important; ' +
                    'width: 95% !important; ' +
                    'max-width: 550px !important; ' +
                    'max-height: 85vh !important; ' +
                    'overflow-y: auto !important; ' +
                    'position: relative !important; ' +
                    'margin: 0 !important; ' +
                    'visibility: visible !important; ' +
                    'opacity: 1 !important; ' +
                    'transform: none !important; ' +
                    'display: block !important;' +
                '">';
                
                pickerHtml += '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px;">';
                pickerHtml += '<h3 style="margin: 0; color: #333;">' + MenuIconsProL10n.select_icon_title + '</h3>';
                pickerHtml += '<button type="button" class="close-picker" style="background: #dc3545; color: white; border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer; font-size: 18px; line-height: 1;">×</button>';
                pickerHtml += '</div>';
                
                // Campo de búsqueda
                pickerHtml += '<div style="margin-bottom: 15px;">';
                pickerHtml += '<input type="text" id="icon-search-' + itemId + '" placeholder="' + MenuIconsProL10n.search_placeholder + '" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">';
                pickerHtml += '<small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">' + MenuIconsProL10n.search_hint.replace('%s', fontAwesome.length) + '</small>';
                pickerHtml += '</div>';
                
                // Añadir estilos CSS específicos para ocultación
                var hideStyle = '<style id="icon-hide-style-' + itemId + '">' +
                    '.icon-grid .icon-option.hidden-icon { ' +
                        'display: none !important; ' +
                        'visibility: hidden !important; ' +
                        'opacity: 0 !important; ' +
                        'height: 0 !important; ' +
                        'width: 0 !important; ' +
                        'overflow: hidden !important; ' +
                        'margin: 0 !important; ' +
                        'padding: 0 !important; ' +
                        'border: none !important; ' +
                        'position: absolute !important; ' +
                        'left: -9999px !important; ' +
                    '} ' +
                    '.icon-grid .icon-option.visible-icon { ' +
                        'display: flex !important; ' +
                        'visibility: visible !important; ' +
                        'opacity: 1 !important; ' +
                        'height: auto !important; ' +
                        'width: auto !important; ' +
                        'position: relative !important; ' +
                        'left: auto !important; ' +
                    '} ' +
                    '/* Forzar reflow del grid cuando hay elementos ocultos */ ' +
                    '.icon-grid:has(.hidden-icon) { ' +
                        'display: grid !important; ' +
                        'grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)) !important; ' +
                    '}' +
                '</style>';
                
                $('head').append(hideStyle);
                
                pickerHtml += '<div class="icon-grid" id="icon-grid-' + itemId + '" style="' +
                    'display: grid !important; ' +
                    'grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)) !important; ' +
                    'gap: 10px !important; ' +
                    'max-height: 400px !important; ' +
                    'overflow-y: auto !important; ' +
                    'padding: 10px !important; ' +
                    'border: 1px solid #eee !important; ' +
                    'border-radius: 4px !important;' +
                '">';
                
                fontAwesome.forEach(function(icon) {
                    var iconName = icon.replace('fas fa-', '').replace('fab fa-', '');
                    pickerHtml += '<div class="icon-option" data-icon="' + icon + '" style="' +
                        'display: flex !important; ' +
                        'flex-direction: column !important; ' +
                        'align-items: center !important; ' +
                        'padding: 12px 8px !important; ' +
                        'cursor: pointer !important; ' +
                        'border: 2px solid transparent !important; ' +
                        'border-radius: 6px !important; ' +
                        'transition: all 0.2s ease !important; ' +
                        'text-align: center !important; ' +
                        'background: #f9f9f9 !important;' +
                    '">';
                    pickerHtml += '<i class="' + icon + '" style="font-size: 24px !important; margin-bottom: 6px !important; color: #333 !important;"></i>';
                    pickerHtml += '<span style="font-size: 10px !important; color: #666 !important; line-height: 1.2 !important; word-break: break-word !important;">' + iconName + '</span>';
                    pickerHtml += '</div>';
                });
                
                pickerHtml += '</div>';
                pickerHtml += '<div style="margin-top: 15px; text-align: center;">';
                pickerHtml += '<button type="button" class="close-picker" style="padding: 8px 16px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">' + MenuIconsProL10n.close + '</button>';
                pickerHtml += '</div>';
                pickerHtml += '</div></div>';
                
                $('body').append(pickerHtml);
                
                // Verificar visibilidad después de un breve delay
                setTimeout(function() {
                    var $picker = $('#' + pickerId);
                    
                    // Forzar visibilidad si hay problemas
                    if (!$picker.is(':visible') || $picker.css('display') === 'none') {
                        $picker.show().css({
                            'display': 'flex !important',
                            'visibility': 'visible !important',
                            'opacity': '1 !important'
                        });
                    }
                }, 100);
                
                // Eventos del picker
                $('#' + pickerId + ' .icon-option').hover(
                    function() {
                        $(this).css({
                            'background': '#e3f2fd !important',
                            'border-color': '#2196f3 !important',
                            'transform': 'translateY(-2px)'
                        });
                    },
                    function() {
                        $(this).css({
                            'background': '#f9f9f9 !important',
                            'border-color': 'transparent !important',
                            'transform': 'translateY(0)'
                        });
                    }
                ).click(function() {
                    var iconValue = $(this).data('icon');
                    $('#edit-menu-item-icon-value-' + itemId).val(iconValue);
                    $('#' + pickerId).remove();
                    $('#icon-hide-style-' + itemId).remove(); // Limpiar estilos CSS
                    $('#no-results-' + itemId).remove(); // Limpiar mensaje no resultados
                    updatePreview(itemId);
                });
                
                // Cerrar con los botones de cerrar
                $('#' + pickerId + ' .close-picker').click(function() {
                    $('#' + pickerId).remove();
                    $('#icon-hide-style-' + itemId).remove(); // Limpiar estilos CSS
                    $('#no-results-' + itemId).remove(); // Limpiar mensaje no resultados
                });
                
                // Cerrar al hacer clic en el overlay
                $('#' + pickerId).click(function(e) {
                    if (e.target === this) {
                        $(this).remove();
                        $('#icon-hide-style-' + itemId).remove(); // Limpiar estilos CSS
                        $('#no-results-' + itemId).remove(); // Limpiar mensaje no resultados
                    }
                });
                
                // Funcionalidad de búsqueda con delegación
                $(document).on('input', '#icon-search-' + itemId, function() {
                    var searchTerm = $(this).val().toLowerCase();
                    var itemIdFromInput = $(this).attr('id').replace('icon-search-', '');
                    var $grid = $('#icon-grid-' + itemIdFromInput);
                    var $options = $grid.find('.icon-option');
                    var visibleCount = 0;
                    
                    if (searchTerm === '') {
                        // Mostrar todos los iconos
                        $options.each(function() {
                            $(this).removeClass('hidden-icon').addClass('visible-icon');
                        });
                        visibleCount = $options.length;
                    } else {
                        // Filtrar iconos
                        $options.each(function() {
                            var $option = $(this);
                            var iconClass = $option.data('icon');
                            var iconName = iconClass.replace('fas fa-', '').replace('fab fa-', '');
                            
                            // Buscar en el nombre del icono y en palabras clave
                            var searchableText = iconName + ' ' + iconClass;
                            
                            // Añadir palabras clave adicionales para búsqueda más intuitiva
                            var keywords = window.menuIconsProKeywords || {};
                            
                            // Añadir palabras clave específicas
                            Object.keys(keywords).forEach(function(key) {
                                if (iconName.includes(key)) {
                                    searchableText += ' ' + keywords[key];
                                }
                            });
                            
                            if (searchableText.toLowerCase().includes(searchTerm)) {
                                $option.removeClass('hidden-icon').addClass('visible-icon');
                                visibleCount++;
                            } else {
                                $option.removeClass('visible-icon').addClass('hidden-icon');
                                
                                // Verificar que se aplicó el estilo
                                setTimeout(function() {
                                    var hasHiddenClass = $option.hasClass('hidden-icon');
                                    var displayStyle = $option.css('display');
                                    var visibilityStyle = $option.css('visibility');
                                }, 10);
                            }
                        });
                    }
                    
                    // Forzar reflow del grid para que se ajuste correctamente
                    var $grid = $('#icon-grid-' + itemIdFromInput);
                    $grid.css('display', 'none').offset(); // Forzar reflow
                    $grid.css('display', 'grid');
                    
                    // Verificar si hay elementos visibles y ajustar el grid
                    var $visibleOptions = $grid.find('.icon-option:not(.hidden-icon)');
                    
                    // Si no hay elementos visibles, mostrar mensaje
                    var $noResults = $('#no-results-' + itemIdFromInput);
                    if (visibleCount === 0 && searchTerm !== '') {
                        if ($noResults.length === 0) {
                            $grid.after('<div id="no-results-' + itemIdFromInput + '" style="text-align: center; padding: 20px; color: #666; font-style: italic;">No se encontraron iconos que coincidan con tu búsqueda.</div>');
                        }
                        $noResults.show();
                    } else {
                        $noResults.hide();
                    }
                    
                    // Mostrar contador de resultados
                    var $counter = $('#search-counter-' + itemIdFromInput);
                    if ($counter.length === 0) {
                        $(this).next('small').after('<div id="search-counter-' + itemIdFromInput + '" style="color: #0073aa; font-size: 12px; margin-top: 5px;"></div>');
                        $counter = $('#search-counter-' + itemIdFromInput);
                    }
                    
                    if (searchTerm === '') {
                        $counter.text('Mostrando todos los iconos (' + fontAwesome.length + ')');
                        $counter.css('color', '#0073aa');
                    } else {
                        if (visibleCount > 0) {
                            $counter.text('Encontrados: ' + visibleCount + ' iconos de ' + fontAwesome.length);
                            $counter.css('color', '#00a32a');
                        } else {
                            $counter.html('No se encontraron iconos. <span style="color: #666;">Prueba con: home, facebook, instagram, email, phone, etc.</span>');
                            $counter.css('color', '#d63638');
                        }
                    }
                });
                
                // Enfocar el campo de búsqueda y mostrar contador inicial
                setTimeout(function() {
                    $('#icon-search-' + itemId).focus();
                    // Mostrar contador inicial
                    var $searchField = $('#icon-search-' + itemId);
                    if ($searchField.length) {
                        var $existingCounter = $('#search-counter-' + itemId);
                        if ($existingCounter.length === 0) {
                            $searchField.next('small').after('<div id="search-counter-' + itemId + '" style="color: #0073aa; font-size: 12px; margin-top: 5px;">Mostrando todos los iconos (' + fontAwesome.length + ')</div>');
                        }
                    }
                    
                    // Verificar que los iconos se han renderizado
                    var $grid = $('#icon-grid-' + itemId);
                    var $options = $grid.find('.icon-option');
                    // Si no hay iconos renderizados, intentar de nuevo
                    if ($options.length === 0) {
                        setTimeout(function() {
                            var $optionsRetry = $('#icon-grid-' + itemId).find('.icon-option');
                        }, 200);
                    }
                }, 300);
            }
            
            // Función para actualizar la vista previa
            function updatePreview(itemId) {
                var iconType = $('#edit-menu-item-icon-type-' + itemId).val();
                var iconValue = $('#edit-menu-item-icon-value-' + itemId).val();
                var iconSize = $('#edit-menu-item-icon-size-' + itemId).val() || 16;
                
                var $preview = $('#menu-icon-preview-' + itemId);
                if (!$preview.length) {
                    var previewHtml = '<div id="menu-icon-preview-' + itemId + '" style="margin-top: 10px; padding: 10px; background: #fff; border: 1px solid #e5e5e5; border-radius: 3px;">' +
                                    '<strong>Vista previa:</strong> <span class="preview-icon"></span>' +
                                    '</div>';
                    $('#edit-menu-item-icon-value-' + itemId).closest('.field-icon-value').after(previewHtml);
                    $preview = $('#menu-icon-preview-' + itemId);
                }
                
                var $previewIcon = $preview.find('.preview-icon');
                $previewIcon.empty();
                
                if (iconType && iconValue) {
                    var iconHtml = '';
                    var sizeStyle = 'width: ' + iconSize + 'px; height: ' + iconSize + 'px; font-size: ' + iconSize + 'px; display: inline-block; vertical-align: middle;';
                    
                    if (iconType === 'fontawesome' && iconValue.trim()) {
                        iconHtml = '<i class="' + iconValue + '" style="' + sizeStyle + '"></i>';
                    } else if (iconType === 'custom' && iconValue.indexOf('http') === 0) {
                        // Usar el nombre del archivo como alt por defecto
                        $alt = basename(parse_url($iconValue, PHP_URL_PATH));
                        iconHtml = '<img src="' + iconValue + '" style="' + sizeStyle + '" alt="' + $alt + '">';
                    }
                    
                    if (iconHtml) {
                        $previewIcon.html(iconHtml);
                        $preview.show();
                    } else {
                        $preview.hide();
                    }
                } else {
                    $preview.hide();
                }
            }
            
            // Eventos con delegación más agresiva
            $(document).on('change', '.menu-icon-type-select, select[name*="menu-item-icon-type"]', function() {
                var itemId = $(this).attr('id').replace('edit-menu-item-icon-type-', '');
                var iconType = $(this).val();
                var $valueField = $('#edit-menu-item-icon-value-' + itemId);
                var $pickerBtn = $('.icon-picker-btn[data-item-id="' + itemId + '"]');
                
                if (iconType === 'fontawesome') {
                    $valueField.attr('placeholder', 'ej: fas fa-home');
                    $pickerBtn.show();
                } else if (iconType === 'custom') {
                    $valueField.attr('placeholder', 'URL de la imagen');
                    $pickerBtn.hide();
                } else {
                    $valueField.attr('placeholder', '');
                    $pickerBtn.hide();
                }
                
                updatePreview(itemId);
            });
            
            $(document).on('input', '.icon-input, input[name*="menu-item-icon-value"]', function() {
                var nameAttr = $(this).attr('name');
                if (nameAttr) {
                    var match = nameAttr.match(/\[(\d+)\]/);
                    if (match) {
                        updatePreview(match[1]);
                    }
                }
            });
            
            $(document).on('input', 'input[name*="menu-item-icon-size"]', function() {
                var nameAttr = $(this).attr('name');
                if (nameAttr) {
                    var match = nameAttr.match(/\[(\d+)\]/);
                    if (match) {
                        updatePreview(match[1]);
                    }
                }
            });
            
            $(document).on('click', '.icon-picker-btn', function(e) {
                e.preventDefault();
                var itemId = $(this).data('item-id');
                var iconType = $('#edit-menu-item-icon-type-' + itemId).val();
                
                if (iconType === 'fontawesome') {
                    showIconPicker(itemId);
                } else {
                    alert('Por favor, selecciona "Font Awesome" como tipo de icono primero.');
                }
            });
            
            // Cerrar picker al hacer clic fuera
            $(document).on('click', function(e) {
                if (!$(e.target).closest('[id^="icon-picker-"], .icon-picker-btn').length) {
                    $('[id^="icon-picker-"]').remove();
                    $('[id^="icon-hide-style-"]').remove(); // Limpiar estilos CSS
                    $('[id^="no-results-"]').remove(); // Limpiar mensajes no resultados
                }
            });
            
            // Función para inicializar vistas previas y detectar campos
            function initializeFields() {
                $('.menu-icon-type-select, select[name*="menu-item-icon-type"]').each(function() {
                    var itemId = $(this).attr('id').replace('edit-menu-item-icon-type-', '');
                    updatePreview(itemId);
                });
                
                // Mostrar/ocultar botones según el tipo seleccionado
                $('.menu-icon-type-select, select[name*="menu-item-icon-type"]').trigger('change');
            }
            
            // Inicializar al cargar
            setTimeout(initializeFields, 1000);
            
            // Re-inicializar cuando se añaden nuevos elementos
            $(document).on('DOMNodeInserted', function() {
                setTimeout(initializeFields, 100);
            });
            
            // Detectar cuando se añaden nuevos elementos del menú
            var observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes && mutation.addedNodes.length > 0) {
                        setTimeout(initializeFields, 100);
                    }
                });
            });
            
            observer.observe(document.getElementById('menu-to-edit') || document.body, {
                childList: true,
                subtree: true
            });
        });
        </script>
        <?php
    }
    
    /**
     * Añadir iconos a los elementos del menú
     */
    public function add_icons_to_menu_items($items, $args) {
        foreach ($items as $item) {
            $icon_type = get_post_meta($item->ID, '_menu_item_icon_type', true);
            $icon_value = get_post_meta($item->ID, '_menu_item_icon_value', true);
            $icon_position = get_post_meta($item->ID, '_menu_item_icon_position', true) ?: 'before';
            $icon_size = get_post_meta($item->ID, '_menu_item_icon_size', true) ?: '16';
            $hide_label = get_post_meta($item->ID, '_menu_item_hide_label', true);
            $icon_tooltip = get_post_meta($item->ID, '_menu_item_icon_tooltip', true);
            
            if ($icon_type && $icon_value) {
                // Pasar el tooltip como variable global temporal
                $GLOBALS['menu_icons_pro_current_tooltip'] = $icon_tooltip;
                $item->icon_html = $this->generate_icon_html($icon_type, $icon_value, $icon_size);
                unset($GLOBALS['menu_icons_pro_current_tooltip']);
                $item->icon_position = $icon_position;
                $item->hide_label = $hide_label;
            }
        }
        
        return $items;
    }
    
    /**
     * Modificar la salida del menú para incluir iconos
     */
    public function modify_menu_output($item_output, $item, $depth, $args) {
        if (isset($item->icon_html) && $item->icon_html) {
            $icon_html = $item->icon_html;
            $position = isset($item->icon_position) ? $item->icon_position : 'before';
            $hide_label = isset($item->hide_label) && $item->hide_label;
            
            if ($hide_label) {
                // Solo mostrar el icono, ocultar el texto
                $item_output = preg_replace('/(<a[^>]*>).*?(<\/a>)/', '$1' . $icon_html . '$2', $item_output);
            } else {
                // Mostrar icono y texto según la posición
                if ($position == 'before') {
                    $item_output = preg_replace('/(<a[^>]*>)/', '$1' . $icon_html . ' ', $item_output);
                } elseif ($position == 'after') {
                    $item_output = preg_replace('/(<\/a>)/', ' ' . $icon_html . '$1', $item_output);
                } elseif ($position == 'only') {
                    $item_output = preg_replace('/(<a[^>]*>).*?(<\/a>)/', '$1' . $icon_html . '$2', $item_output);
                }
            }
        }
        
        return $item_output;
    }
    
    /**
     * Generar HTML del icono
     */
    private function generate_icon_html($icon_type, $icon_value, $icon_size) {
        $size_style = "width: " . intval($icon_size) . "px; height: " . intval($icon_size) . "px; font-size: " . intval($icon_size) . "px;";
        $tooltip = '';
        if (!empty($GLOBALS['menu_icons_pro_current_tooltip'])) {
            $tooltip = $GLOBALS['menu_icons_pro_current_tooltip'];
        }
        $attr = '';
        $tooltip_html = '';
        if ($tooltip) {
            $attr = ' aria-label="' . esc_attr($tooltip) . '"';
            $tooltip_html = '<span class="menu-icons-pro-tooltip">' . esc_html($tooltip) . '</span>';
        }
        $icon_html = '';
        switch ($icon_type) {
            case 'fontawesome':
                $icon_html = "<i class='" . esc_attr($icon_value) . " menu-icon' style='" . esc_attr($size_style) . "'{$attr}></i>";
                break;
            case 'custom':
                if (filter_var($icon_value, FILTER_VALIDATE_URL)) {
                    $alt = basename(parse_url($icon_value, PHP_URL_PATH));
                    $icon_html = "<img src='" . esc_url($icon_value) . "' class='menu-icon custom-icon' style='" . esc_attr($size_style) . "' alt='" . esc_attr($alt) . "'{$attr}>";
                }
                break;
        }
        if ($icon_html && $tooltip_html) {
            return '<span class="menu-icons-pro-icon-tooltip-wrapper" style="position:relative;display:inline-block;">' . $icon_html . $tooltip_html . '</span>';
        }
        return $icon_html;
    }
    
    /**
     * Hook alternativo para compatibilidad con versiones anteriores de WordPress
     */
    public function add_nav_menu_meta_boxes() {
        // Asegurar que se carguen los scripts y estilos en la página de menús
        $this->enqueue_admin_scripts('nav-menus.php');
        
        // Añadir CSS personalizado si es necesario
        ?>
        <style>
        .menu-icons-pro-settings {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin: 10px 0;
        }
        .menu-icons-pro-settings h4 {
            margin: 0 0 15px 0;
            color: #333;
            font-size: 14px;
            font-weight: 600;
            border-bottom: 1px solid #ddd;
            padding-bottom: 8px;
        }
        .icon-picker-btn {
            background: #0073aa;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 8px 12px;
            cursor: pointer;
            font-size: 12px;
        }
        .icon-picker-btn:hover {
            background: #005a87;
        }
        .field-icon-value > div {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .field-icon-value .icon-input {
            flex: 1;
        }
        </style>
        <?php
    }
    
    /**
     * Hook adicional para el footer de nav-menus.php
     */
    public function add_menu_fields_script() {
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Función para añadir campos a un elemento específico
            function addIconFields(itemId, $container) {
                if ($container.find('.menu-icons-pro-settings').length > 0) {
                    return; // Ya tiene los campos
                }
                
                var fieldsHtml = `
                <div class="menu-icons-pro-settings" style="margin: 10px 0; padding: 10px; border: 1px solid #ddd; background: #f9f9f9; border-radius: 4px;">
                    <h4 style="margin: 0 0 15px 0; color: #333; font-size: 14px; font-weight: 600; border-bottom: 1px solid #ddd; padding-bottom: 8px;">Configuración de Icono</h4>
                    
                    <p class="field-icon-type" style="margin: 10px 0;">
                        <label for="edit-menu-item-icon-type-${itemId}" style="display: block; font-weight: 600; margin-bottom: 5px; color: #555;">Tipo de Icono:</label>
                        <select id="edit-menu-item-icon-type-${itemId}" name="menu-item-icon-type[${itemId}]" class="menu-icon-type-select widefat" style="width: 100%; padding: 6px 8px; border: 1px solid #ddd; border-radius: 3px;">
                            <option value="">Sin icono</option>
                            <option value="fontawesome">Font Awesome</option>
                            <option value="custom">Icono personalizado</option>
                        </select>
                    </p>
                    
                    <p class="field-icon-value" style="margin: 10px 0;">
                        <label for="edit-menu-item-icon-value-${itemId}" style="display: block; font-weight: 600; margin-bottom: 5px; color: #555;">Clase/URL del Icono:</label>
                        <div style="display: flex; gap: 5px; align-items: center;">
                            <input type="text" id="edit-menu-item-icon-value-${itemId}" name="menu-item-icon-value[${itemId}]" class="widefat icon-input" placeholder="ej: fas fa-home o URL de imagen" style="flex: 1; padding: 6px 8px; border: 1px solid #ddd; border-radius: 3px;">
                            <button type="button" class="button icon-picker-btn" data-item-id="${itemId}" style="white-space: nowrap; background: #0073aa; color: #fff; border: none; border-radius: 4px; padding: 8px 12px; cursor: pointer; display: none;">Elegir Icono</button>
                        </div>
                        <small class="description" style="color: #666; font-style: italic; font-size: 12px; margin-top: 5px; display: block;">Para Font Awesome: fas fa-home | Para personalizado: URL de la imagen</small>
                    </p>
                    
                    <p class="field-hide-label" style="margin: 10px 0;">
                        <label style="display: flex; align-items: center; gap: 8px; font-weight: normal; cursor: pointer;">
                            <input type="checkbox" id="edit-menu-item-hide-label-${itemId}" name="menu-item-hide-label[${itemId}]" value="1" style="margin: 0;">
                            Ocultar etiqueta de navegación (solo mostrar icono)
                        </label>
                    </p>
                    
                    <p class="field-icon-position" style="margin: 10px 0;">
                        <label for="edit-menu-item-icon-position-${itemId}" style="display: block; font-weight: 600; margin-bottom: 5px; color: #555;">Posición del Icono:</label>
                        <select id="edit-menu-item-icon-position-${itemId}" name="menu-item-icon-position[${itemId}]" class="widefat" style="width: 100%; padding: 6px 8px; border: 1px solid #ddd; border-radius: 3px;">
                            <option value="before">Antes del texto</option>
                            <option value="after">Después del texto</option>
                            <option value="only">Solo icono (sin texto)</option>
                        </select>
                    </p>
                    
                    <p class="field-icon-size" style="margin: 10px 0;">
                        <label for="edit-menu-item-icon-size-${itemId}" style="display: block; font-weight: 600; margin-bottom: 5px; color: #555;">Tamaño del Icono (px):</label>
                        <input type="number" id="edit-menu-item-icon-size-${itemId}" name="menu-item-icon-size[${itemId}]" value="16" min="8" max="64" style="width: 80px; padding: 6px 8px; border: 1px solid #ddd; border-radius: 3px;">
                    </p>
                </div>`;
                
                $container.append(fieldsHtml);
            }
            
            // Función para procesar todos los elementos del menú
            function processMenuItems() {
                // Buscar todos los elementos del menú
                $('.menu-item-settings').each(function() {
                    var $this = $(this);
                    var $dbIdField = $this.find('input[name*="menu-item-db-id"]').first();
                    
                    if ($dbIdField.length > 0) {
                        var itemId = $dbIdField.val();
                        if (itemId && !$this.find('.menu-icons-pro-settings').length) {
                            addIconFields(itemId, $this);
                        }
                    }
                });
                
                // También buscar por estructura alternativa
                $('li.menu-item').each(function() {
                    var $this = $(this);
                    var itemId = $this.attr('id');
                    
                    if (itemId) {
                        itemId = itemId.replace('menu-item-', '');
                        var $settings = $this.find('.menu-item-settings');
                        
                        if ($settings.length > 0 && !$settings.find('.menu-icons-pro-settings').length) {
                            addIconFields(itemId, $settings);
                        }
                    }
                });
            }
            
            // Ejecutar inmediatamente
            processMenuItems();
            
            // Ejecutar después de un pequeño retraso para elementos cargados dinámicamente
            setTimeout(processMenuItems, 500);
            setTimeout(processMenuItems, 1000);
            setTimeout(processMenuItems, 2000);
            
            // Observer para detectar cuando se añaden nuevos elementos
            var observer = new MutationObserver(function(mutations) {
                var shouldProcess = false;
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes && mutation.addedNodes.length > 0) {
                        for (var i = 0; i < mutation.addedNodes.length; i++) {
                            var node = mutation.addedNodes[i];
                            if (node.nodeType === 1 && (
                                $(node).hasClass('menu-item') || 
                                $(node).find('.menu-item').length > 0 ||
                                $(node).hasClass('menu-item-settings') ||
                                $(node).find('.menu-item-settings').length > 0
                            )) {
                                shouldProcess = true;
                                break;
                            }
                        }
                    }
                });
                
                if (shouldProcess) {
                    setTimeout(processMenuItems, 100);
                }
            });
            
            // Observar cambios en el contenedor del menú
            var menuContainer = document.getElementById('menu-to-edit');
            if (menuContainer) {
                observer.observe(menuContainer, {
                    childList: true,
                    subtree: true
                });
            } else {
                // Fallback: observar todo el body
                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });
            }
            
            // Event listener para cuando se añaden nuevos elementos mediante AJAX
            $(document).on('menu-item-added', function() {
                setTimeout(processMenuItems, 100);
            });
        });
        </script>
        <?php
    }
    
    /*
    // Walker personalizado - DESHABILITADO POR COMPATIBILIDAD
    public function edit_nav_menu_walker($walker, $menu_id) {
        // Solo cargar en la página de edición de menús y si estamos en admin
        if (!is_admin() || !current_user_can('edit_theme_options')) {
            return $walker;
        }
        
        // Cargar el walker si es necesario
        menu_icons_pro_load_walker();
        
        // Solo usar el walker personalizado si la clase existe
        if (class_exists('Menu_Icons_Pro_Walker')) {
            return new Menu_Icons_Pro_Walker();
        }
        
        return $walker;
    }
    */
}

// Inicializar el plugin
new MenuIconsPro();
