<?php
function bd_news_assets() {

    // Font Awesome CSS
    wp_enqueue_style( 
        'font-awesome', 
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', 
        array(), 
        '6.5.1' 
    );

    // Main CSS
    wp_enqueue_style( 'main-style', get_stylesheet_uri() );

    // Responsive CSS
    wp_enqueue_style( 
        'bd_news_responsive', 
        get_template_directory_uri() . '/assets/css/responsive.css', 
        array(), 
        null 
    );

    // Header CSS
    wp_enqueue_style( 
        'bd_news_header', 
        get_template_directory_uri() . '/assets/css/header.css', 
        array(), 
        null 
    );

    // Footer CSS
    wp_enqueue_style( 
        'bd_news_footer', 
        get_template_directory_uri() . '/assets/css/footer.css', 
        array(), 
        null 
    );

    // Homepage CSS
    wp_enqueue_style(
        'bd_news_home',
        get_template_directory_uri() . '/assets/css/home.css',
        array(),
        null
    );

    // Single CSS
    wp_enqueue_style(
        'bd_news_single',
        get_template_directory_uri() . '/assets/css/single.css',
        array(),
        null
    );

    //Sidebar CSS
    wp_enqueue_style(
        'bd_news_sidebar',
        get_template_directory_uri() . '/assets/css/sidebar.css',
        array(),
        null
    );

    //Search CSS
    wp_enqueue_style(
        'bd_news_search',
        get_template_directory_uri() . '/assets/css/search.css',
        array(),
        null
    );

    // Category CSS
    wp_enqueue_style(
        'bd_news_category',
        get_template_directory_uri() . '/assets/css/archive.css',
        array(),
        null
    );

    // Footer JS
    wp_enqueue_script( 
        'bd_news_footer', 
        get_template_directory_uri() . '/assets/js/footer.js', 
        array('jquery'), 
        null, 
        true 
    );

    // Header JS
    wp_enqueue_script( 
        'bd_news_header', 
        get_template_directory_uri() . '/assets/js/header.js', 
        array('jquery'), 
        null, 
        true 
    );

    wp_localize_script( 'bd_news_header', 'bd_news_vars', array(
        'ajax_url' => admin_url( 'admin-ajax.php' )
    ));


}
add_action( 'wp_enqueue_scripts', 'bd_news_assets' );


require_once get_template_directory() 
. '/inc/Header-Settings.php';

require_once get_template_directory()
. '/inc/Footer-Setting.php';

require_once get_template_directory()
. '/inc/Social-Setting.php';

require_once get_template_directory()
. '/inc/Brand-Setting.php';

require_once get_template_directory()
. '/inc/visitor-tracker-logic.php';

require_once get_template_directory()
. '/inc/bot-checker.php';

require_once get_template_directory()
. '/inc/ads-checker.php';


// Social Icon Class Matcher Function
function bd_news_get_social_icon( $name ) {
    $name = strtolower( trim( $name ) );

    switch ( $name ) {
        case 'facebook':
        case 'fb':
            return 'fa-brands fa-facebook';

        case 'youtube':
        case 'yt':
            return 'fa-brands fa-youtube';

        case 'twitter':
        case 'x':
            return 'fa-brands fa-twitter-square';

        case 'instagram':
        case 'insta':
        case 'ig':
            return 'fa-brands fa-instagram';

        case 'telegram':
        case 'tg':
            return 'fa-brands fa-telegram';

        case 'linkedin':
            return 'fa-brands fa-linkedin';

        case 'whatsapp':
        case 'wa':
            return 'fa-brands fa-whatsapp';

        default:
            return 'fa-solid fa-link';
    }
}


