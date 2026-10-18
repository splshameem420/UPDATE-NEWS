<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
$custom_header_menu = get_option( 'bd_news_custom_header_menu', array() );
$social_links       = get_option( 'bd_news_social_links', array() );
$topbar_text        = get_option( 'bd_news_topbar_text', '' );
$header_ad_code     = get_option( 'bd_news_header_ad_code', '' );
?>

<header id="masthead" class="site-header">
    
    <!-- TOP BAR -->
    <div class="top-header-bar">
        <div class="header-container flex-between">
            <div class="top-bar-left">
                <span class="visitor-time-info">
                    <i class="dashicons dashicons-calendar-alt"></i> 
                    <span><strong>Local Date:</strong> <span id="v-date">Loading...</span></span>
                    <i class="dashicons dashicons-clock"></i>
                    <span><strong>Local Time:</strong> <span id="v-clock">--:--:--</span></span>

                    <?php if ( ! empty( $topbar_text ) ) : ?>
                        <div class="topbar-announcement">
                            <span class="notice-badge">Notice</span>
                            <span class="notice-text"><?php echo esc_html( $topbar_text ); ?></span>
                        </div>
                    <?php endif; ?>
                </span>

                <span class="divider">|</span>

                <div class="visitor-tracker-info">
                    <span><strong>IP:</strong> <span id="v-ip">Loading...</span></span>
                    <span><strong>Country:</strong> <span id="v-country">Loading...</span></span>
                    <span><strong>Region:</strong> <span id="v-region">Loading...</span></span>
                    <span><strong>City:</strong> <span id="v-city">Loading...</span></span>
                    <span><strong>ISP:</strong> <span id="v-isp">Loading...</span></span>
                    <span><strong>Server:</strong> <span id="v-server">Loading...</span></span>
                </div>
            </div>

            <div class="top-bar-right">
                <?php if ( ! empty( $social_links ) && is_array( $social_links ) ) : ?>
                    <div class="top-social-links">
                        <?php foreach ( $social_links as $social ) : 
                            $name = isset( $social['name'] ) ? $social['name'] : '';
                            $url  = isset( $social['url'] ) ? $social['url'] : '';
                            $icon = function_exists('bd_news_get_social_icon') ? bd_news_get_social_icon( $name ) : 'fa-solid fa-link';
                            if ( empty( $url ) ) continue;
                            ?>
                            <a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer" class="social-icon-item" title="<?php echo esc_attr( ucfirst($name) ); ?>">
                                <i class="<?php echo esc_attr( $icon ); ?>"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <nav class="top-navigation">
                        <?php 
                        if ( has_nav_menu( 'top_bar' ) ) {
                            wp_nav_menu( array( 
                                'theme_location' => 'top_bar', 
                                'container'      => false,
                                'depth'          => 1
                            ) ); 
                        }
                        ?>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- MID HEADER (LOGO & AD) -->
    <div class="mid-header-bar">
        <div class="header-container flex-between">
            <div class="site-branding">
                <?php if ( has_custom_logo() ) : ?>
                    <div class="site-logo"><?php the_custom_logo(); ?></div>
                <?php else : ?>
                    <h1 class="site-title">
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
                            <?php bloginfo( 'name' ); ?>
                        </a>
                    </h1>
                    <p class="site-description"><?php bloginfo( 'description' ); ?></p>
                <?php endif; ?>
            </div>

            <?php if ( ! empty( $header_ad_code ) ) : ?>
                <div class="header-ad-banner">
                    <?php echo $header_ad_code; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- MAIN NAVIGATION BAR -->
    <nav id="site-navigation" class="main-navigation">
        <div class="header-container flex-between">
            <div class="nav-menu-wrapper">
                <?php if ( ! empty( $custom_header_menu ) && is_array( $custom_header_menu ) ) : ?>
                    <ul id="primary-menu" class="menu">
                        <?php foreach ( $custom_header_menu as $item ) : 
                            $title = isset( $item['title'] ) ? $item['title'] : '';
                            $url   = isset( $item['url'] ) ? $item['url'] : '#';
                            if ( empty( $title ) ) continue;
                            ?>
                            <li class="menu-item">
                                <a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $title ); ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <?php
                    if ( has_nav_menu( 'primary' ) ) {
                        wp_nav_menu( array(
                            'theme_location' => 'primary',
                            'menu_id'        => 'primary-menu',
                            'container'      => false,
                        ) );
                    }
                    ?>
                <?php endif; ?>
            </div>

            <div class="header-actions">
                <button id="dark-mode-toggle" class="action-btn" aria-label="Toggle Dark Mode">🌙</button>
                <div class="header-search-trigger">
                    <form role="search" method="get" class="header-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                        <input type="search" class="search-field" placeholder="Search News..." value="<?php echo get_search_query(); ?>" name="s" />
                        <button type="submit" class="search-submit">🔍</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

</header>
