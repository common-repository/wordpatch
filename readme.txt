=== WordPatch ===
Tags: patch, modifications, updates, patches, changes, automatic, upgrades, git, scm, svn, version control, vcs
Requires at least: 4.7
Tested up to: 4.9.5
Requires PHP: 5.4.0
Stable tag: 1.1.7
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatically applies customizations to themes, plugins, and your codebase using patch files (supports git, unix, others). Works even on shared hosts.

== Description ==

= What is WordPatch? =
[WordPatch](https://www.wordpatch.com) is the only plugin that automatically manages and applies custom modifications during updates, without the need to manually override changes, saving WordPress developers valuable time and money.

= What problem does WordPatch solve? =
More than 75 million websites - approximately 25 percent of all sites worldwide - manage their content through WordPress, the most popular content management system (CMS). Yet, as popular and useful as WordPress is, developers who use it are routinely required to make custom modifications to WordPress themes and plugins due to additional requirements for their websites.

= How does WordPatch solve this problem? =
WordPatch allows for automatic updating of custom modifications whenever there are updates to the WordPress platform as well as updates to themes and plugins. WordPatch works by tapping into the WordPress update process and reapplying custom modifications automatically, providing a seamless update experience. In the unlikely event that this process fails, there are safeguards in place that will enable WordPatch to roll back this process, through a time-based rule for administrator intervention as well as an automatic scan of common resolvable services URLs. WordPatch has been designed with portability in mind, being fully functional even on restricted shared hosting environments.

= How does WordPatch work? =
WordPatch automatically applies custom modifications (patches) for any of the following reasons:

* WordPress is updated automatically.
* WordPress has upgraded one or more themes and/or plugins.
* The user has instructed WordPatch to apply one or more mods.

When WordPatch attempts to apply custom modifications, it will either succeed or fail to patch the appropriate files, and will then send the site administrator an email containing the following information:

* The mod or mods that WordPatch attempted to apply.
* The reason that WordPatch attempted to apply these mods.
* A list of the mods that have failed, along with as much information as possible for each failed update.
* Depending on the mode set by the user, either an “approve” link (when in Pessimistic Mode) or a “disapprove” link (in Optimistic Mode, WordPatch’s default setting) for each successful job.

WordPatch supports a variety of different patch files, including Unix patch files, Git patch files, and many others!

= Is WordPatch free? =
In order to support a wide variety of configurations and environments with advanced security features, WordPatch is available on a site-license basis, at scalable subscription pricing starting at $49/year. For a limited time, we're offering early adopters a **1 month free trial**. For more information visit [https://www.jointbyte.com/wordpatch](https://www.jointbyte.com/wordpatch).

= Where can I get help with WordPatch? =
We offer support to all WordPatch licensees, answer questions, and provide detailed information about WordPatch through our support portal at [https://jointbyte.freshdesk.com](https://jointbyte.freshdesk.com).

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/wordpatch` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Configure the plugin through the WordPatch panel, accessible from the admin panel sidebar.
4. Activate your WordPatch installation using the key you received during purchase.

== Changelog ==
= 1.1.7 =
* More robust base64 javascript API
* Fix hardcoded strings in configuration wizards

= 1.1.6 =
* Fix log detail link in breadcrumbs

= 1.1.5 =
* Jobs are now called Mods
* Various styling fixes

= 1.1.4 =
* Assets are now minified properly (JS, CSS, and PNG)
* Added version strings for static asset cache busting
* Empty job paths are now displayed properly
* Schema changes apply now apply without re-activating WordPatch
* Bug fix for the job completion email
* Minor styling fixes

= 1.1.3 =
* Introducing the Simple Patch Wizard!

= 1.1.2 =
* Fixing the JointByte mailer

= 1.1.1 =
* Minor styling fixes

= 1.1.0 =
* Styling overhaul!
* More actions are now available from the WordPatch dashboard.
* Job progress is now updated in real time (for supported browsers)
* Re-worked the navigation to and from the rescue panel
* Various other bug fixes and changes

= 1.0.3 =
* Added automatic redirection landing page when jobs are running.

= 1.0.0 =
* WordPatch is released!
