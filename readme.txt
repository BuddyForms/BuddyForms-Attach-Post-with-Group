=== BuddyForms Attach Post with Group ===
Contributors: svenl77, konradS, buddyforms, gfirem
Tags: buddypress, user, members, profiles, custom post types, taxonomy, frontend posting, frontend editing, groups, post attached to groups
Plugin URI: http://buddyforms.com/downloads/attach-post-with-group/
Requires at least: 3.9
Tested up to: 5.9.3
Stable tag: 1.2.10
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create engaged communities with every post.

== Description ==

With this plugin, you’ll automatically be able to create a new BuddyPress group for pre-assigned BuddyForms post submissions and attach that group to the post. Then User-submitted posts with BuddyForms become a BuddyPress group with all the included functionality and endless possibilities.

<h4>Now “why would you want to create groups for every post”, you ask? </h4>Here’s some quick examples of why it’s the perfect plugin to help your community and business grow:

1) You have a product-based website and use two post types: a "Product" post type and a “Brand" post type. Every product can then easily be categorized as a product of a brand. Every Product and Brand can be a group, so you can have forums or contact persons etc - available product specific or centralised in the brand.

2) You have a listing website that users directly add events to. Wouldn't it be great if a group would be automatically created whenever a user creates a new event? The event suddenly has access to members, forums & everything else - possible within BuddyPress groups!

<h4>How It Works?</h4>
Create and attach new BuddyPress groups to your posts with BuddyForms. Whenever a new post is created, a group is also created (of course you get to choose, which post types create groups in BuddyForms). The permalink will be rewritten to the group and a redirect will publish the posts to the attached group.

<h4>Great Features </h4>
Here’s the list of powerful features that will help manage your BuddyPress groups:

<ul>
<li>* Categorize your groups with all the power WordPress already provides.</li>
<li>* Get the combined benefits of WordPress posts and taxonomies with the added power of BuddyPress groups.</li>
<li>* Filter and sort groups in a new designed and intuitive way.</li>
<li>* Organize your groups within categories and custom post types.</li>
<li>* Use ANY 3rd party post-type based plugins and power them up with BuddyPress Groups.</li>
<li>* Attach Group Type to attach groups to other groups. Create complex relationships between your groups for every form which is attached to groups.</li>
<li>* 3 widgets to display your groups relations in your group sidebar</li>
<li>* Edit the post from the Group Admin</li>
</ul>

<h4>Display Post in the Group</h4>
<ul>
<li>nothing - <i>great if you want to use the home tab</i></li>
<li>create a new tab - <i>Will add anew Tab to your Group Tabs</i></li>
<li>before group activity - <i>Hook your Post content before the Group Activity</i></li>
</ul>

<h4>Overwrite Templates</h4>
If you want to add the post to the home tab, you need to copy the single-post.php from 'includes/templates/buddyforms/groups/single-post.php' to your theme and rename it to front.php 'groups/single/front.php'.
If you want to change the new tab template, copy single-post.php to your theme 'buddyforms/groups/single-post.php

<h4>User Rights Management</h4>
In BuddyForms its already possible to manage Roles an Capability's.
With the Attached Posts to Groups extension all Admins of the group will have the rights to edit the post too.

<h4>DEMO</h4>
See the Plugin in action on our Front and Back-end (Admin) Demo

<h4>Documentation & Support</h4>

<h4>Extensive Documentation and Amazing Support. </h4>
All code is neat, clean and well documented (inline & in the documentation).
The BuddyForms Documentation with tons of resourceful how-to’s and information is now available!

If you’re still having difficulties, get direct support from the developers to help get you back on the right track. Access the HELP buttons at anytime through the BuddyForms Settings Panel in your WP Dashboard or visit our Support Base: http://buddyforms.com

== Installation ==

You can download and install BuddyForms Members using the built in WordPress plugin installer. If you download BuddyForms manually,
make sure it is uploaded to "/wp-content/plugins/buddyforms/".

Activate BuddyPress in the "Plugins" admin panel using the "Activate" link. If you're using WordPress Multisite, you can optionally activate BuddyForms network wide.

== Frequently Asked Questions ==

Which plugins do I need to install before?

You need the BuddyForms plugin and the BuddyPress plugin installed before.
<a href="http://buddyforms.com" target="_blank">Get BuddyForms now!</a>
Get BuddyPress here: http://buddypress.org

When is it the right choice for you?

As soon as you plan a WordPress and BuddyPress powered site where users should be able to submit content from the front-end.
BuddyForms gives you these possibilities for a wide variety of uses.

== Screenshots ==

1. **Attached Groups Options in The FormBuilder** - Define the Attached Groups Options for each form separately.

2. **Attache Groups Taxonomy Form Element** - A Form Element to select a Group you want to create a relationship for.

3. **Widget to display a Group Relation** - Display a group in the Sidebar which is in relationship with this Post. for example Products and Brands.

4. **Widget to display other Attached Posts** - This can be used to see Other Posts attached to a group. For example Products of a Brand.

