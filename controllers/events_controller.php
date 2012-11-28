<?php
class WP_Meetup_Events_Controller extends WP_Meetup_Controller {
    
    protected $uses = array('event_posts', 'events', 'groups', 'api', 'options', 'group_taxonomy');
    
    function __construct() {
	parent::__construct();
	$this->register_cpts();
    }
    
    function register_cpts() {
	register_post_type( 'wp_meetup_event',
	    array(
		    'labels' => array(
			    'name' => __( 'Meetup Events' ),
			    'singular_name' => __( 'Meetup Events' )
		    ),
	    'public' => true,
	    'has_archive' => true,
	    'supports' => array('title', 'editor', 'thumbnail', 'revisions', 'custom-fields', 'comments'),
	    'rewrite' => array('slug' => 'events'),
	    'show_ui' => FALSE
	    )
	);
	
	register_taxonomy('wp_meetup_group', array('wp_meetup_event'), array(
	    'hierarchical' => FALSE,
	    'labels' => array(
		'name' => __('Groups'),
		'singular_name' => __('Group')
	    ),
	    'show_ui' => true,
	    'query_var' => true,
	    'rewrite' => array( 'slug' => 'group' )
	));

    }
    
    function admin_options() {

        if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	
	//if (!empty($_POST)) $this->handle_post_data();
	
	
        
        $data = array();
        $data['has_api_key'] = $this->options->get('api_key') != FALSE;
	$data['groups'] = $this->groups->get_all();
	$data['events'] = $this->events->get_all_upcoming();
	//$data['category'] = $this->options->get_category();
	
	$data['show_plug'] = $this->options->get('show_plug');
	$data['show_plug_probability'] = $this->options->get('show_plug_probability');
	$data['include_home_page'] = $this->options->get('include_home_page');
        $data['display_event_info'] = $this->options->get('display_event_info');
        echo $this->render("options-page.php", $data);
        
    }
    
    function show_upcoming() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	
	//if (!empty($_POST)) $this->handle_post_data();
	
	$data = array();
	$data['events'] = $this->events->get_all_upcoming();
	
