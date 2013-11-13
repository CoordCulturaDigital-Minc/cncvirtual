<?php

require(dirname(__FILE__) . '/includes/theme-options/theme-options.php');
require(dirname(__FILE__) . '/includes/db-updates.php');

function minc_scripts() {
    global $wp_query;
    
    if (is_archive()) {
        // remove o script que cuida da votação e adiciona js que gera a versão da votação para este tema
        wp_dequeue_script('evaluation');
        wp_enqueue_script('evaluation-list', get_stylesheet_directory_uri() . '/js/evaluation-list.js', array('jquery'));
    }
    
    wp_enqueue_script('minc', get_stylesheet_directory_uri() . '/js/minc.js', array('jquery'));
    
    if (is_admin()) {
        wp_enqueue_script('minc-cadastro', get_stylesheet_directory_uri() . '/js/minc-cadastro.js', array('jquery'));
        wp_localize_script('minc-cadastro', 'minc', array('ajaxurl' => admin_url('admin-ajax.php')));
    }
    
    if (!is_admin()) {
        wp_enqueue_style('barra-minc', get_stylesheet_directory_uri() . '/css/barra_minc.css');
    }
}
add_action('wp_print_scripts', 'minc_scripts', 50);

// adiciona script no login
add_action('login_head', function() {
    wp_register_script('minc-login', get_stylesheet_directory_uri() . '/js/minc-login.js', array('jquery'));
    wp_print_scripts(array('minc-login'));
});

/**
 * Retorna HTML com as cidades de um determinado estado.
 * 
 * @param string $uf sigla do estado
 * @param string $selected estado selecionado
 * @return string
 */
function minc_get_cities_options($uf, $currentCity = '') {
    global $wpdb;

    $uf_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM uf WHERE sigla LIKE %s", $uf));

    if (!$uf_id) {
        return "<option value=''>Selecione um estado...</option>";
    }

    $cidades = $wpdb->get_results($wpdb->prepare("SELECT * FROM municipio WHERE ufid = %d order by nome", $uf_id));

    $output = '';

    if (is_array($cidades) && count($cidades) > 0) {
        foreach ($cidades as $cidade) {
            $selected = selected($currentCity, $cidade->nome);
            $output .= "<option value='{$cidade->nome}' $selected>{$cidade->nome}</option>";
        }
    }

    return $output;
}

/**
 * Joga para a tela um HTML com as cidades de um estado.
 * 
 * @return null
 */
function minc_print_cities_options() {
    echo minc_get_cities_options($_POST['uf'], $_POST['selected']);
    die;
}

add_action('wp_ajax_nopriv_minc_get_cities_options', 'minc_print_cities_options');
add_action('wp_ajax_minc_get_cities_options', 'minc_print_cities_options');

/**
 * Retorna os estados
 */
function get_states() {
    global $wpdb;
    return $wpdb->get_results("SELECT * from uf ORDER BY sigla");
}

/**
 * Verifica se um CPF é válido ou não.
 * 
 * @param int $cpf
 * @return boolean
 */
function validaCPF($cpf) {
    // Verifiva se o número digitado contém todos os digitos
    $cpf = str_pad(ereg_replace('[^0-9]', '', $cpf), 11, '0', STR_PAD_LEFT);

    // Verifica se nenhuma das sequências abaixo foi digitada, caso seja, retorna falso
    if (strlen($cpf) != 11 || $cpf == '00000000000' || $cpf == '11111111111' || $cpf == '22222222222' || $cpf == '33333333333' || $cpf == '44444444444' || $cpf == '55555555555' || $cpf == '66666666666' || $cpf == '77777777777' || $cpf == '88888888888' || $cpf == '99999999999') {
        return false;
    } else {   // Calcula os números para verificar se o CPF é verdadeiro
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf{$c} * (($t + 1) - $c);
            }

            $d = ((10 * $d) % 11) % 10;

            if ($cpf{$c} != $d) {
                return false;
            }
        }

        return true;
    }
}

/* Campos adicionais do usuário */

