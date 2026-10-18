<?php
/**
 * Bot Detection & Verification Logic
 * File: inc/bot-checker.php
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'bd_news_verify_search_bot_ip' ) ) {
    /**
     * Reverse DNS + Forward-confirm. ফলাফল ৬ ঘণ্টা ক্যাশ হয়।
     */
    function bd_news_verify_search_bot_ip( $ip, array $domain_patterns ) {
        if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
            return false;
        }

        $cache_key = 'bdnews_vbot_' . md5( $ip . '|' . implode( ',', $domain_patterns ) );
        $cached    = get_transient( $cache_key );
        if ( 'yes' === $cached ) {
            return true;
        }
        if ( 'no' === $cached ) {
            return false;
        }

        $verified  = false;
        $hostname  = gethostbyaddr( $ip );
        $packed_ip = inet_pton( $ip );

        if ( $hostname && $hostname !== $ip && false !== $packed_ip ) {
            foreach ( $domain_patterns as $pattern ) {
                if ( ! preg_match( '#' . $pattern . '#i', $hostname ) ) {
                    continue;
                }

                // Forward-confirm: ওই নামের IP আসল IP-র সাথে মিলতে হবে
                $records = @dns_get_record( $hostname, DNS_A | DNS_AAAA );
                foreach ( (array) $records as $record ) {
                    $resolved = isset( $record['ip'] ) ? $record['ip'] : ( isset( $record['ipv6'] ) ? $record['ipv6'] : '' );
                    if ( $resolved !== '' && inet_pton( $resolved ) === $packed_ip ) {
                        $verified = true;
                        break 2;
                    }
                }
            }
        }

        set_transient( $cache_key, $verified ? 'yes' : 'no', 6 * 60 * 60 );
        return $verified;
    }
}

if ( ! function_exists( 'bd_news_check_bot_status' ) ) {
    function bd_news_check_bot_status() {
        $user_agent = strtolower( (string) ( $_SERVER['HTTP_USER_AGENT'] ?? '' ) );
        $ip_address = (string) ( $_SERVER['REMOTE_ADDR'] ?? '' );

        // --- 1. Custom Bot (Header Token, secret থাকে wp-config.php তে) ---
        $allowed_custom_agents = array( 'my-custom-bot', 'bdnews-app-v1', 'my-checker-bot' );
        $secret_token          = defined( 'BDNEWS_BOT_TOKEN' ) ? BDNEWS_BOT_TOKEN : '';
        $req_token             = (string) ( $_SERVER['HTTP_X_BOT_TOKEN'] ?? '' );

        foreach ( $allowed_custom_agents as $custom_ua ) {
            if ( strpos( $user_agent, $custom_ua ) !== false ) {
                if ( $secret_token !== '' && $req_token !== '' && hash_equals( $secret_token, $req_token ) ) {
                    return array(
                        'is_bot'         => false,
                        'is_good_bot'    => true,
                        'is_preview_bot' => false,
                        'bot_name'       => 'Authenticated Custom Bot',
                    );
                }
                return array(
                    'is_bot'         => true,
                    'is_good_bot'    => false,
                    'is_preview_bot' => false,
                    'bot_name'       => 'Unauthorized Custom Bot',
                );
            }
        }

        // --- 2. Search Engine Bot (Reverse DNS + Forward-confirm) ---
        $good_search_bots = array(
            'google' => array(
                'tokens'   => array( 'googlebot', 'mediapartners-google', 'adsbot-google', 'google-inspectiontool', 'googleother', 'storebot-google' ),
                'patterns' => array( '\.googlebot\.com$', '\.google\.com$', '\.googleusercontent\.com$' ),
            ),
            'bing' => array(
                'tokens'   => array( 'bingbot', 'adidxbot', 'msnbot' ),
                'patterns' => array( '\.search\.msn\.com$' ),
            ),
            'yandex' => array(
                'tokens'   => array( 'yandex' ),
                'patterns' => array( '\.yandex\.(ru|net|com)$' ),
            ),
            'baidu' => array(
                'tokens'   => array( 'baiduspider' ),
                'patterns' => array( '\.baidu\.(com|jp)$' ),
            ),
        );

        foreach ( $good_search_bots as $name => $bot ) {
            foreach ( $bot['tokens'] as $token ) {
                if ( strpos( $user_agent, $token ) !== false ) {
                    if ( bd_news_verify_search_bot_ip( $ip_address, $bot['patterns'] ) ) {
                        return array(
                            'is_bot'         => false,
                            'is_good_bot'    => true,
                            'is_preview_bot' => false,
                            'bot_name'       => ucfirst( $name ) . ' Verified',
                        );
                    }
                    // UA বলছে সার্চ বট কিন্তু যাচাই হয়নি = নকল, নিচে "খারাপ বট" হিসেবে ধরা পড়বে
                    break 2;
                }
            }
        }

        // --- 3. Link Preview Bot (Facebook, Telegram, WhatsApp ইত্যাদি) ---
        $preview_bots = array(
            'facebookexternalhit', 'facebot', 'twitterbot', 'linkedinbot', 'whatsapp',
            'telegrambot', 'discordbot', 'slackbot', 'pinterest', 'applebot', 'duckduckbot',
        );
        foreach ( $preview_bots as $preview ) {
            if ( strpos( $user_agent, $preview ) !== false ) {
                return array(
                    'is_bot'         => false,
                    'is_good_bot'    => false,
                    'is_preview_bot' => true,
                    'bot_name'       => 'Link Preview Bot',
                );
            }
        }

        // --- 4. Bad Bots ---
        $bad_bots = array( 'bot', 'crawl', 'spider', 'curl', 'wget', 'python', 'php', 'scraper', 'headless', 'puppeteer', 'selenium' );
        foreach ( $bad_bots as $bad_bot ) {
            if ( strpos( $user_agent, $bad_bot ) !== false ) {
                return array(
                    'is_bot'         => true,
                    'is_good_bot'    => false,
                    'is_preview_bot' => false,
                    'bot_name'       => 'Unauthorized Crawler/Bot',
                );
            }
        }

        return array(
            'is_bot'         => false,
            'is_good_bot'    => false,
            'is_preview_bot' => false,
            'bot_name'       => 'Human',
        );
    }
}