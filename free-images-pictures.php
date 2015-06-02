<?php
/* Plugin Name: Free images pictures
Plugin URI: http://freeimages.pictures/wp-plugin
Description: Free images from various sources by freeimages.pictures
Version: 0.2
Author: Adam Sbk
Author URI: http://freeimages.pictures/
License: GPLv2 or later
*/

//prefix for this plugin is "fip - free-images-pictures"

include_once("config.php");

class FreeImagesPictures
{
    private $options;
    
    public function __construct() {
        $settings = new FreeImagesPicturesSettingsPage();
        
        $this->options = wp_parse_args( get_option( FreeImagesPicturesSettingsPage::TABLE_ROW_NAME ), FreeImagesPicturesSettingsPage::$DEFAULT_OPTIONS );
    
        //add a button to the content editor, next to the media button
        //this button will show a popup that contains inline content
        add_action('media_buttons_context', array($this, 'fip_add_button'));

        //add some content to the bottom of the page 
        //This will be shown in the inline modal
        add_action('admin_footer', array($this, 'add_inline_popup_content'));

        //add settings of the plugin
        add_action( 'admin_menu', array($this, 'fip_admin_settings'));
        
        //ajax
        add_action( 'admin_enqueue_scripts', array($this, 'fip_add_js_constants'));
        add_action( 'wp_ajax_save_image', array($this, 'save_image_callback'));
    }

    //action to add a custom button to the content editor
    public function fip_add_button($context) {
      
      //path to my icon
      $img = plugins_url( '/dist/logo.png' , __FILE__ );
      
      //the id of the container I want to show in the popup
      $container_id = 'popup_container';
      
      //our popup's title
      $title = 'Free images by freeImages.pictures';

      //append the icon
      $context .= "<a id='fip-search-button' class='button thickbox' title='{$title}'
        href='#TB_inline?width=600&height=550&inlineId={$container_id}'>
        <img src='{$img}' alt='photo' />My Free Images</a>";
      
      return $context;
    }



    public function add_inline_popup_content() {
    ?>
    <?php add_thickbox(); ?>
    <div id="popup_container" style="display:none;">
<h2>Hello! Enjoy free images :)</h2>
      <form action="" method="post" id="free-image-search">
            <input value="<?php echo implode("|", array_keys($this->options['sources'])); ?>" name="enabled-sources" type="hidden"/>
            <input value="<?php echo $this->options['max_results']; ?>" name="max-results" type="hidden"/>
            <input value="<?php echo $this->options['api_key']; ?>" name="api-key" type="hidden"/>
        <div><input id="" name="" class="newtag form-input-tip" size="16" autocomplete="off" value="" type="text" placeholder="type a keyword"/>
        <input class="button" value="Search" type="submit"/>
            <span class="fi-results-number"></span>
        </div>
      </form>
      <div id="found-images" style="overflow:hidden;">Type above to search</div>
    </div>
    <?php
    }



    public function fip_admin_settings() {
        wp_register_script( 'my_plugin_script', plugins_url('/dist/script.js', __FILE__), array('jquery'));
        wp_enqueue_script( 'my_plugin_script' );
    }
    
    //ajax
    function save_image_callback() {
        $safe_img_src = esc_url(filter_input(INPUT_POST, 'img_src', FILTER_DEFAULT));
        if (!filter_var($safe_img_src, FILTER_DEFAULT)) {
            return new WP_Error( 'image_sideload_failed', 'img_src error value' );
        }
        $safe_post_id = filter_input(INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT);
        if (!filter_var($safe_post_id, FILTER_VALIDATE_INT)) {
            return new WP_Error( 'image_sideload_failed', 'post_id error value' );
        }
        $safe_img_title = filter_input(INPUT_POST, 'img_title', FILTER_SANITIZE_SPECIAL_CHARS);
        
        add_filter('sanitize_file_name', array($this, 'remove_chars_filename'), 10, 1);
        //returns img tag or error
        $image = media_sideload_image( $safe_img_src, $safe_post_id, $safe_img_title );
        remove_filter('sanitize_file_name', array($this, 'remove_chars_filename'));
        echo $image;
        die(); // this is required to return a proper result
    }
    
    public function remove_chars_filename($filename) {
        $path_parts = pathinfo($filename);
        $f = urldecode($path_parts['filename']);
        return sanitize_title($f) . '.' . $path_parts['extension'];
    }

    function fip_add_js_constants($hook) {
        // Only applies to dashboard panel
        // in javascript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
        wp_localize_script( 'my_plugin_script', 'ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'post_id' => get_the_ID() ) );
    }

}

if( is_admin() ) {
    $my_settings_page = new FreeImagesPictures();
}

/* Stop Adding Functions Below this Line */
