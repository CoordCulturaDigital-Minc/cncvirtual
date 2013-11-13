<li>
    <div class="interaction clearfix">
        <h1>
            <a href="<?php the_permalink();?>" title="<?php the_title_attribute();?>"><?php the_title();?></a>
            <?php if (get_post_meta(get_the_ID(), '_user_created', true)) : ?>
                <span class="suggest_author">(sugerido por <?php the_author_posts_link(); ?></a>)</span>
            <?php endif; ?>
        </h1>
        
        <?php if ($post->post_excerpt): ?>
            <div class="clear"></div>
            <p><?php echo $post->post_excerpt; ?></p>
        <?php endif; ?>
        
        <div class="clear"></div>
        
        <a href="<?php the_permalink();?>#comments"><div class="comments-number" title="<?php _e('Quantidade de comentários', 'consulta');?>"><?php comments_number('0','1','%');?></div></a>
        <?php if (get_theme_option('use_evaluation')) : ?>
            <?php html::part('show_evaluation'); ?>
        <?php endif; ?>
    </div>
    <?php if (get_theme_option('evaluation_show_on_list')) : ?>
        <div class="evaluation_container" style="display: none;">
            <?php html::part('evaluation')?>
        </div>
    <?php endif; ?>
</li>
