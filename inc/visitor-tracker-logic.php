<?php
/**
 * Visitor Tracker & Geolocation Logic (AJAX ভার্সন)
 * File: inc/visitor-tracker-logic.php
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ---------- ১. কাস্টম VPN ডাটাবেজ টেবিল তৈরি (অটোমেটিক) ----------
if ( ! function_exists( 'bd_news_init_vpn_db_table' ) ) {
    function bd_news_init_vpn_db_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'bdnews_vpn_list';

        if ( get_option( 'bdnews_vpn_db_version' ) !== '1.1' ) {
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE $table_name (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                ip varchar(45) NOT NULL,
                ip_hash varchar(32) NOT NULL,
                country varchar(100) DEFAULT 'Unknown',
                isp varchar(150) DEFAULT 'Unknown',
                detected_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY  (id),
                UNIQUE KEY ip_hash (ip_hash)
            ) $charset_collate;";

            require_once( constant( 'ABSPATH' ) . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );
            update_option( 'bdnews_vpn_db_version', '1.1' );
        }
    }
}
bd_news_init_vpn_db_table();

// ---------- ২. পুরনো VPN রেকর্ড প্রতিদিন মুছে ফেলা (৯০ দিনের বেশি পুরনো) ----------
if ( ! function_exists( 'bd_news_vpn_cleanup' ) ) {
    function bd_news_vpn_cleanup() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'bdnews_vpn_list';
        $wpdb->query( "DELETE FROM $table_name WHERE detected_at < DATE_SUB(NOW(), INTERVAL 90 DAY)" );
    }
}
add_action( 'bdnews_vpn_cleanup_event', 'bd_news_vpn_cleanup' );
if ( ! wp_next_scheduled( 'bdnews_vpn_cleanup_event' ) ) {
    wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'bdnews_vpn_cleanup_event' );
}

// ---------- ৩. VPN IP সেভ করার ফাংশন ----------
if ( ! function_exists( 'bd_news_save_vpn_ip' ) ) {
    function bd_news_save_vpn_ip( $ip, $geo_data ) {
        if ( empty( $geo_data['is_vpn'] ) ) {
            return; // VPN না হলে সেভ করবে না
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'bdnews_vpn_list';

        $wpdb->replace(
            $table_name,
            array(
                'ip'          => $ip,
                'ip_hash'     => md5( $ip ),
                'country'     => $geo_data['country'] ?? 'Unknown',
                'isp'         => $geo_data['isp'] ?? 'Unknown',
                'detected_at' => current_time( 'mysql' ),
            ),
            array( '%s', '%s', '%s', '%s', '%s' )
        );
    }
}

// ---------- ৪. লোকাল VPN ডাটাবেজে IP আছে কিনা চেক ----------
if ( ! function_exists( 'bd_news_check_local_vpn_db' ) ) {
    function bd_news_check_local_vpn_db( $ip ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'bdnews_vpn_list';

        $row = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM $table_name WHERE ip_hash = %s LIMIT 1", md5( $ip ) ),
            ARRAY_A
        );

        if ( $row ) {
            return array(
                'country'  => $row['country'],
                'city'     => 'Unknown',
                'region'   => 'Unknown',
                'isp'      => $row['isp'],
                'server'   => 'Local VPN DB',
                'timezone' => 'Asia/Dhaka',
                'is_vpn'   => true,
            );
        }

        return null;
    }
}

// ---------- ৫. API কলের সীমা (প্রতি মিনিটে সর্বোচ্চ ৪০) ----------
if ( ! function_exists( 'bd_news_geo_budget_ok' ) ) {
    function bd_news_geo_budget_ok() {
        $limit  = 40;
        $bucket = get_transient( 'bdnews_geo_calls' );
        if ( ! is_array( $bucket ) || ( time() - (int) $bucket['t'] ) > 60 ) {
            $bucket = array( 'n' => 0, 't' => time() );
        }
        if ( $bucket['n'] >= $limit ) {
            return false;
        }
        $bucket['n']++;
        set_transient( 'bdnews_geo_calls', $bucket, 120 );
        return true;
    }
}

// ---------- ৬. ভিজিটরের IP ----------
if ( ! function_exists( 'bd_news_get_visitor_ip' ) ) {
    function bd_news_get_visitor_ip() {
        $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? trim( $_SERVER['REMOTE_ADDR'] ) : '';

        // Cloudflare-এর পেছনে থাকলে (wp-config.php তে define করতে হবে)
        if ( defined( 'BDNEWS_BEHIND_CLOUDFLARE' ) && BDNEWS_BEHIND_CLOUDFLARE && ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
            $ip = trim( $_SERVER['HTTP_CF_CONNECTING_IP'] );
        }

        if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
            return '';
        }

        $is_public = (bool) filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE );
        if ( $is_public ) {
            return $ip;
        }

        // Local development: নিজের public IP ব্যবহার করা হয়
        $is_loopback  = in_array( $ip, array( '127.0.0.1', '::1' ), true );
        $is_local_env = function_exists( 'wp_get_environment_type' ) && 'local' === wp_get_environment_type();

        if ( ! $is_loopback && ! $is_local_env ) {
            return ''; // প্রক্সির পেছনের private IP, লুকআপ অর্থহীন
        }

        $public_ip = get_transient( 'bdnews_local_public_ip' );
        if ( false === $public_ip ) {
            $public_ip = '';
            $res       = wp_remote_get( 'https://api.ipify.org', array( 'timeout' => 3 ) );
            if ( ! is_wp_error( $res ) && 200 === wp_remote_retrieve_response_code( $res ) ) {
                $body = trim( wp_remote_retrieve_body( $res ) );
                if ( filter_var( $body, FILTER_VALIDATE_IP ) ) {
                    $public_ip = $body;
                }
            }
            set_transient( 'bdnews_local_public_ip', $public_ip, $public_ip ? 60 : 30 );
        }
        return $public_ip;
    }
}

// ---------- ৭. ডাটা পরিষ্কার করার সাহায্যকারী ----------
if ( ! function_exists( 'bd_news_geo_text' ) ) {
    function bd_news_geo_text( $value, $fallback ) {
        $value = sanitize_text_field( (string) $value );
        return '' !== $value ? mb_substr( $value, 0, 100 ) : $fallback;
    }
}

if ( ! function_exists( 'bd_news_geo_timezone' ) ) {
    function bd_news_geo_timezone( $tz ) {
        $tz = (string) $tz;
        return in_array( $tz, timezone_identifiers_list(), true ) ? $tz : 'Asia/Dhaka';
    }
}

// ---------- ৮. Geo ডাটা আনা (সফল হলে array, ব্যর্থ হলে null) ----------
if ( ! function_exists( 'bd_news_fetch_geo' ) ) {
    function bd_news_fetch_geo( $ip ) {
        // STEP 1: ip-api.com (ফ্রি প্ল্যানে শুধু HTTP, এবং শুধু নন-কমার্শিয়াল ব্যবহার)
        if ( bd_news_geo_budget_ok() ) {
            $res1 = wp_remote_get(
                'http://ip-api.com/json/' . $ip . '?fields=status,country,countryCode,regionName,city,isp,org,as,timezone,proxy,hosting',
                array( 'timeout' => 2 )
            );
            if ( ! is_wp_error( $res1 ) && 200 === wp_remote_retrieve_response_code( $res1 ) ) {
                $d = json_decode( wp_remote_retrieve_body( $res1 ), true );
                if ( is_array( $d ) && isset( $d['status'] ) && 'success' === $d['status'] ) {
                    return array(
                        'country'  => bd_news_geo_text( ( $d['country'] ?? '' ) . ' (' . ( $d['countryCode'] ?? '' ) . ')', 'Unknown' ),
                        'city'     => bd_news_geo_text( $d['city'] ?? '', 'Unknown' ),
                        'region'   => bd_news_geo_text( $d['regionName'] ?? '', 'Unknown' ),
                        'isp'      => bd_news_geo_text( $d['isp'] ?? '', 'Unknown ISP' ),
                        'server'   => bd_news_geo_text( $d['as'] ?? ( $d['org'] ?? '' ), 'Unknown' ),
                        'timezone' => bd_news_geo_timezone( $d['timezone'] ?? '' ),
                        'is_vpn'   => ! empty( $d['proxy'] ) || ! empty( $d['hosting'] ),
                    );
                }
            }
        }

        // STEP 2: ipwho.is (HTTPS)
        if ( bd_news_geo_budget_ok() ) {
            $res2 = wp_remote_get( 'https://ipwho.is/' . $ip, array( 'timeout' => 2 ) );
            if ( ! is_wp_error( $res2 ) && 200 === wp_remote_retrieve_response_code( $res2 ) ) {
                $d = json_decode( wp_remote_retrieve_body( $res2 ), true );
                if ( is_array( $d ) && ! empty( $d['success'] ) ) {
                    // ipwho.is ASN শুধু সংখ্যা দেয়, তাই "AS" প্রিফিক্স যোগ করা হলো
                    $asn    = $d['connection']['asn'] ?? '';
                    $server = '' !== (string) $asn ? 'AS' . $asn . ' ' . ( $d['connection']['org'] ?? '' ) : ( $d['connection']['org'] ?? '' );

                    return array(
                        'country'  => bd_news_geo_text( ( $d['country'] ?? '' ) . ' (' . ( $d['country_code'] ?? '' ) . ')', 'Unknown' ),
                        'city'     => bd_news_geo_text( $d['city'] ?? '', 'Unknown' ),
                        'region'   => bd_news_geo_text( $d['region'] ?? '', 'Unknown' ),
                        'isp'      => bd_news_geo_text( $d['connection']['isp'] ?? '', 'Unknown ISP' ),
                        'server'   => bd_news_geo_text( $server, 'Unknown' ),
                        'timezone' => bd_news_geo_timezone( $d['timezone']['id'] ?? '' ),
                        'is_vpn'   => ! empty( $d['security']['proxy'] ) || ! empty( $d['security']['vpn'] ),
                    );
                }
            }
        }

        return null;
    }
}

// ---------- ৯. AJAX হ্যান্ডলার: ভিজিটরের নিজের তথ্য ফেরত দেয় ----------
if ( ! function_exists( 'bd_news_ajax_visitor_info' ) ) {
    function bd_news_ajax_visitor_info() {
        // এই রেসপন্স কখনোই ক্যাশ হবে না
        nocache_headers();

        $info = array(
            'ip'       => '',
            'country'  => 'Unknown',
            'city'     => 'Unknown',
            'region'   => 'Unknown',
            'isp'      => 'Unknown',
            'server'   => 'Unknown',
            'timezone' => 'Asia/Dhaka',
            'is_vpn'   => false,
        );

        $ip = bd_news_get_visitor_ip();
        if ( '' === $ip ) {
            wp_send_json_success( $info );
        }
        $info['ip'] = $ip;

        // বট হলে API কল নয়
        if ( function_exists( 'bd_news_check_bot_status' ) ) {
            $bot = bd_news_check_bot_status();
            if ( ! empty( $bot['is_bot'] ) || ! empty( $bot['is_good_bot'] ) || ! empty( $bot['is_preview_bot'] ) ) {
                wp_send_json_success( $info );
            }
        }

        $transient_key = 'bdnews_geo_v2_' . md5( $ip );
        $cached_geo    = get_transient( $transient_key );

        if ( false === $cached_geo ) {
            $fetched = bd_news_fetch_geo( $ip );
            if ( $fetched ) {
                $cached_geo = $fetched;
                set_transient( $transient_key, $cached_geo, 12 * HOUR_IN_SECONDS );
                if ( ! empty( $fetched['is_vpn'] ) ) {
                    bd_news_save_vpn_ip( $ip, $fetched );
                }
            } else {
                $local = bd_news_check_local_vpn_db( $ip );
                if ( $local ) {
                    $cached_geo = $local;
                    set_transient( $transient_key, $cached_geo, 12 * HOUR_IN_SECONDS );
                } else {
                    // ব্যর্থ হলে নকল ডাটা নয়; ৫ মিনিট পর আবার চেষ্টা হবে
                    $cached_geo = array( 'failed' => true );
                    set_transient( $transient_key, $cached_geo, 5 * MINUTE_IN_SECONDS );
                }
            }
        }

        if ( is_array( $cached_geo ) && empty( $cached_geo['failed'] ) ) {
            foreach ( array( 'country', 'city', 'region', 'isp', 'server', 'timezone' ) as $key ) {
                if ( isset( $cached_geo[ $key ] ) ) {
                    $info[ $key ] = (string) $cached_geo[ $key ];
                }
            }
            $info['is_vpn'] = ! empty( $cached_geo['is_vpn'] );
        }

        wp_send_json_success( $info );
    }
}
add_action( 'wp_ajax_nopriv_bd_news_visitor_info', 'bd_news_ajax_visitor_info' );
add_action( 'wp_ajax_bd_news_visitor_info', 'bd_news_ajax_visitor_info' );