add_action('edit_user_profile', 'minc_edit_user_details');
add_action('show_user_profile', 'minc_edit_user_details');
function minc_edit_user_details($user) {
    ?>
    <table class="form-table">
        <tr>
            <th>
                <label>CPF</label>
            </th>
            <td>
                <input id="cpf" type="text" name="cpf" value="<?php echo esc_attr(get_user_meta($user->ID, 'cpf', true)); ?>" /><br />
            </td>
        </tr>
        <tr>
            <th><label>Estado</label></th>
            <td>
                <select name="estado" id="estado">
                    <option value=""> Selecione </option>
                    <?php $states = get_states(); ?>
                    <?php foreach ($states as $s): ?>
                        <option value="<?php echo $s->sigla; ?>"  <?php if (get_user_meta($user->ID, 'estado', true) == $s->sigla) echo 'selected'; ?>  >
                            <?php echo $s->nome; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th><label>Município</label></th>
            <td>
                <select name="municipio" id="municipio">
                    <?php echo minc_get_cities_options(get_user_meta($user->ID, 'estado', true), get_user_meta($user->ID, 'municipio', true)); ?>
                </select>
            </td>
        </tr>
        <tr>
            <th><label>Área de Atuação</label></th>
            <td>
                <select name="atuacao"  id="atuacao">
                    <?php $areas = minc_get_theme_option('areas_atuacao'); $areas = explode("\n", $areas); ?>
                    <?php foreach ($areas as $area): ?>
                        <?php $san_area = esc_attr(trim($area)); ?>
                        <option value="<?php echo $san_area; ?>" <?php if (get_user_meta($user->ID, 'atuacao', true) == $san_area) echo 'selected'; ?> ><?php echo $area; ?></option>
                    <?php endforeach; ?>
                    <option value="outra_area_cultura" <?php if (get_user_meta($user->ID, 'atuacao', true) == 'outra_area_cultura') echo 'selected'; ?> >Outra área de cultura</option>
                    <option value="nao_cultura" <?php if (get_user_meta($user->ID, 'atuacao', true) == 'nao_cultura') echo 'selected'; ?> >Não ligado(a) a nenhuma área cultural</option>
                </select>
                <div id="outra_atuacao_container">
                    <p>
                        Especifique: <br /> <input type="text" name="atuacao_outra" value="<?php echo esc_attr(get_user_meta($user->ID, 'atuacao_outra', true)); ?>" />
                    </p>
                </div>
            </td>
        </tr>
        <tr>
            <th><label>Ocupação</label></th>
            <td>
                <select name="ocupacao">
                    <?php $areas = minc_get_theme_option('ocupacoes'); $areas = explode("\n", $areas); ?>
                    <?php foreach ($areas as $area): ?>
                        <?php $san_area = esc_attr(trim($area)); ?>
                        <option value="<?php echo $san_area; ?>" <?php if (get_user_meta($user->ID, 'ocupacao', true) == $san_area) echo 'selected'; ?> ><?php echo $area; ?></option>
                    <?php endforeach; ?>
                    <option value="outra" <?php if (get_user_meta($user->ID, 'ocupacao', true) == 'outra') echo 'selected'; ?> >Outra</option>
                </select>
                <div id="outra_ocupacao_container">
                    <p>
                        Especifique: <br /> <input type="text" name="ocupacao_outra" value="<?php echo esc_attr(get_user_meta($user->ID, 'ocupacao_outra', true)); ?>" />
                    </p>
                </div>
            </td>
        </tr>
        <tr>
            <th><label>Campos dos usuários pré-cadastrados</label></th>
            <td>
                Categoria: <br /> <input type="text" name="categoria" value="<?php echo esc_attr(get_user_meta($user->ID, 'categoria', true)); ?>" />
                <br /><br />
                Sub-Categoria: <br /> <input type="text" name="sub_categoria" value="<?php echo esc_attr(get_user_meta($user->ID, 'sub_categoria', true)); ?>" />
            </td>
        </tr>
        <tr>
            <th><label>Entidade que representa</label></th>
            <td>
                <label>Nome da entidade</label><br />
                <input id="instituicao_nome" type="text" name="instituicao_nome" value="<?php echo esc_attr(get_user_meta($user->ID, 'instituicao', true)); ?>" /><br />			
                <label>CNPJ</label><br />
                <input id="cnpj" type="text" name="cnpj" value="<?php echo esc_attr(get_user_meta($user->ID, 'cnpj', true)); ?>" />
            </td>
        </tr>
    </table>
    <?php
}

add_action('personal_options_update', 'minc_save_user_details');
add_action('edit_user_profile_update', 'minc_save_user_details');
/**
 * Save creators custom fields add via 
 * administrative profile edit page.
 * 
 * @param int $user_id
 * @return null
 */
