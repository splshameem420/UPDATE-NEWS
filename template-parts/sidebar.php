<aside class="bdnews-sidebar yt-suggest-sidebar">

    <div class="yt-suggest-list">
        <?php
        $suggested_args = array(
            'posts_per_page'      => 20,
            'post_status'         => 'publish',
            'orderby'             => 'date',
            'order'               => 'DESC',
            'ignore_sticky_posts' => 1
        );
        $suggested_query = new WP_Query( $suggested_args );

        if ( $suggested_query->have_posts() ) :
            while ( $suggested_query->have_posts() ) : $suggested_query->the_post();
        ?>
            <a href="<?php the_permalink(); ?>" class="yt-suggest-item">
                <div class="yt-suggest-thumb">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <?php the_post_thumbnail( 'medium' ); ?>
                    <?php else : ?>
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/default.jpg" alt="No Image">
                    <?php endif; ?>
                </div>

                <div class="yt-suggest-info">
                    <h4 class="yt-suggest-title"><?php the_title(); ?></h4>
                    <div class="yt-suggest-meta">
                        <span class="views">👁️ <?php echo function_exists('bd_get_post_views') ? bd_get_post_views( get_the_ID() ) : '০'; ?> </span>
                        <span class="dot">•</span>
                        <span class="time"><?php echo human_time_diff( get_post_time('U'), current_time('timestamp') ); ?> ago</span>
                    </div>
                </div>
            </a>
        <?php
            endwhile;
            wp_reset_postdata();
        endif;
        ?>
    </div>
</aside>