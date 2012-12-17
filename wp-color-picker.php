<?php
/*
Plugin Name: WP 3.5 Color Picker
Plugin URI: URI of plugin goes here
Description: Demonstrates how to use the new WordPress 3.5 color picker while easily falling back to farbtastic.
Version: 0.1
Author: wiredimpact
Author URI: http://wiredimpact.com
License: GPL2
*/

/*  Copyright 2012  Wired Impact  (email : info@wiredimpact.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


/**
 * Main class for creating and using the color picker in a WordPress settings page.
 *
 */
class Wp_Color_Picker {
  
  /**
   * Upon instantiation we hook into WordPress to create our settings page.
   */
  public function __construct(){
    add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
    add_action( 'admin_init', array( $this, 'settings_init' ) );
  }
  
  
  /**
  * Add the settings page using the WordPress Settings API
  */
  public function add_settings_page(){
    $settings = add_options_page(
      __( 'WP 3.5 Color Picker' ),
      __( 'WP 3.5 Color Picker' ),
      'manage_options',
      'wp-color-picker-settings',
      array( $this, 'settings_page_content' )
    );
    
    //We use the provided hook_suffix that's returned to add our styles and scripts only on our settings page.
    add_action('load-' . $settings, array($this, 'add_styles_scripts'));
  }
  
  
  /**
   * Build the basic structure for the settings page including the form, fields, and submit button.
   */
  public function settings_page_content(){
    ?>
    <div class="wrap">
      <?php screen_icon(); ?>
      <h2><?php _e( 'WP 3.5 Color Picker' ); ?></h2>
      
      <form id="wp-color-picker-options" action="options.php" method="post">
        
        <?php settings_fields( 'wp_color_picker_options' ); ?>
        <?php do_settings_sections( 'wp-color-picker-settings' ); ?>
        
        <p class="submit">
          <input id="wp-color-picker-submit" name="Submit" type="submit" class="button-primary" value="<?php _e( 'Save Color' ); ?>" />
        </p>
        
      </form>
    </div>
    <?php
  }  
  
   /**
   * Register settings, add a settings section, and add our single color field.
   */
  function settings_init(){    
    register_setting(
      'wp_color_picker_options',
      'color_options',
      array( $this, 'validate_options' )
    );
    
    add_settings_section(
      'wp-color-picker-section',
      __( 'Choose Your Color' ),
      array( $this, 'options_settings_text' ),
      'wp-color-picker-settings'
    );
    
    add_settings_field(
      'color',
      __( 'Color' ),
      array( $this, 'color_input' ),
      'wp-color-picker-settings',
      'wp-color-picker-section'
    );   
  }
  
  /**
   * Settings section help text.
   */
  function options_settings_text(){
    echo '<p>' . __( 'Use the color picker below to choose your color.' ) . '</p>';
  }
  
  /**
   * Display our color field as a text input field.
   */
  function color_input(){
    $options = get_option( 'color_options' );
    $color = ( $options['color'] != "" ) ? sanitize_text_field( $options['color'] ) : '#3D9B0C';
    
    echo '<input id="color" name="color_options[color]" type="text" value="' . $color .'" />';
    
    //This div is only needed for farbtastic, otherwise it is left empty.
    //For more info on using farbtastic check out http://acko.net/blog/farbtastic-jquery-color-picker-plug-in/.
    echo '<div id="colorpicker"></div>'; 
  }
  
  /**
   * Sanitize and validate the submitted field.
   * This is called using register_setting in the WordPress API.
   * 
   * @param array In our case this only contains the hex color we saved, but in most cases you'd have an array with more fields.
   * @return array Sanitized content to be written to the database.
   */
  function validate_options( $input ){
    $valid = array();
    $valid['color'] = sanitize_text_field( $input['color'] );
    
    return $valid;
  }
  
  
  /**
   * Enqueue the styles and scripts needed for the color picker.
   */
  function add_styles_scripts(){
    //Access the global $wp_version variable to see which version of WordPress is installed.
    global $wp_version;
    
    //If the WordPress version is greater than or equal to 3.5, then load the new WordPress color picker.
    if ( 3.5 <= $wp_version ){
      //Both the necessary css and javascript have been registered already by WordPress, so all we have to do is load them with their handle.
      wp_enqueue_style( 'wp-color-picker' );
      wp_enqueue_script( 'wp-color-picker' );
    }
    //If the WordPress version is less than 3.5 load the older farbtasic color picker.
    else {
      //As with wp-color-picker the necessary css and javascript have been registered already by WordPress, so all we have to do is load them with their handle.
      wp_enqueue_style( 'farbtastic' );
      wp_enqueue_script( 'farbtastic' );
    }
    
    //Load our custom Javascript file
    wp_enqueue_script('wp-color-picker-settings', plugin_dir_url(__FILE__) . 'js/settings.js');
  }
}

//Instantiate our class only when in the admin section of WordPress
if( is_admin() ){
  $wp_color_picker = new Wp_Color_Picker;
}