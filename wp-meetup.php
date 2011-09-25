<?php
/*
Plugin Name: WP Meetup
Plugin URI: http://nuancedmedia.com/wordpress-plugin/wordpress-meetup-plugin/
Description: Pulls events from Meetup.com onto your blog
Version: 1.0
Author: Nuanced Media
Author URI: http://nuancedmedia.com/

Copyright 2011  Nuanced Media

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

include(dirname(__FILE__) . DIRECTORY_SEPARATOR . "meetup_api/MeetupAPIBase.php");
include(dirname(__FILE__) . DIRECTORY_SEPARATOR . "model.php");
include(dirname(__FILE__) . DIRECTORY_SEPARATOR . "models/event-posts.php");
include(dirname(__FILE__) . DIRECTORY_SEPARATOR . "models/events.php");
include(dirname(__FILE__) . DIRECTORY_SEPARATOR . "models/options.php");
include(dirname(__FILE__) . DIRECTORY_SEPARATOR . "models/api.php");
include(dirname(__FILE__) . DIRECTORY_SEPARATOR . "controller.php");
include(dirname(__FILE__) . DIRECTORY_SEPARATOR . "controllers/widget.php");
include(dirname(__FILE__) . DIRECTORY_SEPARATOR . "controllers/events_controller.php");

$meetup = new WP_Meetup();

register_activation_hook( __FILE__, array($meetup, 'activate') );
register_deactivation_hook( __FILE__, array($meetup, 'deactivate') );

add_action( 'widgets_init', create_function( '', 'return register_widget("WP_Meetup_Calendar_Widget");' ) );
add_action('admin_menu', array($meetup, 'admin_menu'));

add_shortcode( 'wp-meetup-calendar', array($meetup, 'handle_shortcode') );

wp_register_style('wp-meetup', plugins_url('global.css', __FILE__));
wp_enqueue_style( 'wp-meetup' );


class WP_Meetup {
    
    public $dir;
    public $admin_page_url;
    public $feedback = array('error' => array(), 'message' => array());
    
    public $table_prefix;
    
    public $show_plug = FALSE; // set to FALSE to remove "Meetup.com integration powered by..." from posts
    
    
    function WP_Meetup() {
	
        $this->dir = WP_PLUGIN_DIR . "/wp-meetup/";
	$this->admin_page_url = admin_url("options-general.php?page=wp_meetup");
	
	global $wpdb;
	$this->table_prefix = $wpdb->prefix . "wpmeetup_";
	
	/**/
	
    }
    
    function activate() {
	$events_controller = new WP_Meetup_Events_Controller();
	$events_controller->events->create_table();
    }
    
    function deactivate() {
	$events_controller = new WP_Meetup_Events_Controller();
	$events_controller->events->drop_table();
	$events_controller->options->delete_all();
    }
    
    function group_url_name_to_meetup_url($group_url_name) {
	return "http://www.meetup.com/" . $group_url_name;
    }
    
    function meetup_url_to_group_url_name($meetup_url) {
	$parsed_name = str_replace("http://www.meetup.com/", "", $meetup_url);
        return  strstr($parsed_name, "/") ? substr($parsed_name, 0, strpos($parsed_name, "/")) : $parsed_name;
    }
    
    function admin_menu() {
	$events_controller = new WP_Meetup_Events_Controller();
        add_options_page('WP Meetup Options', 'WP Meetup', 'manage_options', 'wp_meetup', array($events_controller, 'admin_options'));
    }

    function handle_shortcode() {
	$events_controller = new WP_Meetup_Events_Controller();
	
	$data = array();
	$data['events'] = $events_controller->events->get_all();
    
	return $this->render("event-calendar.php", $data);
    }
    
    function render($filename, $vars = array()) {
        if (is_file($this->dir . 'views/' . $filename)) {
            ob_start();
	    extract($vars);
            include $this->dir . 'views/' . $filename;
            return ob_get_clean();
        }
        return false;
    }
    
    function element($tag_name, $content = '', $attributes = NULL) {
	if ($attributes) {
	    $html_string = "<$tag_name";
	    foreach ($attributes as $key => $value) {
		if ($value != '')
		    $html_string .= " {$key}=\"{$value}\"";
	    }
	    $html_string .= ">";
	} else {
	    $html_string = "<$tag_name>";
	}
	$html_string .= $content;
	$html_string .= "</$tag_name>";
	return $html_string;
    }
    
    function pr($args) {
	
	$args = func_get_args();
	foreach ($args as $value) {
		echo "<pre>";
		print_r($value);
		echo "</pre>";
	}
	
    }
    
    function import_model($model) {
        $class_name = "WP_Meetup_" . ucfirst($model);
        $this->$model = new $class_name;
    }
    
}

?>