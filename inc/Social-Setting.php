<?php
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Dashboard-e Social Profile Admin Menu Add Kora
function bd_news_add_social_admin_menu() {
    add_menu_page(
        'Social Settings',          // Page Title
        'Social Links',             // Menu Title
        'manage_options',           // Capability
        'bd-news-social-settings',  // Menu Slug
        'bd_news_social_settings_page', // Callback Function
        'dashicons-share',          // Icon
        22                          // Position
    );
}
add_action( 'admin_menu', 'bd_news_add_social_admin_menu' );

function bd_news_register_social_settings() {
    register_setting( 'bd_news_social_group', 'bd_news_social_links', array(
        'sanitize_callback' => function( $input ) {
            return is_array( $input ) ?$input : array();
        }
    ) );
}
add_action( 'admin_init', 'bd_news_register_social_settings' );

function bd_news_social_settings_page() {
    $social_links = get_option( 'bd_news_social_links', array() );
    ?>
    <div class="wrap">
        <h1>Social Profile Settings</h1>

        <form method="post" action="options.php" style="margin-top: 20px;">
            <?php
            settings_fields( 'bd_news_social_group' );
            ?>

            <h3>Social Media Links</h3>
            <p>Social platform name (e.g. facebook, youtube, twitter, instagram, telegram) ebong URL set korun. Icon automatic match hobe.</p>

            <table class="widefat menu-table" id="social-links-table" style="max-width: 900px; margin-bottom: 15px;">
                <thead>
                    <tr>
                        <th style="width: 30%;">Platform / Short Name</th>
                        <th style="width: 55%;">URL</th>
                        <th style="width: 15%;">Action</th>
                    </tr>
                </thead>
                <tbody id="social-items-wrapper">
                    <?php 
                    if ( ! empty( $social_links ) && is_array($social_links ) ) :
                        foreach ( $social_links as $index =>$item ) : 
                            $name = isset($item['name'] ) ? $item['name'] : '';$url  = isset( $item['url'] ) ?$item['url'] : '';
                            ?>
                            <tr class="social-row">
                                <td>
                                    <input type="text" name="bd_news_social_links[<?php echo $index; ?>][name]" value="<?php echo esc_attr( $name ); ?>" class="regular-text social-name-input" placeholder="e.g. facebook, yt, twitter" required />
                                </td>
                                <td>
                                    <input type="url" name="bd_news_social_links[<?php echo $index; ?>][url]" value="<?php echo esc_attr( $url ); ?>" class="regular-text social-url-input" placeholder="https://..." required />
                                </td>
                                <td><button type="button" class="button remove-social-row">Remove</button></td>
                            </tr>
                        <?php endforeach;
                    else : ?>
                        <tr class="social-row">
                            <td><input type="text" name="bd_news_social_links[0][name]" class="regular-text social-name-input" placeholder="e.g. facebook" required /></td>
                            <td><input type="url" name="bd_news_social_links[0][url]" class="regular-text social-url-input" placeholder="https://facebook.com/..." required /></td>
                            <td><button type="button" class="button remove-social-row">Remove</button></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <button type="button" class="button button-secondary" id="add-social-item" style="margin-bottom: 20px;">+ Add New Social Link</button>

            <?php submit_button( 'Save Social Settings' ); ?>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        let wrapper   = document.getElementById('social-items-wrapper');
        let addButton = document.getElementById('add-social-item');

        if (!wrapper) return;

        if (addButton) {
            addButton.addEventListener('click', function() {
                let rowCount = wrapper.getElementsByTagName('tr').length;
                let firstRow = wrapper.querySelector('.social-row');
                let newRow   = firstRow.cloneNode(true);

                newRow.querySelector('.social-name-input').name = `bd_news_social_links[${rowCount}][name]`;
                newRow.querySelector('.social-url-input').name  = `bd_news_social_links[${rowCount}][url]`;

                newRow.querySelector('.social-name-input').value = '';
                newRow.querySelector('.social-url-input').value  = '';

                wrapper.appendChild(newRow);
            });
        }

        wrapper.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-social-row') && wrapper.getElementsByTagName('tr').length > 1) {
                e.target.closest('tr').remove();
            }
        });
    });
    </script>
    <?php
}