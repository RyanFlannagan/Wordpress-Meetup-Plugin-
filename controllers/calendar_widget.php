<?php
class WP_Meetup_Calendar_Widget extends WP_Widget {
    
    private $core;
    
    function __construct() {
        parent::WP_Widget( 'wp_meetup_calendar_widget', $name = 'WP Meetup Calendar Widget', array('description' => 'Displays Meetup.com events in the current month on a calendar'));
        //$this->events = new WP_Meetup_Events;
        $this->core = new WP_Meetup_Events_Controller();
    }
    
    function form($instance) {
        $title = $instance ? esc_attr($instance['title']) : __('Meetup Events', 'wp-meetup');
	echo "<p>";
        echo '<label for="' . $this->get_field_id('title') . '">' . _e('Title:') . '</label>'; 
	echo '<input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" />';
	echo "</p>";
	
	$linked_page = $instance ? $instance['linked_page'] : NULL; 
	echo "<p>";
	echo $this->core->element('label', __('Calendar page:'), array('for' => $this->get_field_id('linked_page')));
	$pages = get_pages();
	$select_contents = $this->core->element('option', "[" . __('No calendar page') . "]", array('value' => '0'));
	foreach ($pages as $page) {
	    $select_contents .= $this->core->element('option', $page->post_title, array(
		'value' => $page->ID,
		'selected' => $page->ID == $linked_page
	    ));
	}
	echo $this->core->element('select', $select_contents, array(
	    'class' => 'widefat',
	    'id' => $this->get_field_id('linked_page'),
	    'name' => $this->get_field_name('linked_page')
	));
	echo "</p>";
	
	$color = $instance ? $instance['color'] : '#E51937';
	echo "<p>";
	echo $this->core->element('label', __('Header color:'), array('for' => $this->get_field_id('color')));
	echo $this->core->element('input', FALSE, array(
	    'id' => $this->get_field_id('color'),
	    'name' => $this->get_field_name('color'),
	    'class' => 'widefat',
	    'type' => 'text',
	    'value' => $color
	));
	echo "</p>";
    }
    
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
	$instance['linked_page'] = empty($new_instance['linked_page']) ? NULL : $new_instance['linked_page'];
	$instance['color'] = empty($new_instance['color']) ? '#E51937' : $new_instance['color'];
        return $instance;
    }
    
    function widget($args, $instance) {
        extract( $args );
        $title = apply_filters( 'widget_title', $instance['title'] );
        echo $before_widget;
        if ( $title )
            echo $before_title . $title . $after_title;
        
        echo $this->core->render('widget_view.php', array(
	    'events' => $this->core->events->get_all(),
	    'linked_page' => $instance['linked_page'],
	    'header_color' => $instance['color']
	));
        
        echo $after_widget;
    }
    
}