function minc_save_user_details($user_id) {
    update_user_meta($user_id, 'cpf', $_POST['cpf']);
    update_user_meta($user_id, 'estado', $_POST['estado']);
    update_user_meta($user_id, 'municipio', $_POST['municipio']);
    update_user_meta($user_id, 'atuacao', $_POST['atuacao']);
    update_user_meta($user_id, 'atuacao_outra', $_POST['atuacao_outra']);
    update_user_meta($user_id, 'ocupacao', $_POST['ocupacao']);
    update_user_meta($user_id, 'ocupacao_outra', $_POST['ocupacao_outra']);
    update_user_meta($user_id, 'instituicao', $_POST['instituicao_nome']);
    update_user_meta($user_id, 'categoria', $_POST['categoria']);
    update_user_meta($user_id, 'sub_categoria', $_POST['sub_categoria']);
    update_user_meta($user_id, 'cnpj', $_POST['cnpj']);
}

add_action('wp_ajax_troca_senha', 'minc_troca_senha');
/**
 * Troca a senha do usuário
 * 
 * @return null
 */
function minc_troca_senha() {
    if (!$_POST['nova'] || !$_POST['atual'] || !$_POST['nova_confirm'] || ($_POST['nova_confirm'] != $_POST['nova'])) {
        echo 'erro';
        die;
    }

    $cur_user = wp_get_current_user();

    if (wp_check_password($_POST['atual'], $cur_user->user_pass, $cur_user->ID)) {
        wp_set_password($_POST['nova'], $cur_user->ID);

        //refaz o login
        if (is_ssl() && force_ssl_login() && !force_ssl_admin()) {
            $secure_cookie = false;
        } else {
            $secure_cookie = '';
        }

        $user = wp_signon(array('user_login' => $cur_user->user_login, 'user_password' => $_POST['nova']), $secure_cookie);

        echo 'ok';
    } else {
        echo 'senha inválida';
    }

    die;
}

add_action('init', function() {
    // remove o link para o perfil do usuario no header
    remove_action('consulta_show_user_link', 'consulta_show_user_link');
});

add_action('consulta_show_user_link', function() {
    // quando clica no nome do usuario no header abre caixa para mudar de senha
    global $current_user;
    ?>
    <div id="logged-user-name" class="hl-lightbox">
        <?php echo substr($current_user->display_name, 0, 38); ?>
                  
        <section id="alterar-senha" class="hl-lightbox-dialog">
            <header>
                <a href="#" class="hl-lightbox-close"></a>
                <h1>Alterar senha</h1>
            </header>
    		<p>Informe sua senha atual: <br/> <input type="password" id="troca_senha_atual" /></p>
    		<p>Nova senha: <br/> <input type="password" id="troca_senha" /></p>
    		<p>Confirme a nova senha: <br/> <input type="password" id="troca_senha_confirm" /></p>
    		<p>
    			<input type="button" value="Trocar" class="button-submit"  id="troca_senha_submit" />
    
    			<?php html::image('wait.gif', '', '', array('id' => 'troca_senha_loading', 'class' => 'ajax-feedback', 'style' => 'vertical-align:middle;')); ?>
    			<?php html::image('x.png', '', '', array('id' => 'troca_senha_error', 'class' => 'ajax-feedback', 'style' => 'vertical-align:middle;')); ?>
    			<?php html::image('ok.png', '', '', array('id' => 'troca_senha_success', 'class' => 'ajax-feedback', 'style' => 'vertical-align:middle;')); ?>
    		</p>
        </section>
    </div>
    <?php
});

// exibe dados extras do perfil do usuário
add_action('consulta_user_profile', function($authorInfo) {
    ?>
    <div id="profile_data">
        <?php if ($state = get_user_meta($authorInfo->ID, 'estado', true)) : ?>
            <p><b>Estado:</b> <?php echo $state; ?></p>
        <?php endif; ?>
        
        <?php if ($city = get_user_meta($authorInfo->ID, 'municipio', true)) : ?>
            <p><b>Cidade:</b> <?php echo $city; ?></p>
        <?php endif; ?>
        
        <?php if ($area = get_user_meta($authorInfo->ID, 'atuacao', true)) : ?>
            <p><b>Área de atuação:</b> <?php echo $area; ?></p>
        <?php endif; ?>
        
        <?php if ($occupation = get_user_meta($authorInfo->ID, 'ocupacao', true)) : ?>
            <p><b>Ocupação:</b> <?php echo $occupation; ?></p>
        <?php endif; ?>
        
        <?php if ($institution = get_user_meta($authorInfo->ID, 'instituicao_nome', true)) : ?>
            <p><b>Entidade que representa:</b> <?php echo $institution; ?></p>
        <?php endif; ?>
    </div>
    <?php
});


