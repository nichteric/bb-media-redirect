<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <form method="post">
        <?php wp_nonce_field('bb_redirect_settings_save', 'bb_redirect_settings_nonce'); ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="bb_media_redirect">Redirect Media</label></th>
                <td>
                    <input type="checkbox" name="bb_media_redirect" id="bb_media_redirect" value="1" <?php checked(get_option('bb_media_redirect'), 1); ?> />
                </td>
                <td>
                    <p></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="bb_media_redirect_to">Redirect Media To</label></th>
                <td><input type="text" name="bb_media_redirect_to" placeholder="https://www.yoursite.com" "id=" bb_media_redirect_to" value="<?php echo esc_attr(
                	get_option('bb_media_redirect_to')
                ); ?>" /></td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>

    <?php if (get_option('bb_media_redirect')): ?>
    <h2>Test</h2>
    <table class="wp-list-table striped widefat">
        <tr>
            <td><code><?php echo get_site_url(null, '/wp-content/uploads/'); ?>*</code></td>
            <td>&rarr;</td>
            <td><code><?php echo get_option('bb_media_redirect_to'); ?>/wp-content/uploads/</code></td>
            <td><span id="bb_redir_test">🟠</span><span id="bb_redir_fail">🔴</span><span id="bb_redir_ok">🟢</span></td>
        </tr>
    </table>
    <?php $this->media_redirect_test(); ?>
    <?php endif; ?>
</div>
