jQuery(document).ready(function($) {
    $('#insert-catalogueflow-description').on('click', function(e) {
        e.preventDefault();

        var product_id = $('#post_ID').val();
        var nonce = wcCatalogueFlow.nonce;

        // Mostrar un indicador de carga
        $('#wc-catalogueflow-loading').show();

        $.post(wcCatalogueFlow.ajax_url, {
            action: 'catflowcV1_generate_description',
            nonce: nonce,
            product_id: product_id
        }, function(response) {
            $('#wc-catalogueflow-loading').hide();
            if (!response.success) {
                console.log("Error", response);
            }
        });
    });
});
