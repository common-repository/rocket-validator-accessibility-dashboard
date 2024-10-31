<?php
/*
Plugin Name: Rocket Validator Accessibility Dashboard
Author: Rocket Validator
Author URI: https://rocketvalidator.com
Description: Rocket Validator is a digital accessibility monitoring service for busy developers. This plugin connects with your Rocket Validator account to show the latest site validation report for your site, which shows the accessibility and markup issues found by Axe Core and W3C HTML Validator.
Version: 0.1.3
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'includes/encryption-utils.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/api-handler.php';

// Hook for adding admin menus
add_action('admin_menu', 'rocket_validator_add_menu');

// Add activation hook
register_activation_hook(__FILE__, 'rocket_validator_activate');

// Sets default site URL
function rocket_validator_activate() {
    $options = get_option('rocket_validator_options', array());
    $options['u'] = rocket_validator_encrypt_data(get_site_url());
    update_option('rocket_validator_options', $options);
}

// Add deactivation hook
register_deactivation_hook(__FILE__, 'rocket_validator_deactivate');

// Add uninstall hook, same as deactivation
register_uninstall_hook(__FILE__, 'rocket_validator_deactivate');

// Deletes options
function rocket_validator_deactivate() {
    delete_option('rocket_validator_options');
}

function rocket_validator_add_menu() {
    add_menu_page(
        'Rocket Validator Accessibility Dashboard',
        'Rocket Validator',
        'manage_options',
        'rocket-validator-accessibility-dashboard',
        'rocket_validator_dashboard_page',
        'dashicons-analytics',
        100
    );

    add_submenu_page(
        'rocket-validator-accessibility-dashboard',
        'Settings',
        'Settings',
        'manage_options',
        'rocket-validator-settings',
        'rocket_validator_settings_page'
    );
}

// Enqueue admin styles
add_action('admin_enqueue_scripts', 'rocket_validator_enqueue_admin_styles');

function rocket_validator_enqueue_admin_styles() {
    wp_enqueue_style(
        'rocket-validator-admin-style',
        plugin_dir_url(__FILE__) . 'public/css/rocket.css',
        array(),
        '0.1'
    );
}

function rocket_validator_dashboard_page() {
    // Check if API token is set
    $options = get_option('rocket_validator_options');
    $api_token = isset($options['t']) ? rocket_validator_decrypt_data($options['t']) : '';

    if (empty($api_token)) {
        ?>
        <div class="wrap rockval-wrap"">
            <div style="display: flex; justify-content: flex-end;">
                <a href="https://rocketvalidator.com"><img src="<?php echo esc_url(plugin_dir_url(__FILE__) . 'public/img/rocket-full.svg'); ?>" alt="Rocket Validator" style="max-width: 200px;"></a>
            </div>
            <div class="notice notice-info" style="margin-top: 20px;">
                <p><strong>Welcome to Rocket Validator!</strong></p>
                <p>To get started, please go to the <a href="<?php echo esc_url(admin_url('admin.php?page=rocket-validator-settings')); ?>">Rocket Validator Settings</a> page and enter your API token.</p>
                <p>If you don't have an API token yet, you can <a href="https://rocketvalidator.com/api/tokens/new?description=WordPress%20Plugin%20-%20<?php echo esc_attr(urlencode(get_site_url())); ?>" target="_blank">get a free API token on Rocket Validator</a>.</p>
            </div>
        </div>
        <?php
        return;
    }

    // Fetch the latest report
    $report = rocket_validator_fetch_report();

    if (is_wp_error($report)) {
        $error_code = $report->get_error_code();
        $error_message = $report->get_error_message();
        ?>
        <div class="wrap rockval-wrap"">
            <div style="display: flex; justify-content: flex-end;">
                <a href="https://rocketvalidator.com"><img src="<?php echo esc_url(plugin_dir_url(__FILE__) . 'public/img/rocket-full.svg'); ?>" alt="Rocket Validator" style="max-width: 200px;"></a>
            </div>
            <div class="rockval-error" style="margin-top: 20px;">
                <?php if ($error_code === 'invalid_api_token'): ?>
                    <p><b>Invalid API Token.</b></p>
                    <p>Please enter a valid API token in the <a href="<?php echo esc_url(admin_url('admin.php?page=rocket-validator-settings')); ?>">Rocket Validator Settings</a>.</p>
                <?php else: ?>
                    <p><b>Error occurred:</b> <?php echo esc_html($error_message); ?></p>
                    <p>Please check your settings and try again. If the problem persists, contact <a href="https://rocketvalidator.com/contact">support.</a></p>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return;
    }

    if (empty($report['data'])) {
        $options = get_option('rocket_validator_options');
        $site_url = isset($options['u']) ? rocket_validator_decrypt_data($options['u']) : '';
        ?>
        <div class="wrap rockval-wrap""> 
            <div style="display: flex; justify-content: flex-end;">
                <a href="https://rocketvalidator.com"><img src="<?php echo esc_url(plugin_dir_url(__FILE__) . 'public/img/rocket-full.svg'); ?>" alt="Rocket Validator" style="max-width: 200px;"></a>
            </div>

            <div class="rockval-error" style="margin-top: 20px;">
                <p>No matching report could be found for <b><?php echo esc_html($site_url); ?></b>.</p>
                <p>Please check your settings and try again, or <a href="https://rocketvalidator.com/s/new?starting_url=<?php echo esc_attr($site_url); ?>">create a new report for that URL</a>.</p>
            </div>
        </div>
        <?php
        return;
    }
    ?>

    <div class="wrap rockval-wrap"">
        <?php 
            $report_id = $report['data'][0]['id'];
            $report_data = $report['data'][0]['attributes'];
        ?>
     
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <h1 style="margin-right: 20px;">Report for <a href="https://rocketvalidator.com/s/<?php echo esc_attr($report_id); ?>"><?php echo esc_html($report_data['starting_url']); ?></a></h1>
            <a href="https://rocketvalidator.com"><img src="<?php echo esc_url(plugin_dir_url(__FILE__) . 'public/img/rocket-full.svg'); ?>" alt="Rocket Validator" style="max-width: 200px;"></a>
        </div>

        <p class="rockval-report-details">
          <b><?php echo esc_html($report_data['num_pages']); ?></b> web <?php echo $report_data['num_pages'] === 1 ? 'page' : 'pages'; ?> checked. 
          Last check: <?php echo esc_html(wp_date('F j, Y, H:i', strtotime($report_data['last_checked_at']))); ?>
        </p>

        <div class="rockval-counters">
             <?php if ($report_data['perform_a11y_checks']) : 
                $a11y_issues = $report_data['checks']['a11y']['issues'];
                $severity = $a11y_issues['severity'];
            ?>

                    <div class="rockval-counter-box <?php echo $severity['critical'] > 0 ? 'rockval-bg-error' : 'rockval-bg-success'; ?>">
                        <span class="rockval-counter-number"><?php echo esc_html($severity['critical']); ?></span>
                        <span class="rockval-counter-label">critical A11Y <?php echo $severity['critical'] === 1 ? 'issue' : 'issues'; ?></span>
                    </div>
                    <div class="rockval-counter-box <?php echo $severity['serious'] > 0 ? 'rockval-bg-error' : 'rockval-bg-success'; ?>">
                        <span class="rockval-counter-number"><?php echo esc_html($severity['serious']); ?></span>
                        <span class="rockval-counter-label">serious A11Y <?php echo $severity['serious'] === 1 ? 'issue' : 'issues'; ?></span>
                    </div>
                    <div class="rockval-counter-box <?php echo $severity['moderate'] > 0 ? 'rockval-bg-warning' : 'rockval-bg-success'; ?>">
                        <span class="rockval-counter-number"><?php echo esc_html($severity['moderate']); ?></span>
                        <span class="rockval-counter-label">moderate A11Y <?php echo $severity['moderate'] === 1 ? 'issue' : 'issues'; ?></span>
                    </div>
                    <div class="rockval-counter-box <?php echo $severity['minor'] > 0 ? 'rockval-bg-warning' : 'rockval-bg-success'; ?>">
                        <span class="rockval-counter-number"><?php echo esc_html($severity['minor']); ?></span>
                        <span class="rockval-counter-label">minor A11Y <?php echo $severity['minor'] === 1 ? 'issue' : 'issues'; ?></span>
                    </div>
            <?php endif; ?>

            <?php if ($report_data['perform_html_checks']) : 
                $html_issues = $report_data['checks']['html']['issues'];
            ?>
            
                <div class="rockval-counter-box <?php echo $html_issues['errors'] > 0 ? 'rockval-bg-error' : 'rockval-bg-success'; ?>">
                    <span class="rockval-counter-number"><?php echo esc_html($html_issues['errors']); ?></span>
                    <span class="rockval-counter-label">HTML errors</span>
                </div>
                <div class="rockval-counter-box <?php echo $html_issues['warnings'] > 0 ? 'rockval-bg-warning' : 'rockval-bg-success'; ?>">
                    <span class="rockval-counter-number"><?php echo esc_html($html_issues['warnings']); ?></span>
                    <span class="rockval-counter-label">HTML warnings</span>
                </div>
            <?php endif; ?>
         </div>
    
        <?php if  ($report_data['perform_a11y_checks']) : ?>
            <?php
            $common_a11y_issues = rocket_validator_fetch_common_a11y_issues($report_id);

            if (!is_wp_error($common_a11y_issues)) {
                $impact_levels = ['critical', 'serious', 'moderate', 'minor'];

                foreach ($impact_levels as $impact) :
                    $filtered_issues = array_filter($common_a11y_issues['data'], function($issue) use ($impact) {
                        return $issue['attributes']['impact'] === $impact;
                    });

                    usort($filtered_issues, function($a, $b) {
                        return $b['attributes']['how_many'] - $a['attributes']['how_many'];
                    });
            ?>
                    <h2>Common <?php echo esc_html(ucfirst($impact)); ?> Accessibility Issues</h2>
                    <?php if (empty($filtered_issues)) : ?>
                        <p class="rockval-no-issues">✓ No <?php echo esc_html($impact); ?> accessibility issues found.</p>
                    <?php else : ?>
                        <table class="wp-list-table widefat striped">
                            <thead>
                                <tr>
                                    <th style="text-align: right; width: 45px;">Count</th>
                                    <th>Issue</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($filtered_issues as $issue) : ?>
                                <tr>
                                    <td class="rockval-issue-count"><?php echo esc_html($issue['attributes']['how_many']); ?></td>
                                    <td>
                                        <a class="rockval-issue-link" href="https://rocketvalidator.com/s/<?php echo esc_attr($report_id); ?>/v/<?php echo esc_attr($issue['id']); ?>"><?php echo esc_html($issue['attributes']['help']); ?></a>
                                        <?php if (!empty($issue['attributes']['tags'])) : ?>
                                            <div class="rockval-tag-list">
                                                <?php foreach ($issue['attributes']['tags'] as $tag) : ?>
                                                    <span class="rockval-tag"><?php echo esc_html($tag); ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
            <?php
                endforeach;
            }
            ?>
        <?php endif; ?>

        <?php if ($report_data['perform_html_checks']) ?>
            <h2>Common HTML Errors</h2>
            
            <?php
            $common_html_issues = rocket_validator_fetch_common_html_issues($report_id);

            if (is_wp_error($common_html_issues)) : ?>
                <p class="rockval-error">Error fetching common HTML issues: <?php echo esc_html($common_html_issues->get_error_message()); ?></p>
            <?php else :
                // Filter and sort errors
                $errors = array_filter($common_html_issues['data'], function($issue) {
                    return $issue['attributes']['issue_type'] === 'error';
                });
                usort($errors, function($a, $b) {
                    return $b['attributes']['how_many'] - $a['attributes']['how_many'];
                });

                if (empty($errors)) : ?>
                    <pclass="rockval-no-issues">✓ No HTML errors found.</p>
                <?php else : ?>
                    <table class="wp-list-table widefat striped">
                        <thead>
                            <tr>
                                <th style="text-align: right; width: 45px;">Count</th>
                                <th>Issue</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($errors as $issue) : ?>
                            <tr>
                                <td class="rockval-issue-count"><?php echo esc_html($issue['attributes']['how_many']); ?></td>
                                <td>
                                    <a class="rockval-issue-link" href="https://rocketvalidator.com/s/<?php echo esc_attr($report_id); ?>/i/<?php echo esc_attr($issue['id']); ?>"><?php echo esc_html($issue['attributes']['message']); ?></a>
                                
                                    <?php if (!empty($issue['attributes']['tags'])) : ?>
                                        <div class="rockval-tag-list">
                                            <?php foreach ($issue['attributes']['tags'] as $tag) : ?>
                                                <span class="rockval-tag"><?php echo esc_html($tag); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                               
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif;
            endif;
            ?>

            <h2>Common HTML Warnings</h2>

            <?php
            if (!is_wp_error($common_html_issues)) {
                // Filter and sort warnings
                $warnings = array_filter($common_html_issues['data'], function($issue) {
                    return $issue['attributes']['issue_type'] !== 'error';
                });
                
                usort($warnings, function($a, $b) {
                    return $b['attributes']['how_many'] - $a['attributes']['how_many'];
                });

                if (empty($warnings)) : ?>
                    <p class="rockval-no-issues">✓ No HTML warnings found.</p>
                <?php else : ?>
                    <table class="wp-list-table widefat striped">
                        <thead>
                            <tr>
                                <th style="text-align: right; width: 45;">Count</th>
                                <th>Issue</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($warnings as $issue) : ?>
                            <tr>
                                <td class="rockval-issue-count"><?php echo esc_html($issue['attributes']['how_many']); ?></td>
                                <td>
                                    <a class="rockval-issue-link" href="https://rocketvalidator.com/s/<?php echo esc_attr($report_id); ?>/i/<?php echo esc_attr($issue['id']); ?>"><?php echo esc_html($issue['attributes']['message']); ?></a>
                                    <?php if (!empty($issue['attributes']['tags'])) : ?>
                                        <div class="rockval-tag-list">
                                            <?php foreach ($issue['attributes']['tags'] as $tag) : ?>
                                                <span class="rockval-tag"><?php echo esc_html($tag); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif;
            }
            ?>

            <h2>Remember to test manually, too!</h2>
            <p>This site validation report details the issues identified by automated testing with Axe Core and W3C HTML Validator. It is essential to recognise that automated testing represents only one aspect of the overall validation process. No automated tool can provide absolute assurance that your web pages are free from issues.</p>
            <p>The absence of issues identified by Rocket Validator does not guarantee that a site is entirely free from issues. It is also advisable to perform manual testing on a range of devices and browsers.</p>

        </div>
    <?php
}