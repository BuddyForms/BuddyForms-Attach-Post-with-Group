=== BuddyForms Attach Post with Group ===
Contributors: svenl77, themekraft
Tags: buddypress, user, members, profiles, custom post types, taxonomy, frontend posting, frontend editing, groups, post attached to groups
Requires at least: 4.0
Tested up to: 4.2.2
Stable tag: 1.1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create engaged communities with every post.

== Description ==

This is the BuddyForms Attach Posts to Groups extension. You need the BuddyForms plugin installed for the plugin to work. <a href="http://themekraft.com/store/wordpress-front-end-editor-and-form-builder-buddyforms/" target="_blank">Get BuddyForms now!</a>

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

If you’re still having difficulties, get direct support from the developers to help get you back on the right track. Access the HELP buttons at anytime through the BuddyForms Settings Panel in your WP Dashboard or visit our Support Base: https://themekraft.zendesk.com/hc/en-us/categories/200022561-BuddyForms

== Installation ==

You can download and install BuddyForms Members using the built in WordPress plugin installer. If you download BuddyForms manually,
make sure it is uploaded to "/wp-content/plugins/buddyforms/".

Activate BuddyPress in the "Plugins" admin panel using the "Activate" link. If you're using WordPress Multisite, you can optionally activate BuddyForms network wide.

== Frequently Asked Questions ==

Which plugins do I need to install before?

You need the BuddyForms plugin and the BuddyPress plugin installed before.
<a href="http://themekraft.com/store/wordpress-front-end-editor-and-form-builder-buddyforms/" target="_blank">Get BuddyForms now!</a>
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