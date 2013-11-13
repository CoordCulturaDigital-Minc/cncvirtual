jQuery(window).load(function() {
    //oculta a barra do minc até a página ser carregada
    jQuery("body").css("margin-top", '0');
    jQuery("#geral").css("display", 'block');
});
jQuery(document).ready(function() {
    //troca de senha
    jQuery('#troca_senha_submit').click(function() {
        var atual = jQuery('#troca_senha_atual').val();
        var nova = jQuery('#troca_senha').val();
        var nova_confirm = jQuery('#troca_senha_confirm').val();
        
        if (nova != nova_confirm) {
            alert('Nova senha e senha de confirmação estão diferentes');
            return;
        }
        
        if (!atual || !nova || !nova_confirm) {
            alert('Por favor preencha todos os campos');
            return;
        }
        
        jQuery('#troca_senha_atual').attr('disabled', true);
        jQuery('#troca_senha').attr('disabled', true);
        jQuery('#troca_senha_confirm').attr('disabled', true);
        jQuery('#troca_senha_submit').attr('disabled', true);
        
        jQuery('#troca_senha_error').hide();
        jQuery('#troca_senha_success').hide();
        jQuery('#troca_senha_loading').show();
        
        jQuery.ajax({
            url: consulta.ajaxurl, 
            type: 'post',
            data: {action: 'troca_senha', atual: atual, nova: nova, nova_confirm: nova_confirm},
            success: function(data) {
                jQuery('#troca_senha_loading').hide();
                
                if (data == 'ok') {
                    jQuery('#troca_senha_success').show();
                } else {
                    alert(data);
                    jQuery('#troca_senha_error').show();
                }
                
                jQuery('#troca_senha_atual').attr('disabled', false);
                jQuery('#troca_senha').attr('disabled', false);
                jQuery('#troca_senha_confirm').attr('disabled', false);
                jQuery('#troca_senha_submit').attr('disabled', false);
                
            }
        });
    });
});
