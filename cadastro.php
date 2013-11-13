<?php
/*
Template Name: Cadastro
*/

wp_enqueue_script('minc-cadastro', get_bloginfo('stylesheet_directory') . '/js/minc-cadastro.js', array('jquery'));
wp_localize_script('minc-cadastro', 'minc', array('ajaxurl' => admin_url('admin-ajax.php')));
$msgs = array();

if (isset($_POST['action']) && $_POST['action'] == 'register') {
    $user_login = sanitize_user($_POST['user_email']);
    $user_email = $_POST['user_email'];
    $user_pass = $_POST['user_pass'];
    $errors = array();
    
    if (!isset($_POST['concordo']) || $_POST['concordo'] != 1) {
        $errors['termos'] = __('É preciso concordar com os termos de uso da plataforma', 'minc');
    }
    
    if (username_exists($user_login)) {
        $errors['user'] =  __('Já existe um usário com este nome no nosso sistema. Por favor, escolha outro nome.', 'minc');
    }

    if (email_exists($user_email)) {
        $errors['email'] =  __('Este e-mail já está registrado em nosso sistema. Por favor, cadastre-se com outro e-mail.', 'minc');
    }
    
    if (!filter_var( $user_email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] =  __('O e-mail informado é inválido.', 'minc');
    }
    
    if ($_POST['user_pass'] != $_POST['user_pass_c']) {
        $errors['pass_confirm'] =  __('As senhas informadas não são iguais.', 'minc');
    }
    
    if (!validaCPF($_POST['cpf'])) { 
        $errors['valid_cpf'] =  __('CPF inválido', 'minc');
    }
    
    if (strlen($user_email) == 0) {
        $errors['email'] =  __('O e-mail é obrigatório para o cadastro no site.', 'minc');
    }
        
    if (strlen($_POST['nome']) == 0) {
        $errors['email'] =  __('O e-mail é obrigatório para o cadastro no site.', 'minc');
    }

    if (strlen($user_pass) == 0) {
        $errors['pass'] =  __('A senha é obrigatória para o cadastro no site.', 'minc');
    }
        
    if (strlen($_POST['estado']) == 0 || strlen($_POST['municipio']) == 0) {
        $errors['local'] = __('Por favor selecione um estado e um município', 'minc');
    }

    if (!sizeof($errors) > 0) {
        $data['user_login'] = $user_login;
        $data['user_pass'] = $user_pass;
        $data['user_email'] =  $user_email;
        $data['display_name'] = $_POST['nome'];
        
        $data['role'] = 'subscriber' ;
        $user_id = wp_insert_user($data);
        
        if (!$user_id) {
            if ($errmsg = $user_id->get_error_message('blog_title')) {
                echo $errmsg;
            }
        }
        
        update_user_meta($user_id, 'cpf', $_POST['cpf']);
        update_user_meta($user_id, 'estado', $_POST['estado']);
        update_user_meta($user_id, 'municipio', $_POST['municipio']);
        update_user_meta($user_id, 'atuacao', $_POST['atuacao']);
        update_user_meta($user_id, 'atuacao_outra', $_POST['atuacao_outra']);
        update_user_meta($user_id, 'ocupacao', $_POST['ocupacao']);
        update_user_meta($user_id, 'ocupacao_outra', $_POST['ocupacao_outra']);
        
        if ($_POST['instituicao_nome']) {
            update_user_meta($user_id, 'instituicao', $_POST['instituicao_nome']);
        }
            
        if ($_POST['cnpj']) {    
            update_user_meta($user_id, 'cnpj', $_POST['cnpj']);
        }
        
        wp_new_user_notification($user_id, $user_pass);
        
        do_action('minc_user_register', $user_id);
        
        // depois de fazer o registro, faz login
        if (is_ssl() && force_ssl_login() && !force_ssl_admin() && (0 !== strpos($redirect_to, 'https')) && (0 === strpos($redirect_to, 'http'))) {
            $secure_cookie = false;
        } else {
            $secure_cookie = '';
        }

        $user = wp_signon(array('user_login' => $user_login, 'user_password' => $user_pass), $secure_cookie);

        if (!is_wp_error($user)) {
            wp_safe_redirect( $_POST['redirect_to'] );
            exit();
        }
    } else {
        foreach($errors as $type=>$msg)
            $msgs['error'][] = $msg;
    }
}

the_post();

?>

