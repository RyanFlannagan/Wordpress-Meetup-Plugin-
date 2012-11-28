<div class="wrap <?php echo ($show_plug) ? 'good-person' : 'bad-person' ?>">
<h2>WP Meetup Events</h2>
<?php $this->display_feedback(); ?>
<?php echo $this->open_form(); ?>

<h3>Update Events Posts</h3>
<p>
    WP Meetup fetches the latest updates to your meetup events every hour and updates your event posts accordingly.  However, if you want recent changes to be reflected immediately, you can force an update.
</p>
<p>
    <input type="submit" name="update_events" value="Update Event Posts" class="button-primary" />
</p>

<div id="wp-meetup-events">
<?php if ($events): ?>
<h3>Events (Upcoming in the next month)</h3>
<pre>
<?php //var_dump($events); ?>
</pre>

<?php
$post_status_map = array(
    'publish' => 'Published',
    'pending' => 'Pending',
    'draft' => 'Draft',
    'future' => 'Scheduled',
    'private' => 'Private',
    'trash' => 'Trashed'
);

$headings = array(
NULL,
    'Group',
    'Event Name',
    'Event Date',
    'Date Posted',
    'RSVP Count',
'Action'
);
$rows = array();
//$this->pr($events);
foreach ($events as $event) {
    $rows[] = array(
        $this->element('input', NULL, array('type' => 'checkbox', 'name' => 'posts[]', 'value' => $event->post_id)),
	$this->element('a', $event->group->name, array('href' => $event->group->link)),
        $this->element('a', $event->name, array('href' => get_permalink($event->post_id))),
        date('D M j, Y, g:i A', $event->time + $event->utc_offset),
        date('Y/m/d', strtotime($event->post->post_date)) . "<br />" . $post_status_map[$event->post->post_status],
        $event->yes_rsvp_count . " going",
	$this->element('a', "Edit", array('href' => get_edit_post_link($event->post_id)))
    );
}
echo $this->data_table($headings, $rows);

echo $this->element('p', $this->element('input', NULL, array('type' => 'submit', 'name' => 'trash_selected', 'value' => 'Trash Selected', 'class' => 'button-primary')));
?>





<?php elseif(count($groups) > 0): ?>

<p>There are no available events listed for this group.</p>

<?php endif; ?>
</div><!--#wp-meetup-events-->
<?php echo $this->close_form(); ?>
</div><!--#wrap-->

