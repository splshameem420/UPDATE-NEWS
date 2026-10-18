<?php
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Dashboard-e Footer Admin Menu Add Kora
function bd_news_add_footer_admin_menu() {
    add_menu_page(
        'Footer Settings',          // Page Title
        'Footer',                   // Menu Title
        'manage_options',           // Capability
        'bd-news-footer-settings',  // Menu Slug
        'bd_news_footer_settings_page', // Callback Function
        'dashicons-layout',         // Icon
        21                          // Position
    );
}
add_action( 'admin_menu', 'bd_news_add_footer_admin_menu' );

function bd_news_register_footer_settings() {
    // Column 1 Text & Button Settings
    register_setting( 'bd_news_footer_group', 'bd_news_footer_about_title' );
    register_setting( 'bd_news_footer_group', 'bd_news_footer_about_text' );
    register_setting( 'bd_news_footer_group', 'bd_news_footer_btn_text' );
    register_setting( 'bd_news_footer_group', 'bd_news_footer_btn_url' );

    // Column 2, 3, 4 Titles & Menus
    register_setting( 'bd_news_footer_group', 'bd_news_footer_col2_title' );
    register_setting( 'bd_news_footer_group', 'bd_news_footer_col2_menu', array( 'sanitize_callback' => 'bd_news_sanitize_menu_input' ) );

    register_setting( 'bd_news_footer_group', 'bd_news_footer_col3_title' );
    register_setting( 'bd_news_footer_group', 'bd_news_footer_col3_menu', array( 'sanitize_callback' => 'bd_news_sanitize_menu_input' ) );

    register_setting( 'bd_news_footer_group', 'bd_news_footer_col4_title' );
    register_setting( 'bd_news_footer_group', 'bd_news_footer_col4_menu', array( 'sanitize_callback' => 'bd_news_sanitize_menu_input' ) );

    // Bottom Red Bar
    register_setting( 'bd_news_footer_group', 'bd_news_custom_footer_menu', array( 'sanitize_callback' => 'bd_news_sanitize_menu_input' ) );
    register_setting( 'bd_news_footer_group', 'bd_news_footer_copyright' );
}
add_action( 'admin_init', 'bd_news_register_footer_settings' );

function bd_news_sanitize_menu_input( $input ) {
    return is_array( $input ) ?$input : array();
}

