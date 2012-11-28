<div class="wrap <?php echo ($show_plug) ? 'good-person' : 'bad-person' ?>">
<h2>WP Meetup Groups</h2>
<?php //$this->pr($GLOBALS); ?>
<?php $this->display_feedback(); ?>
<?php echo $this->open_form(); ?>
<?php
if (count($groups) > 0) :
    
    $rows = array();
    foreach ($groups as $key => $group) {
        $rows[] = array(
            $this->element('a', $group->name, array('href' => $group->link)),
            $this->element('input', NULL, array('type' => 'hidden', 'name' => "groups[$key][id]", 'value' => $group->id)) . 
            $this->element('input', NULL, array('type' => 'text', 'name' => "groups[$key][color]", 'value' => $group->color, 'class' => 'color')),
            $this->element('a', 'Remove Group', array('href' => $this->groups_page_url . '&remove_group_id=' . $group->id))
        );
    }
    echo $this->data_table(array('Group Name', 'Color', 'Remove Group'), $rows, array('id' => 'groups-table'));
    
?>
<h3>Add new group</h3>
<p>
    <label>New Group URL</label>
    <input type="text" name="group_url" size="30" value="http://www.meetup.com/" />
</p>
<?php else: ?>
<p>
    To pull in your Meetup.com events, provide your group's Meetup.com URL, e.g. "http://www.meetup.com/tucsonhiking"
</p>
<p>
    <label>Meetup.com Group URL: </label>
    <input type="text" name="group_url" size="30" value="http://www.meetup.com/" />
</p>
<?php endif; ?>
<p>
    <input type="submit" value="Update Options" class="button-primary" />
</p>
<?php echo $this->close_form(); ?>
</div>