function bd_news_vpn_modal_html() {
    $ip       = isset( $GLOBALS['visitor_ip'] ) ? $GLOBALS['visitor_ip'] : '';
    $isp      = isset( $GLOBALS['isp_name'] ) ? $GLOBALS['isp_name'] : '';
    $city     = isset( $GLOBALS['city_location'] ) ? $GLOBALS['city_location'] : '';
    $region   = isset( $GLOBALS['region_name'] ) ? $GLOBALS['region_name'] : '';
    $country  = isset( $GLOBALS['country_name'] ) ? $GLOBALS['country_name'] : '';
    $tz       = isset( $GLOBALS['visitor_timezone'] ) ? $GLOBALS['visitor_timezone'] : '';
    $server   = isset( $GLOBALS['isp_server'] ) ? $GLOBALS['isp_server'] : '';

    ob_start();
    ?>
    <div style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.96); z-index: 999999; display: flex; align-items: center; justify-content: center; padding: 20px; box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
        <div style="background: #ffffff; width: 100%; max-width: 480px; padding: 30px; border-radius: 12px; text-align: center; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5); border-top: 6px solid #dc2626; box-sizing: border-box;">
            <div style="font-size: 50px; margin-bottom: 10px; line-height: 1;">🛡️</div>
            <h2 style="color: #0f172a; margin: 0 0 10px 0; font-size: 22px; font-weight: bold;">VPN / Proxy Detected!</h2>
            <p style="color: #475569; font-size: 14px; line-height: 1.6; margin-bottom: 20px;">
                Your <strong>VPN / Proxy</strong> service is active. Please disconnect it and refresh the page.
            </p>
            <div style="background: #f8fafc; padding: 12px 16px; border-radius: 6px; font-size: 13px; color: #334155; margin-bottom: 20px; text-align: left; border: 1px solid #e2e8f0; line-height: 1.8;">
                <div><strong>Your IP:</strong> <span style="color: #dc2626; font-weight: bold;"><?php echo esc_html( $ip ); ?></span></div>
                <div><strong>ISP:</strong> <?php echo esc_html( $isp ); ?></div>
                <div><strong>Location:</strong> <?php echo esc_html( $city . ', ' . $region . ', ' . $country ); ?></div>
                <div><strong>Timezone:</strong> <?php echo esc_html( $tz ); ?></div>
                <div><strong>Server:</strong> <?php echo esc_html( $server ); ?></div>
                <div><strong>Status:</strong> <span style="color: #dc2626; font-weight: bold;">Access Restricted (VPN/Proxy)</span></div>
            </div>
            <button onclick="window.location.reload();" style="background: #2563eb; color: #ffffff; border: none; padding: 12px 24px; font-size: 14px; font-weight: bold; border-radius: 6px; cursor: pointer; width: 100%;">
                🔄 Disconnect VPN &amp; Refresh Page
            </button>
        </div>
    </div>
    <?php
    return trim( ob_get_clean() );
}

// ---------- সার্ভার-সাইড ব্লক (আসল 403) ----------
add_action( 'template_redirect', 'bd_news_access_guard', 1 );
function bd_news_access_guard() {
    // অ্যাডমিন নিজেকে লক করবে না
    if ( current_user_can( 'manage_options' ) ) {
        return;
    }

    // অযাচিত বট
    if ( ! empty( $GLOBALS['is_bot'] ) && empty( $GLOBALS['is_good_bot'] ) ) {
        nocache_headers();
        wp_die( 'Access denied.', 'Forbidden', array( 'response' => 403 ) );
    }

    // VPN / Proxy
    if ( ! empty( $GLOBALS['is_vpn'] ) ) {
        nocache_headers();
        wp_die( bd_news_vpn_modal_html(), 'VPN Access Restricted', array( 'response' => 403 ) );
    }
}

function bd_set_post_views($postID) {
    $count_key = 'bd_post_views_count';
    $count = get_post_meta($postID, $count_key, true);
    if($count=='') {
        $count = 0;
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
    } else {
        $count++;
        update_post_meta($postID, $count_key, $count);
    }
}

function bd_track_post_views ($post_id) {
    if ( !is_single() ) return;
    if ( empty ( $post_id) ) {
        global $post;
        $post_id = $post->ID;    
    }
    bd_set_post_views($post_id);
}
add_action( 'wp_head', 'bd_track_post_views');

function bd_get_post_views($postID){
    $count_key = 'bd_post_views_count';
    $count = get_post_meta($postID, $count_key, true);
    if($count=='') {
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
        return "0 Views";
    }
    return $count . ' Views';
}

// Search Security
// শুধু পোস্টে সার্চ + সার্চ শব্দের দৈর্ঘ্য সীমা
function mytheme_search_query( $query ) {
    if ( $query->is_search() && $query->is_main_query() && ! is_admin() ) {
        $query->set( 'post_type', 'post' );
        $query->set( 's', mb_substr( $query->get( 's' ), 0, 100 ) );
    }
}
add_action( 'pre_get_posts', 'mytheme_search_query' );

// সার্চ পেজ noindex
function mytheme_noindex_search() {
    if ( is_search() ) {
        echo '<meta name="robots" content="noindex, follow">' . "\n";
    }
}
add_action( 'wp_head', 'mytheme_noindex_search' );