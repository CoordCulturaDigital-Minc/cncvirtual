<?php

// cria opção para as áreas de atuação e ocupações
if (!get_option('minc-theme-db-update-1')) {
    update_option('minc-theme-db-update-1', 1);
    require_once('ocupacoes.php');
    
    $theme_options = get_option('minc_theme_options', array());
    $theme_options = array_merge($theme_options, $ocupacoes);
    update_option('minc_theme_options', $theme_options);
}

// cria as tabelas de municipios e ufs
if (!get_option('minc-theme-db-update-2')) {
    update_option('minc-theme-db-update-2', 1);
    global $wpdb;
    require_once('brasil.php');
    
    foreach ($brasil_queries as $query) {
        $wpdb->query($query);
    }
}