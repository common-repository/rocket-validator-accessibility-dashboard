<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

function rocket_validator_get_version() {
    $plugin_data = get_file_data(__DIR__ . '/../rocket-validator-accessibility-dashboard.php', array('Version' => 'Version'), false);
    return $plugin_data['Version'];
}

function rocket_validator_get_user_agent() {
    $wp_version = get_bloginfo('version');
    $site_url = get_site_url();
    $plugin_version = rocket_validator_get_version();
    return "WordPress/{$wp_version}; {$site_url}; Rocket Validator Accessibility Dashboard {$plugin_version}";
}

function rocket_validator_cache_key($prefix) {
    $options = get_option('rocket_validator_options');
    $site_url = $options['u'] ?? '';
    return $prefix . md5($site_url);
}

function rocket_validator_validate_token($api_token) {
    $api_url = 'https://rocketvalidator.com/api/v1/reports?page[size]=1';

    // Try to decrypt the token, if it fails, assume it's already decrypted
    $decrypted_token = rocket_validator_decrypt_data($api_token);
    $token_to_use = $decrypted_token !== false ? $decrypted_token : $api_token;

    $response = wp_remote_get($api_url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $token_to_use,
            'Content-Type'  => 'application/json',
        ),
        'user-agent' => rocket_validator_get_user_agent()
    ));

    if (is_wp_error($response)) {
        return false;
    }

    $status_code = wp_remote_retrieve_response_code($response);

    return $status_code === 200;
}

function rocket_validator_fetch_report() {
    $cache_key = rocket_validator_cache_key('rocket_validator_report_');
    $cached_data = get_transient($cache_key);
    
    if ($cached_data !== false) {
        return $cached_data;
    }

    $options = get_option('rocket_validator_options');
    $api_key = isset($options['t']) ? rocket_validator_decrypt_data($options['t']) : '';
    $site_url = isset($options['u']) ? rocket_validator_decrypt_data($options['u']) : '';
    
    $api_url = 'https://rocketvalidator.com/api/v1/reports?page[size]=1&filter[url]=' . urlencode($site_url);

    $response = wp_remote_get($api_url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type'  => 'application/json',
        ),
        'user-agent' => rocket_validator_get_user_agent()
    ));

    $status_code = wp_remote_retrieve_response_code($response);
   
    if ($status_code === 401) {
        return new WP_Error('invalid_api_token', 'Invalid API Token');
    }
   
    if ($status_code === 429) {
        return new WP_Error('rate_limit_exceeded', 'Rate limit exceeded');
    }

    if (is_wp_error($response)) {
        return $response;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return new WP_Error('json_error', 'Error decoding JSON response');
    }

    set_transient($cache_key, $data, 1 * MINUTE_IN_SECONDS);

    return $data;
}

function rocket_validator_fetch_common_html_issues($report_id) {
    return rocket_validator_fetch_common_issues($report_id, 'html');
}

function rocket_validator_fetch_common_a11y_issues($report_id) {
    return rocket_validator_fetch_common_issues($report_id, 'a11y');
}

function rocket_validator_fetch_common_issues($report_id, $issue_type) {
    $cache_key = rocket_validator_cache_key("rocket_validator_common_{$issue_type}_issues_");

    $cached_data = get_transient($cache_key);
    
    if ($cached_data !== false) {
        return $cached_data;
    }

    $options = get_option('rocket_validator_options');
    $api_key = isset($options['t']) ? rocket_validator_decrypt_data($options['t']) : '';
    
    $api_url = "https://rocketvalidator.com/api/v1/reports/{$report_id}/common_{$issue_type}_issues?page[size]=100";

    $response = wp_remote_get($api_url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type'  => 'application/json',
        ),
        'user-agent' => rocket_validator_get_user_agent()
    ));

    if (is_wp_error($response)) {
        return $response;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return new WP_Error('json_error', 'Error decoding JSON response');
    }

    set_transient($cache_key, $data, 1 * MINUTE_IN_SECONDS);

    return $data;
}
