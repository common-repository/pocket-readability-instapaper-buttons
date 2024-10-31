<?php

/*
Plugin Name: Pocket, Readability, Instapaper Buttons for Wordpress
Plugin URI: http://seeyoucloud.com
Description: Add automatically read later buttons on your blog and offer the ability to tens of millions users who read blog posts with apps like Pocket, Instapaper or Readability, to quickly save your great in-depth articles on one click.
Version: 1.1
Author: Pierre-André Dewitte
Author URI: http://seeyoucloud.com
*/

define( 'PADPW_URL', plugins_url('/', __FILE__) );
define( 'PADPW_DIR', dirname(__FILE__) );
define( 'PADPW_VERSION', '1.0' );
define( 'PADPW_OPTION', 'padpw_ext' );
define( 'PADPW_PLUGNAME' , 'pocket-readability-instapaper-buttons');
define( 'PADPW_SETTINGS', 'pocket-readability-instapaper-buttons-settings');

require_once( PADPW_DIR . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'class.client.php'  );
require_once( PADPW_DIR . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'functions.plugin.php'  );
require_once( PADPW_DIR . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'functions.tpl.php'  );

// Activation, uninstall
register_activation_hook( __FILE__, 'ReadLaterForWordpress_Install' );
register_deactivation_hook ( __FILE__, 'ReadLaterForWordpress_Uninstall' );

function ReadLaterForWordpress_Init() {
global $myExt;

// Load translations
load_plugin_textdomain ( 'pocket-readability-instapaper-buttons', false, basename(rtrim(dirname(__FILE__), '/')) . '/languages' );

// Load client
$myExt['client'] = new ReadLaterForWordpress_Client();

// Admin
if ( is_admin() ) {
require_once( PADPW_DIR . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'class.admin.php'  );
require_once( PADPW_DIR . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'class.admin.page.php'  );
$myExt['admin'] = new ReadLaterForWordpress_Admin();
$myExt['admin_page'] = new ReadLaterForWordpress_Admin_Page();
wp_register_style('myPluginOptionsStylesheet',plugins_url('/css/admin_style.css',__FILE__));
}
}
add_action( 'plugins_loaded', 'ReadLaterForWordpress_Init' );


function custom_action_links($links,$file){
    array_unshift($links,"<a href='". admin_url('admin.php?page='.PADPW_SETTINGS) . "'>". __('Settings')."</a>");
    return $links;
}
add_filter('plugin_action_links_'.plugin_basename(__FILE__),'custom_action_links',10,2);


function ReadLaterForWordpress_Display($content){
    if(get_option('pocket_size') == 'small'){
        $pocket_count = 'none';
    }else if(get_option('pocket_size') == 'medium'){
        $pocket_count = 'horizontal';
    }else if(get_option('pocket_size') == 'large'){
        $pocket_count = 'vertical';
    }
    
    if (get_option('pocket_visibility') == 'yes'){
        $pocket_button = "<a data-pocket-label='pocket' data-pocket-count='".$pocket_count."' class='pocket-btn' data-lang='en'></a>";
        $pocket_script = "<script type='text/javascript'>!function(d,i){if(!d.getElementById(i)){var j=d.createElement('script');j.id=i;j.src='https://widgets.getpocket.com/v1/j/btn.js?v=1';var w=d.getElementById(i);d.body.appendChild(j);}}(document,'pocket-btn-js');</script>";
    }
    else{
        $pocket_button = "";
        $pocket_script = "";
    }
    
    if (get_option('readability_visibility') == 'yes'){
        $readability_button = "<div style='max-width:200px' class='rdbWrapper' data-show-read-now='0' data-show-read-later='1' data-show-send-to-kindle='0' data-show-print='0' data-show-email='1' data-orientation='0' data-version='1'></div>";
        $readability_script = "<script type='text/javascript'>(function() {var s = document.getElementsByTagName('script')[0],rdb = document.createElement('script'); rdb.type = 'text/javascript'; rdb.async = true; rdb.src = document.location.protocol + '//www.readability.com/embed.js'; s.parentNode.insertBefore(rdb, s); })();</script>";
    }
    else{
        $readability_button = "";
        $readability_script = "";
    }
    
    if (get_option('instapaper_visibility') == 'yes'){
        $instapaper_frame = "<iframe border='0' scrolling='no' width='78' height='17' allowtransparency='true' frameborder='0'
 style='margin-bottom: -3px; z-index: 1338; border: 0px; background-color: transparent; overflow: hidden;'
 src='http://www.instapaper.com/e2?url=____&title=____&description=____'
></iframe>";
    }
    else{
        $instapaper_frame="";
    }
    
    if(get_option('kindle_visibility') == 'yes'){
        $kindle_button = "<div class='kindleWidget' style='margin-right:20px;display:inline-block;padding:3px;cursor:pointer;font-size:11px;font-family:Arial;white-space:nowrap;line-height:1;border-radius:3px;border:#ccc thin solid;color:black;background:transparent url('https://d1xnn692s7u6t6.cloudfront.net/button-gradient.png') repeat-x;background-size:contain;'><img style='vertical-align:middle;margin:0;padding:0;border:none;' src='https://d1xnn692s7u6t6.cloudfront.net/white-15.png' /><span style='vertical-align:middle;margin-left:3px;'>Kindle</span></div>";
        $kindle_script = "<script type='text/javascript' src='https://d1xnn692s7u6t6.cloudfront.net/widget.js'></script>
<script type='text/javascript'>(function k(){window.$SendToKindle&&window.$SendToKindle.Widget?$SendToKindle.Widget.init({}):setTimeout(k,500);})();</script>";
    }
    else{
        $kindle_button="";
        $kindle_script="";
    }

    $startbox = "<ul style='list-style:none;display:flex;display:-ms-flex;display:-webkit-flex;'><li>";
    $middlebox = "</li><li>";
    $endbox = "</li></ul>";
    if(get_option('position') == 'before'){
        $content = $startbox.$pocket_button . $pocket_script . $middlebox. $kindle_button. $kindle_script. $middlebox .$readability_button . $readability_script . $middlebox. $instapaper_frame . $endbox.$content;
    }else if(get_option('position') == 'after'){
        $content = $content . $startbox.$pocket_button . $pocket_script .$middlebox. $kindle_button. $kindle_script .$middlebox.$readability_button . $readability_script .$middlebox .$instapaper_frame . $endbox ;
    }else if(get_option('position') == 'both_position'){
        $content = $startbox.$pocket_button.$middlebox.$kindle_button.$middlebox.$readability_button.$middlebox.$instapaper_frame.$endbox.$content.$startbox.$pocket_button.$middlebox.$kindle_button.$middlebox.$readability_button.$middlebox.$instapaper_frame.$pocket_script.$kindle_script.$readability_script.$endbox;
    }
    return $content;
}
add_action('loop_start','checkIfPageOrPost');

