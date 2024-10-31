=== Rocket Validator Accessibility Dashboard ===
Contributors: rocketvalidator
Tags: accessibility, html, validator
Requires at least: 6.6.2
Tested up to: 6.6.2
Requires PHP: 8.2
Stable tag: 0.1.3
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Connect your Rocket Validator account to show the latest site-wide accessibility & HTML validation report for your site.

== Description ==
This plugin displays the most recent site-wide accessibility and HTML validation report for your website, retrieved from your connected Rocket Validator account and displayed within your WordPress admin interface.

Rocket Validator is a Software as a Service (SaaS) solution used by organizations, web agencies, and developers across the globe to streamline the validation process for large websites.

The automated web crawler scans your site and checks the internal web pages to automatically identify accessibility and HTML issues, producing a summary report with the main issues found.

The free version of Rocket Validator includes 100 free checks per month and allows users to check up to 25 web pages per site for HTML issues. Upgrading to a Pro account unlocks a range of advanced features, including reports with 5,000 web pages per site, A11Y and HTML checks powered by Axe Core and W3C HTML Validator Nu, and scheduling, deploy hooks, muting rules, device viewport emulation, and guest accounts. Pro accounts are entitled to perform up to 50,000 monthly checks.

= Note about automated testing =
This site validation report details the issues identified by automated testing with Axe Core and W3C HTML Validator. It is essential to recognise that automated testing represents only one aspect of the overall validation process. No automated tool can provide absolute assurance that your web pages are free from issues.

The absence of issues identified by Rocket Validator does not guarantee that a site is entirely free from issues. It is also advisable to perform manual testing on a range of devices and browsers.

= Note about external service integration =
This plugin uses the Rocket Validator API to retrieve the site validation report from your Rocket Validator account.

You must have a Rocket Validator account to use this plugin. Sign up for a free account and get 100 free monthly HTML checks and 25 pages per report. Or, subscribe to a paid plan to get all the features, including Axe Core checks, scheduling, muting, device viewport emulation, and more.

Once you have an account, you can create an API token to connect your Rocket Validator account to your WordPress site. This plugin uses the API token to authenticate on the Rocket Validator API and retrieve the latest site validation report from your Rocket Validator account.

Sign up: [https://rocketvalidator.com/registration/new](https://rocketvalidator.com/registration/new)
Pricing: [https://rocketvalidator.com/pricing](https://rocketvalidator.com/pricing)
Terms of service: [https://rocketvalidator.com/terms](https://rocketvalidator.com/terms)
Documentation: [https://docs.rocketvalidator.com](https://docs.rocketvalidator.com)

= Installation =
All you need to connect to your Rocket Validator account is an API token which you can get after you sign up for a free Rocket Validator account.

1. Install the Rocket Validator Accessibility Dashboard plugin.
2. Sign up for free Rocket Validator account at [https://rocketvalidator.com/registration/new](https://rocketvalidator.com/registration/new)
3. Run your first report for your site at [https://rocketvalidator.com/s/new](https://rocketvalidator.com/s/new)
4. Create a read-only API token at [https://rocketvalidator.com/api/tokens/new](https://rocketvalidator.com/api/tokens/new)
5. Go to the WP plugin settings and enter your API token. Also double check the site URL for the report matches the one for your site.
6. All set up! Go to the main page of the Rocket Validator Accessibility Dashboard to see your report.

== Frequently Asked Questions ==
= Is there a free trial? =
Yes, you can sign up for a free trial to check up to 25 pages for HTML issues. Also, if you want to try all the features (accessibility validation, schedules, deploy hooks, muting) you can get a Pro trial at a reduced price.

= What kind of issues does the report include? =
The trial version shows HTML issues found by the W3C Validator Nu checker. This checker also finds CSS issues for inline styles. Here\'s a list of common HTML issues found by Rocket Validator:

[https://rocketvalidator.com/html-validation](https://rocketvalidator.com/html-validation)

If you have a Pro account, you also get accessibility issues found by Axe Core. Here\'s a list of common accessibility issues found by Rocket Validator:

[https://rocketvalidator.com/html-validation](https://rocketvalidator.com/html-validation)

== Screenshots ==
1. Main dashboard showing the issues found on your site
2. Settings page where you enter the API token and site URL

== Changelog ==

= 0.1.3 =

- Relax PHP version requirement to 8.2.

= 0.1.2 =

- Minor fixes in settings page.

= 0.1.1 =

- Updated banner images.
- Added links to the Rocket Validator site, documentation, pricing, and terms of service.

= 0.1.0 =

Initial version. Settings let you enter an API token and site URL, the dashboard connects with the Rocket Validator API to get the latest site validation report for the site.