5. **Overview FormBuilder** - This is a Screenshot of the Form builder with BuddyForms Attached Groups enabled.


== Changelog ==
= 1.2.10 - 17 May 2022 =
* Fixed issue with duplicate hook.
* Updated readme.txt

= 1.2.9 - 23 Feb 2022 =
* Added two new hooks to customize the cropping values ​​on the group avatar image.
* Fixed issue related to frontend shortcode malfunction.
* Tested up to WordPress 5.9

= 1.2.8 - 29 Sep 2021 =
* Tested up to WordPress 5.8.

= 1.2.7 - 23 May 2021 =
* Improved UI of the attached post's view tab.
* Tested up to WordPress 5.7.

= 1.2.6 - 29 Jan 2021 =
* Fixed issue with BF ACF fields on the edit screen of the group post.
* Fix issue with the list items on the Post Group tab. The items aren't marked as current when interacting with them.
* Fix issue related to endless loop on the process deletion of group or post.
* Fixed Rance Condition on class BuddyForms_GroupControl loading.

= 1.2.5 - 19 Nov 2020 = 
* Fixed some typos in the code.
* Fixed the feature to render post metadata on the Group Front-end.
* Added a fallback when Singular Name isn't set.

= 1.2.4 - 10 Dec 2019 =
* Improved compatibility with the last version of BuddyForms.
* Removed deprecate use of the function `create_function`.

= 1.2.3 -  Mar. 02 2019 =
Remove sdk and load from core

= 1.2.2 =
Freemius Integration
Smaller changes

= 1.2.1 =
Fixed an issue with the dependencies management. If pro was activated it still ask for the free version. Fixed now with a new default BUDDYFORMS_PRO_VERSION in the core to check if the pro is active.

= 1.2 =
Add dependencies management with tgm
Use the slug not the name in the query
Add 3 new options to the attached group widget to show hide title avatar and excerpt


= 1.1.9 =
Huge update some parts got a complete rewrite
Hooks rename session
Only show form type related form elements
Create new functions to show hide metaboxes
Work on the conditionals admin ui
Add postbox_classes to make the postbox visible.
Rename session
Support for the form builder select box added
Start rewrite the widgets
Add dependencies check
New function bf_apwg_generate_attached_tax to update the taxonomy only if needed
Rename quite a lot to make it better understandable
Change the taxonomy name to have it more logical
Fixed a couple of smaller issues
Fixed attached form category was not saved correctly

= 1.1.8 =
Clean up the code
Add one small check if $buddyforms is an array to avoid a notice in the foreach

= 1.1.6 =
Smaller changes

= 1.1.5 =
Make it work with the latest version of buddyforms. the buddyforms array has changed so I adjust the code too the new structure

= 1.1.4 =
Enable avatar management and group delete.
Thanks to imath for the super great patch https://buddypress.trac.wordpress.org/attachment/ticket/5202/5202.02.patch
We use his function with our bf_ prefix to create group avatars from featured image until it gets in core ;)
Increase the theme_redirect priority to 999. For some strange reason from the last WordPress update people report issues and this fix them all.

= 1.1.3 =
add check DOING_AJAX
change the url to buddyforms.com
new language files for Spanish
restructure the logic of the plugin.
create a new nav bar for the post view and edit
move the edit out of the admin section and use the admin section for options
create a new option to select the group user role needed to edit the post
rework the templates
clean up the code

= 1.1.3 =
Rename session. Make all more logic and self explaining thanks to user feedback.
Rebuild the ui and widgets

= 1.1.2 =
add a new function buddyforms_groups_group_settings_edited to sync the post status
add new filter bf_attached_group_status and bf_attached_group_save

= 1.1.1 =
Add a check if the post_excerpt exists in the group creation and use the post_excerpt for the group content if possible.
Made more strings translatable. Merge pull request #1 from RugWarrior/master
Added PO/MO files.
spelling correction
Rename the plugin from "BuddyForms Attach Posts to Groups Extension" to "BuddyForms Attach Post with Group"

= 1.1 =
give group admins the rights to change the post
rewrite the post integration
create a new template single-post.php and make it hook able
works with revision now
changed the how the form gets saved if used in the group
remove groups-home this is done different in buddypress from version 1.7 and its time to remove this dependencies
remove delete group and avatar tabs from group admin
add 3 new widgets
edit attached post in the group
add support for the latest BuddyPress 2.2
changed plugin uri

= 1.0.5 =
check if function exists to avoid breaking code if the buddypress groups component is not activated.
changed the hook to $form_args
rename function to more specific function names

= 1.0.4 =
Added to new Shortcodes and Template Tags to link to the Attached Group/Post
css bug fixes

= 1.0.2 =
Add a new option to redirect a group to the attached post
Fixed a url rewrite issue

= 1.0.1 =
* clean up code
* remove old hooks
* add 'show_option_none' => 'Nothing Selected'

= 1.0 =
final 1.0 version
