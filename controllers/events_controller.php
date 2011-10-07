<?php
class WP_Meetup_Events_Controller extends WP_Meetup_Controller {
    
    protected $uses = array('event_posts', 'events', 'groups', 'api', 'options');
    
    function admin_options() {

        if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	
	if (!empty($_POST)) $this->handle_post_data();
	
	if (!empty($_GET) && array_key_exists('remove_group_id', $_GET)) {
	    $this->groups->remove($_GET['remove_group_id']);
	    //$this->events->remove_by_group_id($_GET['remove_group_id']);
	}
        
        $data = array();
        $data['has_api_key'] = $this->options->get('api_key') != FALSE;
	$data['groups'] = $this->groups->get_all();
	$data['events'] = $this->events->get_all_upcoming();
        
        echo $this->render("options-page.php", $data);
        
    }

    
    function handle_post_data() {
        
        if (array_key_exists('api_key', $_POST) && $_POST['api_key'] != $this->options->get('api_key')) {

		$this->options->set('api_key', $_POST['api_key']);
		$this->feedback['message'][] = "Successfullly updated your API key!";

        }
	
        if (array_key_exists('group_url', $_POST)) {
            $parsed_name = $this->meetup_url_to_group_url_name($_POST['group_url']);
	    if ($parsed_name != "") {
		
		if (!in_array($parsed_name, $this->groups->get_url_names())) {
		    
		    if ($group_data = $this->api->get_group($parsed_name)) {
			
			$group = array(
			    'id' => $group_data->id,
			    'name' => $group_data->name,
			    'url_name' => $group_data->group_urlname,
			    'link' => $group_data->link
			);
			
			$this->groups->save($group);
			$this->regenerate_events();
			
			$this->feedback['message'][] = "Successfullly added your group";
		    } else {
			$this->feedback['error'][] = "The Group URL you entered isn't valid.";
		    }
		    
		} else {
		    $this->feedback['error'][] = "The group URL you entered refers to a group you've already added.";
		}
	    }
        }
	
	if (array_key_exists('category', $_POST) && $_POST['category'] != $this->options->get('category')) {
	    
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
	    $this->event_posts->set_date($event->post_id, $event->time, $event->utc_offset, $this->options->get('publish_buffer'));
	}
    }
    
    function recategorize_event_posts() {
	$events = $this->events->get_all();
	foreach ($events as $event) {
	    $this->event_posts->recategorize($event->post_id, $this->options->get('category_id'));
	}
    }
    
    function save_event_posts($events) {
	
	foreach ($events as $key => $event) {
            
	    $post_id = $this->event_posts->save_event($event, $this->options->get('publish_buffer'), $this->options->get('category_id'));
	    //pr($this->options->get('category_id'));
	    $this->events->update_post_id($event->id, $post_id);
	}
	
    }
    
    function update_events() {
	$groups = $this->groups->get_url_names();
	//$this->pr($groups);
	if ($event_data = $this->api->get_events($groups)) {
	    //$this->pr($event_data);
	    $this->events->save_all($event_data);
	    
	    $events = $this->events->get_all();
	    
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
    
    function the_content_filter($content) {
	if ($event = $this->events->get_by_post_id($GLOBALS['post']->ID)) {
	    
	    //$this->pr($event);
	    $show_plug = $this->show_plug ? rand(0,4) == 0 : FALSE;
	    $event_adjusted_time = $event->time + $event->utc_offset/1000;
	    
	    $event_meta = "<div class=\"wp-meetup-event\">";
	    $event_meta .= "<a href=\"{$event->event_url}\" class=\"wp-meetup-event-link\">View event on Meetup.com</a>";
	    $event_meta .= "<dl class=\"wp-meetup-event-details\">";
	    $event_meta .= "<dt>Date</dt><dd>" . date("l, F j, Y, g:i A", $event_adjusted_time) . "</dd>";
	    $event_meta .= ($event->venue) ? "<dt>Venue</dt><dd>" .  $event->venue->name . "</dd>" : "";
	    $event_meta .= "</dl>";
	    $event_meta .= "</div>";
	    
	    $plug = "";
	    if ($show_plug)
		$plug .= "<p class=\"wp-meetup-plug\">Meetup.com integration powered by <a href=\"http://nuancedmedia.com/\">Nuanced Media</a>.</p>";
	    
	    return $event_meta . "\n" . $content . "\n" . $plug;
	
	}
	return $content;
    }
    
    function cron_update_events() {
	if ($this->options->get('api_key') && $this->options->get('group_url_name')) {
	    $this->update_events();
	    return TRUE;
	}
	return FALSE;
    }
    
}