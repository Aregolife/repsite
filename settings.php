<?php

if (!class_exists('Repsite_Settings'))
{
	class Repsite_Settings {

        
        function repsite_add_admin_menu(  ) { 

            add_submenu_page( 'options-general.php', 'Repsite Settings', 'Repsite', 'manage_options', 'repsite', array($this,'repsite_options_page'));

        }

        function my_get_options(){
            $defaults = array(
                'firestorm_website_url' => "https://aregolife.com/",
                'guest_of' => "You are a guest of: ",
                'email' => "Email: ",
                'phone' => "Phone: ",
                'html_search_value' => '<div id="et-info">',

            );
            
            return wp_parse_args( get_option( 'repsite_settings', $defaults), $defaults );
        }

        function repsite_settings_init(  ) { 

            register_setting( 'RepsitePage', 'repsite_settings' );

            add_settings_section(
                'repsite_RepsitePage_section', 
                __( 'General Settings', 'repsite' ), 
                array($this,'repsite_settings_section_callback'), 
                'RepsitePage'
            );

            add_settings_field( 
                'firestorm_website_url', 
                __( 'Domain to Firestorm Retail Shopping System', 'repsite' ), 
                array($this,'firestorm_website_url_render'), 
                'RepsitePage', 
                'repsite_RepsitePage_section' 
            );

            add_settings_field( 
                'add_to_header', 
                __( 'Add Distributor info to Header?', 'repsite' ), 
                array($this,'add_to_header_render'), 
                'RepsitePage', 
                'repsite_RepsitePage_section' 
            );
            
            add_settings_field( 
                'guest_of', 
                __( 'Text Label for Header', 'repsite' ), 
                array($this,'guest_of_render'), 
                'RepsitePage', 
                'repsite_RepsitePage_section' 
            );

            add_settings_field( 
                'email', 
                __( 'Text Label for Email', 'repsite' ), 
                array($this,'email_render'), 
                'RepsitePage', 
                'repsite_RepsitePage_section' 
            );
            
            add_settings_field( 
                'phone', 
                __( 'Text Label for Phone', 'repsite' ), 
                array($this,'phone_render'), 
                'RepsitePage', 
                'repsite_RepsitePage_section' 
            );

            add_settings_field( 
                'html_search_value', 
                __( 'HTML selector', 'repsite' ), 
                array($this,'html_search_value_render'), 
                'RepsitePage', 
                'repsite_RepsitePage_section' 
            );

            


        }


        function firestorm_website_url_render(  ) { 

            $options = $this->my_get_options();
            ?>
<input type='text' size="75" name='repsite_settings[firestorm_website_url]' value='<?php echo $options["firestorm_website_url"]; ?>'>
<?php

        }


        function add_to_header_render(  ) { 

            $options = $this->my_get_options();
            $value = array_key_exists("add_to_header",$options);
            ?>
<input type='checkbox' name='repsite_settings[add_to_header]' <?php checked( $value, 1 ); ?> value='1'>
<?php

        }


        function guest_of_render(  ) { 

            $options = $this->my_get_options();
            ?>
<input type='text' size="75" name='repsite_settings[guest_of]' value='<?php echo esc_html($options["guest_of"]); ?>'>
<?php

        }
        
        
        function email_render(  ) { 

            $options = $this->my_get_options();
            ?>
<input type='text' size="75" name='repsite_settings[email]' value='<?php echo esc_html($options["email"]); ?>'>
<?php

        }
        
        function phone_render(  ) { 

            $options = $this->my_get_options();
            ?>
<input type='text' size="75" name='repsite_settings[phone]' value='<?php echo esc_html($options["phone"]); ?>'>
<?php

        }


        function html_search_value_render(  ) { 

            $options = $this->my_get_options();
            ?>
<input type='text' size="75" name='repsite_settings[html_search_value]' value='<?php echo esc_html($options["html_search_value"]); ?>'>
<?php

        }


        function repsite_settings_section_callback(  ) { 

            //echo __( 'Settings', 'repsite' );

        }


        function repsite_options_page(  ) { 

            ?>

<div class="wrap">
    <h1>
        <?php echo __( 'Repsite', 'repsite' );?>
    </h1>
    <form action='options.php' method='post'>
        <?php
                settings_fields( 'RepsitePage' );
                do_settings_sections( 'RepsitePage' );
                submit_button();
                ?>

    </form>
</div>
<?php

        }

    }
}


?>
