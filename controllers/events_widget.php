<?php
class WP_Meetup_Events_Widget extends WP_Widget {
    
    private $core;
    
    function __construct() {
        parent::WP_Widget( 'wp_meetup_events_widget', $name = 'WP Meetup Upcoming Events Widget', array('description' => 'Displays upcoming Meetup.com events'));
        $this->core = new WP_Meetup_Events_Controller();
    }
    
    function form($instance) {
        $title = $instance ? esc_attr($instance['title']) : __('Upcoming Events', 'wp-meetup');
	$limit = $instance ? esc_attr($instance['limit']) : 3;
	echo "<p>";
        echo '<label for="' . $this->get_field_id('title') . '">' . _e('Title:') . '</label>'; 
	echo '<input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" />';
	echo "</p>";
	echo "<p>";
	echo '<label for="' . $this->get_field_id('limit') . '">' . _e('Number of events to show: ') . '</label>'; 
	echo '<input id="' . $this->get_field_id('limit') . '" name="' . $this->get_field_name('limit') . '" type="text" value="' . $limit . '" size="3"/>';
	echo "</p>";
    }
    
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
	$instance['limit'] = strip_tags($new_instance['limit']) * 1;
        return $instance;
    }
    
    function widget($args, $instance) {
        extract( $args );
        $title = apply_filters( 'widget_title', $instance['title'] );
        echo $before_widget;
        if ( $title )
            echo $before_title . $title . $after_title;
        $limit = empty($instance['limit']) ? 3 : $instance['limit'];
	$upcoming_events = $this->core->events->get_upcoming($limit);
	$events = array();
	foreach ($upcoming_events as $event) {
		$post = get_post($event->post_id);
		if ($post->post_status == 'publish') 
			$events[] = $event;
	}
        echo $this->core->render('events-widget.php', array('events' => $events));
        
        echo $after_widget;
    }
    
}
