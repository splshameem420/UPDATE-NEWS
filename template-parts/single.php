

<main id="primary" class="site-main news-container">
    <div class="news-wrapper">
        
        <!-- Main Content Area -->
        <div class="news-content-area">
            <?php
            while ( have_posts() ) :
                the_post();
            ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('single-news-post'); ?>>
                    
                    <!-- Category & Title -->
                    <header class="news-header">
                        <div class="news-category">
                            <?php the_category(' '); ?>
                        </div>
                        <h1 class="news-title"><?php the_title(); ?></h1>
                    </header>

                    <!-- Meta Information -->
                    <div class="news-meta">
                        <span class="meta-item author">
                            👤 <?php the_author(); ?>
                        </span>
                        <span class="meta-item date user-local-date" data-utc-time="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                            📅 Loading...
                        </span>
                        <span class="meta-item time-ago">
                            ⏱️ Published <?php echo human_time_diff( get_post_time('U'), current_time('timestamp') ); ?> ago
                        </span>
                        <span class="meta-item views">
                            👁️ <?php echo bd_get_post_views( get_the_ID() ); ?>
                        </span>

                    </div>

                    <!-- Featured Image -->
                    <?php if ( has_post_thumbnail() ) : ?>
                        <div class="news-featured-image">
                            <?php the_post_thumbnail('large'); ?>
                            <?php if ( get_the_post_thumbnail_caption() ) : ?>
                                <figcaption class="image-caption">
                                    <?php echo get_the_post_thumbnail_caption(); ?>
                                </figcaption>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Body Content -->
                    <div class="news-body-content">
                        <?php
                        the_content();

                        wp_link_pages( array(
                            'before' => '<div class="page-links">পৃষ্ঠা: ',
                            'after'  => '</div>',
                        ) );
                        ?>
                    </div>

                    <!-- Tags -->
                    <footer class="news-footer">
                        <?php if (has_tag()) : ?>
                            <div class="news-tags">
                                <span class="tag-label">Tags:</span>
                                <?php the_tags('', ' ', ''); ?>
                            </div>
                        <?php endif; ?>
                    </footer>

                    <!-- Related Posts -->
                    <div class="related-news-section">
                        <h3 class="related-title"> Releted News </h3>
                        <div class="related-news-grid">
                            <?php
                            $orig_post = $post;
                            global $post;

                            $args = array(
                                'posts_per_page'      => 4,
                                'post__not_in'        => array($post->ID),
                                'ignore_sticky_posts' => 1 
                            );

                            $latest_query = new WP_Query( $args );

                            if ( $latest_query->have_posts() ) {
                                while ( $latest_query->have_posts() ) {
                                    $latest_query->the_post();
                                    ?>
                                    <article class="related-card">
                                        <a href="<?php the_permalink(); ?>" class="related-link">
                                            <?php if (has_post_thumbnail()) : ?>
                                                <div class="related-thumb">
                                                    <?php the_post_thumbnail('medium'); ?>
                                                </div>
                                            <?php endif; ?>
                                            <h4 class="related-card-title"><?php the_title(); ?></h4>
                                        </a>
                                    </article>
                                    <?php
                                }
                            }

                            $post = $orig_post;
                            wp_reset_postdata();
                            ?>

                            <?php
                            $orig_post = $post;
                            global $post;

                            $args = array(
                                'posts_per_page'      => 4,
                                'post__not_in'        => array($post->ID),
                                'meta_key'            => 'bd_post_views_count',
                                'orderby'             => 'meta_value_num',
                                'order'               => 'DESC',
                                'ignore_sticky_posts' => 1
                            );

                            $popular_query = new WP_Query( $args );
                            if ( ! $popular_query->have_posts() ) {
                                $args['orderby'] = 'comment_count';
                                $popular_query = new WP_Query( $args );
                            }

                            if ( $popular_query->have_posts() ) {
                                while ( $popular_query->have_posts() ) {
                                    $popular_query->the_post();
                                    ?>
                                    <article class="related-card">
                                        <a href="<?php the_permalink(); ?>" class="related-link">
                                            <?php if (has_post_thumbnail()) : ?>
                                                <div class="related-thumb">
                                                    <?php the_post_thumbnail('medium'); ?>
                                                </div>
                                            <?php endif; ?>
                                            <h4 class="related-card-title"><?php the_title(); ?></h4>
                                        </a>
                                    </article>
                                    <?php
                                }
                            }

                            $post = $orig_post;
                            wp_reset_postdata();
                            ?>

                            <?php
                            $orig_post = $post;
                            global $post;
                            $categories = get_the_category($post->ID);
                            if ($categories) {
                                $category_ids = array();
                                foreach($categories as $individual_category) $category_ids[] = $individual_category->term_id;
                                $args = array(
                                    'category__in' => $category_ids,
                                    'post__not_in' => array($post->ID),
                                    'posts_per_page' => 4,
                                    'ignore_sticky_posts' => 1
                                );
                                $my_query = new wp_query( $args );
                                if( $my_query->have_posts() ) {
                                    while( $my_query->have_posts() ) {
                                        $my_query->the_post();
                                        ?>
                                        <article class="related-card">
                                            <a href="<?php the_permalink(); ?>" class="related-link">
                                                <?php if (has_post_thumbnail()) : ?>
                                                    <div class="related-thumb">
                                                        <?php the_post_thumbnail('medium'); ?>
                                                    </div>
                                                <?php endif; ?>
                                                <h4 class="related-card-title"><?php the_title(); ?></h4>
                                            </a>
                                        </article>
                                        <?php
                                    }
                                }
                            }
                            $post = $orig_post;
                            wp_reset_postdata();
                            ?>
                        </div>
                    </div>

                </article>
            <?php endwhile; ?>
        </div>

        <!-- Sidebar Area -->
        <aside class="news-sidebar">
            <?php get_sidebar(); ?>
        </aside>

    </div>
</main>