add_action('wp_print_styles', function() {
    $options = get_option('consulta_theme_options');
    $linkColor = isset($options['link_color']) ? $options['link_color'] : '#00A0D0';
    $titleColor = isset($options['title_color']) ? $options['title_color'] : '#006633';
    ?>
    <style>
    /* Colors */
    
    .tema #object_evaluation label { cursor: pointer; background-color: #e6e6e6; padding: 3px 5px; border-radius: 4px; -moz-border-radius: 4px; -webkit-border-radius: 4px; }
    .tema #object_evaluation label.checked { background-color: <?php echo $linkColor; ?>; color: white; }
    .suggest_author { color: <?php echo $linkColor; ?>; }
    
    </style>
    
    <link rel="stylesheet" type="text/css" href="http://www.cultura.gov.br/minc-internet-lf6_1ga2-theme/css/barra-brasil.css?browserId=other&themeId=mincinternetlf6_1ga2_WAR_mincinternetlf6_1ga2theme&languageId=pt_BR&b=6120&t=1365596309000" />
    <?php
    
}, 20);

// permite que usuários comuns acessem apenas a página
// do seu perfil no admin
add_action('admin_init', function() {
    global $pagenow;
    
    $user = wp_get_current_user();

    if ($pagenow != 'profile.php' && in_array('subscriber', $user->roles)) {
        wp_redirect(admin_url('profile.php'));
        exit;
    }
});

// remove entradas do menu do wp-admin para usuários comuns
add_action('admin_menu', function() {
    $user = wp_get_current_user();
    
    if (in_array('subscriber', $user->roles)) {
        remove_menu_page('index.php');
        remove_menu_page('edit.php');
        remove_menu_page('edit.php?post_type=object');
        remove_menu_page('edit-comments.php');
        remove_menu_page('tools.php');
    }
});

add_action('wp_before_admin_bar_render', function() {
    $user = wp_get_current_user();
    
    if (in_array('subscriber', $user->roles)) {
        global $wp_admin_bar;
        
        $wp_admin_bar->remove_menu('comments');
        $wp_admin_bar->remove_menu('new-content');
    }
});

//remetente dos emails:
add_filter( 'wp_mail_from', 'custom_mail_sender_email');
add_filter( 'wp_mail_from_name', 'custom_mail_sender_name');

function custom_mail_sender_name($from_name) {
	return 'Consulta Pública';
}

function custom_mail_sender_email($from_email) {
	return get_option('admin_email');
}

// redireciona a página de registro do wordpress para a página de registro do vale cultura
add_action('login_form_register', function() {
    wp_safe_redirect(home_url('cadastro'));
    die;
});

// redireciona os usuários, exceto admins, para a página principal da consulta
add_filter("login_redirect", function($redirect_to, $request, $user) {
    if (isset($user->roles) && is_array($user->roles)) {
        if (in_array('administrator', $user->roles)) {
            return $redirect_to;
        } else {
            return home_url(get_theme_option('object_url'));
        }
    }
    
    return $redirect_to;
}, 10, 3);

