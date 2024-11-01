<?php
/*
Plugin Name: WOP Instagram Feed
Description: show to instagram feeds by user id .
Plugin URI: https://wordpress.org/plugins/wop-instagrm-feed/
Author: Wordpress Outsourcing Partners
Author URI: https://wordpress-outsourcing-partners.com/
Version: 1.0.1
License: GPL2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

defined( 'ABSPATH' ) or die( 'Ah ah ah, you didn\'t say the magic word' );

/* add wop instram setting page menu */
add_action( 'admin_menu', 'wig_menu' );
function wig_menu() {
	add_menu_page( __( 'WOP Instagram Settings', 'textdomain' ),'WOP Instagram','manage_options','options-general.php?page=wig_options','wig_options','dashicons-welcome-widgets-menus',90);
}

function wig_styles() {
	wp_enqueue_style( 'wigstyles', WP_PLUGIN_URL. '/wop-instagram-feed/css/wigstyle.css');
}
add_action('init', 'wig_styles');

/* add wop instram setting page */
function wig_options() {

	/* Create options for wop instram setting */
	add_option('wig_uid');
	add_option('wig_utoken');
	add_option('wig_feed_count');
	add_option('wig_img_size');

	$hidden_field_name = 'wig_submit_hidden';
    $wig_uid = 'wig_uid';
	$wig_utoken = 'wig_utoken';
    $wig_feed_count = 'wig_feed_count';
	$wig_img_size = 'wig_img_size';
   
    $wig_uid_val = get_option($wig_uid);
	$wig_utoken_val = get_option($wig_utoken);
	$wig_feed_count_val = get_option($wig_feed_count);
	$wig_img_size_val = get_option($wig_img_size);
?>
<?php


/* check wop instram setting page is updated */
if(isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y') {
       if ( empty( $_REQUEST['wig_submit_hidden'] ) || empty( $_REQUEST['wig-nonce'] ) ) { 
		 return;
	   }

	    $wig_uid_val = sanitize_text_field($_POST[$wig_uid]);
		$wig_utoken_val = sanitize_text_field($_POST[$wig_utoken]);
		$wig_feed_count_val = sanitize_text_field($_POST[$wig_feed_count]);
		$wig_img_size_val = sanitize_text_field($_POST[$wig_img_size]);
        update_option( $wig_uid, $wig_uid_val );
		update_option( $wig_utoken, $wig_utoken_val );
		update_option( $wig_feed_count, $wig_feed_count_val );
		update_option( $wig_img_size, $wig_img_size_val );
      ?>
      <div class="card pressthis" style="max-width: 96%;">
      	<div class="updated"><p><strong><?php _e('Settings saved.', 'menu-brs' ); ?></strong></p></div>
      </div>
    <?php
    }
?>

<div class="card pressthis" style="max-width: 96%;">
<h1>WOP Instagram Settings</h1>
<form name="form1" method="post" action="">
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
<table class="widefat importers striped">
  <tbody>
    <tr>
      <td class="import-system row-title"><?php _e("User ID:", 'menu-brs' ); ?></td>
      <td class="desc"><input type="text" name="<?php echo $wig_uid; ?>" value="<?php echo $wig_uid_val; ?>" style="min-width: 500px;"></td>
    </tr>
    
    <tr>
      <td class="import-system row-title"><?php _e("User Access Token:", 'menu-brs' ); ?></td>
      <td class="desc"><input type="text" name="<?php echo $wig_utoken; ?>" value="<?php echo $wig_utoken_val; ?>" style="min-width: 500px;"></td>
    </tr>
    
    <tr>
      <td class="import-system row-title"><?php _e("Feed Count:", 'menu-brs' ); ?></td>
      <td class="desc"><input type="number" name="<?php echo $wig_feed_count; ?>" value="<?php echo $wig_feed_count_val; ?>" max="20" style="min-width: 500px;"></td>
    </tr>
    
    <tr>
      <td class="import-system row-title"><?php _e("Image Size:", 'menu-brs' ); ?></td>
      <td class="desc"> 
        <select name="<?php echo $wig_img_size; ?>" style="min-width: 500px;">
            <option <?php if($wig_img_size_val == 'thumbnail') { echo 'selected'; } ?> value="thumbnail">Thumbnail</option>
            <option <?php if($wig_img_size_val == 'low_resolution') { echo 'selected'; } ?> value="low_resolution">Medium</option>
            <option <?php if($wig_img_size_val == 'standard_resolution') { echo 'selected'; } ?> value="standard_resolution">Large</option>
        </select>
      </td>
    </tr>
  </tbody>
</table>
<p class="submit">
<?php wp_nonce_field('wig_submit_hidden', 'wig-nonce' );   ?>

<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
</p>
</form>
</div>
<?php
}

add_shortcode('wop_instagram_feed', 'wop_instagram_feed');
function wop_instagram_feed() {
    global $post;
	$wig_user_id = get_option('wig_uid');
	$wig_user_token = get_option('wig_utoken');
	$wig_image_size = get_option('wig_img_size');
	$wig_feed_count = get_option('wig_feed_count')-1;
	
	/* Get recent instagram feed  */
	$remote_wp = wp_remote_get("https://api.instagram.com/v1/users/{$wig_user_id}/media/recent/?access_token={$wig_user_token}");
	$instagram_response = json_decode( $remote_wp['body'] );
	 
	if( $remote_wp['response']['code'] == 200 ) {
	 
		echo '<div class="wiginstagram"><ul>'; 
		  for ($feed = 0; $feed <= $wig_feed_count; $feed++) {
			?>
			<li><a target="_blank" href="<?php echo $instagram_response->data[$feed]->link; ?>"><img alt="" src="<?php echo $instagram_response->data[$feed]->images->$wig_image_size->url; ?>"></a></li>
			<?php  
		  }
		 echo '</ul></div>';
	} elseif ( $remote_wp['response']['code'] == 400 ) {
		echo '<b>' . $remote_wp['response']['message'] . ': </b>' . $instagram_response->meta->error_message;
	}
	 
}
?>