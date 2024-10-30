<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class catflowcV1_Admin {

    public static function init() {
        add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_box' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
    }

    public static function add_meta_box() {
        add_meta_box(
            'catflowcV1_meta_box',
            esc_html__( 'AI Description Generator', 'catalogueflow-for-woocommerce' ),
            array( __CLASS__, 'meta_box_callback' ),
            'product',
            'side',
            'high'
        );
    }

    public static function meta_box_callback( $post ) {
        ?>
        <p>
            <?php esc_html_e( 'Genera una descripción completa para tu producto utilizando el motor de inteligencia artificial de catalogueflow', 'catalogueflow-for-woocommerce' ); ?> 
            <?php  if(empty(esc_attr( get_option('catflowcV1_app_key')) ) || empty(esc_attr( get_option('catflowcV1_app_secret') ))): ?>
            <p><?php esc_html_e( 'Parece que no has configurado el APP Key y el Secret Key.', 'catalogueflow-for-woocommerce' ); ?> 
            <a href="/wp-admin/options-general.php?page=wc-catalogueflow-settings">Configuración</a></p>
            <?php endif; ?>
        </p>
        <p>
            <button id="wc-catalogueflow-generate-description" class="button button-primary"><?php esc_html_e( 'Generar Descripción', 'catalogueflow-for-woocommerce' ); ?></button>
        </p>
        <div id="wc-catalogueflow-loading" style="display:none;">
            <p><?php esc_html_e( 'Por favor, espera un momento, esto puede demorar entre 15 a 30 segundos.', 'catalogueflow-for-woocommerce' ); ?></p>
            <img src="<?php echo esc_url( catflowcV1_PLUGIN_URL . 'includes/assets/images/loading.gif' ); ?>" width="20px" alt="<?php esc_attr_e( 'Loading...', 'catalogueflow-for-woocommerce' ); ?>">
        </div>
        <?php
    }

    public static function enqueue_scripts( $hook ) {
        if ( 'post.php' === $hook || 'post-new.php' === $hook ) {
            wp_enqueue_script( 'wc-catalogueflow-admin', esc_url( catflowcV1_PLUGIN_URL . 'includes/assets/js/admin.js' ), array( 'jquery' ), catflowcV1_VERSION, true );
            wp_localize_script( 'wc-catalogueflow-admin', 'wcCatalogueFlow', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'catflowcV1_nonce' ),
            ));
        }
    }
}

?>
