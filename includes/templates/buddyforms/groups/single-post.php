<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php do_action('buddyforms_before_groups_single_title') ?> 
	<div class="entry">
		<?php do_action('buddyforms_groups_single_title') ?> 
	</div>      
	<?php do_action('buddyforms_before_groups_single_content') ?> 
	<div class="entry">
	    <?php do_action('buddyforms_groups_single_content') ?> 
	</div>
	<?php do_action('buddyforms_after_groups_single_content') ?> 
</div>
