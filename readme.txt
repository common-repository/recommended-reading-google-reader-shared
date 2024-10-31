=== Recommended Reading: Google Reader Shared ===
Contributors: jakemgold, thinkoomph
Donate link: http://www.get10up.com/plugins/recommended-reading-google-reader-shared-wordpress/
Tags: google, reader, shared, rss, feeds, recommended, blogs, widget, shortcode
Requires at least: 2.8
Tested up to: 2.8.6
Stable tag: 4.0.4

Recommended Reading gets the shared items from your Google Reader account. Includes easy configuration, widget 
support, ability to embed in a page or post (shortcode), and caching for performance.

== Description ==

Recommended Reading: Google Reader Shared gets the shared items from your Google Reader account.

Want to easily share posts you recommend from other blogs? Want to share selected posts from your business 
partners' news feeds or friends' blogs? The "Recommended Reading" plug in is the easy way to do it! All 
you need is a free Google Reader account.

It includes an easy to use configuration panel inside the WordPress settings menu. From this panel you 
can control every aspect of the plug-in, including:

   1. Your Google Reader ID... or look it up dynamically with your Google username and password!
   2. Number of posts to show.
   3. Format of post dates (or hide dates).
   4. Option to show the source blog with link.
   5. How many characters from the post's content or summary to show (including all, or none).
   6. Handling of links (new window, nofollow properties)
   7. Display of your notes.
   8. Advanced content output and styling when embedding on a page or post.
   9. A link back to your full shared items feed at Google

Use the sidebar widget, embed in a page or post with simple shortcode, or, for advanced users, call the list 
by a function in your template.

Precise CSS classes throughout the output (with common WordPress conventions) allows granular control over 
the look within templates. Powerful caching stores and compares the the feed's provided update information, 
giving you fast performance with a feed that's always up to date.

Version 4.0 is a major update that introduces dramatic changes and improvements "under the hood". Key 
components, including feed loading and parsing, were rewritten to take full advantage of the latest WordPress 
API and to improve performance. As a result of the re-writes, 4.0 introduces dramatic performance improvements 
across the board, as well as PHP4 and broader host compatibility. Best practices, including a plug-in 
uninstall script, contextual help, and a dramatic reduction in option table rows have all been introduced.
While there are no new "visible" features for your readers, significant fit and finish has been added to the 
plug-in configuration page. 

All told, 4.0 represents a "code maturity" milestone, that will facilitate the addition of new features in
the coming months. We will address any issues that result from the broader rollout of this major update 
quickly: please report them in the comments section on our website.


== Installation ==

1. Install easily with the WordPress plugin control panel or manually download the plugin and upload the folder 
`recommended-reading-google-reader-shared` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin by going to the "Rec. Reading" menu item under "Settings"
4. Widget users can add it to the sidebar by going to the "Widgets" menu under "Appearance" and adding the "Rec
Reading" widget
5. If you want to display the output on a page or post, just type `[recreading]` into the page or post content.
6. Template authors can output the list anywhere in by calling the function: `google_reader_shared()`; pass the
value "true" - `google_reader_shared(true)` - to replicate shortcode output.

== FAQ ==

= How do I set up a Google Reader account? =

Everything you need to know: http://reader.google.com

= How do I share posts? =

Click the "Share" button at the bottom of any post inside Google Reader.

= How do I make shortcode based posts only show items shared before I published my post? =

In the “Display on page / post (shortcode)” controls at the bottom of the Rec Reading settings page check off the 
first item, in bold, “As of publish date”, to show items published before you published your post!

= I want to regularly share my latest items with my readers. Can Recommended Reading automatically show all the
shared posts between the publication date of the current post and the last post to use the shortcode? =

Yes! First, check off the "As of publish date" option (discussed in the question). You can now check the next option, 
"up to last post" to do just that!

= Do I need to be at my computer to update my recommended reading? =