function bd_news_footer_settings_page() {
    $all_posts    = get_posts( array( 'numberposts' => -1, 'post_status' => 'publish' ) );
    $all_pages    = get_pages();$all_cats     = get_categories( array( 'hide_empty' => false ) );

    // Helper function to render menu table
    function render_menu_builder_table( $table_id,$field_name, $saved_items,$all_posts, $all_pages,$all_cats ) {
        ?>
        <table class="widefat menu-table" id="<?php echo esc_attr( $table_id ); ?>" style="max-width: 1000px; margin-bottom: 15px;">
            <thead>
                <tr>
                    <th style="width: 22%;">Menu Title</th>
                    <th style="width: 18%;">Type</th>
                    <th style="width: 25%;">Select Item</th>
                    <th style="width: 25%;">URL</th>
                    <th style="width: 10%;">Action</th>
                </tr>
            </thead>
            <tbody class="menu-items-wrapper">
                <?php 
                if ( ! empty( $saved_items ) && is_array($saved_items ) ) :
                    foreach ( $saved_items as $index =>$item ) : 
                        $item_type = isset($item['type'] ) ? $item['type'] : 'custom';$item_id   = isset( $item['id'] ) ?$item['id'] : '';
                        $title     = isset($item['title'] ) ? $item['title'] : '';$url       = isset( $item['url'] ) ?$item['url'] : '';
                        ?>
                        <tr class="menu-row">
                            <td><input type="text" name="<?php echo $field_name; ?>[<?php echo$index; ?>][title]" value="<?php echo esc_attr( $title ); ?>" class="regular-text title-input" placeholder="Title" required /></td>
                            <td>
                                <select name="<?php echo $field_name; ?>[<?php echo$index; ?>][type]" class="menu-type-select">
                                    <option value="custom" <?php selected($item_type, 'custom'); ?>>Custom Link</option>
                                    <option value="post" <?php selected($item_type, 'post'); ?>>Post</option>
                                    <option value="page" <?php selected($item_type, 'page'); ?>>Page</option>
                                    <option value="category" <?php selected($item_type, 'category'); ?>>Category</option>
                                </select>
                            </td>
                            <td>
                                <span class="custom-link-notice">— N/A —</span>
                                <select class="target-select post-select" style="display:none;">
                                    <option value="">-- Select Post --</option>
                                    <?php foreach($all_posts as$p): ?>
                                        <option value="<?php echo $p->ID; ?>" data-url="<?php echo get_permalink($p->ID); ?>" <?php selected($item_id, $p->ID); ?>><?php echo esc_html($p->post_title); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select class="target-select page-select" style="display:none;">
                                    <option value="">-- Select Page --</option>
                                    <?php foreach($all_pages as$pg): ?>
                                        <option value="<?php echo $pg->ID; ?>" data-url="<?php echo get_permalink($pg->ID); ?>" <?php selected($item_id, $pg->ID); ?>><?php echo esc_html($pg->post_title); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select class="target-select category-select" style="display:none;">
                                    <option value="">-- Select Category --</option>
                                    <?php foreach($all_cats as$c): ?>
                                        <option value="<?php echo $c->term_id; ?>" data-url="<?php echo get_category_link($c->term_id); ?>" <?php selected($item_id, $c->term_id); ?>><?php echo esc_html($c->name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="text" name="<?php echo $field_name; ?>[<?php echo$index; ?>][url]" value="<?php echo esc_attr( $url ); ?>" class="regular-text url-input" placeholder="https://..." /></td>
                            <td><button type="button" class="button remove-menu-row">Remove</button></td>
                        </tr>
                    <?php endforeach;
                else : ?>
                    <tr class="menu-row">
                        <td><input type="text" name="<?php echo $field_name; ?>[0][title]" class="regular-text title-input" placeholder="Title" /></td>
                        <td>
                            <select name="<?php echo $field_name; ?>[0][type]" class="menu-type-select">
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
                                <?php foreach($all_posts as$p): ?><option value="<?php echo $p->ID; ?>" data-url="<?php echo get_permalink($p->ID); ?>"><?php echo esc_html($p->post_title); ?></option><?php endforeach; ?>
                            </select>
                            <select class="target-select page-select" style="display:none;">
                                <option value="">-- Select Page --</option>
                                <?php foreach($all_pages as$pg): ?><option value="<?php echo $pg->ID; ?>" data-url="<?php echo get_permalink($pg->ID); ?>"><?php echo esc_html($pg->post_title); ?></option><?php endforeach; ?>
                            </select>
                            <select class="target-select category-select" style="display:none;">
                                <option value="">-- Select Category --</option>
                                <?php foreach($all_cats as$c): ?><option value="<?php echo $c->term_id; ?>" data-url="<?php echo get_category_link($c->term_id); ?>"><?php echo esc_html($c->name); ?></option><?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="text" name="<?php echo $field_name; ?>[0][url]" class="regular-text url-input" placeholder="https://..." /></td>
                        <td><button type="button" class="button remove-menu-row">Remove</button></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <button type="button" class="button button-secondary add-menu-btn" data-target="<?php echo esc_attr( $table_id ); ?>" data-fieldname="<?php echo esc_attr( $field_name ); ?>">+ Add Item</button>
        <?php
    }
    ?>

    <div class="wrap">
        <h1>Footer Settings</h1>

        <form method="post" action="options.php" style="margin-top: 20px;">
            <?php settings_fields( 'bd_news_footer_group' ); ?>

            <style>
                .footer-settings-card { background: #fff; padding: 20px; border: 1px solid #ccd0d4; margin-bottom: 25px; border-radius: 5px; }
                .footer-settings-card h2 { margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; }
                .title-input { width: 100% !important; max-width: 200px; }
                .menu-type-select { min-width: 130px !important; width: 100%; }
                .target-select { min-width: 180px !important; width: 100%; }
                .url-input { width: 100% !important; min-width: 220px; }
            </style>

            <!-- MID FOOTER SETTINGS -->
            <div class="footer-settings-card">
                <h2>Mid Footer Section (4 Columns)</h2>

                <h3>Column 1: About / Subscribe Box</h3>
                <table class="form-table" style="margin-bottom: 20px;">
                    <tr>
                        <th>Column Title</th>
                        <td><input type="text" name="bd_news_footer_about_title" value="<?php echo esc_attr( get_option('bd_news_footer_about_title', 'Company') ); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th>Description</th>
                        <td><textarea name="bd_news_footer_about_text" class="regular-text" rows="2"><?php echo esc_textarea( get_option('bd_news_footer_about_text', 'Red The Latest News Reports In Our Website.') ); ?></textarea></td>
                    </tr>
                    <tr>
                        <th>Button Text</th>
                        <td><input type="text" name="bd_news_footer_btn_text" value="<?php echo esc_attr( get_option('bd_news_footer_btn_text', 'Subscribe Now') ); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th>Button Link</th>
                        <td><input type="url" name="bd_news_footer_btn_url" value="<?php echo esc_attr( get_option('bd_news_footer_btn_url', '#') ); ?>" class="regular-text" /></td>
                    </tr>
                </table>

                <hr>

                <h3>Column 2: Menu</h3>
                <p>Title: <input type="text" name="bd_news_footer_col2_title" value="<?php echo esc_attr( get_option('bd_news_footer_col2_title', 'Category') ); ?>" class="regular-text" /></p>
                <?php render_menu_builder_table( 'footer-col2-table', 'bd_news_footer_col2_menu', get_option('bd_news_footer_col2_menu', array()), $all_posts, $all_pages,$all_cats ); ?>

                <hr style="margin-top:25px;">

                <h3>Column 3: Menu</h3>
                <p>Title: <input type="text" name="bd_news_footer_col3_title" value="<?php echo esc_attr( get_option('bd_news_footer_col3_title', 'Company') ); ?>" class="regular-text" /></p>
                <?php render_menu_builder_table( 'footer-col3-table', 'bd_news_footer_col3_menu', get_option('bd_news_footer_col3_menu', array()), $all_posts, $all_pages,$all_cats ); ?>

                <hr style="margin-top:25px;">

                <h3>Column 4: Menu</h3>
                <p>Title: <input type="text" name="bd_news_footer_col4_title" value="<?php echo esc_attr( get_option('bd_news_footer_col4_title', 'Contact') ); ?>" class="regular-text" /></p>
                <?php render_menu_builder_table( 'footer-col4-table', 'bd_news_footer_col4_menu', get_option('bd_news_footer_col4_menu', array()), $all_posts, $all_pages,$all_cats ); ?>
            </div>

            <!-- LOW FOOTER SETTINGS -->
            <div class="footer-settings-card">
                <h2>Low Footer Section (Red Bar)</h2>
                <h3>Bottom Bar Menu</h3>
                <?php render_menu_builder_table( 'footer-low-table', 'bd_news_custom_footer_menu', get_option('bd_news_custom_footer_menu', array()), $all_posts, $all_pages,$all_cats ); ?>

                <h3>Copyright Text</h3>
                <input type="text" name="bd_news_footer_copyright" value="<?php echo esc_attr( get_option('bd_news_footer_copyright', '© 2026 Update News. All rights reserved.') ); ?>" class="large-text" />
            </div>

            <?php submit_button( 'Save All Footer Settings' ); ?>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
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

            let wrapper  = row.closest('.menu-items-wrapper');
            let rowIndex = Array.from(wrapper.children).indexOf(row);
            let firstInputName = row.querySelector('.title-input').getAttribute('name');
            let baseName = firstInputName.substring(0, firstInputName.indexOf('['));

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
                    post.setAttribute('name', `${baseName}[${rowIndex}][id]`);
                    activeSelect = post;
                } else if (selectType === 'page') {
                    page.style.display = 'inline-block';
                    page.setAttribute('name', `${baseName}[${rowIndex}][id]`);
                    activeSelect = page;
                } else if (selectType === 'category') {
                    cat.style.display = 'inline-block';
                    cat.setAttribute('name', `${baseName}[${rowIndex}][id]`);
                    activeSelect = cat;
                }

                if (activeSelect) {
                    let selectedOption = activeSelect.options[activeSelect.selectedIndex];
                    url.value = (selectedOption && selectedOption.dataset.url) ? selectedOption.dataset.url : '';
                }
            }
        }

        document.querySelectorAll('.menu-row').forEach(function(row) {
            handleTypeChange(row);
        });

        document.addEventListener('change', function(e) {
            let row = e.target.closest('tr');
            if (!row) return;
            if (e.target.classList.contains('menu-type-select')) {
                handleTypeChange(row);
            } else if (e.target.classList.contains('target-select')) {
                let selectedOption = e.target.options[e.target.selectedIndex];
                row.querySelector('.url-input').value = (selectedOption && selectedOption.dataset.url) ? selectedOption.dataset.url : '';
            }
        });

        document.querySelectorAll('.add-menu-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                let tableId   = this.dataset.target;
                let fieldName = this.dataset.fieldname;
                let wrapper   = document.querySelector(`#${tableId} .menu-items-wrapper`);
                let rowCount  = wrapper.getElementsByTagName('tr').length;
                let firstRow  = wrapper.querySelector('.menu-row');
                let newRow    = firstRow.cloneNode(true);

                newRow.querySelector('.title-input').name = `${fieldName}[${rowCount}][title]`;
                newRow.querySelector('.menu-type-select').name = `${fieldName}[${rowCount}][type]`;
                newRow.querySelector('.url-input').name = `${fieldName}[${rowCount}][url]`;

                newRow.querySelector('.title-input').value = '';
                newRow.querySelector('.menu-type-select').value = 'custom';
                newRow.querySelector('.url-input').value = '';

                wrapper.appendChild(newRow);
                handleTypeChange(newRow);
            });
        });

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-menu-row')) {
                let wrapper = e.target.closest('.menu-items-wrapper');
                if (wrapper.getElementsByTagName('tr').length > 1) {
                    e.target.closest('tr').remove();
                }
            }
        });
    });
    </script>
    <?php
}