function checkIfPageOrPost(){
    if(is_page() && (get_option('visibility') == 'pages' || get_option('visibility') == 'both_visibility')){
            add_filter('the_content','ReadLaterForWordpress_Display');
    }
    if(is_single() && (get_option('visibility') == 'posts' || get_option('visibility') == 'both_visibility')){
            add_filter('the_content','ReadLaterForWordpress_Display');
    }
}

// Admin customisation and options menu
add_action('admin_menu','ReadLaterForWordpress_menu');
add_action('admin_init','register_padpwsettings');
function ReadLaterForWordpress_menu(){
    add_options_page('Pocket Readability Instapaper Buttons options','Pocket Readability Instapaper Buttons','manage_options',PADPW_SETTINGS,'ReadLaterForWordpress_options');
    add_action('admin_print_styles','ReadLaterForWordpress_Styles');
}
function ReadLaterForWordpress_Styles(){
    wp_enqueue_style('myPluginOptionsStylesheet');
}
function register_padpwsettings(){
    register_setting('padpw-settings-group','position');
    register_setting('padpw-settings-group','visibility');
    register_setting('padpw-settings-group','pocket_size');
    register_setting('padpw-settings-group','pocket_visibility');
    register_setting('padpw-settings-group','readability_visibility');
    register_setting('padpw-settings-group','instapaper_visibility');
    register_setting('padpw-settings-group','kindle_visibility');
}

