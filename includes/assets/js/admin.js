jQuery(document).ready(function($) {

    function startDescriptionGeneration(product_id, nonce) {
        console.log("Generando descripcion para producto: ", product_id);
        $('#wc-catalogueflow-loading').show();

        $.post(wcCatalogueFlow.ajax_url, {
            action: 'catflowcV1_generate_description',
            nonce: nonce,
            product_id: product_id
        }, function(response) {
            if (response.success) {
                checkDescriptionStatus(response.data.sku, nonce, true, 10);
            } else {
                $('#wc-catalogueflow-loading').hide();
                console.log("Error", response);
            }
        });
    }

    function checkDescriptionStatus(sku, nonce, firstCheck, attempts) {

        console.log("Consultando a la api de catalogueflow por el producto: ", sku, ", Intento pendientes: ", attempts);

        var timeout = firstCheck ? 10000 : 5000; // 10 segundos para la primera verificación, 5 segundos para las siguientes
        var attempts = attempts || 10;

        setTimeout(function() {
            $.post(wcCatalogueFlow.ajax_url, {
                action: 'catflowcV1_check_status',
                nonce: nonce,
                sku: sku
            }, function(response) {

                if(attempts <= 0){
                    console.log("To many intents", response);
                    return;
                }

                if (response.success) {
                    $('#wc-catalogueflow-loading').hide();
                    // Actualizar la descripción del editor WYSIWYG
                    if (typeof tinymce !== 'undefined') {
                        var editor = tinymce.get('content');
                        if (editor) {
                            editor.setContent(response.data.description);
                        } else {
                            $('#content').val(response.data.description);
                        }
                    } else {
                        $('#content').val(response.data.description);
                    }
                } else {
                    attempts--;
                    checkDescriptionStatus(sku, nonce, false, attempts); // Siguientes verificaciones
                }
            });
        }, timeout); // Verificar según el tiempo establecido
    }

    $('#wc-catalogueflow-generate-description').on('click', function(e) {
        e.preventDefault();
        var product_id = $('#post_ID').val();
        var nonce = wcCatalogueFlow.nonce;
        startDescriptionGeneration(product_id, nonce);
    });

    $('#insert-catalogueflow-description').on('click', function(e) {
        e.preventDefault();
        var product_id = $('#post_ID').val();
        var nonce = wcCatalogueFlow.nonce;
        startDescriptionGeneration(product_id, nonce);
    });
});
