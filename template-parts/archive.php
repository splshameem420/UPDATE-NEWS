<main class="bdnews-cat-page">

    <header class="bdnews-cat-header">
        <h1>
            <?php the_archive_title(); ?>
        </h1>
        <?php if ( get_the_archive_description() ) : ?>
            <div class="bdnews-archive-desc">
                <?php the_archive_description(); ?>
            </div>
        <?php endif; ?>
    </header>

    <?php if ( have_posts() ) : ?>

        <div class="bdnews-grid">
            <?php while ( have_posts() ) : the_post(); ?>

                <article id="post-<?php the_ID(); ?>" class="bdnews-card">
                    
                    <div class="bdnews-thumb-wrap">
                        <a href="<?php the_permalink(); ?>">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <?php the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy' ) ); ?>
                            <?php else : ?>
                                <img src="https://via.placeholder.com/640x360?text=No+Image" alt="<?php the_title_attribute(); ?>">
                            <?php endif; ?>
                        </a>
                    </div>

                    <div class="bdnews-content">
                        <h2 class="bdnews-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h2>

                        <div class="bdnews-meta">
                            <span><?php echo esc_html( get_the_date() ); ?></span>
                        </div>

                        <p class="bdnews-excerpt">
                            <?php echo esc_html( wp_trim_words( get_the_excerpt(), 18, '...' ) ); ?>
                        </p>
                    </div>

                </article>

            <?php endwhile; ?>
        </div>

        <?php
        the_posts_pagination( array(
            'mid_size'  => 2,
            'prev_text' => 'Ager',
            'next_text' => 'Porer',
        ) );
        ?>

    <?php else : ?>

        <div class="bdnews-no-result">
            <p> Sorry Not Found </p>
        </div>

    <?php endif; ?>

</main>