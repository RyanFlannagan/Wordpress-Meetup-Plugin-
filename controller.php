<?php
class WP_Meetup_Controller extends WP_Meetup {
    
    public $event_posts;
    public $events;
    public $options;
    public $api;
    
    function __construct() {
        parent::__construct();
        $this->event_posts = new WP_Meetup_Event_Posts();
	
	$this->events = new WP_Meetup_Events();
	
	$this->options = new WP_Meetup_Options();
	$this->api = new WP_Meetup_Api();
    }
    
}