// cabeçalho do minc
add_action('consulta_body_top', function() {
    ?>
    <div id="geral"> 
        <div id="barra-brasil"> 
            <div class="barra"> 
                <ul> 
                    <li><a href="http://www.acessoainformacao.gov.br" class="ai" title="Acesso à informação">www.sic.gov.br</a></li> 
                    <li><a href="http://www.brasil.gov.br" class="brasilgov" title="Portal de Estado do Brasil">www.brasil.gov.br</a></li> 
                </ul> 
            </div> 
        </div> 
        <div id="wrapper"> <a href="#main-content" id="skip-to-content">Pular para o conteúdo</a> 
            <header id="banner" role="banner"> 
                <div id="topo"> 
                    <div id="sombra-topo"> 
                        <div id="barra-superior"> 
                            <ul id="barra-compartilhe"> 
                                <li id="rss"><a href="http://www.cultura.gov.br/rss"><img src="http://www.cultura.gov.br/minc-internet-lf6_1ga2-theme/images/minc/rss.png" title="RSS" alt="RSS"></a></li>
                                <li id="facebook"><a href="https://www.facebook.com/MinisterioDaCultura" target="_blank"><img src="http://www.cultura.gov.br/minc-internet-lf6_1ga2-theme/images/minc/facebook.png" title="Facebook" alt="Facebook"></a></li> 
                                <li id="twitter"><a href="https://twitter.com/culturagovbr" target="_blank"><img src="http://www.cultura.gov.br/minc-internet-lf6_1ga2-theme/images/minc/twitter.png" title="Twitter" alt="Twitter"></a></li> 
                                <li id="youtube"><a href="http://www.youtube.com/ministeriodacultura/" target="_blank"><img src="http://www.cultura.gov.br/minc-internet-lf6_1ga2-theme/images/minc/youtube.png" title="Youtube" alt="Youtube"></a></li> 
                                <li id="mais"><a href="http://www.flickr.com/photos/ministeriodacultura"><img src="http://www.cultura.gov.br/minc-internet-lf6_1ga2-theme/images/minc/flickr.png" title="Flickr" alt="Flickr"></a></li> 
                            </ul> 
                            <ul id="barra-fale"> 
                                <li><a href="http://www.cultura.gov.br/fale-com-o-minc">Fale com o Ministério</a> <span class="nao-italico"> |</span><a href="http://www.cultura.gov.br/ouvidoria"> Ouvidoria</a></li> 
                            </ul>
     
                            <nav class="sort-pages modify-pages" id="navigation"> 
                                <h1> <span>Navegação</span> </h1> 
                                <ul id="aui_3_4_0_1_543"> <!-- Carregando a classe Util Feita pela SEA --> <!-- configura o groupId grupo do guest--> <!-- Group ID da Sessao de Comunicacao do Portal --> 
                                    <li id="aui_3_4_0_1_692"> <a href="http://www.cultura.gov.br/inicio" tabindex="-1" id="aui_3_4_0_1_564"><span id="aui_3_4_0_1_695">cultura.gov</span></a> </li> 
                                    <li id="aui_3_4_0_1_659" class=""> <a href="http://www.cultura.gov.br/acesso-a-informacao" tabindex="-1" id="aui_3_4_0_1_566"><span id="aui_3_4_0_1_662"> Acesso à Informação</span></a> 
                                        
                                    </li> 
                                    <li id="aui_3_4_0_1_655" class=""> <a href="http://www.cultura.gov.br/o-ministerio" tabindex="-1" id="aui_3_4_0_1_596"><span> O Ministério</span></a> 
                                    
                                </li> 
                                <li id="aui_3_4_0_1_650" class=""> <a href="http://www.cultura.gov.br/apoio-a-projetos" tabindex="-1" id="aui_3_4_0_1_612"><span id="aui_3_4_0_1_653"> Apoio a Projetos</span></a> 
                                    
                                </li> 
                                <li id="aui_3_4_0_1_680" class=""> <a href="http://www.cultura.gov.br/o-dia-a-dia-da-cultura" tabindex="-1" id="aui_3_4_0_1_620"><span id="aui_3_4_0_1_679"> O dia a dia da Cultura</span></a> 
                                    
                                </li> 
                                    <li id="barra-bandeiras"> <span> <a class="taglib-icon" href="http://www.cultura.gov.br/inicio?p_p_id=82&amp;p_p_lifecycle=1&amp;p_p_state=normal&amp;p_p_mode=view&amp;p_p_col_count=2&amp;_82_struts_action=%2Flanguage%2Fview&amp;_82_redirect=%2F&amp;languageId=pt_BR" id="xeen" lang="pt-BR" tabindex="-1"> <img class="icon" src="http://www.cultura.gov.br/minc-internet-lf6_1ga2-theme/images/language/pt_BR.png" alt="português (Brasil)" title="português (Brasil)"> </a> </span> </li> 
                                </ul> 
                            </nav> 
                        </div> 
                    </div> 
                </div> 
            </header>   
        </div> 
    </div>
    <?php
});

// redireciona pagina de cadastro do WP para pagina propria de cadastro do tema
add_action('init', function() {
    global $pagenow;
    
    if ('wp-login.php' == $pagenow && isset($_REQUEST['action']) && $_REQUEST['action'] == 'register') {
        wp_redirect(home_url('cadastro'));
        exit();
    }
});
