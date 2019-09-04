=== Helpful ===
Contributors: pixelbart
Donate link: https://www.buymeacoffee.com/pixelbart
Tags: helpful, poll, feedback, reviews, vote, review, voting
Requires at least: 4.6
Tested up to: 5.2
Requires PHP: 5.4.0
Stable tag: 4.0.21
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

Important are the PHP version and the plugin version. You can check the settings of the plugin. In the sidebar you will find what you are looking for.

= Is AMP supported by the plugin? =

I'm afraid not. I have not dealt with it and only know that there are problems with Javascript. If you have a solution, you are welcome to share it in the forum.

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

= 4.0.21 =
* Fixed a bug in the dashboard widget where it was not clear for a function whether a variable was an integer or not.
* The CSS class for the contra button is now `.helpful-contra` and no longer `.helpful-pro` (Thx to [derunterstrich](https://github.com/derunterstrich)).
* Date: 04.09.2019

= 4.0.20 =
* General option to hide admin columns inserted by Helpful.
* Fixed a bug where it was not checked if the variable was set.
* The admin widget is now shown when Helpful is activated for the first time, as well as the individual elements in it.
* Date: 27.08.2019

= 4.0.19 =
* Fixed a bug that caused `core/assets/vendor/chartjs/Chart.min.css` files not to be embedded.
* Added option in the Helpful settings under System to reactivate the classic editor for WordPress.

= 4.0.18 =
* Added option in Helpful settings under System to load Helpful first.
* Added WordPress caching for statistics in Total. Emptied as soon as someone votes with Helpful or maintenance is performed.
* You can now activate a cancel button for the feedback form in the Helpful settings under Feedback. The text for this can also be changed there. Don't forget to empty the cache. (The CSS class for the button: `.helpful-cancel`)

= 4.0.17 =
* Most and least helpful entries in dashboard widget extended. Here you can find more information about Helpful for each post. The publishing date for these two values can also be deactivated in the Helpful settings under Details.

= 4.0.16 =
* Filters and actions inserted and optimized. ([Learn more](https://helpful-plugin.info/documentation/filter-action/))
* Percentages are now rounded.

= 4.0.15 =
* Fixed a bug that prevented Helpful from running when Helpful was seen more than once.

= 4.0.14 =
* When performing a maintenance in the Helpful settings under System, feedback texts are now corrected and slashes removed.

= 4.0.13 =
* Added new tags to insert Helpful data of the particular post into texts of Helpful. Available helpers: `{pro}`, `{contra}`, `{total}`, `{pro_percent}`, `{contra_percent}`, `{permalink}`, `{author}`
* Feedback via the feedback form is now saved correctly. HTML are automatically removed for security reasons, but breaks and paragraphs remain.

= 4.0.12 =
* Bug fixed that could be triggered since version 4.0.10 by database updates that can be triggered by admin notes.

= 4.0.11 =
* Added an option in the Helpful Settings under System that allows you to completely disable Helpful Notes in the Admin.

= 4.0.10 =
* The CSS was revised again, because themes and plugins could overwrite the CSS of Helpful. The problem is now hopefully solved.
* The PHP code has also been reworked and now complies with the WordPress coding standards. The problems caused by not checking `is_array()` should now be fixed.

= 4.0.9 =
* Blank theme added. With this theme Helpful will not include a theme and you can design Helpful completely by yourself with CSS. An `blank.css` file can be found in the `examples` folder of Helpful. Do not fill this file, but copy or move it, otherwise it will be overwritten during an update.

= 4.0.8 =
* The maintenance procedure in the Helpful settings, which can be found there under System, now also checks whether the database tables exist and creates them if they do not exist.
* FAQ updated

= 4.0.7 =
* Formatting of PHP improved and more comments added
* The required PHP version has been adjusted. Helpful requires at least PHP version 5.4.0, but higher versions are recommended.
* It is now possible to reset individual contributions again.

= 4.0.6 =
* Fixed `Deprecated: Non-static method Helpful_Customizer::registerCustomizer() should not be called statically`

= 4.0.5 =
* Tab design integrated in settings. This will take you to the customizer where you can change your design.
* The Helpful logo can now be found in the settings.
* CSS corrected so that the designs should look good again.
* The custom CSS added for Helpful now works again. The field is now customizable just like the themes. Codemirror is no longer included for the text field.

= 4.0.4 =
* Added percentages in all charts. Cache must be cleared to see the changes.
* Added percentages behind most and least helpful in dashboard widget.

= 4.0.3 =
* Fixed PHP errors in dashboard ([Read more](https://wordpress.org/support/topic/dashboard-errors-5/))
* Fixed PHP errors in the post overview. ([Read more](https://wordpress.org/support/topic/errors-constantly-being-generated-by-helpful/))

= 4.0.2 =
* Since version 4.0.0 nothing was output anymore if you used the shortcode. This should be fixed now.

= 4.0.1 =
* Now displays the percentage value in doughnuts as well as the value. The extension does not work with bar and stacked charts. (Statistics in Widget)
* 2 translations corrected

= 4.0.0 =
* **Important:** After the update the settings have to be reconfigured because a lot has changed in the background. Also the CSS classes for the Frotend have changed. If there are display problems, please empty the cache.
* You can get more information here: [Version 4.0.0](https://wordpress.org/support/topic/version-4-0-0/)
* Now charts.js is used to display statistics.
* There is a new admin page.
* The feedback is now displayed more clearly in the admin area.
* The themes were revised and also Helpful itself. It is possible that the theme has to be adapted via CSS if changes have been made there.
* Numerous filters have been installed to facilitate Helpful's expansion.
* The documentation on the Helpful website has been extended: [helpful-plugin.info](https://helpful-plugin.info/documentation/)

= 3.1.5 =
* Fixed feedback permissions so editors and administrators can now view, edit, and delete feedback.

= 3.1.4 =
* Fixed a bug that did not allow deleting Helpful from a single post.

= 3.1.3 =
* Bug `call_user_func_array() expects parameter 1 to be a valid callback` fixed

= 3.1.2 =
* Now you can use `{author}` in the preferences to show the name of the author of the current post.
* Now PHP sessions will be used again if cookies cannot be set. Unfortunately I have not found a better solution.
* The `get_browser()` functions were almost all removed because there were too many problems with them.
* No major corrections were made, as I am also working on a major update for Helpful.

= 3.1.1 =
* There's a new theme. This is used on the landing page and is called Clean. The theme works best if you don't specify any text (except the headline).
* It is now possible to make changes in the shortcode. With `[helpful heading="My heading"]` you can change the heading.
* Fixed some bugs in the other themes.
* Helpful runs under 5.2.

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
