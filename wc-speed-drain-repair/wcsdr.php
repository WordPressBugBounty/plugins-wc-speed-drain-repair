<?php
/*
Plugin Name: WooCommerce Speed Drain Repair - WP Fix It
Version: 2.0
Plugin URI: https://www.wpfixit.com
Description: WooCommerce can really drain server resources and slow down the load of your site. This plugin stops loading the items you do not need and speeds up WordPress core admin-ajax.php file. There are no settings for this plugin and will do what is needs to as soon as it is activated.
Author: WP Fix It
Author URI: https://www.wpfixit.com
*/
add_action( 'wp_enqueue_scripts', 'repair_woocommerce_speed_styles', 99 );
  
function repair_woocommerce_speed_styles() {
    //remove generator meta tag
    remove_action( 'wp_head', array( $GLOBALS['woocommerce'], 'generator' ) );
  
    //first check that woo exists to prevent fatal errors
    if ( function_exists( 'is_woocommerce' ) ) {
        //dequeue scripts and styles
        if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() ) {
            wp_dequeue_style( 'woocommerce_frontend_styles' );
            wp_dequeue_style( 'woocommerce_fancybox_styles' );
            wp_dequeue_style( 'woocommerce_chosen_styles' );
            wp_dequeue_style( 'woocommerce_prettyPhoto_css' );
            wp_dequeue_script( 'wc_price_slider' );
            wp_dequeue_script( 'wc-single-product' );
            wp_dequeue_script( 'wc-add-to-cart' );
            wp_dequeue_script( 'wc-cart-fragments' );
            wp_dequeue_script( 'wc-checkout' );
            wp_dequeue_script( 'wc-add-to-cart-variation' );
            wp_dequeue_script( 'wc-single-product' );
            wp_dequeue_script( 'wc-cart' );
            wp_dequeue_script( 'wc-chosen' );
            wp_dequeue_script( 'woocommerce' );
            wp_dequeue_script( 'prettyPhoto' );
            wp_dequeue_script( 'prettyPhoto-init' );
            wp_dequeue_script( 'jquery-blockui' );
            wp_dequeue_script( 'jquery-placeholder' );
            wp_dequeue_script( 'fancybox' );
            wp_dequeue_script( 'jqueryui' );
        }
    }
 }
 
 /* Activate the plugin and do something. */
function repair_woocommerce_speed_plugin_action_links( $links ) {
    echo '<style>span#p-icon{width:23px!important}span#p-icon:before{width:32px!important;font-size:23px!important;color:#3B657D!important;background:0 0!important;box-shadow:none!important}</style>';
$links = array_merge( array(
'<a href="https://www.wpfixit.com/wordpress-speed-optimization-service/" target="_blank">' . __( '<b><span id="p-icon" class="dashicons dashicons-performance"></span> <span style="color:#f99568">GET SPEED</span></b>', 'textdomain' ) . '</a>'
), $links );
return $links;
}
add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'repair_woocommerce_speed_plugin_action_links' );
/* Activate the plugin and do something. */
register_activation_hook( __FILE__, 'repair_woocommerce_speed_welcome_message' );
function repair_woocommerce_speed_welcome_message() {
set_transient( 'repair_woocommerce_speed_welcome_message_notice', true, 5 );
}
add_action( 'admin_notices', 'repair_woocommerce_speed_welcome_message_notice' );
function repair_woocommerce_speed_welcome_message_notice(){
/* Check transient, if available display notice */
if( get_transient( 'repair_woocommerce_speed_welcome_message_notice' ) ){
?>
<div class="updated notice is-dismissible">
	<style>div#message {display: none}</style>
<p>&#127881; <strong>WP Fix It - WooCommerce Speed Drain Repair</strong> has been activated and is already making your website faster.
</div>
<?php
/* Delete transient, only display this notice once. */
delete_transient( 'repair_woocommerce_speed_welcome_message_notice' );
}
}

// Check if WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	function repair_woocommerce_speed_needed_notice() {
		$message = sprintf(
		/* translators: Placeholders: %1$s and %2$s are <strong> tags. %3$s and %4$s are <a> tags */
			esc_html__( '%1$sWooCommerce Speed Drain Repair %2$s requires WooCommerce to function. Please %3$sinstall WooCommerce%4$s.', 'repair_woocommerce_speed' ),
			'<strong>',
			'</strong>',
			'<a href="' . admin_url( 'plugins.php' ) . '">',
			'&nbsp;&raquo;</a>'
		);
		echo sprintf( '<div class="error"><p>%s</p></div>', $message );
	}
	add_action( 'admin_notices', 'repair_woocommerce_speed_needed_notice' );
	return;
}