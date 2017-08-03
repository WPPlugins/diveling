<?php
/*
Plugin Name: Diveling annotation layer
Plugin URI: http://diveling.com
Description: This plugin is an user engagement engine. It creates a JavaScript layer on top of your site pages which allows your users to create annotations, share them and use highlights on the page to get search results. To install this plugin: 1. Click on activate under the plugin name. 2. Go to the link on settings on the left bar on the WP dashboard and click on: Diveling plugin. 3. Once you have installed the code, do not forget to click on the verification link that we sent to your email. When your site is verified the Diveling icon will appear when your pages are loaded. If there is a problem please contact us at support@diveling.com.
Version: 1.1.2
Author: Diveling
Author URI: http://diveling.com/
License: This software is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/
if ( !class_exists( 'InsertHeadersAndFooters' ) ) {
define('IHAFURL', plugins_url('', __FILE__));
if (is_admin()) {
wp_register_style('IHAFStyleSheet', IHAFURL . '/ihaf.css');
wp_enqueue_style( 'IHAFStyleSheet');
}
class InsertHeadersAndFooters {
function InsertHeadersAndFooters() {
add_action( 'init', array( &$this, 'init' ) );
add_action( 'admin_init', array( &$this, 'admin_init' ) );
add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
add_action( 'wp_head', array( &$this, 'wp_head' ) );
add_action( 'wp_footer', array( &$this, 'wp_footer' ) );
}
function init() {
load_plugin_textdomain( 'insert-headers-and-footers', false, dirname( plugin_basename ( __FILE__ ) ).'/lang' );
}
function admin_init() {
register_setting( 'insert-headers-and-footers', 'ihaf_insert_header', 'trim' );
register_setting( 'insert-headers-and-footers', 'ihaf_insert_footer', 'trim' );
}
function admin_menu() {
add_submenu_page( 'options-general.php', 'Diveling Plugin', 'Diveling Plugin', 'manage_options', __FILE__, array( &$this, 'options_panel' ) );
}
function wp_head() {
$meta = get_option( 'ihaf_insert_header', '' );
if ( $meta != '' ) {
echo $meta, "\n";
}
}
function wp_footer() {
if ( !is_admin() && !is_feed() && !is_robots() && !is_trackback() ) {
$text = get_option( 'ihaf_insert_footer', '' );
$text = convert_smilies( $text );
$text = do_shortcode( $text );
if ( $text != '' ) {
echo $text, "\n";
}
}
}
function fetch_rss_items( $num, $feed ) {
include_once( ABSPATH . WPINC . '/feed.php' );
$rss = fetch_feed( $feed );
// Bail if feed doesn't work
if ( !$rss || is_wp_error( $rss ) )
return false;
$rss_items = $rss->get_items( 0, $rss->get_item_quantity( $num ) );
// If the feed was erroneous
if ( !$rss_items ) {
$md5 = md5( $feed );
delete_transient( 'feed_' . $md5 );
delete_transient( 'feed_mod_' . $md5 );
$rss = fetch_feed( $feed );
$rss_items = $rss->get_items( 0, $rss->get_item_quantity( $num ) );
}
return $rss_items;
}
function options_panel() { ?>
<div id="ihaf-wrap">
  <div class="wrap">
    <?php screen_icon(); ?>
    <h2>Diveling Annoatation Plugin</h2>
      <div class="ihaf-wrap">
        <form id="ihaf-form" name="dofollow" action="options.php" method="post">
          <?php settings_fields( 'insert-headers-and-footers' ); ?>
          <a id="ihaf-register" href="http://admin.diveling.com/publisher/" target="_blank">Publisher Registration</a>
          <label class="ihaf-labels" for="ihaf_insert_header">Scripts in header:</label>
          <textarea rows="5" cols="57" id="insert_header" name="ihaf_insert_header">
          <?php echo esc_html( get_option( 'ihaf_insert_header' ) ); ?></textarea><br />
          These scripts will be inserted in <code>&lt;head&gt;</code> section when you select 'Inser Code.'
          <label class="ihaf-labels footerlabel" for="ihaf_insert_footer">Scripts in footer:</label>
          <textarea rows="5" cols="57" id="ihaf_insert_footer" name="ihaf_insert_footer">
          <?php echo esc_html( get_option( 'ihaf_insert_footer' ) ); ?></textarea><br />
          <p class="submit">
          <input type="submit" name="Submit" value="Insert Code" />
          </p>
        </form>
      </div>
      <div class="ihaf-fieldset">
          <h2>Instructions</h2>
          <h4>1. Please click on the "Publisher Registration" button or  visit http://diveling.com/publisher/ to register your website.</h4>
          <h4>2.Paste the javascript code that you get in the registration process in this text box.</h4>
          <h4>3.Press Insert Code.</h4>
          <h4>4.Press on the confirm link we sent to your email.</h4>
      </div>
    </div>
  </div>
<?php
}
}
add_action('wp_dashboard_setup', 'ihaf_dashboard_widgets');
function ihaf_dashboard_widgets() {
global $wp_meta_boxes;
wp_add_dashboard_widget('wpbeginnerihafwidget', 'Latest from WPBeginner', 'ihaf_widget');
}
function ihaf_widget() {
require_once(ABSPATH.WPINC.'/rss.php');
if ( $rss = fetch_rss( 'http://wpbeginner.com/feed/' ) ) { ?>
<div class="rss-widget">
<a href="http://www.wpbeginner.com/" title="WPBeginner - Beginner's guide to WordPress">
<img src="http://cdn.wpbeginner.com/pluginimages/wpbeginner.gif" class="alignright" alt="WPBeginner"/></a>
<ul>
<?php
$rss->items = array_slice( $rss->items, 0, 5 );
foreach ( (array) $rss->items as $item ) {
echo '<li>';
echo '<a class="rsswidget" href="'.clean_url( $item['link'], $protocolls=null, 'display' ).'">'. ($item['title']) .'</a> ';
echo '<span class="rss-date">'. date('F j, Y', strtotime($item['pubdate'])) .'</span>';
echo '</li>';
}
?>
</ul>
<div style="border-top: 1px solid #ddd; padding-top: 10px; text-align:center;">
<a href="http://feeds2.feedburner.com/wpbeginner">
<img src="http://cdn.wpbeginner.com/pluginimages/feed.png" alt="Subscribe to our Blog"
style="margin: 0 5px 0 0; vertical-align: top; line-height: 18px;"/> Subscribe with RSS</a>
&nbsp; &nbsp; &nbsp;
<a href="http://wpbeginner.us1.list-manage.com/subscribe?u=549b83cc29ff23c36e5796c38&id=4c340fd3aa">
<img src="http://cdn.wpbeginner.com/pluginimages/email.gif" alt="Subscribe via Email"/> Subscribe by email</a>
&nbsp; &nbsp; &nbsp;
<a href="http://facebook.com/wpbeginner/">
<img src="http://cdn.wpbeginner.com/pluginimages/facebook.png" alt="Join us on Facebook"
style="margin: 0 5px 0 0; vertical-align: middle; line-height: 18px;" />Join us on Facebook</a>
</div>
</div>
<?php }
}
$wp_insert_headers_and_footers = new InsertHeadersAndFooters();
}
