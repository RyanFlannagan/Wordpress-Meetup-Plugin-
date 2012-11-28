<div class="wrap">
<h2>RSVP Button</h2>
<?php $this->display_feedback(); ?>
<?php echo $this->open_form(); ?>
    
<p>Meetup provides an RVSP button to add to your event posts.  To activate it, visit the <a href="http://www.meetup.com/meetup_api/buttons/" target="_blank">RSVP button form</a> and fill in the form with any event address in the first field.  Where it asks, "Where will you embed this Meetup?," fill out your website's information.  Click "Get embed code" and copy the contents of the first box and paste it here:</p>

<p>
<?php
echo $this->element('label', $this->element('input', FALSE, array('type' => 'checkbox', 'name' => 'use_rsvp_button', 'id' => 'rsvp_button', 'checked' => $this->options->get('use_rsvp_button'))) . "Use RSVP Buttons", array('for' => 'rsvp_button'));
?>
</p>
<p>
<?php
$textarea_content = "";
if ($script_url = $this->options->get('button_script_url'))
    $textarea_content = $this->element('script', '', array('type' => 'text/javascript', 'src' => $script_url));
echo $this->element('textarea', $textarea_content, array(
	'name' => 'button_script_html',
	'rows' => 5,
	'cols' => 60
));
?>
</p>
<p>
<?php
echo $this->element('input', FALSE, array('type' => 'submit', 'value' => 'Update options', 'class' => 'button-primary'));
?>
</p>
<?php echo $this->close_form(); ?>
