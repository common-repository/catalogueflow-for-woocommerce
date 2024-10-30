<?php
/**
 * Plugin Name: CatalogueFlow for WooCommerce
 * Plugin URI: https://www.catalogflow.ai/woocommerce/
 * Description: Plugin para integrar WooCommerce con la plataforma CatalogueFlow para generar descripciones de productos con Inteligencia Artificial.
 * Version: 1.1.4
 * Author: Claudio Marrero - Catalogflow.ai
 * Author URI: https://www.catalogflow.ai
 * License: GPL2
 * Text Domain: catalogueflow-for-woocommerce
 */

// Evitar acceso directo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Definir constantes del plugin
define( 'catflowcV1_VERSION', '1.1.4' );
define( 'catflowcV1_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'catflowcV1_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Incluir archivos necesarios
require_once catflowcV1_PLUGIN_DIR . 'includes/class-wc-catalogueflow-settings.php';
require_once catflowcV1_PLUGIN_DIR . 'includes/class-wc-catalogueflow-admin.php';
require_once catflowcV1_PLUGIN_DIR . 'includes/class-wc-catalogueflow-product.php';
require_once catflowcV1_PLUGIN_DIR . 'includes/class-wc-catalogueflow-webhook.php';
require_once catflowcV1_PLUGIN_DIR . 'includes/class-wc-catalogueflow-editor.php';

// Inicializar el plugin
function catflowcV1_init() {
    // Inicializar clases
    catflowcV1_Settings::init();
    catflowcV1_Admin::init();
    catflowcV1_Product::init();
    catflowcV1_Webhook::init();
    catflowcV1_Editor::init();
}
add_action( 'plugins_loaded', 'catflowcV1_init' );

// Activación y desactivación del plugin
function catflowcV1_activate() {
    add_option('catflowcV1_activation_redirect', true);
    add_option('catflowcV1_activation_nonce', wp_create_nonce('catflowcV1_activation_nonce'));
}
register_activation_hook( __FILE__, 'catflowcV1_activate' );

function catflowcV1_deactivate() {
    // Código a ejecutar en la desactivación del plugin
}
register_deactivation_hook( __FILE__, 'catflowcV1_deactivate' );

// Encolar CSS de administración
function catflowcV1_enqueue_admin_styles($hook) {
    if ( 'settings_page_wc-catalogueflow-settings' !== $hook ) {
        return;
    }
    wp_enqueue_style( 'wc-catalogueflow-admin', esc_url(catflowcV1_PLUGIN_URL . 'includes/assets/css/admin.css'), array(), catflowcV1_VERSION );
}
add_action( 'admin_enqueue_scripts', 'catflowcV1_enqueue_admin_styles' );

// Redirigir a la página de configuración al activar el plugin
add_action('admin_init', 'catflowcV1_redirect_on_activation');
function catflowcV1_redirect_on_activation() {
    if (get_option('catflowcV1_activation_redirect', false)) {
        // Verificar nonce
        $nonce = get_option('catflowcV1_activation_nonce', false);
        if (!isset($nonce) || !wp_verify_nonce($nonce, 'catflowcV1_activation_nonce')) {
            return; // No nonce, no redirección
        }

        // Borrar opciones usadas para redirección y nonce
        delete_option('catflowcV1_activation_redirect');
        delete_option('catflowcV1_activation_nonce');

        if (!isset($_GET['activate-multi'])) {
            wp_redirect(admin_url('options-general.php?page=wc-catalogueflow-settings'));
            exit; // Asegurarse de que la ejecución se detenga aquí
        }
    }
}

// Añadir enlace a la página de configuración en la página de plugins
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'catflowcV1_add_settings_link' );
function catflowcV1_add_settings_link( $links ) {
    $settings_link = '<a href="options-general.php?page=wc-catalogueflow-settings">' . __('Settings') . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}

add_action('wp_head', 'catflowcV1_add_virtual_assistant_script');

