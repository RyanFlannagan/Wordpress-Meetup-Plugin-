<?php
/*
Plugin Name: WP Meetup
Plugin URI: http://nuancedmedia.com/
Description: Pulls events from Meetup.com onto your blog
Version: 1.0
Author: Nuanced Media
Author URI: http://nuancedmedia.com/
*/

include(dirname(__FILE__) . DIRECTORY_SEPARATOR . "meetup_api/MeetupAPIBase.php");
include(dirname(__FILE__) . DIRECTORY_SEPARATOR . "event-posts.php");
include(dirname(__FILE__) . DIRECTORY_SEPARATOR . "events.php");
include(dirname(__FILE__) . DIRECTORY_SEPARATOR . "options.php");
include(dirname(__FILE__) . DIRECTORY_SEPARATOR . "widget.php");

$meetup = new WP_Meetup();

class WP_Meetup {
    
    private $dir;
    private $admin_page_url;
    private $feedback = array('error' => array(), 'message' => array());

    private $mu_api;
    private $event_posts;
    public $events;
    private $options;
    
    private $table_prefix;
    
    private $show_plug = TRUE; // set to FALSE to remove "Meetup.com integration powered by..." from posts
    
    
    function WP_Meetup($add_actions = TRUE) {
	
        $this->dir = WP_PLUGIN_DIR . "/wp-meetup/";
	$this->admin_page_url = admin_url("options-general.php?page=wp_meetup");
	
	global $wpdb;
	$this->table_prefix = $wpdb->prefix . "wpmeetup_";
	
	$this->event_posts = new WP_Meetup_Event_Posts();
	
	$this->events = new WP_Meetup_Events();
	$this->events->table_name = $this->table_prefix . "events";
	
	$this->options = new WP_Meetup_Options();
	
	//$this->get_all_options();
	
	if ($add_actions) {
	    register_activation_hook( __FILE__, array($this, 'activate') );
	    register_deactivation_hook( __FILE__, array($this, 'deactivate') );
	    
	    add_action( 'widgets_init', create_function( '', 'return register_widget("WP_Meetup_Calendar_Widget");' ) );
	    add_action('admin_menu', array($this, 'admin_menu'));
	    
	    add_shortcode( 'wp-meetup-calendar', array($this, 'handle_shortcode') );
	    
	    wp_register_style('wp-meetup', plugins_url('global.css', __FILE__));
	    wp_enqueue_style( 'wp-meetup' );
	}
	
    }
    
    function activate() {
	//$this->pr("Activated!");
	$this->events->create_table();
    }
    
    function deactivate() {
	//$this->pr($this->option_map);
	$this->events->drop_table();
	$this->options->delete_all();
    }
    
    function group_url_name_to_meetup_url($group_url_name) {
	return "http://www.meetup.com/" . $group_url_name;
    }
    
    function meetup_url_to_group_url_name($meetup_url) {
	$parsed_name = str_replace("http://www.meetup.com/", "", $meetup_url);
        return  strstr($parsed_name, "/") ? substr($parsed_name, 0, strpos($parsed_name, "/")) : $parsed_name;
    }
    
    function handle_post_data() {
        
        if (array_key_exists('api_key', $_POST) && $_POST['api_key'] != $this->options->get('api_key')) {

		$this->options->set('api_key', $_POST['api_key']);
		$this->feedback['message'][] = "Successfullly updated your API key!";

        }
	
        if (array_key_exists('group_url', $_POST)) {
            $parsed_name = $this->meetup_url_to_group_url_name($_POST['group_url']);
	    if ($parsed_name != $this->options->get('group_url_name')) {
		if ($this->get_group($parsed_name)) {
		    $this->options->set('group_url_name', $parsed_name);
		    $this->regenerate_events();
		    
		    $this->feedback['message'][] = "Successfullly added your group";
		} else {
		    $this->feedback['error'][] = "The Group URL you entered isn't valid.";
		}
	    }
        }
	
	if (array_key_exists('category', $_POST)/* && $_POST['category'] != $this->options->get('category')*/) {
	    //pr($this->options->get('category_id'), $this->options->get('category'));
	    
	    $this->options->set('category', $_POST['category']);
	    $this->recategorize_event_posts();

	    $this->feedback['message'][] = "Successfullly updated your event category.";
	}
	
	if (array_key_exists('publish_buffer', $_POST) && $_POST['publish_buffer'] != $this->options->get('publish_buffer')) {
	    $this->options->set('publish_buffer', $_POST['publish_buffer']);
	    

	    $this->update_post_statuses();
	    
	    $this->feedback['message'][] = "Successfullly updated your publishing buffer.";
	}
	
	if (array_key_exists('update_events', $_POST)) {

	    $this->update_events();
	    $this->feedback['message'][] = "Successfullly updated event posts.";
	}
	
    }
    
