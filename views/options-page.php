<div class="wrap">

<h2>WP Meetup Options</h2>
<p class="description">
    Options for Meetup.com integration. <a href="http://wordpress.org/extend/plugins/wp-meetup/">Visit plugin page</a>.
</p>


<?php $this->display_feedback(); ?>

<?php echo $this->open_form(); ?>

<div id="wp-meetup-container" class="sidebar-right">
    
    <div class="sidebar">
        
        <a href="http://nuancedmedia.com/" title="Website design, Online Marketing and Business Consulting"><img src="<?php echo $this->plugin_url . "images/logo.jpg"; ?>" alt="Nuanced Media" /></a>
        
        <div id="fb-root"></div>
        <script>(function(d, s, id) {
          var js, fjs = d.getElementsByTagName(s)[0];
          if (d.getElementById(id)) {return;}
          js = d.createElement(s); js.id = id;
          js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
          fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));</script>
        <div id="wp-meetup-social">
            <div class="fb-like" data-href="https://www.facebook.com/NuancedMedia" data-send="false" data-layout="button_count" data-width="100" data-show-faces="true"></div>
                
                <!-- Place this tag where you want the +1 button to render -->
            <g:plusone annotation="inline" width="216" href="http://nuancedmedia.com/"></g:plusone>
        </div>
        <!-- Place this render call where appropriate -->
        <script type="text/javascript">
          (function() {
            var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
            po.src = 'https://apis.google.com/js/plusone.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
          })();
        </script>
        
        <h3>WP Meetup Links</h3>
        <?php
        $link_list = array(
            'Wordpress.org Plugin Directory listing' => 'http://wordpress.org/extend/plugins/wp-meetup/',
            'WP Meetup Plugin homepage' => 'http://nuancedmedia.com/wordpress-meetup-plugin/'
        );
        ?>
        <ul id="wp-meetup-links-list">
            <?php
            foreach ($link_list as $content => $href) {
                echo $this->element('li', $this->element('a', $content, array('href' => $href)));
            }
            ?>
        </ul>

    </div>
    
    
    <div id="main">
        <?php if (!$has_api_key): ?>
        
        <h3>API Key</h3>
        <p>
            To use WP Meetup, you need to provide your <a href="http://www.meetup.com/meetup_api/key/">Meetup.com API key</a>.  Just paste that key here:
        </p>
        
        <p>
            <label>Meetup.com API Key: </label>
            <input type="text" name="api_key" size="30" value="<?php echo $this->options->get('api_key'); ?>" />
        </p>
        
        <h3>Group URL</h3>
        <p>
            To pull in your Meetup.com events, provide your group's Meetup.com URL, e.g. "http://www.meetup.com/tucsonhiking"
        </p>
        <p>
            <label>Meetup.com Group URL: </label>
            <input type="text" name="group_url" size="30" value="http://www.meetup.com/" />
        </p>
        
        <?php endif; ?>
        
        
        
        
        
        
        <h3>Publishing Options</h3>
        <div id="publishing-options">
            <!--include on home page
            --display event information
            
            --cpt information-->
            
            <!-- comment -->
            <?php //echo $publish_option; ?>
            <?php
            echo $this->element('p', $this->element('label',
                $this->element('input', NULL, array(
                    'type' => 'checkbox',
                    'name' => 'publish_options[]',
                    'value' => 'include_home_page',
                    'checked' => $include_home_page
                )) . "Include event posts on home page"
            ));
            
            echo $this->element('p', $this->element('label',
                $this->element('input', NULL, array(
                    'type' => 'checkbox',
                    'name' => 'publish_options[]',
                    'value' => 'display_event_info',
                    'checked' => $display_event_info
                )) . "Display event information (date, group, and link) on event posts"
            ));
            ?>
            
            <?php
            $date_select = "<select name=\"publish_buffer\">";
            $options = array(
                '1 week' => '1 weeks',
                '2 weeks' => '2 weeks',
                '1 month' => '1 month',
                '2 months' => '2 months'
            );
            foreach ($options as $label => $value) {
                $date_select .= "<option value=\"{$value}\"" . ($this->options->get('publish_buffer') == $value ? ' selected="selected"' : "") . ">$label</option>";
            }
            $date_select .= "</select>";
            ?>
            
             <p>
                <label>Publish event posts <?php echo $date_select; ?> before the event date.</label>
            </p>
                
            <div class="publish_options_info">
                <p>
                    For developers: The name of the custom post type is <code>wp_meetup_event</code>.  The archive is accessible from <a href="<?php echo home_urL('events'); ?>"><?php echo home_urL('events'); ?></a>.  The posts have a taxonomy called <code>wp_meetup_group</code>, which holds the name of the group.  The following custom fields are available: <code>time</code>, <code>utc_offset</code>, <code>event_url</code>, <code>venue</code> (as a serialized array), <code>rsvp_limit</code>, <code>yes_rsvp_count</code>, <code>maybe_rsvp_count</code>.
                </p>
            </div>
            
           
        </div>
       
        
        
        <p>
            <input type="submit" name="update_publish_options" value="Update Options" class="button-primary" />
        </p>

        
        <?php echo $this->close_form(); ?>
        
        
    </div><!--#main-->
    
    
</div><!--#wp-meetup-container-->
</div><!--.wrap-->

