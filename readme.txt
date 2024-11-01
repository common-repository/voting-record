=== Voting Record ===
Contributors: davidjmillerorg
Tags: politics, widget,
Requires at least: 2.5
Tested up to: 2.8.6
Stable tag: trunk

Elected officials or citizens can record and display their votes or the votes of one or more elected officials they follow.

== Description ==

Voting Record will allow recording of votes cast for display and reference purposes. Elected officials can record and display their votes and citizens can track and display the votes of an elected official they follow.

Votes are entered on a dashboard widget. A vote management page is available from the posts menu. Recent votes are shown by adding `<?php recent_votes(); ?>` in your theme templates or `[RECENT-VOTES]` within a text widget. You can show a search votes form by using the shortcode `[SEARCH-VOTES]` on a page or post. Options for Voting Record include:

Options Include:
<ul>
<li>The option to specify a primary voter (if most or all of the votes being tracked are from one person).</li>
<li>How many recent votes to show - limited by number of votes or number of days. This includes a "Days plus" option which displays the specified number of days plus any extra votes to reach a minimum list size specified by the user (defaults to 5).</li>
<li>Output for the recent votes and the search results are specified by templates. Each template contains a variable to specify what should precede the list, a variable to specify what should follow the list, a variable to show what should display if there are no results, and a template of how the list items should be formatted. List items allow the use of the following template tags - {bill} to display the name of the bill, {vote} to display the vote cast, {voter} displays the name of the voter, {date} displays the date the vote was cast, {desc} displays the description, {result} displays the overall pass/fail outcome of the vote, {tally} displays the overall vote tally. In addition to those standard tags, the header and footer for the search results can use the {count} tamplate tag to display a count of the results from the current search.</li>
</ul>

== Installation ==

To install it simply unzip the file linked above and save it in your plugins directory under wp-content. In the plugin manager activate the plugin. Settings for the plugin may be altered under the Voting Record page of the Options menu (version 2.3) or Settings menu (version 2.5 or later).

== Frequently Asked Questions ==

= What gets displayed when no results are returned? =

When no results are returned the "Text and Code if no Recent Votes" or else the "Text and Code if no Search Results" is displayed - the codes that precede or follow the list are not displayed if there are no results.

== Screenshots ==

1. This is the dashboard widget where votes are entered

2. This is the vote management link on the Posts menu

3. This is the options page for Voting Record

== Changelog ==

= 2.0 =
* Shortcodes for multi-post pages now point to the new multi-page functions introduced in version 2.9 for use in sidebars where you want a similarity list for the first post on a multi-post page.

= 1.7 =
* Improved the handling of the variable for the header on the recent votes list.

= 1.6 =
* Fixed a bug in the new “Days plus” display option for recent votes.

= 1.5 =
* Added the “Days plus” display option for recent votes which allows you to set a minimum number of recent votes to display if recent days are scarce on votes (defaults to 5 votes if no valid default is set).
* Also fixed some bugs in the vote management page.

= 1.4 =
* Fixed a bug in the display of recent votes.

= 1.3 =
* Those using widgets in their sidebars can use [ RECENT-VOTES ] within a text widget to display recent votes.

= 1.2 =
* Fixed a bug in the vote searching function.
* I also added a class to the vote search form to allow for styling.

= 1.1 =
* Fixed a bug in the processing of the Recent Votes Leader variable.

= 1.0 =
* First general release.
* This version finally adds the ability for visitors to search votes.