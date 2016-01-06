=== Plugin Name ===
Contributors: ijmacd, josk79
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=5T9XQBCS2QHRY&lc=NL&item_name=Jos%20Koenis&item_number=wordpress%2dplugin&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Tags: qtranslate, mqtranslate, polylang
Requires at least: 4.0.0
Tested up to: 4.4
Stable tag: 0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

P2Q: Polylang to qTranslate

== Description ==

Migrates Polylang translations to qTranslate.

Compatible with:
[qTranslate X](https://wordpress.org/plugins/qtranslate-x/),
[qTranslate Slug](https://wordpress.org/plugins/qtranslate-slug/),
[WooCommerce & qTranslate-X](https://wordpress.org/plugins/woocommerce-qtranslate-x) (best used in combination with qTranslate X) and
[qTranslate support for WooCommerce](https://wordpress.org/plugins/qtranslate-support-for-woocommerce/)

Might be compatible with other qTranslate forks and helper-plugins as well, but not tested.

Goodbye Polylang, Hello qTranslate!

Note: This plugin will save you a lot of work, but more configuration and tweaking might be necessary.

Note 2: This plugin is a clone of W2Q: WPML to qTranslate with appropriate modifications. (Donation links are the original author's, not mine)

== Installation ==

1. Upload the plugin in the `/wp-content/plugins/` directory, or automatically install it through the 'New Plugin' menu in WordPress
2. Activate the plugin through the 'Plugins' menu in WordPress

= Polylang to qTranslate Migration =

1. Create a non-production environment to perform the migration. Make sure you have a backup if you want to migrate the production environment.
2. Disable Polylang
3. Install and configure qTranslate X (or any other fork). Test if your environment is still ok.
4. Go to Settings - P2Q: Polylang to qTranslate and press 'Execute'
5. Wait...
6. Test your environment. Some tweaks might be required.
7. Make a [donation](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=5T9XQBCS2QHRY&lc=NL&item_name=Jos%20Koenis&item_number=wordpress%2dplugin&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted). Greatly appreciated!

== Frequently Asked Questions ==

= How does it work =

The plugin walks through translation entries in the wp_term_taxonomy table.
If the element is a post (post_translations) it will be migrated:
The translations for the element will be merged into the element in the default language.
If the element is a post, any comments for the post will also be attached to the new sole element.
Once merged, the translation posts will be removed from the database.

= Will it migrate strings translations? =

No. Only posts will be migrated.

= Will all my URLs stay the same? =

No, slugs are not migrated by default. Some urls will break. If the 'qTranslate Slug'-plugin is installed during migration, the slugs for posts and terms will also be translated for you. Note: the taxonomy slugs will not be translated (e.g. /product-cat/ )

= Can I make a donation? =

Sure! [This](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=5T9XQBCS2QHRY&lc=NL&item_name=Jos%20Koenis&item_number=wordpress%2dplugin&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted) is the link. Greatly appreciated!

Like I mentioned earlier, I am just piggybacking on the work of the original author of the W2Q plugin so I have left his donation link intact.

== Changelog ==

= 0.1 =
* First public version - Cloned from W2Q: WPML to qTranslate 0.9.3
