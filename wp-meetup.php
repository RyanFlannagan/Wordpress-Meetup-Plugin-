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

$meetup = new WP_Meetup();

class WP_Meetup {
    
    private $dir;
    private $admin_page_url;
    private $feedback = array('error' => array(), 'message' => array(), 'success' => array());

    private $mu_api;
    private $event_posts;
    private $events;
    private $options;
    
    private $table_prefix;
    
    
    function WP_Meetup() {
	
        $this->dir = WP_PLUGIN_DIR . "/wp-meetup/";
	$this->admin_page_url = admin_url("options-general.php?page=wp_meetup");
	
	global $wpdb;
	$this->table_prefix = $wpdb->prefix . "wpmeetup_";
	
	$this->event_posts = new WP_Meetup_Event_Posts();
	
	$this->events = new WP_Meetup_Events();
	$this->events->table_name = $this->table_prefix . "events";
	
	$this->options = new WP_Meetup_Options();
	
	//$this->get_all_options();
	
        register_activation_hook( __FILE__, array($this, 'activate') );
	register_deactivation_hook( __FILE__, array($this, 'deactivate') );
        add_action('admin_menu', array($this, 'admin_menu'));
	
	wp_register_style('wp-meetup-global-style', plugins_url('global.css', __FILE__));
        wp_enqueue_style( 'wp-meetup-global-style' );
	
    }
    
    function activate() {
	//$this->pr("Activated!");
	$this->events->create_table();
    }
    
    function deactivate() {
	//$this->pr($this->option_map);
	$this->events->drop_table();
	foreach ($this->option_map as $key => $value) {
	    delete_option(is_array($value) ? $value[0] : $value);
	}
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
		$this->feedback['success'][] = "Successfullly updated your API key!";

        }
        
        if (array_key_exists('group_url', $_POST)) {
            $parsed_name = $this->meetup_url_to_group_url_name($_POST['group_url']);
	    if ($parsed_name != $this->options->get('group_url_name')) {
		if ($this->get_group($parsed_name)) {
		    $this->options->set('group_url_name', $parsed_name);
		    $this->regenerate_events();
		    
		    $this->feedback['success'][] = "Successfullly added your group";
		} else {
		    $this->feedback['error'][] = "The Group URL you entered isn't valid.";
		}
	    }
        }
        
	if (array_key_exists('category', $_POST) && $_POST['category'] != $this->options->get('category')) {
	    
	    $old_category_id = $this->options->get('category_id');
	    $this->options->set('category', $_POST['category']);
	    $new_category_id = $this->options->get('category_id');
	    
	    $this->recategorize_event_posts($old_category_id, $new_category_id);
	    

	    $this->feedback['success'][] = "Successfullly updated your event category.";
	}
	
	if (array_key_exists('publish_buffer', $_POST) && $_POST['publish_buffer'] != $this->options->get('publish_buffer')) {
	    $this->options->set('publish_buffer', $_POST['publish_buffer']);
	    

	    $this->update_post_statuses();
	    
	    $this->feedback['success'][] = "Successfullly updated your publishing buffer.";
	}
	
	if (array_key_exists('regenerate_events', $_POST)) {

	    $this->regenerate_events();
	    $this->feedback['success'][] = "Successfullly regenerated event posts.";
	}
	
    }
    
    function update_post_statuses() {
	$events = $this->events->get_all();
	foreach ($events as $event) {
	    $this->event_posts->set_date($event->post_id, $event->time, $this->options->get('publish_buffer'));
	}
    }
    
    function recategorize_event_posts($old_category_id, $new_category_id) {
	$events = $this->events->get_all();
	foreach ($events as $event) {
	    $this->event_posts->recategorize($event->post_id, $old_category_id, $new_category_id);
	}
    }
    
    function add_event_posts($events) {
	
	foreach ($events as $event) {
	    if (!$event->post_id) {
		$post_id = $this->event_posts->add($event, $this->options->get('publish_buffer'), $this->options->get('category_id'));
		$this->events->update_post_id($event->id, $post_id);
	    }
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
	if ($event_data = $this->get_events()) {
	    
	    $this->events->save_all($event_data);
	    
	    $events = $this->events->get_all();
	    $this->add_event_posts($events);
	    
	    //$data['events'] = $this->events->get_all();
	    
	}
    }
    
    
    function admin_menu() {
	
        add_options_page('WP Meetup Options', 'WP Meetup', 'manage_options', 'wp_meetup', array($this, 'admin_options'));
    }
    
    function get_events($start = 0, $end = "1m") {
	//$this->pr('getting events');
	if (!$this->options->get('group_url_name') || !$this->options->get('api_key'))
	    return FALSE;
	
        $this->mu_api = new MeetupAPIBase($this->options->get('api_key'), '2/events');
        $this->mu_api->setQuery( array(
            'group_urlname' => $this->options->get('group_url_name'),
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
	$data['events'] = $this->events->get_all();
        
        echo $this->get_include_contents($this->dir . "options-page.php", $data);
        
    }
    


    
    function get_include_contents($filename, $vars = array()) {
        if (is_file($filename)) {
            ob_start();
	    extract($vars);
            include $filename;
            return ob_get_clean();
        }
        return false;
    }
    
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