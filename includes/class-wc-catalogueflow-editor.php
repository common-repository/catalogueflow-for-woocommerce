<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class catflowcV1_Editor {

    public static function init() {
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
        // add_action( 'media_buttons', array( __CLASS__, 'add_media_button' ) );
        add_action( 'admin_head', array( __CLASS__, 'add_tinymce_plugin' ) );
    }

    public static function enqueue_scripts() {
        wp_enqueue_script( 'wc-catalogueflow-editor', catflowcV1_PLUGIN_URL . 'includes/assets/js/editor.js', array( 'jquery' ), catflowcV1_VERSION, true );
        wp_localize_script( 'wc-catalogueflow-editor', 'wcCatalogueFlow', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'catflowcV1_nonce' ),
        ));
    }

    public static function add_media_button() {
        echo '<a href="#" id="insert-catalogueflow-description" class="button">Generar Descripción</a>';
    }

    public static function add_tinymce_plugin() {
        if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
            return;
        }

        if ( get_user_option( 'rich_editing' ) !== 'true' ) {
            return;
        }

        add_filter( 'mce_external_plugins', array( __CLASS__, 'add_tinymce_plugin_script' ) );
        add_filter( 'mce_buttons', array( __CLASS__, 'register_tinymce_button' ) );
    }

    public static function add_tinymce_plugin_script( $plugin_array ) {
        $plugin_array['catalogueflow'] = catflowcV1_PLUGIN_URL . 'includes/assets/js/tinymce-plugin.js';
        return $plugin_array;
    }

    public static function register_tinymce_button( $buttons ) {
        array_push( $buttons, 'catalogueflow' );
        return $buttons;
    }
}