function catflowcV1_add_virtual_assistant_script() {
    // Obtén las claves app_key y secret_key desde las opciones de la base de datos
    $appKey = get_option('catflowcV1_app_key');
    $secretKey = get_option('catflowcV1_app_secret');

    // Verifica si las claves están configuradas
    if (!$appKey || !$secretKey) {
        return;
    }

    $is_enabled = get_option('catflowcV1_enable_virtual_assistant', 0);
    if (!$is_enabled) {
        return;
    }

    // Construir la URL del endpoint para obtener el Virtual Assistant
    $api_url_virtual_assistant = "https://public.catalogflow.ai:5000/v1/chat/script/woocommerce?appKey={$appKey}&secretKey={$secretKey}";

    // Hacer la llamada al endpoint para obtener el script del Virtual Assistant
    $response_virtual_assistant = wp_remote_get($api_url_virtual_assistant);

    // Verificar si la llamada fue exitosa
    if (is_wp_error($response_virtual_assistant)) {
        return;
    }

    // Extraer el cuerpo de la respuesta
    $body_virtual_assistant = wp_remote_retrieve_body($response_virtual_assistant);

    // Decodificar el JSON de la respuesta
    $data_virtual_assistant = json_decode($body_virtual_assistant, true);

    // Verificar si la respuesta contiene el script
    if (isset($data_virtual_assistant['code'])) {
        // Inyectar el código del Virtual Assistant en el header de la tienda
        echo "<script>{$data_virtual_assistant['code']}</script>";
    }
}

add_action('wp_head', 'catflowcV1_add_product_assistant_script');

function catflowcV1_add_product_assistant_script() {
    // Solo continuar si estamos en una página de producto
    if (!is_product()) {
        return;
    }

    // Obtén las claves app_key y secret_key desde las opciones de la base de datos
    $appKey = get_option('catflowcV1_app_key');

    // Verifica si las claves están configuradas
    if (!$appKey) {
        return;
    }

    // Obtener el producto actual y su SKU
    global $post;
    $product = wc_get_product($post->ID);
    if (!$product || !$product->get_sku()) {
        // Si el SKU está vacío, usar el ID del producto como fallback
        $productSKU = $product->get_id();
    } else {
        $productSKU = $product->get_sku();
    }

    // Construir la URL del endpoint para obtener el Product Assistant
    $api_url_product_assistant = "https://public.catalogflow.ai/api/v1/productassistant/code?appKey={$appKey}";

    // Hacer la llamada al endpoint para obtener el script del Product Assistant
    $response_product_assistant = wp_remote_get($api_url_product_assistant);

    // Verificar si la llamada fue exitosa
    if (is_wp_error($response_product_assistant)) {
        return;
    }

    // Extraer el cuerpo de la respuesta
    $body_product_assistant = wp_remote_retrieve_body($response_product_assistant);

    // Decodificar el JSON de la respuesta
    $data_product_assistant = json_decode($body_product_assistant, true);

    // Verificar si la respuesta contiene el script
    if (isset($data_product_assistant['head'])) {
        // Reemplazar el SKU en el script y agregar al header
        $head_script = str_replace('[YOUR_PRODUCT_SKU]', esc_js($productSKU), $data_product_assistant['head']);
        echo $head_script;
    }
}

// Añadir el div del Product Assistant debajo de la descripción del producto
add_action('woocommerce_after_single_product_summary', 'catflowcV1_add_product_assistant_container', 20);

function catflowcV1_add_product_assistant_container() {
    // Solo agregar el contenedor si estamos en una página de producto
    if (!is_product()) {
        return;
    }

    // Obtén las claves app_key y secret_key desde las opciones de la base de datos
    $appKey = get_option('catflowcV1_app_key');

    // Verifica si las claves están configuradas
    if (!$appKey) {
        return;
    }

    // Obtener el producto actual y su SKU
    global $post;
    $product = wc_get_product($post->ID);
    if (!$product) {
        return;
    }

    $productSKU = $product->get_sku();

    // Construir la URL del endpoint para obtener el Product Assistant
    $api_url_product_assistant = "https://public.catalogflow.ai/api/v1/productassistant/code?appKey={$appKey}";

    // Hacer la llamada al endpoint para obtener el contenedor del Product Assistant
    $response_product_assistant = wp_remote_get($api_url_product_assistant);

    // Verificar si la llamada fue exitosa
    if (is_wp_error($response_product_assistant)) {
        return;
    }

    // Extraer el cuerpo de la respuesta
    $body_product_assistant = wp_remote_retrieve_body($response_product_assistant);

    // Decodificar el JSON de la respuesta
    $data_product_assistant = json_decode($body_product_assistant, true);

    // Verificar si la respuesta contiene el contenedor
    if (isset($data_product_assistant['body'])) {
        // Reemplazar el SKU en el contenedor y agregar después de la descripción del producto
        $body_content = str_replace('[YOUR_PRODUCT_SKU]', esc_html($productSKU), $data_product_assistant['body']);
        echo $body_content;
    }
}