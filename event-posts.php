<?php

class WP_Meetup_Event_Posts {
    
    public $parent;

    
    function add($event) {
        
        
        $post_status = strtotime("+" . $this->parent->get_option('publish_buffer')) >=  $event->time ? 'publish' : 'future';
        
        $description = "<div class=\"wp-meetup-event\">";
        $description .= "<a href=\"{$event->event_url}\" class=\"wp-meetup-event-link\">View event on Meetup.com</a>";
        $description .= "<ul class=\"wp-meetup-event-details\">";
        $description .= "<li>Date : " . date("l, F j, Y, g:i A", $event->time + $event->utc_offset/1000) . "</li>";
        $description .= ($event->venue) ? "<li>Venue : " .  $event->venue->name . "</li>" : "";
        $description .= "</ul>";
        $description .= "</div>";
        $description .= $event->description;

        $post = array(
            'post_category' => array($this->parent->category_id),
            'post_content' => $description,
            'post_title' => $event->name,
            'post_status' => $post_status,
            'post_date' => $post_status == 'publish' ? date("Y-m-d H:i:s") : date("Y-m-d H:i:s", strtotime("-" . $this->parent->get_option('publish_buffer'), $event->time)) 
        );

        $post_id = wp_insert_post($post);
        
        return $post_id;
        
    }
    
    function remove($post_id = FALSE) {
	//$this->pr("Time to update post dates");
	//$posts = $this->get_all();
	
	//foreach ($posts_ids as $post_id) {
	    wp_delete_post($post_id, TRUE);
	//}
	
    }
    
    /*function get_all($id_only = FALSE) {
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
    }*/
    
}