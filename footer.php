<?php
/**
 * Dynamic Footer Template
 */

// Top Bar Social Links
$social_links = get_option( 'bd_news_social_links', array() );
$brand_sites  = get_option( 'bd_news_brand_sites', array() );

$brand_direction  = get_option( 'bd_news_brand_direction', 'left_to_right' );
$scroll_class     = ( $brand_direction === 'right_to_left' ) ? 'scroll-rtl' : 'scroll-ltr';

// Mid Section Data
$col1_title = get_option( 'bd_news_footer_about_title', 'Company' );
$col1_text  = get_option( 'bd_news_footer_about_text', 'Red The Latest News Reports In Our Website.' );
$col1_btn_t = get_option( 'bd_news_footer_btn_text', 'Subscribe Now' );
$col1_btn_u = get_option( 'bd_news_footer_btn_url', '#' );

$col2_title = get_option( 'bd_news_footer_col2_title', 'Category' );
$col2_menu  = get_option( 'bd_news_footer_col2_menu', array() );

$col3_title = get_option( 'bd_news_footer_col3_title', 'Company' );
$col3_menu  = get_option( 'bd_news_footer_col3_menu', array() );

$col4_title = get_option( 'bd_news_footer_col4_title', 'Contact' );
$col4_menu  = get_option( 'bd_news_footer_col4_menu', array() );

// Low Section Data
$footer_menu      = get_option( 'bd_news_custom_footer_menu', array() );
$footer_copyright = get_option( 'bd_news_footer_copyright', '© ' . date('Y') . ' Update News. All rights reserved.' );
?>

<footer class="site-footer">

    <!-- 1. FOOTER TOP -->
    <div class="footer-top">
        <div class="footer-container">
            <div class="footer-top-content">
                
                <div class="footer-brand-wrapper">
                    <h2 class="footer-main-title">
                        <a href="<?php echo esc_url( home_url('/') ); ?>"><?php bloginfo( 'name' ); ?></a>
                    </h2>

                    <?php if ( ! empty( $brand_sites ) && is_array( $brand_sites ) ) : ?>
                        <div class="brand-horizontal-ticker">
                            <span class="ticker-label">Our Network:</span>
                            <div class="horizontal-slider-wrapper" id="brandSliderWrapper">
                                <div class="horizontal-track <?php echo esc_attr( $scroll_class ); ?>" id="brandSliderTrack">
                                    
                                    <!-- Original Items -->
                                    <div class="brand-track-group">
                                        <?php foreach ( $brand_sites as $site ) : 
                                            $s_name = isset($site['name']) ? $site['name'] : '';
                                            $s_logo = isset($site['logo']) ? trim($site['logo']) : '';
                                            $s_url  = isset($site['url']) ? $site['url'] : '#';
                                        ?>
                                            <div class="brand-logo-item">
                                                <a href="<?php echo esc_url( $s_url ); ?>" target="_blank" rel="noopener">
                                                    <?php if ( ! empty( $s_logo ) ) : ?>
                                                        <img src="<?php echo esc_url( $s_logo ); ?>" alt="<?php echo esc_attr( $s_name ); ?>" title="<?php echo esc_attr( $s_name ); ?>" />
                                                    <?php else : ?>
                                                        <span class="text-logo-badge"><?php echo esc_html( $s_name ); ?></span>
                                                    <?php endif; ?>
                                                </a>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <!-- Duplicate Items for Infinite Loop -->
                                    <div class="brand-track-group duplicate-group">
                                        <?php foreach ( $brand_sites as $site ) : 
                                            $s_name = isset($site['name']) ? $site['name'] : '';
                                            $s_logo = isset($site['logo']) ? trim($site['logo']) : '';
                                            $s_url  = isset($site['url']) ? $site['url'] : '#';
                                        ?>
                                            <div class="brand-logo-item">
                                                <a href="<?php echo esc_url( $s_url ); ?>" target="_blank" rel="noopener">
                                                    <?php if ( ! empty( $s_logo ) ) : ?>
                                                        <img src="<?php echo esc_url( $s_logo ); ?>" alt="<?php echo esc_attr( $s_name ); ?>" title="<?php echo esc_attr( $s_name ); ?>" />
                                                    <?php else : ?>
                                                        <span class="text-logo-badge"><?php echo esc_html( $s_name ); ?></span>
                                                    <?php endif; ?>
                                                </a>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="footer-social-icons">
                    <?php if ( ! empty( $social_links ) && is_array( $social_links ) ) : ?>
                        <?php foreach ( $social_links as $social ) : 
                            $name = isset( $social['name'] ) ? strtolower( trim( $social['name'] ) ) : '';
                            $url  = isset( $social['url'] ) ? $social['url'] : '#';
                            $icon = function_exists( 'bd_news_get_social_icon' ) ? bd_news_get_social_icon( $name ) : 'fa-solid fa-link';
                        ?>
                            <a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer" class="social-icon">
                                <i class="<?php echo esc_attr( $icon ); ?>"></i>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

    <!-- 2. FOOTER MID (Controlled via Admin Footer Menu) -->
    <div class="footer-mid">
        <div class="footer-container">
            <div class="footer-mid-grid">
                
                <!-- Column 1 -->
                <div class="footer-col">
                    <h3 class="footer-widget-title"><?php echo esc_html( $col1_title ); ?></h3>
                    <p><?php echo esc_html( $col1_text ); ?></p>
                    <?php if ( ! empty( $col1_btn_t ) ) : ?>
                        <a href="<?php echo esc_url( $col1_btn_u ); ?>" class="subscribe-btn"><?php echo esc_html( $col1_btn_t ); ?></a>
                    <?php endif; ?>
                </div>

                <!-- Column 2 -->
                <div class="footer-col">
                    <h3 class="footer-widget-title"><?php echo esc_html( $col2_title ); ?></h3>
                    <?php if ( ! empty( $col2_menu ) && is_array( $col2_menu ) ) : ?>
                        <ul>
                            <?php foreach ( $col2_menu as $item ) : ?>
                                <li><a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

                <!-- Column 3 -->
                <div class="footer-col">
                    <h3 class="footer-widget-title"><?php echo esc_html( $col3_title ); ?></h3>
                    <?php if ( ! empty( $col3_menu ) && is_array( $col3_menu ) ) : ?>
                        <ul>
                            <?php foreach ( $col3_menu as $item ) : ?>
                                <li><a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

                <!-- Column 4 -->
                <div class="footer-col">
                    <h3 class="footer-widget-title"><?php echo esc_html( $col4_title ); ?></h3>
                    <?php if ( ! empty( $col4_menu ) && is_array( $col4_menu ) ) : ?>
                        <ul>
                            <?php foreach ( $col4_menu as $item ) : ?>
                                <li><a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

    <!-- 3. FOOTER LOW -->
    <div class="footer-low">
        <div class="footer-container footer-low-flex">
            <div class="footer-copyright">
                <p> <a href="<?php echo esc_url( home_url('/') ); ?>"><?php bloginfo( 'name' ); ?> </a> &copy; <?php echo date(" Y "); echo esc_html( $footer_copyright ); ?></p>
            </div>

            <?php if ( ! empty( $footer_menu ) && is_array( $footer_menu ) ) : ?>
                <ul class="footer-low-menu">
                    <?php foreach ( $footer_menu as $item ) : ?>
                        <li><a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

</footer>

<?php wp_footer(); ?>
</body>
</html>