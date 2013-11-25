<?php

// sobrescreve o arquivo com o mesmo nome no template pai para permitir que o usuário
// vote sem precisar abrir o objeto

$postId = get_the_ID();
$evaluationLabel = get_theme_option('evaluate_button');
$evaluationOptions = get_theme_option('evaluation_labels');
$userVote = str_replace('_label_', 'label_', get_user_vote($postId));

?>

<?php if (get_theme_option('evaluation_show_on_list') && (get_theme_option('evaluation_public_results') || is_user_logged_in())) : ?>
    <div class="show_evaluation" title="<?php _e('Opiniões', 'consulta'); ?>">
        <span class="count_object_votes_icon"></span>
    	<span class="count_object_votes">
    		<?php echo count_votes($post->ID); ?>
    	</span>
    	<?php echo $evaluationLabel; ?>
    </div>
<?php endif; ?>

<?php if (is_user_logged_in()): ?>
    <div class="user_evaluation">
        <form id="object_evaluation">
            <div class="object_evaluation_feedback" style="display: none; margin-right: 5px;"><img src="<?php bloginfo('stylesheet_directory'); ?>/img/accept.png" alt="" /><div style="float:right">&nbsp;Voto computado!</div></div>
            <input type="hidden" id="post_id" name="post_id" value="<?php the_ID(); ?>" />    	
            <?php foreach ($evaluationOptions as $key => $value) : ?>
                <?php if (empty($value)) break; ?>
                <input type="radio" id="<?php echo $key; ?>" name="object_evaluation" <?php checked($userVote === $key); ?> />

                <label class="<?php if( !$key ) echo "nao-avaliar"; ?> <?php if ($userVote === $key OR (!$userVote && !$key) ) echo ' checked'; ?>">
                    <?php echo $value; ?></label>
                <?php //var_dump($userVote ,$key); ?>
            <?php endforeach; ?>
            
        </form>
    </div>
    
    <p class="js-feedback clear" style="display:none;">
        <em><?php echo get_theme_option('evaluation_limit_msg'); ?></em>
    </p>
<?php endif; ?>
