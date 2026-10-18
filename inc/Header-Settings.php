<?php
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Dashboard-e Header Admin Menu Add Kora
function bd_news_add_header_admin_menu() {
    add_menu_page(
        'Header Settings',          // Page Title
        'Header',                   // Menu Title
        'manage_options',           // Capability
        'bd-news-header-settings',  // Menu Slug
        'bd_news_header_settings_page', // Callback Function
        'dashicons-layout',         // Icon
        20                          // Position
    );
}
add_action( 'admin_menu', 'bd_news_add_header_admin_menu' );

function bd_news_register_header_settings() {
    register_setting( 'bd_news_header_group', 'bd_news_custom_header_menu', array(
        'sanitize_callback' => function( $input ) {
            return is_array( $input ) ?$input : array();
        }
    ) );

    register_setting( 'bd_news_header_group', 'bd_news_header_ad_code' );
    register_setting( 'bd_news_header_group', 'bd_news_topbar_text' );
}
add_action( 'admin_init', 'bd_news_register_header_settings' );

function bd_news_header_settings_page() {
    $menu_items = get_option( 'bd_news_custom_header_menu', array() );$all_posts  = get_posts( array( 'numberposts' => -1, 'post_status' => 'publish' ) );
    $all_pages  = get_pages();$all_cats   = get_categories( array( 'hide_empty' => false ) );
    ?>
    <div class="wrap">
        <h1>Header Settings</h1>

        <form method="post" action="options.php" style="margin-top: 20px;">
            <?php
            settings_fields( 'bd_news_header_group' );
            ?>

            <h3>1. Header Menu Settings</h3>
            <p>URL Are Auto Select On Your Selection Item</p>

            <style>
                #header-menu-table th, #header-menu-table td { vertical-align: middle; padding: 10px; }
                .title-input { width: 100% !important; max-width: 200px; }
                .menu-type-select { min-width: 130px !important; width: 100%; }
                .target-select { min-width: 180px !important; width: 100%; }
                .url-input { width: 100% !important; min-width: 220px; }
            </style>
            
            <table class="widefat" id="header-menu-table" style="max-width: 1000px; margin-bottom: 15px;">
                <thead>
                    <tr>
                        <th style="width: 22%;">Menu Title</th>
                        <th style="width: 18%;">Type</th>
                        <th style="width: 25%;">Select Item</th>
                        <th style="width: 25%;">URL</th>
                        <th style="width: 10%;">Action</th>
                    </tr>
                </thead>
                <tbody id="menu-items-wrapper">
                    <?php 
                    if ( ! empty( $menu_items ) && is_array($menu_items ) ) :
                        foreach ( $menu_items as $index =>$item ) : 
                            $item_type = isset($item['type'] ) ? $item['type'] : 'custom';$item_id   = isset( $item['id'] ) ?$item['id'] : '';
                            $title     = isset($item['title'] ) ? $item['title'] : '';$url       = isset( $item['url'] ) ?$item['url'] : '';
                            ?>
                            <tr class="menu-row">
                                <td>
                                    <input type="text" name="bd_news_custom_header_menu[<?php echo $index; ?>][title]" value="<?php echo esc_attr( $title ); ?>" class="regular-text title-input" placeholder="Example: Home" required />
                                </td>
                                <td>
                                    <select name="bd_news_custom_header_menu[<?php echo $index; ?>][type]" class="menu-type-select">
                                        <option value="custom" <?php selected($item_type, 'custom'); ?>>Custom Link</option>
                                        <option value="post" <?php selected($item_type, 'post'); ?>>Post</option>
                                        <option value="page" <?php selected($item_type, 'page'); ?>>Page</option>
                                        <option value="category" <?php selected($item_type, 'category'); ?>>Category</option>
                                    </select>
                                </td>
                                <td>
                                    <span class="custom-link-notice" <?php echo ($item_type !== 'custom') ? 'style="display:none;"' : ''; ?>>— N/A —</span>

                                    <select class="target-select post-select" style="<?php echo ($item_type !== 'post') ? 'display:none;' : ''; ?>">
                                        <option value="">-- Select Post --</option>
                                        <?php foreach($all_posts as$p): ?>
                                            <option value="<?php echo $p->ID; ?>" data-url="<?php echo get_permalink($p->ID); ?>" <?php selected($item_id, $p->ID); ?>><?php echo esc_html($p->post_title); ?></option>
                                        <?php endforeach; ?>
                                    </select>

                                    <select class="target-select page-select" style="<?php echo ($item_type !== 'page') ? 'display:none;' : ''; ?>">
                                        <option value="">-- Select Page --</option>
                                        <?php foreach($all_pages as$pg): ?>
                                            <option value="<?php echo $pg->ID; ?>" data-url="<?php echo get_permalink($pg->ID); ?>" <?php selected($item_id, $pg->ID); ?>><?php echo esc_html($pg->post_title); ?></option>
                                        <?php endforeach; ?>
                                    </select>

                                    <select class="target-select category-select" style="<?php echo ($item_type !== 'category') ? 'display:none;' : ''; ?>">
                                        <option value="">-- Select Category --</option>
                                        <?php foreach($all_cats as$c): ?>
                                            <option value="<?php echo $c->term_id; ?>" data-url="<?php echo get_category_link($c->term_id); ?>" <?php selected($item_id, $c->term_id); ?>><?php echo esc_html($c->name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="bd_news_custom_header_menu[<?php echo $index; ?>][url]" value="<?php echo esc_attr( $url ); ?>" class="regular-text url-input" placeholder="https://..." <?php echo ($item_type !== 'custom') ? 'readonly style="background:#f1f5f9;"' : ''; ?> />
                                </td>
                                <td><button type="button" class="button remove-menu-row">Remove</button></td>
                            </tr>
                        <?php endforeach;
                    else : ?>
                        <tr class="menu-row">
                            <td><input type="text" name="bd_news_custom_header_menu[0][title]" class="regular-text title-input" placeholder="Example: Home" required /></td>
                            <td>
                                <select name="bd_news_custom_header_menu[0][type]" class="menu-type-select">
                                    <option value="custom">Custom Link</option>
                                    <option value="post">Post</option>
                                    <option value="page">Page</option>
                                    <option value="category">Category</option>
                                </select>
                            </td>
                            <td>
                                <span class="custom-link-notice">— N/A —</span>
                                <select class="target-select post-select" style="display:none;">
                                    <option value="">-- Select Post --</option>
                                    <?php foreach($all_posts as$p): ?>
                                        <option value="<?php echo $p->ID; ?>" data-url="<?php echo get_permalink($p->ID); ?>"><?php echo esc_html($p->post_title); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select class="target-select page-select" style="display:none;">
                                    <option value="">-- Select Page --</option>
                                    <?php foreach($all_pages as$pg): ?>
                                        <option value="<?php echo $pg->ID; ?>" data-url="<?php echo get_permalink($pg->ID); ?>"><?php echo esc_html($pg->post_title); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select class="target-select category-select" style="display:none;">
                                    <option value="">-- Select Category --</option>
                                    <?php foreach($all_cats as$c): ?>
                                        <option value="<?php echo $c->term_id; ?>" data-url="<?php echo get_category_link($c->term_id); ?>"><?php echo esc_html($c->name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="text" name="bd_news_custom_header_menu[0][url]" class="regular-text url-input" placeholder="https://..." /></td>
                            <td><button type="button" class="button remove-menu-row">Remove</button></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <button type="button" class="button button-secondary" id="add-more-menu" style="margin-bottom: 25px;">+ Add New Menu Item</button>

            <hr style="margin: 20px 0;">

            <h3>2. Other Header Options</h3>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Top Bar Notice / Announcement</th>
                    <td>
                        <input type="text" name="bd_news_topbar_text" value="<?php echo esc_attr( get_option('bd_news_topbar_text') ); ?>" class="regular-text" placeholder="Today's Breaking News / Announcement..." />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Header Ad Banner (728x90)</th>
                    <td>
                        <textarea name="bd_news_header_ad_code" rows="4" class="large-text code" placeholder="Enter your ad code here..."><?php echo esc_textarea( get_option('bd_news_header_ad_code') ); ?></textarea>
                    </td>
                </tr>
            </table>

            <?php submit_button( 'Save Header Settings' ); ?>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        let wrapper = document.getElementById('menu-items-wrapper');
        let addButton = document.getElementById('add-more-menu');

        function handleTypeChange(row) {
            let selectType = row.querySelector('.menu-type-select').value;
            let notice     = row.querySelector('.custom-link-notice');
            let post       = row.querySelector('.post-select');
            let page       = row.querySelector('.page-select');
            let cat        = row.querySelector('.category-select');
            let url        = row.querySelector('.url-input');

            notice.style.display = 'none';
            post.style.display   = 'none';
            page.style.display   = 'none';
            cat.style.display    = 'none';

            post.removeAttribute('name');
            page.removeAttribute('name');
            cat.removeAttribute('name');

            let rowIndex = Array.from(wrapper.children).indexOf(row);

            if (selectType === 'custom') {
                notice.style.display = 'inline-block';
                url.readOnly = false;
                url.style.background = '#ffffff';
            } else {
                url.readOnly = true;
                url.style.background = '#f1f5f9';

                let activeSelect = null;
                if (selectType === 'post') {
                    post.style.display = 'inline-block';
                    post.setAttribute('name', `bd_news_custom_header_menu[${rowIndex}][id]`);
                    activeSelect = post;
                } else if (selectType === 'page') {
                    page.style.display = 'inline-block';
                    page.setAttribute('name', `bd_news_custom_header_menu[${rowIndex}][id]`);
                    activeSelect = page;
                } else if (selectType === 'category') {
                    cat.style.display = 'inline-block';
                    cat.setAttribute('name', `bd_news_custom_header_menu[${rowIndex}][id]`);
                    activeSelect = cat;
                }

                if (activeSelect) {
                    updateUrlFromSelect(activeSelect, url);
                }
            }
        }

        function updateUrlFromSelect(selectElem, urlInput) {
            let selectedOption = selectElem.options[selectElem.selectedIndex];
            if (selectedOption && selectedOption.dataset.url) {
                urlInput.value = selectedOption.dataset.url;
            } else {
                urlInput.value = '';
            }
        }

        document.querySelectorAll('.menu-row').forEach(function(row) {
            handleTypeChange(row);
        });

        wrapper.addEventListener('change', function(e) {
            let row = e.target.closest('tr');
            if (e.target.classList.contains('menu-type-select')) {
                handleTypeChange(row);
            } else if (e.target.classList.contains('target-select')) {
                let urlInput = row.querySelector('.url-input');
                updateUrlFromSelect(e.target, urlInput);
            }
        });

        if (addButton) {
            addButton.addEventListener('click', function() {
                let rowCount = wrapper.getElementsByTagName('tr').length;
                let firstRow = wrapper.querySelector('.menu-row');
                let newRow   = firstRow.cloneNode(true);

                newRow.querySelector('.title-input').name = `bd_news_custom_header_menu[${rowCount}][title]`;
                newRow.querySelector('.menu-type-select').name = `bd_news_custom_header_menu[${rowCount}][type]`;
                newRow.querySelector('.url-input').name = `bd_news_custom_header_menu[${rowCount}][url]`;

                newRow.querySelector('.title-input').value = '';
                newRow.querySelector('.menu-type-select').value = 'custom';
                newRow.querySelector('.url-input').value = '';

                wrapper.appendChild(newRow);
                handleTypeChange(newRow);
            });
        }

        wrapper.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-menu-row') && wrapper.getElementsByTagName('tr').length > 1) {
                e.target.closest('tr').remove();
            }
        });
    });
    </script>
    <?php
}