    function update_post_statuses() {
	$events = $this->events->get_all();
	foreach ($events as $event) {
	    $this->event_posts->set_date($event->post_id, $event->time, $this->options->get('publish_buffer'));
	}
    }
    
    function recategorize_event_posts() {
	$events = $this->events->get_all();
	foreach ($events as $event) {
	    $this->event_posts->recategorize($event->post_id, $this->options->get('category_id'));
	}
    }
    
    function save_event_posts($events) {
	
	foreach ($events as $event) {

	    $post_id = $this->event_posts->save_event($event, $this->options->get('publish_buffer'), $this->options->get('category_id'), $this->show_plug);
	    //pr($this->options->get('category_id'));
	    $this->events->update_post_id($event->id, $post_id);
	}
	
    }
    
    function update_events() {
	if ($event_data = $this->get_events()) {
	    
	    $this->events->save_all($event_data);
	    
	    $events = $this->events->get_all();
	    //pr($events);
	    $this->save_event_posts($events);
	    
	}
    }
    
    function remove_all_event_posts() {
	$events = $this->events->get_all();
	foreach ($events as $event) {
	    $this->event_posts->remove($event->post_id);
	}
	$this->events->remove_all();
    }
    
    function regenerate_events() {
	$this->remove_all_event_posts();
	$this->update_events();
    }
    
    
    function admin_menu() {
	
        add_options_page('WP Meetup Options', 'WP Meetup', 'manage_options', 'wp_meetup', array($this, 'admin_options'));
    }
    
    function get_events($start = FALSE, $end = "2m") {
	if ($start === FALSE) {
	    $start = mktime(0, 0, 0, date('n'), 1, date('Y'));
	    $start *= 1000;
	    $start = number_format($start, 0, '.', '');
	}

	if (!$this->options->get('group_url_name') || !$this->options->get('api_key'))
	    return FALSE;
	
        $this->mu_api = new MeetupAPIBase($this->options->get('api_key'), '2/events');
        $this->mu_api->setQuery(array(
            'group_urlname' => $this->options->get('group_url_name'),
	    'status' => 'upcoming,past',
            'time' => $start . "," . $end
        ));

        set_time_limit(0);
        $this->mu_api->setPageSize(200);
        $response = $this->mu_api->getResponse();
        
        return $response->results;
        
    }
    
    function get_group($group_url_name = FALSE, $api_key = FALSE) {
        
	if (!$group_url_name)
	    $group_url_name = $this->options->get('group_url_name');
	    
	if (!$api_key)
	    $api_key = $this->options->get('api_key');
	    
	if (!$api_key || !$group_url_name)
	    return FALSE;
	    
	//$this->pr($group_url_name, $api_key);
	
        $this->mu_api = new MeetupAPIBase($api_key, 'groups');
        $this->mu_api->setQuery( array('group_urlname' => $group_url_name) ); 
        set_time_limit(0);
        $this->mu_api->setPageSize(200);
        $group_info = $this->mu_api->getResponse();
	
        return (count($group_info->results) > 0) ? $group_info->results[0] : FALSE;
        
    }
    
    function admin_options() {
	
	if (!empty($_POST)) $this->handle_post_data();
	
        if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
        
        $data = array();
        $data['has_api_key'] = $this->options->get('api_key') != FALSE;
        $data['group_url'] = $this->group_url_name_to_meetup_url($this->options->get('group_url_name'));
        
	$data['group'] = $this->get_group();
	$data['events'] = $this->events->get_all_upcoming();
        
        echo $this->get_include_contents("options-page.php", $data);
        
    }

    function handle_shortcode() {
	$data = array();
	$data['events'] = $this->events->get_all();
    
	return $this->get_include_contents("event-calendar.php", $data);
    }
    
    function get_include_contents($filename, $vars = array()) {
        if (is_file($this->dir . $filename)) {
            ob_start();
	    extract($vars);
            include $this->dir . $filename;
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
    
}

if ($_SERVER['HTTP_HOST'] == 'localhost') {
    function pr($args) {
	
	$args = func_get_args();
	foreach ($args as $value) {
		echo "<pre>";
		print_r($value);
		echo "</pre>";
	}
	
    }
}

?>