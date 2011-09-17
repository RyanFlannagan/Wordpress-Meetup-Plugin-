<?php
/*
Plugin Name: WP Meetup
Plugin URI: http://nuancedmedia.com/
Description: Pulls events from Meetup.com onto your blog
Version: 1.0
Author: Nuanced Media
Author URI: http://nuancedmedia.com/
*/

include("meetup_api/MeetupAPIBase.php");

$meetup = new WP_Meetup();

class WP_Meetup {
    
    private $dir;
    private $options = array();
    private $admin_page_url;
    private $mu_api;
    private $feedback = array('error' => array(), 'message' => array(), 'success' => array());
    private $category_id;
    
    function WP_Meetup() {
	
        $this->dir = WP_PLUGIN_DIR . "/wp-meetup/";
        $this->options['api_key'] = get_option('wp_meetup_api_key', FALSE);
        $this->options['group_url_name'] = get_option('wp_meetup_group_url_name', FALSE);
        $this->admin_page_url = admin_url("options-general.php?page=wp_meetup");
	
	$category = "meetup";
	if (!$this->category_id = get_cat_ID($category)) {
	    $this->category_id = wp_create_category( $category );
	}
	
	if (!empty($_POST)) $this->handle_post_data();
        
        add_action('admin_menu', array($this, 'admin_menu'));
        
    }
    
    function admin_menu() {
        add_options_page('WP Meetup Options', 'WP Meetup', 'manage_options', 'wp_meetup', array($this, 'admin_options'));
    }
    
    function handle_post_data() {
        
        if (array_key_exists('api_key', $_POST)) {
	    /*if ($this->get_group('tucsonhiking', $_POST['api_key'])) {*/
		update_option('wp_meetup_api_key', $_POST['api_key']);
		$this->options['api_key'] =  $_POST['api_key'];
		$this->feedback['success'][] = "Successfullly updated your API key!";
	    /*} else {
		$this->feedback['error'][] = "The API key you entered isn't valid.";
	    }*/
        }
        
        
        if (array_key_exists('group_url', $_POST)) {
            $parsed_name = str_replace("http://www.meetup.com/", "", $_POST['group_url']);
            $parsed_name = strstr($parsed_name, "/") ? substr($parsed_name, 0, strpos($parsed_name, "/")) : $parsed_name;
	    
	    if ($this->get_group($parsed_name)) {
		update_option('wp_meetup_group_url_name', $parsed_name);
		$this->options['group_url_name'] =  $parsed_name;
		$this->feedback['success'][] = "Successfullly added your group";
	    } else {
		$this->feedback['error'][] = "The Group URL you entered isn't valid.";
	    }
        }
        
    }
    
    function get_events($start = 0, $end = "1m") {
        
	if (!$this->options['group_url_name'] || !$this->options['api_key'])
	    return FALSE;
	
        $this->mu_api = new MeetupAPIBase($this->options['api_key'], '2/events');
        $this->mu_api->setQuery( array(
            'group_urlname' => $this->options['group_url_name'],
            'time' => $start . "," . $end
        )); 
        set_time_limit(0);
        $this->mu_api->setPageSize(200);
        $response = $this->mu_api->getResponse();
        
        return $response->results;
        
    }
    
    function get_group($group_url_name = FALSE, $api_key = FALSE) {
        
	if (!$group_url_name)
	    $group_url_name = $this->options['group_url_name'];
	    
	if (!$api_key)
	    $api_key = $this->options['api_key'];
	    
	if (!$api_key || !$group_url_name)
	    return FALSE;
	    
	//$this->pr($group_url_name, $api_key);
	
        $this->mu_api = new MeetupAPIBase($api_key, 'groups');
        $this->mu_api->setQuery( array('group_urlname' => $group_url_name) ); //Replace with a real group's URL name - it's what comes after the www.meetup.com/
        set_time_limit(0);
        $this->mu_api->setPageSize(200);
        $group_info = $this->mu_api->getResponse();
	
	//$this->pr($group_info);
	
        return (count($group_info->results) > 0) ? $group_info->results[0] : FALSE;
        
    }
    
    function add_event_posts($events) {
	
	
	
	$existing_posts = $this->get_event_posts(TRUE);
	
	
	//$this->pr($existing_posts);
	//$this->pr($events);
	
	foreach ($events as $event) {
	    
	    if (!in_array($event->id, $existing_posts)) {
		$post = array(
		    'post_category' => array($this->category_id),
		    'post_content' => $event->description,
		    'post_title' => $event->name,
		    'post_status' => 'publish'
		);
		
		$post_id = wp_insert_post($post);
		add_post_meta($post_id, 'wp_meetup_id', $event->id);
		add_post_meta($post_id, 'wp_meetup_time', $event->time);
		add_post_meta($post_id, 'wp_meetup_rsvp_count', $event->yes_rsvp_count);
	    }
	    
	}
	
	//$this->pr($posts);
	
	//return $posts;
    
        
    }
    
    function get_event_posts($id_only = FALSE) {
	$posts = array();
	$the_query = new WP_Query(array(
	    'cat' => $this->category_id,
	    'posts_per_page' => -1
	));
	if ($id_only) {
	    while ($the_query->have_posts()) : $the_query->the_post();
		$posts[] = get_post_meta(get_the_ID(), 'wp_meetup_id', TRUE);
	    endwhile;
	} else {
	    $posts = $the_query->posts;
	}
	wp_reset_query();
	
	return $posts;
    }
    
    function admin_options() {
	
        if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
        
        $data = array();
        $data['has_api_key'] = !empty($this->options['api_key']);
        $data['group_url'] = !empty($this->options['group_url_name']) ? "http://www.meetup.com/" . $this->options['group_url_name'] : FALSE;
        
	$data['group'] = $this->get_group();
	if ($events = $this->get_events()) {
	    $this->add_event_posts($events);
	    $data['events'] = $this->get_event_posts();
	}
        
        echo $this->get_include_contents($this->dir . "options-page.php", $data);
        
    }
    
    function get_include_contents($filename, $vars = array()) {
        if (is_file($filename)) {
            ob_start();
            foreach ($vars as $name => $value) {
                $$name = $value;
            }
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