function ReadLaterForWordpress_options(){
    if(!current_user_can('manage_options')){
        wp_die( __('You do not have sufficient permissions to access this page.'));
    }
?>
<div class="wrap">
<h2>Pocket, Readability, Instapaper buttons plugin</h2>
<p style="font-style:italic;">Made by Pierre-André Dewitte - a Pocket's top 5% user</p>
<form method="post" action="options.php">
    <h3><?php _e('General settings',PADPW_PLUGNAME) ?></h3>
    <table class="form-table">
    <tr valign="top">
            <th scope="row"><?php _e('Buttons position',PADPW_PLUGNAME) ?></th>
            <td>
                <input type="radio" name="position" value="before" <?php checked('before', get_option('position','before')); ?> /> <?php _e('Before post content',PADPW_PLUGNAME) ?></br>
                <input type="radio" name="position" value="after" <?php checked('after', get_option('position','before')); ?> /> <?php _e('After post content',PADPW_PLUGNAME) ?></br>
                <input type="radio" name="position" value="both_position" <?php checked('both_position', get_option('position','before')); ?> /> <?php _e('Before and after post content',PADPW_PLUGNAME) ?>
            </td>
        </tr>
        
        <tr valign="top">
            <th scope="row"><?php _e('Buttons visibility',PADPW_PLUGNAME) ?></th>
            <td>
                <input type="radio" name="visibility" value="posts" <?php checked('posts', get_option('visibility','posts')); ?> /> <?php _e('On posts only',PADPW_PLUGNAME) ?></br>
                <input type="radio" name="visibility" value="pages" <?php checked('pages', get_option('visibility','posts')); ?> /> <?php _e('On pages only',PADPW_PLUGNAME) ?></br>
                <input type="radio" name="visibility" value="both_visibility" <?php checked('both_visibility', get_option('visibility','posts')); ?> /> <?php _e('On both post and pages',PADPW_PLUGNAME) ?>
            </td>
        </tr>
    </table>
    <div class="reader-group">
        <img class="icon-logo" src="<?php echo(plugins_url(PADPW_PLUGNAME.'/img/logo_pocket.png', dirname( __FILE__ ) )) ; ?>"/><h3><?php _e('Pocket Button Settings',PADPW_PLUGNAME) ?></h3>
        <?php settings_fields( 'padpw-settings-group' ); ?>
        <?php do_settings_sections( 'padpw-settings-group' ); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e('Button size',PADPW_PLUGNAME) ?></th>
                <td>
                    <input type="radio" name="pocket_size" value="small" <?php checked('small', get_option('pocket_size','medium')); ?> /> <?php _e('Small','pocket-readability-instapaper-buttons');?></br>
                    <input type="radio" name="pocket_size" value="medium" <?php checked('medium', get_option('pocket_size','medium')); ?> /> <?php _e('Medium','pocket-readability-instapaper-buttons');?></br>
                    <input type="radio" name="pocket_size" value="large" <?php checked('large', get_option('pocket_size','medium')); ?> /> <?php _e('Large','pocket-readability-instapaper-buttons'); ?>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php _e('Button visibility',PADPW_PLUGNAME) ?></th>
                <td>
                    <input type="radio" name="pocket_visibility" value="yes" <?php checked('yes', get_option('pocket_visibility','yes')); ?> /> <?php _e('Visible',PADPW_PLUGNAME) ?></br>
                    <input type="radio" name="pocket_visibility" value="no" <?php checked('no', get_option('pocket_visibility','yes')); ?> /> <?php _e('Invisible',PADPW_PLUGNAME) ?></br>
                </td>
            </tr>
        </table>
    </div>
    <div class="reader-group">
        <img class="icon-logo" src="<?php echo(plugins_url(PADPW_PLUGNAME.'/img/logo_readability.png', dirname( __FILE__ ) )) ; ?>"/><h3><?php _e('Readability Button Settings',PADPW_PLUGNAME) ?></h3>
        <table class="form-table">

            <tr valign="top">
                <th scope="row"><?php _e('Button visibility',PADPW_PLUGNAME) ?></th>
                <td>
                    <input type="radio" name="readability_visibility" value="yes" <?php checked('yes', get_option('readability_visibility','yes')); ?> /> <?php _e('Visible',PADPW_PLUGNAME) ?></br>
                    <input type="radio" name="readability_visibility" value="no" <?php checked('no', get_option('readability_visibility','yes')); ?> /> <?php _e('Invisible',PADPW_PLUGNAME) ?></br>
                </td>
            </tr>
        </table>
    </div>
    <div class="reader-group">
        <img class="icon-logo" src="<?php echo(plugins_url(PADPW_PLUGNAME.'/img/logo_instapaper.png', dirname( __FILE__ ) )) ; ?>"/><h3><?php _e('Instapaper Button Settings',PADPW_PLUGNAME) ?></h3>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e('Button visibility',PADPW_PLUGNAME) ?></th>
                <td>
                    <input type="radio" name="instapaper_visibility" value="yes" <?php checked('yes', get_option('instapaper_visibility','yes')); ?> /> <?php _e('Visible',PADPW_PLUGNAME) ?></br>
                    <input type="radio" name="instapaper_visibility" value="no" <?php checked('no', get_option('instapaper_visibility','yes')); ?> /> <?php _e('Invisible',PADPW_PLUGNAME) ?></br>
                </td>
            </tr>
        </table>
    </div>
    <div class="reader-group">
        <img class="icon-logo" src="<?php echo(plugins_url(PADPW_PLUGNAME.'/img/logo_kindle.png', dirname( __FILE__ ) )) ; ?>"/><h3><?php _e('Kindle Button Settings',PADPW_PLUGNAME) ?></h3>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e('Button visibility',PADPW_PLUGNAME) ?></th>
                <td>
                    <input type="radio" name="kindle_visibility" value="yes" <?php checked('yes', get_option('kindle_visibility','yes')); ?> /> <?php _e('Visible',PADPW_PLUGNAME) ?></br>
                    <input type="radio" name="kindle_visibility" value="no" <?php checked('no', get_option('kindle_visibility','yes')); ?> /> <?php _e('Invisible',PADPW_PLUGNAME) ?></br>
                </td>
            </tr>
        </table>
    </div>
    <?php submit_button(); ?>

</form>
</div>
<?php } ?>