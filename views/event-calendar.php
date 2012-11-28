<?php

$group_ul_contents = "";
foreach ($groups as $group) {
    $group_ul_contents .= $this->element(
        'li',
        $this->element('a', $group->name, array('href' => $group->link, 'style' => 'border-left: 20px solid ' . $group->color)),
        array()
    );
}
echo $this->element('ul', $group_ul_contents, array('id' => 'wp-meetup-groups'));

//print_r($events);
$events_by_date = array();
foreach ($events as $event) {
    $event_time = $event->time + $event->utc_offset;
    $event_date = mktime(0, 0, 0, date('n', $event_time), date('j', $event_time), date('Y', $event_time));
    $date_key = date('Y-m-d', $event_date);
    
    if (!array_key_exists($date_key, $events_by_date))
        $events_by_date[$date_key] = array();
    
    $events_by_date[$date_key][] = $event;
}

unset($events);

//pr($events_by_date);

if (count($events_by_date) > 0) {
    $today = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
    
    $div_contents = "";
    for ($m = 0; $m < $number_of_months; $m++) { // 'm' counts the months, 'i' counts the weeks, 'j' counts the days in that week
        $current_month = date('n') + $m;
        $first_of_the_month = mktime(0, 0, 0, $current_month, 1, date('Y'));
        $date_start = mktime(0, 0, 0, $current_month, 1-date('w', $first_of_the_month), date('Y')); // monday of the first week of the month (which may not lie in the same month)
        $end_date = mktime(0, 0, 0, $current_month+1, -1, date('Y')); // last day of the current month
		
        $theadrow_contents = '';
        foreach (array('Su', 'M', 'T', 'W', 'Th', 'F', 'Sa') as $day) {
            $theadrow_contents .= $this->element('th', $day);
        }
        $thead_contents = $this->element('tr', $theadrow_contents);
        
        $tbody_contents = '';
        $current_date = $date_start;
        
        $i = 0;
        while ($current_date < $end_date) {
            $tr_contents = '';
            
            for ($j = 0; $j < 7; $j++) {
                $current_date = strtotime('+' . (($i*7)+$j) . 'days', $date_start);
                
                $td_classes = array();
                if (date('w', $current_date) == 0 || date('w', $current_date) == 6) { // sunday ('w'=0) and saturday ('w'=6) are the weekend
                    $td_classes[] = 'weekend';
                }
                
                if ($current_date == $today)
                    $td_classes[] = 'today';
                
                $td_contents = date('j', $current_date);
                $date_key = date('Y-m-d', $current_date);
                if (array_key_exists($date_key, $events_by_date)) {
                    $ul_contents = "";
                    
                    
                    foreach ($events_by_date[$date_key] as $event) {
                        $event_status = time() < $event->time + $event->utc_offset ? 'upcoming' : 'past';
                        $anchor_attributes = array('href' => get_permalink($event->post->ID));
                        if ($event_status == 'upcoming')
                            $anchor_attributes['style'] = 'background-color:' . $event->group->color;
                        
                        if ($event->post->post_status == 'publish') {
                            $ul_contents .= $this->element('li',
                                $this->element('a',
                                    $this->element('span', date("g:i A", $event->time + $event->utc_offset)) . $event->name,
                                    $anchor_attributes
                                ),
                                array('class' => ($event_status) . " " . $event->group->url_name)
                            );
                        } else {
                            $ul_contents .= $this->element('li',
                                $this->element('span', date("g:i A", $event->time + $event->utc_offset)) . $event->name,
                                array('class' => ($event_status) . " " . $event->group->url_name)
                            );
                        }
                    }
                    $td_contents .= $this->element('ul', $ul_contents);
                } else {
					$td_contents .= $this->element('ul',$this->element('li',$this->element('span',"&nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp ")));
				}
                
                if ((date('n', $current_date) % 12) == ($current_month % 12)) { // evaluating mod 12 fixes bug of months in later years all being 'out of range'
                    $tr_contents .= $this->element('td', $td_contents, array('class' => implode(' ', $td_classes)));
                } else {
                    $tr_contents .= $this->element('td', "", array('class' => 'out-of-range'));
                }
                
            }
            
            $tbody_contents .= $this->element('tr', $tr_contents);
            $i++;
        }
        
        $div_contents .= $this->element('h2', date('F Y', $first_of_the_month));
		// if current_month is this month (given by date('n')) then table_class is 'current-month'; otherwise the table_class is 'next-month'
        $table_class = $current_month - date('n') == 0 ? "current-month" : "next-month";
        $div_contents .= $this->element('table', $this->element('thead', $thead_contents) . $this->element('tbody', $tbody_contents), array('class' => $table_class,'cellpadding' => 0, 'cellspacing' => 0));
         
    }
    
    echo $this->element('div', $div_contents, array('id' => 'wp-meetup-calendar'));
} else {
    echo $this->element('p', "No events listed.");
}

?>