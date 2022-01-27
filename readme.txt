=== Helpful ===
Contributors: pixelbart
Donate link: https://www.buymeacoffee.com/pixelbart
Tags: helpful, poll, feedback, reviews, vote, review, voting
Requires at least: 4.6
Tested up to: 5.9
Requires PHP: 5.6.20
Stable tag: 4.4.68
License: MIT License
License URI: https://opensource.org/licenses/MIT

Add a fancy feedback form under your posts or post types and ask your visitors a question. Give them the ability to vote with yes or no.

== Description ==

Add a fancy feedback form under your posts or post types and ask your visitors a simple question: Was this helpful?
The plugin give them the ability to vote with yes or no. If it is not enough, you can look in your post list to get
an overview about your votes (pros and cons). With the integrated dashboard widget you only need to login, to find
what you need. Simply change your form theme or add your own css in the options.

Languages: English, German
Demo: [klakki.me](https://klakki.me/) (German)
Support: [wordpress.org](https://wordpress.org/support/plugin/helpful/)
Documentation: [helpful-plugin.info](https://helpful-plugin.info/documentation/) (English)
Github: [Helpful](https://github.com/pixelbart/helpful)

**Features**

1. fully changeable texts and questions
1. disable for users who already voted
1. simple feedback form after vote
1. page, cpt overview statistics
1. custom post type support
1. dashboard statistics
1. custom css
1. 5 themes
1. NO AMP SUPPORT

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/helpful` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the Settings->Helpful screen to configure the

== Frequently Asked Questions ==

= Can I show votes after voting? =

You can. Simply use `{pro}` and `{contra}` in your texts to show the vote number.

= Can I use my own CSS files? =

You can. Choose `theme` as theme in the helpful options. Then create an `helpful` folder and an `theme.css` file in it. Fill the `theme.css` file with your CSS and you're done.

= Are there any shortcodes I can use? =

You can. Just use the shortcode `[helpful]` to output Helpful.

= Do I need to consider anything when reporting problems? =

In WordPress you can click on Tools, Site Health and then on Report. There should be a point Helpful. The information there might be useful for support.

= Is AMP supported by the plugin? =

I'm afraid not. Helpful does not support AMP because Helpful works with jQuery. If you know a solution, you are welcome to share it on [Github](https://github.com/pixelbart/helpful).

= I'm a programmer, can I help? =

Sure. Helpful is now also available on [Github](https://github.com/pixelbart/helpful). I will always update the code first and fix issues with Codacy. You are always welcome to help. Forke Helpful and start bullying!

= Can I output the author of the post? =

You can. Use `{author}` for it in your texts. The `display_name` of the author will then be used.

= How can I change the Helpful theme? =

Go to Design in the Helpful settings, or visit your WordPress Customizer. There you will find a Helpful menu item.

= How can I reset a single post? =

First you have to activate the Metabox in the Helpful settings. Once you have done this, a box will appear below the posts (in the admin area) where you can reset the post.

= My feedback is not displayed, what can I do? =

Switch to the Helpful settings and click on the System tab. There you will find the item Maintenance. Perform the maintenance once. Helpful will do the rest for you.

== Changelog ==

For the Helpful changelog, please see [the Releases page on GitHub](https://github.com/pixelbart/helpful/releases).
