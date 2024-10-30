<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class catflowcV1_Webhook {

    public static function init() {
        add_action( 'rest_api_init', array( __CLASS__, 'register_webhook_endpoint' ) );
    }

    public static function register_webhook_endpoint() {
        register_rest_route( 'wc-catalogueflow/v1', '/webhook', array(
            'methods' => 'POST',
            'callback' => array( __CLASS__, 'handle_webhook' ),
            'permission_callback' => '__return_true',
        ));
    }

    public static function handle_webhook( $request ) {
        $body = $request->get_body();
        $data = json_decode( $body, true );

        if ( empty( $data ) || ! isset( $data['sku'] ) || ! isset( $data['description'] ) ) {
            return new WP_Error( 'invalid_data', 'Faltan datos en la solicitud.', array( 'status' => 400 ) );
        }

        $sku = sanitize_text_field( $data['sku'] );
        $description = wp_kses_post( $data['description'] );
        $short_description = isset( $data['shortDescription'] ) ? wp_kses_post( $data['shortDescription'] ) : '';
        $name = isset( $data['name'] ) ? sanitize_text_field( $data['name'] ) : '';
        $meta_description = isset( $data['metaDescription'] ) ? sanitize_text_field( $data['metaDescription'] ) : '';
        $meta_keywords = isset( $data['metaKeywords'] ) ? sanitize_text_field( $data['metaKeywords'] ) : '';

        // Intentar obtener el producto por SKU
        $product_id = wc_get_product_id_by_sku( $sku );
        $product = wc_get_product( $product_id );

        // Si no se encuentra el producto por SKU, intentar obtenerlo por ID
        if ( ! $product && isset( $data['product_id'] ) ) {
            $product_id = intval( $data['product_id'] );
            $product = wc_get_product( $product_id );
        }

        if ( ! $product ) {
            return new WP_Error( 'product_not_found', 'Producto no encontrado.', array( 'status' => 404 ) );
        }

        // Actualizar la descripción del producto
        $product->set_description( $description );

        // Actualizar otras propiedades del producto en WooCommerce
        if ( ! empty( $short_description ) ) {
            $product->set_short_description( $short_description );
        }

        if ( ! empty( $name ) ) {
            $product->set_name( $name );
        }

        // Actualizar los metadatos del producto
        if ( ! empty( $meta_description ) ) {
            update_post_meta($product_id, '_yoast_wpseo_metadesc', $meta_description);
        }

        if ( ! empty( $meta_keywords ) ) {
            update_post_meta($product_id, '_yoast_wpseo_metakeywords', $meta_keywords);
        }

        // Guardar los cambios en el producto
        $product->save();

        return rest_ensure_response( array( 'success' => true ) );
    }
}

catflowcV1_Webhook::init();