	echo $this->render("admin-events.php", $data);
    }
    
    function show_groups() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	
	//if (!empty($_POST)) $this->handle_post_data();
	
	if (!empty($_GET) && array_key_exists('remove_group_id', $_GET)) {
	    if ($group = $this->groups->get($_GET['remove_group_id'])) {
		
		$this->group_taxonomy->remove($group->name);
		$this->groups->remove($group->id);
		
	    }
	}
	
	$data = array();
	$data['groups'] = $this->groups->get_all();
	echo $this->render("admin-groups.php", $data);
    }

	function rsvp_button() {
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		echo $this->render("admin-rsvp-button.php");
	}
    
    function dev_support() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	
	//if (!empty($_POST)) $this->handle_post_data();
	
	$data = array();
	$data['show_plug'] = $this->options->get('show_plug');
	$data['show_plug_probability'] = $this->options->get('show_plug_probability');
	
	echo $this->render("admin-dev-support.php", $data);
    }

    
    function handle_post_data() {
        if (array_key_exists('api_key', $_POST) && $_POST['api_key'] != $this->options->get('api_key')) {

		$is_key_valid = $this->api->is_valid_key($_POST['api_key']);
		if ($is_key_valid == TRUE) {
			$this->options->set('api_key', $_POST['api_key']);
			$this->feedback['message'][] = "Successfully updated your API key!";
		} else {
			$this->feedback['error'][] = 'Your API key is invalid';
		}
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
			
			//$this->pr($group_data);
			
			$this->groups->save($group);
			$this->regenerate_events();
			
			// add the group to the custom taxonomy if applicable
			$this->group_taxonomy->save($group_data->name, array('description' => $group_data->description));
			
			$this->feedback['message'][] = "Successfully added your group";
		    } else {
			$this->feedback['error'][] = "The Group URL you entered isn't valid.";
		    }
		    
		} else {
		    $this->feedback['error'][] = "The group URL you entered refers to a group you've already added.";
		}
	    }
        }
	
	if(array_key_exists('groups', $_POST)) {
	    foreach ($_POST['groups'] as $group) {
		$this->groups->save($group);
	    }
	}
	
	if (array_key_exists('publish_buffer', $_POST) && $_POST['publish_buffer'] != $this->options->get('publish_buffer')) {
	    $this->options->set('publish_buffer', $_POST['publish_buffer']);
	    

	    $this->update_post_statuses();
	    
	    $this->feedback['message'][] = "Successfully updated your publishing buffer.";
	}
	
	if (array_key_exists('show_plug', $_POST)) {
	    $show_plug_option = $_POST['show_plug'] == 'true';
	    if ($show_plug_option != $this->options->get('show_plug')) {
		$this->options->set('show_plug', $show_plug_option);
		$this->feedback['message'][] = "Successfully updated your support for the developers.";
	    }
	}
	
	if (array_key_exists('show_plug_probability', $_POST)
	    && $_POST['show_plug_probability'] != $this->options->get('show_plug_probability')) {
	    //$this->pr($this->options->get('show_plug_probability'), $_POST['show_plug_probability']);
	    $this->options->set('show_plug_probability', $_POST['show_plug_probability']);
	    $this->feedback['message'][] = "Successfully updated the probability of Nuanced Media's link appearing on your event posts";
	}
	
	if (array_key_exists('update_publish_options', $_POST)) {
	    if (!array_key_exists('publish_options', $_POST))
		$_POST['publish_options'] = array();
	    foreach (array('include_home_page', 'display_event_info') as $option_key) {
		$this->options->set($option_key, in_array($option_key, $_POST['publish_options']));
	    }
	    $this->feedback['message'][] = "Successfully updated your publishing options";
	}
	
	if (array_key_exists('button_script_html', $_POST)) {
		$pattern = '/src=\\\"(.*?)\\\"/';
		preg_match($pattern, $_POST['button_script_html'], $matches);
		if (count($matches) == 2) {
			$script_url = $matches[1];
			$this->options->set('button_script_url', $script_url);
			$this->feedback['message'][] = "Successfully added rsvp buttons.";
		} else {
			$this->feedback['error'][] = "Error parsing script contents. Copy and paste script information again.";
		}
		
		if (array_key_exists('use_rsvp_button', $_POST)) {
		    $this->options->set('use_rsvp_button', TRUE);
		} else {
		    $this->options->set('use_rsvp_button', FALSE);
		}
	}

	if (array_key_exists('update_events', $_POST)) {
	    $this->update_events();
	    $this->feedback['message'][] = "Successfully updated event posts.";
	}

	if (array_key_exists('trash_selected', $_POST)) {
		if (array_key_exists('posts', $_POST)) {
			foreach ($_POST['posts'] as $post) {
				wp_trash_post($post);
			}
			$this->feedback['message'][] = "Trashed " . count($_POST['posts']) . " posts.";
		}
	}
	
    }
    
        
    function update_post_statuses() {
	$events = $this->events->get_all();
	foreach ($events as $event) {
	    $this->event_posts->set_date($event->post_id, $event->time, $event->utc_offset, $this->options->get('publish_buffer'));
	}
    }
    
    /*function recategorize_event_posts() {
	$events = $this->events->get_all();
	foreach ($events as $event) {
	    $this->event_posts->recategorize($event->post_id, $this->options->get('category_id'));
	}
    }*/
    
    function save_event_posts($events) {
	
	foreach ($events as $key => $event) {
            
	    $post_id = $this->event_posts->save_event($event, $this->options->get('publish_buffer'));
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
	if (($event = $this->events->get_by_post_id($GLOBALS['post']->ID)) && $this->options->get('display_event_info')) {
	    //$this->pr($event);
	    $show_plug = $this->options->get('show_plug') ? rand(0,100)/100 <= $this->options->get('show_plug_probability') : FALSE;
	    $event_adjusted_time = $event->time + $event->utc_offset;
	    //$this->pr($event);
	    $event_meta = "<div class=\"wp-meetup-event\">";
	    if ($this->options->get('use_rsvp_button') == TRUE) {
		$event_meta .= $this->element('a', 'RSVP', array('href' => $event->event_url, 'data-event' => $event->id, 'class' => 'mu-rsvp-btn'));
		$event_meta .= " <a href=\"{$event->event_url}\" class=\"wp-meetup-event-link-small\">View event on Meetup</a>";
	    } else {
		$event_meta .= "<a href=\"{$event->event_url}\" class=\"wp-meetup-event-link\">View event on Meetup.com</a>";
	    }
	    $event_meta .= "<dl class=\"wp-meetup-event-details\">";
	    if ($this->groups->count() > 1)
		$event_meta .= "<dt>Group</dt><dd>{$event->group->name}</dd>";
	    $event_meta .= "<dt>Date</dt><dd>" . date("l, F j, Y, g:i A", $event_adjusted_time) . "</dd>";
	    $event_meta .= ($event->venue) ? "<dt>Venue</dt><dd>" .  $event->venue->name . "</dd>" : "";
	    $event_meta .= "</dl>";
	    $event_meta .= "</div>";
	    
	    $plug = "";
	    if ($show_plug)
		$plug .= "<p class=\"wp-meetup-plug\">Meetup.com integration powered by <a href=\"http://nuancedmedia.com/\" title=\"Website design, Online Marketing and Business Consulting\">Nuanced Media</a>.</p>";
	    
	    return $event_meta . "\n" . $content . "\n" . $plug;
	
	}
	return $content;
    }
    
    function cron_update_events() {
	if ($this->options->get('api_key')) {
	    $this->update_events();
	    return TRUE;
	}
	return FALSE;
    }
    
}
