<div class="wrap <?php echo ($show_plug) ? 'good-person' : 'bad-person' ?>">
<h2>WP Meetup Developer Support Policy</h2>
<?php $this->display_feedback(); ?>
<?php echo $this->open_form(); ?>
<div id="wp-meetup-container">
    
<div id="wp-meetup-support-us" class="sidebar">
    <h3>Support the Developers</h3>
    <?php
    $show_plug_options = "";
    
    $show_plug_options .= $this->element('option', 'good person and', array('value' => 'true', 'selected' => $show_plug == TRUE));
    $show_plug_options .= $this->element('option', 'bad person and do not', array('value' => 'false', 'selected' => $show_plug == FALSE));
    ?>
    <p>I am a <select name="show_plug"><?php echo $show_plug_options; ?></select>support the open-source community.</p>
    
    <?php
    $probability_select_content = "";
    foreach (range(1, 50) as $chance_in_fifty) {
        $probability_select_content .= $this->element('option', $chance_in_fifty, array('value' => 1/$chance_in_fifty, 'selected' => $show_plug_probability == number_format(1/$chance_in_fifty, 13)));
    }
    $probability_select = $this->element('select', $probability_select_content, array('name' => 'show_plug_probability'));
    ?>
    <p>By selecting "Good Person" you will have a 1 in <?php echo $probability_select; ?> chance of linking to our website Meetup event posts that are posted to your blog.</p>
    
    <p>By selecting "BAD Person" you are not a good person ;| (Angry face)</p>
    
    <?php if (!$show_plug): ?>
    <div class="wp-meetup-caption">
        <img src="<?php echo $this->plugin_url . "images/starving_dev.jpg"; ?>" alt="We're starving!" />
        <p>Please support us, we need to eat!!!!</p>
    </div>
    <?php endif; ?>
    
    <p>
        <input type="submit" value="Update Options" class="button-primary" />
    </p>
</div>

<div id="main">
    <h3>We'd appreciate your support</h3>
    <p>We are a small Tucson company and we're providing this plugin free of charge.  However, we'd appreciate being rewarded for our efforts by allowing us to include nearly inconspicuous links on your event posts to our homepage.  By default, no links to our website are displayed on your site.  Annoying messages, though, persist your admin area begging you to change your settings so that we can display links on your event posts.  If you allow us to add those links to your post, the nasty messages will suddenly dissappear.</p>
    
    <h3>Get rid of annoying messages</h3>
    <p>If you would not like to support Nuanced Media by allowing our links to appear on your event posts, but need to get rid of the angry messages on the admin interface, you'll need to open wp-meetup.php located in your WordPress installation's wp-content/plugins directory.  In that file, find the line that reads <code>add_action('admin_notices', array($meetup, 'admin_notices'), 12);</code> and remove it.  This will get rid of those annoying messages even if you choose not to support us.</p>
</div>

</div>
<?php echo $this->close_form(); ?>