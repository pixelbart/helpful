=== Helpful ===
Contributors: pixelbart
Donate link: https://www.paypal.me/834rd
Tags: helpful, poll, feedback, reviews, vote, review, voting
Requires at least: 4.6
Tested up to: 5.1
Stable tag: 3.1.0
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

You can. Simply use {pro} and {contra} in your texts to show the vote number.

= Can I use my own CSS files? =

You can. Choose `theme` as theme in the helpful options. Then create an `helpful` folder and an `theme.css` file in it. Fill the `theme.css` file with your CSS and you're done.

= Are there any shortcodes I can use? =

You can. Just use the shortcode `[helpful]` to output Helpful.

= Do I need to consider anything when reporting problems? =

Important are the PHP version and the plugin version. You can check the settings of the plugin. In the sidebar you will find what you are looking for.

= Is AMP supported by the plugin? =

I'm afraid not. I have not dealt with it and only know that there are problems with Javascript. If you have a solution, you are welcome to share it in the forum.

= Why is the code editor not displayed in the options? =

For the code editor you need at least version 4.9 of WordPress. Without this version, a text field will be displayed instead of the editor. The editor is used for custom CSS.

= I'm a programmer, can I help? =

Sure. Helpful is now also available on [Github](https://github.com/pixelbart/helpful). I will always update the code first and fix issues with Codacy. You are always welcome to help. Forke Helpful and start bullying!

== Changelog ==

= 3.1.1 =
* Added new theme (clean; without content text!)
* Added attributes for shortcode (heading, content, etc.)
* Fixed some css issues

= 3.1.0 =
* The plugin is now additionally hosted on Github and issues are detected using Codacy.
* The code has been rewritten and comments are added.
* Fixed major issues using Codacy and a lot of coffee.
* Bug with user ID fixed. Thanks to [@iovamihai](https://wordpress.org/support/users/iovamihai/).
* Not in the plugin: There is now a [landing page](https://helpful-plugin.info/) (still in work). This page is now also specified in the credits. So you advertise Helpful and not me.

= 3.0.11 =
* When registering the feedback some variables were still set to true. Therefore the feedback could be found when creating URLs. This is no longer the case.
* In one of the PHP classes comments have been added to explain certain values.

= 3.0.10 =
* Error with `wp_enqueue_code_editor` fixed by asking if the function exists before. The code editor for the design options does not seem to work with older WordPress versions.

= 3.0.9 =
* The Javascript has been rewritten to support Internet Explorer.

= 3.0.8 =
* Instead of automatically shortening the content of the column with the linked post, there is now an option for this.
* There is a new option to activate a meta box. With this you can see the current pros and cons. You can also reset the current post.

= 3.0.7 =
* Fixed bug that made it possible to submit the feedback form without clicking on the button
* Fixed bug that caused when you clicked on the icon inside the dashboard widget to redirect to the feedback overview

= 3.0.6 =
* Fixed translation issues
* Width in first column increased

= 3.0.5 =
* Added some options for the feedback overview
* Removed feedback entries from search

= 3.0.4 =
* Fixed dashboard widget

= 3.0.3 =
* Added option to display feedback messages directly in the tables, not beautiful - but functional!
* Added an option to set the link in the Dashboard widget to the overview and not to the individual entries

= 3.0.2 =
* Feedback post type corrected so that it does not appear in the frontend

= 3.0.1 =
* Fixed a bug in the sidebar of the settings for the plugin
* Added option to display percentages first (in tables and in widget)
* Fixed translation in sidebar of plugin settings

= 3.0.0 =
* It is now possible to display a simple feedback form after voting, yay!
* Added percentage values when hovering on table columns
* Added a simple blacklist check based on `wp_blacklist_check` for feedback form
* CSS, code and settings revised
* Moved Helpful from settings to its own menu
* Dashboard-Widget bug fixed
* Themes modified for the feedback form

= 2.4.20 =
* Added option so users can voter several times
* Fixed: remove container if no text exists after voting

= 2.4.19 =
* Added cookie for user identification (users can now vote again until the cookie is set)
* Removed md5 remote address for user identification
* Fixed click events for buttons

= 2.4.18 =
* Use custom timezone only if exists (default ist wordpress timezone)
* Fixed auto reset options on reactivating plugin

= 2.4.16 =
* Added timezone option (used if wordpress timezone not working)
* Fixed javascript in loops
* Fixed timezone

= 2.4.15 =
* Added private post types

= 2.4.14 =
* Tested WordPress 5.1
* Fixed Shortcode
* Hide helpful on frontpage (use shortcode instead)

= 2.4.13 =
* Fixed new option

= 2.4.12 =
* Added option so users can only vote once on the entire website

= 2.4.11 =
* Fixed `wp_editor();`
* Fixed translation

= 2.4.10 =
* Removed option for timezones and use insteed the wordpress timezone

= 2.4.9 =
* Added option for timezones
* Changed credits
* Removed `wp_editor();`

= 2.4.8 =
* Added new css classes if no content exists or counters are disabled
* Added version after enqueued files for easier supporting
* Added new design
* Fixed some comments
* Fixed custom theme support

= 2.4.7 =
* Fixed frontend scripts

= 2.4.6 =
* Fixed frontend scripts

= 2.4.5 =
* Tested with WordPress 5.0
* Fixed a issue in after message
* Fixed translation in dashboard widget (now uses the columns translation)
* {permalink} added for outputting the permalink

= 2.4.4 =
* WPML Support

= 2.4.3 =
* Cleaned code
* Added custom theme
* Fixed themes with border-box
* Fixed undefined variables
* Using codemirror from wordpress core
* Fixed widget css (after removing credits)

= 2.4.2 =
* Sidebar voting link added
* Settings no longer reset (only votes)
* Recently helpful and unhelpful entries (widget)
* Number of entries (widget)

= 2.4.1 =
* Fixed column option
* Accurate percentages in the widget

= 2.4.0 =
* Better but simple statistics (thx to @caspero and @anefarious1)
* Improvements (thx to @faterson)
* Bug fixes

= 2.3.2 =
* Fixed Shortcode

= 2.3.1 =
* Fixed admin enqueue (codemirror)

= 2.3 =
* Contact Form 7 Support
* Code optimization

= 2.2 =
* Refreshed languages
* Changed default language to en_US
* Added new de_DE and en_US language files
* Code optimization

= 2.1 =
* Refreshed languages

= 2.0 =
* Converted pro to free version
* Added shortcode `[helpful]`
* Added option, to hide helpful in content for better use with shortcode

= 1.1 =
* 4.9 Tested and fixed

= 1.0.4 =
* Fixed a bug within post metas

= 1.0.2 =
* Fixed a bug in dashboard widget

= 1.0.1 =
* WordPress 4.8 Tested

= 1.0.0 =
* Release
