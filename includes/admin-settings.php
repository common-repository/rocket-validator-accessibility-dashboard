<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_init', 'rocket_validator_settings_init');

add_action('admin_enqueue_scripts', 'rocket_validator_enqueue_admin_scripts');

function rocket_validator_settings_init() {
    // Register a new setting for "rocket_validator_settings" page
    register_setting('rocket_validator_settings', 'rocket_validator_options', 'rocket_validator_options_validate');

    // Register a new section in the "rocket_validator_settings" page
    add_settings_section(
        'rocket_validator_section',
        'Connect your Rocket Validator account',
        'rocket_validator_section_callback',
        'rocket_validator_settings'
    );

    // Register new fields in the "rocket_validator_section" section, inside the "rocket_validator_settings" page
    add_settings_field(
        'rocket_validator_api_token',
        'API Token',
        'rocket_validator_api_token_callback',
        'rocket_validator_settings',
        'rocket_validator_section'
    );

    add_settings_field(
        'rocket_validator_site_url',
        'Site URL',
        'rocket_validator_site_url_callback',
        'rocket_validator_settings',
        'rocket_validator_section'
    );
}

function rocket_validator_section_callback() {
    echo '<p>Connect your Rocket Validator account to show the latest site validation report for your site.</p>';
}

function rocket_validator_api_token_callback() {
    $options = get_option('rocket_validator_options');
    $api_token = isset($options['t']) ? rocket_validator_decrypt_data($options['t']) : '';
    ?>
    <div style="display: flex; align-items: center; max-width: 400px;">
        <input type="password" id="rocket_validator_api_token" name="rocket_validator_options[t]" value="<?php echo esc_attr($api_token); ?>" style="flex-grow: 1;" aria-describedby="api_token_description" autocomplete="off" />
        <button type="button" id="toggle_api_token" class="button" style="margin-left: 10px;" aria-controls="rocket_validator_api_token">
            <span class="screen-reader-text">Show API Token</span>
            <span aria-hidden="true">Show</span>
        </button>
    </div>
    <p class="description" id="api_token_description">Get a free API token on <a href="https://rocketvalidator.com/api/tokens/new?description=WordPress%20Plugin%20-%20<?php echo urlencode(get_site_url()); ?>" target="_blank">Rocket Validator</a>. A read-only token is all you need.</p>
    
    <?php
}

function rocket_validator_site_url_callback() {
    $options = get_option('rocket_validator_options');
    $site_url = isset($options['u']) ? rocket_validator_decrypt_data($options['u']) : get_site_url();
    echo '<input type="url" name="rocket_validator_options[u]" value="' . esc_attr($site_url) . '" style="width: 100%; max-width: 400px;" />';
    echo '<p class="description">This should be the same as the starting URL you used when you created your report.</p>';

    $site_url = $options['u'] ?? get_site_url();
    echo '<p class="description">If you don\'t have a report yet, you can create one <a href="https://rocketvalidator.com/s/new?starting_url=' . esc_attr(urlencode($site_url)) . '" target="_blank">here</a>.</p>';
}

function rocket_validator_options_validate($input) {
    // Check the nonce
    if (!isset($_POST['rocket_validator_settings_nonce']) || !wp_verify_nonce(sanitize_key(wp_unslash($_POST['rocket_validator_settings_nonce'])), 'rocket_validator_settings_nonce')) {
        add_settings_error('rocket_validator_options', 'invalid_nonce', 'Invalid or missing nonce. Please try again.', 'error');
        return get_option('rocket_validator_options');
    }

    $new_input = array();
    $old_options = get_option('rocket_validator_options');

    if (isset($input['t'])) {
        if (rocket_validator_validate_token($input['t'])) {
            $new_input['t'] = rocket_validator_encrypt_data(sanitize_text_field($input['t']));
        } else {
            add_settings_error('rocket_validator_options', 'invalid_api_token', 'Invalid API Token. Please check and try again.', 'error');
            $new_input['t'] = $old_options['t'] ?? '';
        }
    } else {
        $new_input['t'] = $old_options['t'] ?? '';
    }

    if (isset($input['u']) && !empty($input['u'])) {
        $site_url = esc_url_raw($input['u']);
        if (filter_var($site_url, FILTER_VALIDATE_URL) && preg_match('/^https?:\/\//', $site_url)) {
            $new_input['u'] = rocket_validator_encrypt_data($site_url);
        } else {
            add_settings_error('rocket_validator_options', 'invalid_site_url', 'Invalid Site URL. Please enter a valid URL starting with http:// or https://.', 'error');
            $new_input['u'] = $old_options['u'] ?? '';
        }
    } else {
        $new_input['u'] = rocket_validator_encrypt_data(get_site_url());
    }

    return $new_input;
}

function rocket_validator_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html('You do not have sufficient permissions to access this page.'));
    }

    ?>
    <div class="wrap rockval-wrap">
        <h1>Rocket Validator <?php echo esc_html(get_admin_page_title()); ?></h1>

        <?php settings_errors(); ?>

        <form action="options.php" method="post">
            <?php
            settings_fields('rocket_validator_settings');
            do_settings_sections('rocket_validator_settings');
            wp_nonce_field('rocket_validator_settings_nonce', 'rocket_validator_settings_nonce');
            submit_button('Save Settings');
            ?>
        </form>
    </div>
    <?php
}

function rocket_validator_enqueue_admin_scripts($hook) {
    if ('rocket-validator_page_rocket-validator-settings' !== $hook) {
        return;
    }

    wp_register_script(
        'rocket-validator-admin-settings',
        plugins_url('public/js/admin-settings.js', dirname(__FILE__)),
        array('jquery'),
        filemtime(plugin_dir_path(dirname(__FILE__)) . 'public/js/admin-settings.js'),
        true
    );

    wp_enqueue_script('rocket-validator-admin-settings');
}

?>