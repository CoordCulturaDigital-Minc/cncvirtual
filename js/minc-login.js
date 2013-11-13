jQuery(document).ready(function() {
    // corrige o texto do input para o nome do usuário no login
    if (jQuery("#loginform label[for='user_login']").length) {
        html = jQuery("#loginform label[for='user_login']").html().replace('Nome de usuário', 'Nome de usuário ou e-mail');
        jQuery("#loginform label[for='user_login']").html(html);
    }
});