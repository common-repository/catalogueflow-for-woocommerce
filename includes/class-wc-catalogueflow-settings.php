<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class catflowcV1_Settings {

    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'add_settings_page' ) );
        add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
    }

    public static function add_settings_page() {
        add_options_page(
            'CatalogueFlow Settings',
            'CatalogueFlow',
            'manage_options',
            'wc-catalogueflow-settings',
            array( __CLASS__, 'settings_page' )
        );
    }

    public static function register_settings() {
        register_setting( 'catflowcV1_settings', 'catflowcV1_app_key' );
        register_setting( 'catflowcV1_settings', 'catflowcV1_app_secret' );
        register_setting( 'catflowcV1_settings', 'catflowcV1_enable_virtual_assistant' );

        register_setting( 'catflowcV1_settings', 'catflowcV1_capabilities', array(
            'default' => array(
                'name' => 1,
                'description' => 1,
                'shortDescription' => 1,
                'metaDescription' => 1,
                'metaKeywords' => 1,
                'highlight' => 1,
                'ean' => 0
            )
        ));
        register_setting( 'catflowcV1_settings', 'catflowcV1_description_format', array(
            'default' => '<p>Initial_description</p>
<h3>CARACTERISTICS</h3>
<ul>
    <li>caracteristic_1</li>
    <li>caracteristic_2</li>
    <li>caracteristic_n...</li>
</ul>
<h3>TECHNICAL DETAILS</h3>
<ul>
    <li>technical_detail 1</li>
    <li>technical_detail_2</li>
    <li>technical_detail_n...</li>
</ul>
<p>final_description</p>'
        ));
        register_setting( 'catflowcV1_settings', 'catflowcV1_short_description_format' );
        register_setting( 'catflowcV1_settings', 'catflowcV1_sizes' );
        register_setting( 'catflowcV1_settings', 'catflowcV1_use_other_fields' );
        register_setting( 'catflowcV1_settings', 'catflowcV1_language' );
    }

    public static function settings_page() {
        $webhook_url = rest_url('wc-catalogueflow/v1/webhook');
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'CatalogueFlow Settings', 'catalogueflow-for-woocommerce' ); ?></h1>
            <p><?php esc_html_e( 'Para obtener tu App Key y App Secret, regístrate en', 'catalogueflow-for-woocommerce' ); ?> <a href="https://www.catalogflow.ai/es/woocommerce.html" target="_blank">CatalogueFlow</a>. <?php esc_html_e( 'El registro es gratuito', 'catalogueflow-for-woocommerce' ); ?></p>
            <form method="post" action="options.php">
                <?php settings_fields( 'catflowcV1_settings' ); ?>
                <?php do_settings_sections( 'catflowcV1_settings' ); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">App Key</th>
                        <td>
                            <input type="text" name="catflowcV1_app_key" value="<?php echo esc_attr( get_option('catflowcV1_app_key') ); ?>" />
                            <p class="description"><?php esc_html_e( 'La clave de la aplicación proporcionada por CatalogueFlow.', 'catalogueflow-for-woocommerce' ); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">App Secret</th>
                        <td>
                            <input type="password" name="catflowcV1_app_secret" value="<?php echo esc_attr( get_option('catflowcV1_app_secret') ); ?>" />
                            <p class="description"><?php esc_html_e( 'El secreto de la aplicación proporcionado por CatalogueFlow.', 'catalogueflow-for-woocommerce' ); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Language</th>
                        <td>
                            <select name="catflowcV1_language">
                                <option value="Español" <?php selected( get_option('catflowcV1_language'), 'Español' ); ?>><?php esc_html_e( 'Español', 'catalogueflow-for-woocommerce' ); ?></option>
                                <option value="Inglés" <?php selected( get_option('catflowcV1_language'), 'Inglés' ); ?>><?php esc_html_e( 'Inglés', 'catalogueflow-for-woocommerce' ); ?></option>
                                <!-- Añadir más opciones de idioma según sea necesario -->
                            </select>
                            <p class="description"><?php esc_html_e( 'El idioma en el que se generarán las descripciones.', 'catalogueflow-for-woocommerce' ); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php esc_html_e( 'Habilitar o deshabilitar Personal Shopper', 'catalogueflow-for-woocommerce' ); ?></th>
                        <td>
                            <input type="checkbox" name="catflowcV1_enable_virtual_assistant" value="1" <?php checked(1, get_option('catflowcV1_enable_virtual_assistant', 0)); ?> />
                            <p class="description"><?php esc_html_e( 'Habilita esta opción si quieres mostrar personal shopper en tu tienda.', 'catalogueflow-for-woocommerce' ); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e( 'Capabilities', 'catalogueflow-for-woocommerce' ); ?></th>
                        <td>
                            <?php $capabilities = get_option('catflowcV1_capabilities', array(
                                'name' => 1,
                                'description' => 1,
                                'shortDescription' => 1,
                                'metaDescription' => 1,
                                'metaKeywords' => 1,
                                'highlight' => 1,
                                'ean' => 0
                            )); ?>
                            <div class="capability-item">
                                <label>
                                    <input type="checkbox" name="catflowcV1_capabilities[name]" value="1" <?php checked( 1, isset($capabilities['name']) ? $capabilities['name'] : 0 ); ?> /> Name
                                </label>
                                <p class="description"><?php esc_html_e( 'Activa esta opción para permitir que CatalogueFlow reescriba el nombre del producto.', 'catalogueflow-for-woocommerce' ); ?></p>
                            </div>
                            <div class="capability-item">
                                <label>
                                    <input type="checkbox" name="catflowcV1_capabilities[description]" value="1" <?php checked( 1, isset($capabilities['description']) ? $capabilities['description'] : 0 ); ?> /> <?php esc_html_e( 'Description', 'catalogueflow-for-woocommerce' ); ?>
                                </label>
                                <p class="description"><?php esc_html_e( 'Activa esta opción para generar una descripción larga del producto.', 'catalogueflow-for-woocommerce' ); ?></p>
                            </div>
                            <div class="capability-item">
                                <label>
                                    <input type="checkbox" name="catflowcV1_capabilities[shortDescription]" value="1" <?php checked( 1, isset($capabilities['shortDescription']) ? $capabilities['shortDescription'] : 0 ); ?> /> <?php esc_html_e( 'Short Description', 'catalogueflow-for-woocommerce' ); ?>
                                </label>
                                <p class="description"><?php esc_html_e( 'Activa esta opción para generar una descripción corta del producto.', 'catalogueflow-for-woocommerce' ); ?></p>
                            </div>
                            <div class="capability-item">
                                <label>
                                    <input type="checkbox" name="catflowcV1_capabilities[metaDescription]" value="1" <?php checked( 1, isset($capabilities['metaDescription']) ? $capabilities['metaDescription'] : 0 ); ?> /> <?php esc_html_e( 'Meta Description', 'catalogueflow-for-woocommerce' ); ?>
                                </label>
                                <p class="description"><?php esc_html_e( 'Activa esta opción para generar una meta descripción del producto para SEO.', 'catalogueflow-for-woocommerce' ); ?></p>
                            </div>
                            <div class="capability-item">
                                <label>
                                    <input type="checkbox" name="catflowcV1_capabilities[metaKeywords]" value="1" <?php checked( 1, isset($capabilities['metaKeywords']) ? $capabilities['metaKeywords'] : 0 ); ?> /> <?php esc_html_e( 'Meta Keywords', 'catalogueflow-for-woocommerce' ); ?>
                                </label>
                                <p class="description"><?php esc_html_e( 'Activa esta opción para generar palabras clave meta del producto para SEO.', 'catalogueflow-for-woocommerce' ); ?></p>
                            </div>
                            <div class="capability-item">
                                <label>
                                    <input type="checkbox" name="catflowcV1_capabilities[highlight]" value="1" <?php checked( 1, isset($capabilities['highlight']) ? $capabilities['highlight'] : 0 ); ?> /> <?php esc_html_e( 'Highlight', 'catalogueflow-for-woocommerce' ); ?>
                                </label>
                                <p class="description"><?php esc_html_e( 'Activa esta opción para generar puntos destacados del producto.', 'catalogueflow-for-woocommerce' ); ?></p>
                            </div>
                            <div class="capability-item">
                                <label>
                                    <input type="checkbox" name="catflowcV1_capabilities[ean]" value="1" <?php checked( 1, isset($capabilities['ean']) ? $capabilities['ean'] : 0 ); ?> /> <?php esc_html_e( 'EAN', 'catalogueflow-for-woocommerce' ); ?>
                                </label>
                                <p class="description"><?php esc_html_e( 'Activa esta opción para permitir la generación del código EAN del producto.', 'catalogueflow-for-woocommerce' ); ?></p>
                            </div>
                            <p class="description"><?php esc_html_e( 'Selecciona las capacidades que deseas habilitar para la generación de descripciones.', 'catalogueflow-for-woocommerce' ); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e( 'Description Format', 'catalogueflow-for-woocommerce' ); ?></th>
                        <td>
                            <textarea name="catflowcV1_description_format" rows="10" cols="50"><?php echo esc_textarea( get_option('catflowcV1_description_format', '<p>Initial_description</p>
<h3>CARACTERISTICS</h3>
<ul>
    <li>caracteristic_1</li>
    <li>caracteristic_2</li>
    <li>caracteristic_n...</li>
</ul>
<h3>TECHNICAL DETAILS</h3>
<ul>
    <li>technical_detail 1</li>
    <li>technical_detail_2</li>
    <li>technical_detail_n...</li>
</ul>
<p>final_description</p>') ); ?></textarea>
                            <p class="description"><?php esc_html_e( 'El formato en el que se generará la descripción larga.', 'catalogueflow-for-woocommerce' ); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e( 'Short Description Format', 'catalogueflow-for-woocommerce' ); ?></th>
                        <td>
                            <textarea name="catflowcV1_short_description_format" rows="5" cols="50"><?php echo esc_textarea( get_option('catflowcV1_short_description_format', '<p>short_description_text</p>') ); ?></textarea>
                            <p class="description"><?php esc_html_e( 'El formato en el que se generará la descripción corta.', 'catalogueflow-for-woocommerce' ); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Sizes</th>
                        <td>
                            <?php $sizes = get_option('catflowcV1_sizes', array('title' => 70, 'description' => 2000, 'shortDescription' => 200)); ?>
                            <?php esc_html_e( 'Title:', 'catalogueflow-for-woocommerce' ); ?> <input type="number" name="catflowcV1_sizes[title]" value="<?php echo esc_attr( $sizes['title'] ); ?>" /><br>
                            <?php esc_html_e( 'Description:', 'catalogueflow-for-woocommerce' ); ?> <input type="number" name="catflowcV1_sizes[description]" value="<?php echo esc_attr( $sizes['description'] ); ?>" /><br>
                            <?php esc_html_e( 'Short Description:', 'catalogueflow-for-woocommerce' ); ?> <input type="number" name="catflowcV1_sizes[shortDescription]" value="<?php echo esc_attr( $sizes['shortDescription'] ); ?>" />
                            <p class="description"><?php esc_html_e( 'Define los tamaños máximos para el título, la descripción y la descripción corta.', 'catalogueflow-for-woocommerce' ); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e( 'Use Other Fields', 'catalogueflow-for-woocommerce' ); ?></th>
                        <td>
                            <input type="checkbox" name="catflowcV1_use_other_fields" value="1" <?php checked( 1, get_option('catflowcV1_use_other_fields', 1) ); ?> /> <?php esc_html_e( 'Activar otros campos', 'catalogueflow-for-woocommerce' ); ?>
                            <p class="description"><?php esc_html_e( 'Activa esta opción para utilizar otros campos personalizados del producto en la generación de descripciones.', 'catalogueflow-for-woocommerce' ); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e( 'Webhook URL', 'catalogueflow-for-woocommerce' ); ?></th>
                        <td>
                            <input type="text" readonly value="<?php echo esc_attr( $webhook_url ); ?>" />
                            <p class="description"><?php esc_html_e( 'Esta es la URL del webhook que deberás configurar en tu cuenta de CatalogueFlow en', 'catalogueflow-for-woocommerce' ); ?> <a href="https://app.catalogueflow.com/settings/webhooks" target="_blank"><?php esc_html_e( 'tus configuraciones de webhooks', 'catalogueflow-for-woocommerce' ); ?></a>.</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
            <p><?php esc_html_e( '¿No tienes cuenta?', 'catalogueflow-for-woocommerce' ); ?> <a href="https://www.catalogflow.ai/es/woocommerce.html" target="_blank"><?php esc_html_e( 'Regístrate aquí', 'catalogueflow-for-woocommerce' ); ?></a></p>
            <p><a href="https://www.youtube.com/watch?v=your_video_id" target="_blank"><?php esc_html_e( 'Mira el tutorial en video', 'catalogueflow-for-woocommerce' ); ?></a></p>
        </div>
        <?php
    }
}
