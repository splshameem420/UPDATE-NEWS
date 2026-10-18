<?php
/**
 * Template Name: Youtube Style Homepage
 * Author: BD News
 */
?>

<div class="yt-container">
    <div class="yt-grid">
        <?php
        $yt_posts = get_posts(array(
            'numberposts' => 100,
            'post_status' => 'publish'
        ));

        if ($yt_posts):
            foreach ($yt_posts as $post):
                setup_postdata($post);
                $categories = get_the_category();
                if ( !empty($categories)) {
                    $cat_name = esc_html($categories[0]->name);
                    $cat_link = esc_url( get_category_link( $categories[0]->term_id ) );
                }
                $au_name = get_the_author();
                ?>
                <div class="yt-card">
                    <!-- Thumbnail with Play Icon / Duration Tag -->
                    <a href="<?php the_permalink(); ?>" class="yt-thumbnail-wrapper">
                        <?php if (has_post_thumbnail()): ?>
                            <?php the_post_thumbnail('medium_large', array('class' => 'yt-thumbnail')); ?>
                        <?php else: ?>
                            <img src="https://via.placeholder.com/640x360" class="yt-thumbnail" alt="News Image">
                        <?php endif; ?>

                        <span class="yt-badge"><?php echo $au_name; ?></span>
                    </a>

                    <!-- Details Section -->
                    <div class="yt-details">
                        <!-- Category/Channel Avatar Circle -->
                        <div class="yt-avatar">
                            <span><?php echo mb_substr($au_name, 0, 1); ?></span>
                        </div>

                        <div class="yt-info">
                            <h3 class="yt-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h3>
                            <div class="yt-meta">
                                <div class="yt-ch-cat">
                                    <a href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>"
                                        class="yt-channel">
                                        <?php echo esc_html($au_name); ?>
                                    </a>
                                    <span class="dot">&bull;</span>
                                    <a href="<?php echo $cat_link; ?>"
                                        class="cat"><?php echo $cat_name; ?>
                                    </a>
                                </div>
                                <div class="yt-meta-sub">
                                    <span><?php echo get_the_date(); ?></span>
                                    <span class="dot">&bull;</span>
                                    <span><?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ago'; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
            endforeach;
            wp_reset_postdata();
        else: ?>
            <p>No Post Found</p>
        <?php endif; ?>
    </div>

</div>