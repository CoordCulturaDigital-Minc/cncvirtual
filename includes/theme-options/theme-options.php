<?php

function minc_get_theme_default_options() {
    return array();
}

function minc_get_theme_option($option_name) {
    $option = wp_parse_args( 
        get_option('minc_theme_options'),
        minc_get_theme_default_options()
    );
    return isset($option[$option_name]) ? $option[$option_name] : false;
}

add_action('admin_init', 'minc_theme_options_init');
add_action('admin_menu', 'minc_theme_options_menu', 100);

function minc_theme_options_init() {
    register_setting('minc_theme_options', 'minc_theme_options', 'minc_theme_options_validate_callback');
}

function minc_theme_options_menu() {
    $page_title = 'Áreas de atuação e ocupações';
    $menu_title = 'Áreas de atuação e ocupações';
    
    add_submenu_page('theme_options', $page_title, $menu_title, 'manage_options', 'child_theme_options', 'minc_theme_options_page_callback');
}

function minc_theme_options_validate_callback($input) {
    return $input;
}

function minc_theme_options_page_callback() {
?>
    <div class="wrap span-20">
        <h2><?php echo __('Áreas de atuação e ocupações', 'minc'); ?></h2>
        <br /><br />
        <form action="options.php" method="post" class="clear prepend-top">
            <?php settings_fields('minc_theme_options'); ?>
            <?php $options = wp_parse_args( 
                get_option('minc_theme_options'), 
                minc_get_theme_default_options()
            );?>
            <div class="span-20 ">
                <div class="span-6 last">
                    <label for="areas_atuacao"><strong>Áreas de Atuação (1 por linha)</strong></label><br/>
                    <textarea id="areas_atuacao" class="all-options" name="minc_theme_options[areas_atuacao]"><?php echo isset($options['areas_atuacao']) ? htmlspecialchars($options['areas_atuacao']) : ''; ?></textarea>
                    <br/><br/>
                    
                    <label for="ocupacoes"><strong>Ocupações (1 por linha)</strong></label><br/>
                    <textarea id="ocupacoes" class="all-options" name="minc_theme_options[ocupacoes]"><?php echo isset($options['ocupacoes']) ? htmlspecialchars($options['ocupacoes']) : ''; ?></textarea>
                    <br/><br/>
                    
                </div>
            </div>
            
            <p class="textright clear prepend-top">
                <input type="submit" class="button-primary" value="<?php _e('Save Changes'); ?>" />
            </p>
        </form>
    </div>

<?php 
}