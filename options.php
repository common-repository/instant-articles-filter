<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

 $pageURL = 'http';
if(isset($_SERVER["HTTPS"]))
if ($_SERVER["HTTPS"] == "on") {
    $pageURL .= "s";
}
$pageURL .= "://";
if ($_SERVER["SERVER_PORT"] != "80") {
    $pageURL .= $_SERVER["HTTP_HOST"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
} else {
    $pageURL .= $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
}
$reloadURL = str_replace(array('&activateAll','&deactivateAll'), '', $pageURL);
        

if (isset($_GET['activateAll'])) {
    $posts = get_posts(array('numberposts' => -1) );
    foreach($posts as $p) {
		update_post_meta($p->ID, 'wp_fia_filter', '1');     
    }
    echo "<br>Done updating all posts. All posts will now show as Facebook Instant Articles.<br>";
    echo "<a href='$reloadURL' class='button button-huge button-primary'>OK</a>";
    return;
} else if (isset($_GET['deactivateAll'])) {
    $posts = get_posts(array('numberposts' => -1) );
    foreach($posts as $p) {
		update_post_meta($p->ID, 'wp_fia_filter', '');     
    }
    echo "<br>Done updating all posts. None of the current posts will show as Facebook Instant Articles.<br>";
    echo "<a href='$reloadURL' class='button button-huge button-primary'>OK</a>";
    return;
}
?>
<style type="text/css">
	form table.form-table tr td{
		vertical-align: top;
	}
	textarea {
		clear:both;
		width:500px;
		height:150px;
	}
</style>
<div class="wrap">
	<h2>Instant Articles Filter</h2>
	<span class='description'>Plugin provided by <a href='http://www.thepennyhoarder.com/?source=iaf'>The Penny Hoarder</a>.</span>
	<form method="post" action="options.php"> 
		<?php 
			settings_fields( 'tph_fia_filter_settings' ); 
			do_settings_sections( 'tph_fia_filter_settings' ); 
			$settings = get_option('tph_fia_filter_settings');
			// print_r($settings);
		?>
		<table class="form-table">
			<tr>
				<td>Use Checkbox:</td>
				<td>
					<input type='checkbox' value='1' name='tph_fia_filter_settings[checkbox_filter]' <?php echo checked(@$settings['checkbox_filter'], 1); ?> /> Filter by checkbox on posts?<br>
					<input type='checkbox' value='1' name='tph_fia_filter_settings[checked_by_default]' <?php echo checked(@$settings['checked_by_default'], 1); ?> /> Should the checkbox be checked by default?<br>
					<a href='http://<?php echo $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] ?>&activateAll' class='activateAll'>Activate ALL posts now?</a><br>
					<a href='http://<?php echo $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] ?>&deactivateAll' class='deactivateAll'>Deactivate ALL posts now?</a>
				</td>
			</tr>

			<tr>
				<td>Remove Category:</td>
				<td>
					<input type='checkbox' value='1' name='tph_fia_filter_settings[remove_category]' <?php echo checked(@$settings['remove_category'], 1); ?> /> Remove category from displaying on Instant Articles?<br>
				</td>
			</tr>

			<tr>
				<td>Post Types:</td>
				<td>
					Which post types should be allowed to be FIA?<br>
					<span class='description'>If nothing is selected below, it will default to showing only posts.</span><br><br>
					<?php
						foreach ( get_post_types( '', 'names' ) as $post_type ) {
							if (in_array($post_type, array('attachment','revision','nav_menu_item','nf_sub','surl',))) {
								continue;
							}
							?>
							<input type='checkbox' value='1' name='tph_fia_filter_settings[post_type][<?php echo $post_type; ?>]' <?php echo checked(@$settings['post_type'][$post_type], 1, 0); ?> /> <?php echo ucfirst($post_type); ?><br>
							<?php
						}
					?>
				</td>
			</tr>

			<tr>
				<td>Tags or Keywords:</td>
				<td>
					You may use any combination of tags and categories below.<br><b>NOTE:</b> If you put any text in the boxes below, the checkbox feature above will be ignored and inactive.<br><br>
					Allowed Tags<br>
					<textarea class='tagInput' name='tph_fia_filter_settings[allowed_tag]'><?php echo esc_textarea($settings['allowed_tag']); ?></textarea><br>
					Denied Tags<br>
					<textarea class='tagInput' name='tph_fia_filter_settings[denied_tag]'><?php echo esc_textarea($settings['denied_tag']); ?></textarea><br><br>

					Allowed Categories<br>
					<textarea class='catInput' name='tph_fia_filter_settings[allowed_cat]' ><?php echo esc_textarea($settings['allowed_cat']); ?></textarea><br>
					Denied Categories<br>
					<textarea class='catInput' name='tph_fia_filter_settings[denied_cat]' ><?php echo esc_textarea($settings['denied_cat']); ?></textarea><br>
				</td>
			</tr>
		</table>
		<?php submit_button(); ?>
	</form>
			
	<script type="text/javascript">
		jQuery(function ($) {
			$(document).ready(function() {
				$('.tagInput').suggest("<?php echo admin_url( 'admin-ajax.php' ); ?>?action=ajax-tag-search&tax=post_tag", {multiple:true, multipleSep: ","});
				$('.catInput').suggest("<?php echo admin_url( 'admin-ajax.php' ); ?>?action=ajax-tag-search&tax=category", {multiple:true, multipleSep: ","});
			});
		});
	</script>
</div>