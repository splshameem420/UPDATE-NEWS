<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function bd_news_add_brand_admin_menu() {
    add_menu_page(
        'Brand Settings',          
        'Brand Settings',          
        'manage_options',          
        'bd-news-brand-settings',  
        'bd_news_brand_settings_page', 
        'dashicons-networking',      
        23                         
    );
}
add_action( 'admin_menu', 'bd_news_add_brand_admin_menu' );

function bd_news_register_brand_settings() {
    register_setting( 'bd_news_brand_group', 'bd_news_brand_direction' );
    register_setting( 'bd_news_brand_group', 'bd_news_brand_sites', array(
        'sanitize_callback' => function( $input ) {
            return is_array( $input ) ?$input : array();
        }
    ) );
}
add_action( 'admin_init', 'bd_news_register_brand_settings' );

function bd_news_brand_settings_page() {
    $brand_sites      = get_option( 'bd_news_brand_sites', array() );$scroll_direction = get_option( 'bd_news_brand_direction', 'left_to_right' );
    ?>
    <div class="wrap">
        <h1>Network Brand Settings</h1>

        <form method="post" action="options.php" style="margin-top: 20px;">
            <?php settings_fields( 'bd_news_brand_group' ); ?>

            <table class="form-table" style="max-width: 900px;">
                <tr>
                    <th scope="row"><label for="bd_news_brand_direction">Scroll Direction</label></th>
                    <td>
                        <select name="bd_news_brand_direction" id="bd_news_brand_direction">
                            <option value="left_to_right" <?php selected( $scroll_direction, 'left_to_right' ); ?>>Left to Right (Bam theke Dane)</option>
                            <option value="right_to_left" <?php selected( $scroll_direction, 'right_to_left' ); ?>>Right to Left (Dan theke Bame)</option>
                        </select>
                    </td>
                </tr>
            </table>

            <h3>Network Websites / Logos Horizontal Slider</h3>
            <p>Site Name/Text Logo ebong Target Link add korun. Logo URL faka rakhle Name-ti HTML Logo hishebe dekhabe.</p>

            <table class="widefat" id="brand-sites-table" style="max-width: 900px; margin-bottom: 15px;">
                <thead>
                    <tr>
                        <th style="width: 30%;">Site Name (Text Logo)</th>
                        <th style="width: 35%;">Logo Image URL (Optional)</th>
                        <th style="width: 25%;">Website URL</th>
                        <th style="width: 10%;">Action</th>
                    </tr>
                </thead>
                <tbody id="brand-items-wrapper">
                    <?php 
                    if ( ! empty( $brand_sites ) && is_array($brand_sites ) ) :
                        foreach ( $brand_sites as$index => $item ) :$name     = isset( $item['name'] ) ?$item['name'] : '';
                            $logo_url = isset($item['logo'] ) ? $item['logo'] : '';$url      = isset( $item['url'] ) ?$item['url'] : '';
                            ?>
                            <tr class="brand-row">
                                <td>
                                    <input type="text" name="bd_news_brand_sites[<?php echo $index; ?>][name]" value="<?php echo esc_attr( $name ); ?>" class="regular-text brand-name-input" placeholder="e.g. Sports Tube" required />
                                </td>
                                <td>
                                    <input type="url" name="bd_news_brand_sites[<?php echo $index; ?>][logo]" value="<?php echo esc_attr( $logo_url ); ?>" class="regular-text brand-logo-input" placeholder="https://.../logo.png" />
                                </td>
                                <td>
                                    <input type="url" name="bd_news_brand_sites[<?php echo $index; ?>][url]" value="<?php echo esc_attr( $url ); ?>" class="regular-text brand-url-input" placeholder="https://..." required />
                                </td>
                                <td><button type="button" class="button remove-brand-row">Remove</button></td>
                            </tr>
                        <?php endforeach;
                    else : ?>
                        <tr class="brand-row">
                            <td><input type="text" name="bd_news_brand_sites[0][name]" class="regular-text brand-name-input" placeholder="e.g. Sports Tube" required /></td>
                            <td><input type="url" name="bd_news_brand_sites[0][logo]" class="regular-text brand-logo-input" placeholder="https://.../logo.png" /></td>
                            <td><input type="url" name="bd_news_brand_sites[0][url]" class="regular-text brand-url-input" placeholder="https://..." required /></td>
                            <td><button type="button" class="button remove-brand-row">Remove</button></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <button type="button" class="button button-secondary" id="add-brand-item" style="margin-bottom: 20px;">+ Add New Brand</button>

            <?php submit_button( 'Save Brand Settings' ); ?>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        let wrapper   = document.getElementById('brand-items-wrapper');
        let addButton = document.getElementById('add-brand-item');

        if (!wrapper) return;

        if (addButton) {
            addButton.addEventListener('click', function() {
                let rowCount = wrapper.getElementsByTagName('tr').length;
                let firstRow = wrapper.querySelector('.brand-row');
                let newRow   = firstRow.cloneNode(true);

                newRow.querySelector('.brand-name-input').name = `bd_news_brand_sites[${rowCount}][name]`;
                newRow.querySelector('.brand-logo-input').name = `bd_news_brand_sites[${rowCount}][logo]`;
                newRow.querySelector('.brand-url-input').name  = `bd_news_brand_sites[${rowCount}][url]`;

                newRow.querySelector('.brand-name-input').value = '';
                newRow.querySelector('.brand-logo-input').value = '';
                newRow.querySelector('.brand-url-input').value  = '';

                wrapper.appendChild(newRow);
            });
        }

        wrapper.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-brand-row') && wrapper.getElementsByTagName('tr').length > 1) {
                e.target.closest('tr').remove();
            }
        });
    });
    </script>
    <?php
}