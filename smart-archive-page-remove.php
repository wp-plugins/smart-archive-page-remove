<?php
/*
Plugin Name: smart Archive Page Remove
Plugin URI: http://smartware.cc/free-wordpress-plugins/smart-archive-page-remove
Description: Completely remove unwated Archive Pages from your Blog
Version: 1.0
Author: smartware.cc
Author URI: http://smartware.cc
License: GPL2
*/

/*  Copyright 2015  smartware.cc  (email : sw@smartware.cc)

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

if ( ! defined( 'WPINC' ) ) {
	die;
}

class SmartArchivePageRemove {
  public $plugin_name;
  public $version;
  public $settings;
  public $settings_names;
  public $option_name;
  
	public function __construct() {
		$this->plugin_name = 'smartArchivePageRemove';
		$this->version = '1.0';
    $this->option_name = 'smart_archive_page_remove';
    $this->init();
	} 
  
  // get all settings
  private function get_settings() {
    $this->settings = array();
    $options = get_option( $this->option_name );
    if ( $options == '' ) {
      $settings = array();
    } else {
      $settings = unserialize( $options );
    }
    $defaults = array(
      'author' => false,
      'category' => false,
      'tag' => false,
      'daily' => false,
      'monthly' => false,
      'yearly' => false
    );
    $this->settings = shortcode_atts( $defaults, $settings );
    $this->settings_names = array(
      'author' => 'Author Archive Page',
      'category' => 'Category Archive Page',
      'tag' => 'Tag Archive Page',
      'daily' => 'Daily Archive Page',
      'monthly' => 'Monthly Archive Page',
      'yearly' => 'Yearly Archive Page'
    );
  }
  
  private function init() {
    add_action( 'init', array( $this, 'add_text_domains' ) );
    add_action( 'wp', array( $this, 'archive_remove' ) );
    add_action( 'admin_init', array( $this, 'admin_init' ) );
    add_action( 'admin_head', array( $this, 'admin_style' ) );
    add_action( 'admin_menu', array( $this, 'admin_menu' ) );
    add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_settings_link' ) ); 
  }
  
  // handle the archives to remove
  function archive_remove() {
    global $wp_query;
    if ( is_archive() ) {
      $this->get_settings();
      $archive = array(
        'author' => $wp_query->is_author(),
        'category' => $wp_query->is_category(),
        'tag' => $wp_query->is_tag(),
        'daily' => $wp_query->is_day(),
        'monthly' => $wp_query->is_month(),
        'yearly' => $wp_query->is_year()
      );
      foreach ( $archive as $key => $value ) {
        if ( $value && $this->settings[$key] ) {
          $wp_query->set_404();
          status_header(404);
          break;
        }
      }
    }
  }
    
    // init the admin section
  function admin_init() {
    $this->get_settings();
    add_settings_section( 'smart-archive-page-remove-settings', '', array( $this, 'admin_section_title' ), 'smartarchivepageremovesettings' );
    register_setting( 'smart-archive-page-remove_settings', 'smart_archive_page_remove', array( $this, 'get_post_settings' ) );
    foreach ( $this->settings as $key => $value ) {
      $this->add_single_settings_field( $key );
    }
  }
  
  // add single settings field
  function add_single_settings_field( $name ) {
    add_settings_field( 
      'smartarchivepageremovesettings_' . $name, 
      __( $this->settings_names[$name], 'smart-archive-page-remove' ), 
      array( $this, 'admin_show_field' ), 
      'smartarchivepageremovesettings', 
      'smart-archive-page-remove-settings', 
      array( 'option_name' => $this->option_name, 'name' => $name, 'value' => $this->settings[$name], 'label_for' => $name ) 
    );
  }
  
  // render option field 
  function admin_show_field( $args ) {
    echo '<input type="checkbox" name="' . $args['option_name'] . '[' . $args['name'] . ']" id="' . $args['name'] . '" value="1"' . ( ( $args['value'] == true ) ?  'checked="checked"' : '' ) . ' /><label for="' . $args['name'] . '" class="check"></label>';
  }
  
  // echo title for settings section
  function admin_section_title() {
    echo '<p><strong>' . __( 'Remove the following Archive Pages', 'smart-archive-page-remove' ) . '</strong></p><hr />';
  }
  
  // adds the options page to admin menu
  function admin_menu() {
    add_options_page( __( 'Archive Pages', "smart-archive-page-remove" ), __( 'Archive Pages', 'smart-archive-page-remove' ), 'manage_options', 'smartarchivepageremovesettings', array( $this, 'admin_page' ) );
  }
  
  function get_post_settings( $in_set ) {
    $out_set = array();
    if ( $in_set ) {
      foreach ( $in_set as $key => $value ) {
        $out_set[$key] = true;
      }
    }
    return serialize( $out_set );
  }
 
  // creates the options page
  function admin_page() {
    if ( !current_user_can( 'manage_options' ) )  {
      wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    ?>
    <div class="wrap">
      <?php screen_icon(); ?>
      <h2><?php _e('Archive Pages', 'smart-archive-page-remove'); ?></h2>
      <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
          <div id="post-body-content">
            <div class="meta-box-sortables ui-sortable">
              <form method="post" action="options.php" class="smartarchivepageremove">
                <div class="postbox">
                  <div class="inside">
                    <p style="line-height: 32px; padding-left: 40px; background-image: url(<?php echo plugins_url( 'pluginicon.png', __FILE__ ); ?>); background-repeat: no-repeat;">smart Archive Page Remove Version <?php echo $this->version; ?></p>
                  </div>
                </div>
                <div class="postbox">
                  <div class="inside">
                    <?php
                      settings_fields( 'smart-archive-page-remove_settings' );
                      do_settings_sections( 'smartarchivepageremovesettings' );
                      submit_button(); 
                    ?>
                  </div>
                </div>
              </form>
            </div>
          </div>
          <?php { $this->show_meta_boxes(); } ?>
        </div>
        <br class="clear">
      </div>    
    </div>
    <?php
  }
  
  // add css
  function admin_style() {
    if ( get_current_screen()->id == 'settings_page_smartarchivepageremovesettings' ) { 
      ?>
      <style type="text/css">
        .smartarchivepageremove input[type="checkbox"] {
            display: none;
        }
        .smartarchivepageremove input[type="checkbox"] + label.check {
          display: inline-block;  
          border: 2px solid #DDD;
          box-shadow: 0px 0px 5px 0px rgba(42,42,42,.75); 
          border-style:solid; 
          border-radius:5px; 
          width: 30px;
          height: 30px;
          line-height: 30px;
          text-align: center;
          font-family: dashicons;
          font-size: 2em;
          margin-right: 10px;
        }
        .smartarchivepageremove input[type="checkbox"] + label.check:before {
          content: "";  
        }
        .smartarchivepageremove input[type="checkbox"]:checked + label.check:before {
          content: "\f147";
        }
      </style>
      <?php
    }
  }
  
  // addd text domains
  function add_text_domains() {  
    load_plugin_textdomain( 'smart-archive-page-remove_general', false, basename( dirname( __FILE__ ) ) . '/languages' );
    load_plugin_textdomain( 'smart-archive-page-remove', false, basename( dirname( __FILE__ ) ) . '/languages' );
  }
  
  // show meta boxes
  function show_meta_boxes() {
    ?>
    <div id="postbox-container-1" class="postbox-container">
      <div class="meta-box-sortables">
        <div class="postbox">
          <h3><span><?php _e( 'Like this Plugin?', 'smart-archive-page-remove_general' ); ?></span></h3>
          <div class="inside">
            <ul>
              <li><div class="dashicons dashicons-wordpress"></div>&nbsp;&nbsp;<a href="http://wordpress.org/extend/plugins/smart-archive-page-remove/"><?php _e( 'Please rate the plugin', 'smart-archive-page-remove_general' ); ?></a></li>
              <li><div class="dashicons dashicons-admin-home"></div>&nbsp;&nbsp;<a href="http://smartware.cc/free-wordpress-plugins/smart-archive-page-remove/"><?php _e( 'Plugin homepage', 'smart-archive-page-remove_general'); ?></a></li>
              <li><div class="dashicons dashicons-admin-home"></div>&nbsp;&nbsp;<a href="http://smartware.cc/"><?php _e( 'Author homepage', 'smart-archive-page-remove_general' );?></a></li>
              <li><div class="dashicons dashicons-googleplus"></div>&nbsp;&nbsp;<a href="https://plus.google.com/+SmartwareCc"><?php _e( 'Authors Google+ Page', 'smart-archive-page-remove_general' ); ?></a></li>
              <li><div class="dashicons dashicons-facebook-alt"></div>&nbsp;&nbsp;<a href="https://www.facebook.com/smartware.cc"><?php _e( 'Authors facebook Page', 'smart-archive-page-remove_general' ); ?></a></li>
            </ul>
          </div>
        </div>
        <div class="postbox">
          <h3><span><?php _e( 'Need help?', 'smart-archive-page-remove_general' ); ?></span></h3>
          <div class="inside">
            <ul>
              <li><div class="dashicons dashicons-wordpress"></div>&nbsp;&nbsp;<a href="http://wordpress.org/plugins/smart-archive-page-remove/faq/"><?php _e( 'Take a look at the FAQ section', 'smart-archive-page-remove_general' ); ?></a></li>
              <li><div class="dashicons dashicons-wordpress"></div>&nbsp;&nbsp;<a href="http://wordpress.org/support/plugin/smart-archive-page-remove"><?php _e( 'Take a look at the Support section', 'smart-archive-page-remove_general'); ?></a></li>
              <li><div class="dashicons dashicons-admin-comments"></div>&nbsp;&nbsp;<a href="http://smartware.cc/contact/"><?php _e( 'Feel free to contact the Author', 'smart-archive-page-remove_general' ); ?></a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    <?php
  }
  
  // add a link to settings page in plugin list
  function add_settings_link( $links ) {
    return array_merge( $links, array( '<a href="' . admin_url( 'options-general.php?page=smartarchivepageremovesettings' ) . '">' . __( 'Settings' ) . '</a>') );
  }

}

$smartArchivePageRemove = new SmartArchivePageRemove();
?>