=== Login No Captcha reCAPTCHA ===
Contributors: robertpeake, robert.peake
Tags: google,nocaptcha,recaptcha,security,login,bots
Requires at least: 3.0.0
Tested up to: 4.3
Stable tag: 1.1.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds a Google No Captcha ReCaptcha checkbox to your Wordpress login screen.

== Description ==

Adds a Google No Captcha ReCaptcha checkbox to your Wordpress login screen. Denies access to automated scripts while making it easy on humans to log in by checking a box. As Google says, it is "Tough on bots, easy on humans."

== Installation ==

Install as normal for WordPress plugins.

== Frequently Asked Questions ==

= Why should I install this plugin? =

Many Worpdress sites are bombarded by automated scripts trying to log in to the admin over and over. 

The No Captcha is a very simple, Google-supported test to quickly deny access to automated scripts. It is great by itself to instantly make your Wordpress site more secure, or can be used with other plugins (like Google Authenticator, Limit Login Attempts, etc.) as part of a defense-in-depth strategy.

= There are a lot of other plugins for this, why should I install <em>this</em> one? =

I've gone to great lengths to try to make sure this plugin is easy to use and install, that it is compatible with different Wordpress configurations, supports multiple languages, and that you won't accidentally lock yourself out of the admin by using it. I use it myself on my own sites as well. So far, it just works.

= Where can I learn more about Google reCAPTCHA? =

<a href="https://www.google.com/recaptcha/intro/index.html">https://www.google.com/recaptcha/intro/index.html</a>

= What are your boring legal disclaimers? =

This plugin is not affiliated with or endorsed by Google in any way. Google is a registered trademark of Google, Inc. By using reCAPTCHA you agree  the <a href="https://www.google.com/intl/en/policies/terms/">terms of service</a> set out by Google. The author provides no warranty as to the suitability to any purpose of this software. You agree to use it entirely at your own risk.

== Screenshots ==

1. Configuration options screen
2. Login screen once configured

== Changelog ==

= 1.1.4 =

 * Better display of captcha when javascript disabled (thanks to webmasteral)

= 1.1.3 =

 * Improved handling of certain Google responses

= 1.1.2 =

 * Improved just-in-time script registration (only for admin/login)

= 1.1.1 =

 * Remove warning about enqueueing css/js too early

= 1.1 =

 * Major security improvement: now supporting reCaptcha checking with javascript disabled (thanks to mfjtf)

= 1.0.3 =

 * Resolve issue with Wordpress hosted on an inaccessible domain (e.g. localhost)

= 1.0.2 =

 * Resolve bug with wp_remote_post() payload

= 1.0.1 =

 * Resolve linking issue due to repository maintainers renaming the plugin

= 1.0.0 =

* Initial release