<?php get_header(); ?>
<section id="main-section" class="span-15 prepend-1 append-1">		
	<article id="post-<?php the_ID(); ?>" <?php post_class('clearfix');?>>	  
		<header>					
			<h1>Cadastro</h1>					
		</header>
		<div class="post-content clearfix">
            <?php print_msgs($msgs);?>
            
            <?php if (!is_user_logged_in()): ?>
                <?php the_content(); ?>
                
                <form class="minc_register" method="post">
                    <input type="hidden" name="redirect_to" value="<?php echo home_url(); ?>" />
                    <input type="hidden" name="action" value="register" />
                    
                    <h3 class="subtitulo"><?php _oi('Dados para login no sistema', 'Cadastro: titulo da área de email e senha'); ?></h3>
                    <p>   
                        <label>Email</label><br />
                        <input id="email" type="text" name="user_email" value="<?php echo isset($_POST['user_email']) ? esc_attr($_POST['user_email']) : ''; ?>" /><br />
                        <label>Senha</label><br />
                        <input id="pass" type="password" name="user_pass" /><br />
                        <label>Confirmar senha</label><br />
                        <input id="pass_c" type="password" name="user_pass_c" /><br />
                    </p>    
                    <h3 class="subtitulo"><?php _oi('Sobre você', 'Cadastro: titulo da área de nome, cpf, etc'); ?></h3>
                    <p>
                        <label>Nome</label><br />
                        <input id="nome" type="text" name="nome" value="<?php echo isset($_POST['nome']) ? esc_attr($_POST['nome']) : ''; ?>" /><br />
                        
                        <label>CPF</label><br />
                        <input id="cpf" type="text" name="cpf" value="<?php echo isset($_POST['cpf']) ? esc_attr($_POST['cpf']) : ''; ?>" /><br />
                        <label>Estado</label><br />
                        <select name="estado" id="estado">
                            <option value=""> Selecione </option>
                            <?php $states = get_states(); ?>
                            <?php foreach ($states as $s): ?>
                                <option value="<?php echo $s->sigla; ?>"  <?php if (isset($_POST['estado']) && $_POST['estado'] == $s->sigla) echo 'selected'; ?>  >
                                    <?php echo $s->nome; ?>
                                </option>
                            <?php endforeach; ?>
                        </select><br />
                        <label>Município</label><br />
                        <select name="municipio" id="municipio">
                            <option value="">Selecione</option>
                        </select><br />			
                        <label>Área de Atuação</label><br />
                        <select name="atuacao" id="atuacao">
                            <?php $areas = minc_get_theme_option('areas_atuacao'); $areas = explode("\n", $areas); ?>
                            <?php foreach ($areas as $area) : ?>
                                <?php $san_area = esc_attr(trim($area)); ?>
                                <option value="<?php echo $san_area; ?>" <?php if (isset($_POST['atuacao']) && $_POST['atuacao'] == $san_area) echo 'selected'; ?> ><?php echo $area; ?></option>
                            <?php endforeach; ?>
                            <option value="outra_area_cultura" <?php if (isset($_POST['atuacao']) && $_POST['atuacao'] == 'outra_area_cultura') echo 'selected'; ?> >Outra área de cultura</option>
                            <option value="nao_cultura" <?php if (isset($_POST['atuacao']) && $_POST['atuacao'] == 'nao_cultura') echo 'selected'; ?> >Não ligado(a) a nenhuma área cultural</option>
                        </select>
                        <span id="atuacao_outra_container">
                            <br />
                            Especifique: <br /> <input type="text" name="atuacao_outra" value="<?php echo isset($_POST['atuacao_outra']) ? esc_attr($_POST['atuacao_outra']) : ''; ?>" />
                        </span>
                        <br />
                        <label>Ocupação</label><br />
                        <select name="ocupacao" id="ocupacao">
                            <?php $areas = minc_get_theme_option('ocupacoes'); $areas = explode("\n", $areas); ?>
                            <?php foreach ($areas as $area) : ?>
                                <?php $san_area = esc_attr(trim($area)); ?>
                                <option value="<?php echo $san_area; ?>" <?php if (isset($_POST['ocupacao']) && $_POST['ocupacao'] == $san_area) echo 'selected'; ?> ><?php echo $area; ?></option>
                            <?php endforeach; ?>
                            <option value="outra" <?php if (isset($_POST['ocupacao']) && $_POST['ocupacao'] == 'outra') echo 'selected'; ?> >Outra</option>
                        </select>
                        
                        <span id="ocupacao_outra_container">
                            <br />
                            Especifique: <br /> <input type="text" name="ocupacao_outra" value="<?php echo isset($_POST['ocupacao_outra']) && esc_attr($_POST['ocupacao_outra']); ?>" />
                        </span>
                    </p>
                    <p>
                        <?php _oi('Se você representa uma entidade, insira os dados abaixo', 'Cadastro: dados da entidade'); ?>
                        <br /><br />	
                        <label>Nome da entidade</label><br />
                        <input id="instituicao_nome" type="text" name="instituicao_nome" value="<?php echo isset($_POST['instituicao_nome']) ? esc_attr($_POST['instituicao_nome']) : ''; ?>" /><br />			
                        <label>CNPJ</label><br />
                        <input id="cnpj" type="text" name="cnpj" value="<?php echo isset($_POST['cnpj']) ? esc_attr($_POST['cnpj']) : ''; ?>" />
                    </p>
                    <p><input type="checkbox" name="concordo" value="1" <?php isset($_POST['concordo']) ? checked($_POST['concordo'], true) : ''; ?>/> Li e concordo com os <a href="<?php echo network_site_url('termos-de-uso'); ?>">termos de uso</a> da consulta.</p>
                    <input type="submit" value="Cadastrar" class="button-submit" />
                </form>
            <?php else: ?>
                <p>
                    <?php _oi('Você já está cadastrado!', 'Cadastro: Mensagem que aparece nesta página quando usuário já está logado'); ?>
                </p>
            <?php endif; ?>
		</div>
		<!-- .post-content -->
	</article>
	<!-- .post -->
</section>
<!-- #main-section -->
<aside id="main-sidebar" class="span-6 append-1 last">
	<?php get_sidebar(); ?>
</aside>
<?php get_footer(); ?>