Nope! Since the plug in gets posts from the Google Reader shared feed, you can update your website's / blog's feed
from any Google Reader interface that supports shared items. Google has a free mobile version of Google Reader...
recommend items from your mobile phone! http://www.google.com/reader/m 

= Additional frequeuntly asked questions are covered in the plug-in help. Just click the help tab near the header
on the plug-in configuration page. =


== Screenshots ==

1. Sceenshot of output, using widget.
2. Screenshot of configuration panel.
3. Screenshot of shortcode output.

== Changelog ==

=v1.01=
Added "Quick Set" option to display settings
Vastly improved handling of ID validation
Fixed source post links

=v1.02=
Fixed description of "Trim Post Content"
Option for opening links in a new window
Improved output error checking for config saved with invalid feed ID
JavaScript and PHP code optimizations throughout

=v1.5=
Added caching based on feed update tag
Added control over display of shared item notes
Added control over widget title
More standard widget output
Added more help to plugin options
Added options to support plugin author

=v1.51=
Fixed path issue causing validation and lookup issues

=v1.52=
Improved path handling for validation and lookup
Added check for PHP 5

=v1.522=
Fixed mistake in PHP check

=v1.530=
Improved error handling and requirements check
Minor XML parsing changes attempting to address rare object error

=v1.540=
Smarter requirements checking
Modified code causing fatal error upon attempting activation in PHP4

=v1.541=
Fixed additional fatal error upon attempting activation in PHP4

=v2.0=
Easily embed in a page or post with shortcode; includes special settings!
Option to hide stand alone notes
Add rel="nofollow" to links
Control the preface text for item source and publish date
Improved built in help
Misc code improvements and clean up

=v2.0.1=
Updated options code for compatibility with future versions and Wordpress MU

=v3.0=
Ability to show items prior to post publication date when using shortcode
Major performance improvements across the board, from caching to ID verification
Elimination of "cURL" PHP module requirement; uses `file_get_contents` instead
Administrative interface clean up and polish, including subtle jQuery-powered animations
Quickly access Rec. Reading settings page from plug-in list
Better error handling
Validated compatibility with WordPress 2.8

=v3.1=
May now specify number of items in shortcode via 'items' attribute
New option to show all shared items since last use of shortcode
Improved date/time handling, including GMT offset

=v3.1.1=
Fixed ability to call in template by `google_reader_shared()` function 

=v3.1.2=
Display friendly / descriptive error message when host does not have fopen enabled

=v3.2=
Will now support hosts with either cURL or fopen enabled
cURL now used as primary method for retrieving feed based on performance testing
Improved validation of ID when using fopen method (PHP offset bug)

=v3.2.1=
When checking item date against post date, now uses date item shared, not published
Fixes to time zone handling; correctly determines precise date/time values
Workaround for broken conversion of publish date in earlier versions of PHP

=v3.3=
New option for a "read more" link back to your shared items page at Google
Set the "read more" link to the shared items Atom feed instead of the page
Plug-in cache automatically cleared when re-activating or auto-upgrading plugin

=v4.0=
Version 4.0 is a major update that introduces dramatic changes and improvements "under the hood". Key 
components, including feed loading and parsing, were rewritten to take full advantage of the latest WordPress 
API and to improve performance. As a result of the re-writes, 4.0 introduces dramatic performance improvements 
across the board, as well as PHP4 and broader host compatibility. While there are no new "visible" features 
for your readers, significant fit and finish has been added to the plug-in configuration page. Too many
changes under the hood to list!

=v4.0.1=
Fixes bug related to shortcode pages that don't use the "as of publish date" feature not loading cache
Change HTML filtering to use built in and more precise wp_kses filter instead of PHP strip_tags function

=v4.0.2=
Fixed issue with source preface not appearing.

=v4.0.3=
Fix long caching issues due to switch to SimplePie and WP default cache period

=v4.0.4=
More caching fixes

Future enhancements:
* Schedule a regular shortcode based post!
* Tag support
* Better support for multiple user notes
* Override settings via parameters when called by function or shortcode
* Button to insert shortcode