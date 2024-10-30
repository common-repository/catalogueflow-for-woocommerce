<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class catflowcV1_Product {

    public static function init() {
        add_action( 'wp_ajax_catflowcV1_generate_description', array( __CLASS__, 'generate_description' ) );
        add_action( 'wp_ajax_catflowcV1_check_status', array( __CLASS__, 'check_status' ) );
    }

    public static function generate_description() {
        check_ajax_referer( 'catflowcV1_nonce', 'nonce' );

        $product_id = absint( $_POST['product_id'] );
        if ( ! $product_id ) {
            wp_send_json_error( 'Producto no válido.' );
        }

        $product = wc_get_product( $product_id );
        if ( ! $product ) {
            wp_send_json_error( 'Producto no encontrado.' );
        }

        // Obtener el SKU o usar el ID del producto si no tiene SKU, y forzar a cadena
        $sku = $product->get_sku();
        if ( ! $sku ) {
            $sku = (string) $product_id;
        } else {
            $sku = (string) $sku;
        }

        $capabilities = get_option( 'catflowcV1_capabilities', array() );
        $description_format = get_option( 'catflowcV1_description_format', '<p>long_description_text</p>' );
        $short_description_format = get_option( 'catflowcV1_short_description_format', '<p>short_description_text</p>' );
        $sizes = get_option( 'catflowcV1_sizes', array('title' => 70, 'description' => 2000, 'shortDescription' => 200) );
        $use_other_fields = get_option( 'catflowcV1_use_other_fields', 1 );
        $language = get_option( 'catflowcV1_language', 'Español' );

        $attributes = array();
        if ( $use_other_fields ) {
            foreach ( $product->get_attributes() as $attribute ) {
                $attributes[] = array(
                    'name' => $attribute->get_name(),
                    'value' => implode(', ', $attribute->get_options())
                );
            }
        }

        // Obtener el shortDescription o usar el nombre del producto si está vacío
        $short_description = $product->get_short_description();
        if ( empty( $short_description ) ) {
            $short_description = $product->get_name();
        }

        $description = $product->get_description();
        if ( empty( $description ) ) {
            $description = $product->get_name();
        }

        // Recoger la información del producto
        $data = array(
            'product' => array(
                'sku' => $sku,
                'title' => $product->get_name(),
                'description' => $description,
                'sizes' => $sizes,
                'engine' => 'gpt-3.5-turbo',
                'language' => $language,
                'theme' => 'None',
                'shortDescription' => $short_description,
                'descriptionFormat' => $description_format,
                'shortDescriptionFormat' => $short_description_format,
                'capabilities' => $capabilities,
                'otherFields' => $attributes,
                'webhook' => rest_url('wc-catalogueflow/v1/webhook')
            )
        );

        $api_key = get_option( 'catflowcV1_app_key' );
        $api_secret = get_option( 'catflowcV1_app_secret' );

        if ( ! $api_key || ! $api_secret ) {
            wp_send_json_error( 'API Key o API Secret no configurados.' );
        }

        // Llamada a la API de CatalogueFlow
        $response = self::call_catalogueflow_api( $data, $api_key, $api_secret );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( $response->get_error_message() );
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( empty( $body ) || !isset($body['process']) || $body['process'] !== 'start' ) {
            wp_send_json_error( 'Error en la generación de la descripción: ' . print_r($body, true) );
        }

        // Marcar el producto como en proceso de generación de descripción
        update_post_meta( $product_id, '_catalogueflow_description_generating', '1' );
        update_post_meta( $product_id, '_catalogueflow_check_attempts', 0 );

        // Enviar el SKU como respuesta para usarlo en la verificación del estado
        wp_send_json_success( array( 'message' => 'Descripción en proceso de generación.', 'sku' => $sku ) );
    }

    public static function check_status() {
        check_ajax_referer( 'catflowcV1_nonce', 'nonce' );

        $sku = sanitize_text_field( $_POST['sku'] );
        if ( ! $sku ) {
            wp_send_json_error( 'SKU no válido.' );
        }

        $api_key = get_option( 'catflowcV1_app_key' );
        $api_secret = get_option( 'catflowcV1_app_secret' );

        if ( ! $api_key || ! $api_secret ) {
            wp_send_json_error( 'API Key o API Secret no configurados.' );
        }

        // Llamada a la API de CatalogueFlow para verificar el estado del producto
        $response = self::get_catalogueflow_product( $sku, $api_key, $api_secret );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( $response->get_error_message() );
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        // Verificación de si la descripción está disponible
        if ( isset( $body['error'] ) || empty( $body['description'] ) ) {
            // Incrementar el contador de intentos
            $product_id = wc_get_product_id_by_sku( $sku );
            if (!$product_id ) {
                $product_id = $sku;
            }
            $attempts = (int) get_post_meta( $product_id, '_catalogueflow_check_attempts', true );
            $attempts++;
            update_post_meta( $product_id, '_catalogueflow_check_attempts', $attempts );

            // Limitar el número de intentos a 10 (equivale a 1 minuto si se verifica cada 5 segundos)
            if ( $attempts >= 10 ) {
                delete_post_meta( $product_id, '_catalogueflow_description_generating' );
                wp_send_json_error( 'Descripción aún no disponible después de múltiples intentos. Por favor, intente de nuevo más tarde.' );
            }

            wp_send_json_error( 'Descripción aún no disponible. Respuesta: ' . print_r($body, true) );
        }

        // Buscar el producto en WooCommerce y actualizar la descripción
        $product_id = wc_get_product_id_by_sku( $sku );
        if (!$product_id ) {
            $product_id = $sku;
        }

        $product = wc_get_product( $product_id );
        if ( ! $product ) {
            wp_send_json_error( 'Producto no encontrado.' );
        }

        // Actualizar la descripción del producto
        $product->set_description( $body['description'] );

        // Opcional: Actualizar otras propiedades del producto en WooCommerce
        if (isset($body['shortDescription'])) {
            $product->set_short_description( $body['shortDescription'] );
        }

        if (isset($body['name'])) {
            $product->set_name( $body['name'] );
        }

        // Actualizar los metadatos del producto
        if (isset($body['metaDescription'])) {
            update_post_meta($product_id, '_yoast_wpseo_metadesc', $body['metaDescription']);
        }

        if (isset($body['metaKeywords'])) {
            update_post_meta($product_id, '_yoast_wpseo_metakeywords', $body['metaKeywords']);
        }

        $product->save();

        // Marcar el producto como no generando descripción
        delete_post_meta( $product_id, '_catalogueflow_description_generating' );
        delete_post_meta( $product_id, '_catalogueflow_check_attempts' );

        wp_send_json_success(array(
            'message' => 'Descripción generada y actualizada correctamente.',
            'description' => $body['description']
        ));
    }


    private static function call_catalogueflow_api( $data, $api_key, $api_secret ) {
        $response = wp_remote_post( 'https://public.catalogflow.ai/v1/catalog/product/single', array(
            'body'    => wp_json_encode( $data ),
            'headers' => array(
                'Content-Type'  => 'application/json',
                'x-app-key'     => $api_key,
                'x-secret-key'  => $api_secret,
            ),
        ));

        return $response;
    }

    private static function get_catalogueflow_product( $sku, $api_key, $api_secret ) {
        $response = wp_remote_get( "https://public.catalogflow.ai/v1/catalog/product/{$sku}", array(
            'headers' => array(
                'Content-Type'  => 'application/json',
                'x-app-key'     => $api_key,
                'x-secret-key'  => $api_secret,
            ),
        ));

        return $response;
    }
}
