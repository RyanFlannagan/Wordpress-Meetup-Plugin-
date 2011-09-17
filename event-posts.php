<?php

class WP_Meetup_Event_Posts {
    
    //public $category_id;
    public $parent;

    
    function add($events) {
	
	
	
	$existing_posts = $this->get_all(TRUE);
	$added_post_count = 0;
	
	
	//$this->pr($existing_posts);
	//$this->pr($events);
	
	foreach ($events as $event) {
	    
	    if (!in_array($event->id, $existing_posts)) {
		$added_post_count++;
		$post_status = strtotime("+" . $this->parent->get_option('publish_buffer')) >=  $event->time ? 'publish' : 'future';

		$post = array(
		    'post_category' => array($this->parent->category_id),
		    'post_content' => $event->description,
		    'post_title' => $event->name,
		    'post_status' => $post_status,
		    'post_date' => $post_status == 'publish' ? date("Y-m-d H:i:s") : date("Y-m-d H:i:s", strtotime("-" . $this->parent->get_option('publish_buffer'), $event->time)) 
		);

		$post_id = wp_insert_post($post);
		add_post_meta($post_id, 'wp_meetup_id', $event->id);
		add_post_meta($post_id, 'wp_meetup_time', $event->time);
		add_post_meta($post_id, 'wp_meetup_rsvp_count', $event->yes_rsvp_count);
	    }
	    
	}
	
	if ($added_post_count > 0)
	    $this->feedback['success'][] = "Successfullly posted {$added_post_count} new events";
	
	//$this->pr($posts);
	
	//return $posts;
    
        
    }
    
    function remove_all() {
	//$this->pr("Time to update post dates");
	$posts = $this->get_all();
	
	foreach ($posts as $post) {
	    wp_delete_post($post->ID);
	}
	
    }
    
    function get_all($id_only = FALSE) {
	$posts = array();
	$the_query = new WP_Query(array(
	    'cat' => $this->parent->category_id,
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
    
}