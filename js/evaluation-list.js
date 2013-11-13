jQuery(document).ready(function() {
    // voto do usuario em uma das opcoes da avaliacao de um objeto
    // substitui o controle da votação do evaluation.js
    jQuery('#object_evaluation label').live('click', function() {
        var radioButton = jQuery(this).prev();
        var label = jQuery(this);
        jQuery('body').css('cursor', 'progress');
        jQuery.ajax({
            url: consulta.ajaxurl,
            type: 'post',
            data: {action: 'object_evaluation', userVote: radioButton.attr('id'), postId: jQuery(this).siblings('#post_id').val() },
            dataType: 'json',
            success: function(data) {
                
                jQuery('body').css('cursor', 'auto');
                
                //caso não tenha registrado o voto, não faz nada por enquanto
                if(!data.voted){
                    label.parents('.interaction').find('.js-feedback').slideDown().delay(5000).slideUp();
                    return;
                }
                
                radioButton.closest('li').find('.count_object_votes').html(data.count);
                radioButton.parents('li').children('.evaluation_container').html(data.html);
                if(data.count)
                label.attr('checked', true);
                label.siblings('label').removeClass('checked');
                label.addClass('checked');
                jQuery('label.nao-avaliar').show();
                jQuery('label.nao-avaliar.checked').hide();
                radioButton.parent().find('.object_evaluation_feedback').show();
                radioButton.parent().find('.object_evaluation_feedback').delay(1500).fadeOut('slow');
                jQuery('.tema .evaluation_container .user_evaluation').remove();
            }
        });
    });
    
    // controla a exibicao da caixa de avaliacao na listagem de objetos
    jQuery('.show_evaluation').click(function() {
        jQuery(this).parent().siblings('.evaluation_container').toggle('slow');
    });
    
    jQuery('label.nao-avaliar.checked').hide();
    
    // remove o html da votação na listagem de objetos já que neste tema ela aparece em outro lugar
    jQuery('.tema .evaluation_container .user_evaluation').remove()
});
