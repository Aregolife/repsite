<?php
/*
Plugin Name:  Repsite
Description:  Replicated website using Firestorm
Version:      1.1.10
Author:       Eric Larsen
*/
require_once (plugin_dir_path(__FILE__) . "widget.php");
require_once (plugin_dir_path(__FILE__) . "settings.php");


if (!class_exists('Repsite_Aregolife'))
{
	class Repsite_Aregolife

	{
		static $instance = false;
        const version = '1.1.10';
        public $options;
    
		public function __construct()
		{
             $repsite_settings = new Repsite_Settings();
             $this->options = $repsite_settings->my_get_options();
            
			if (is_admin())
			{
				add_action('wp_ajax_repsite_header', array(
					$this,
					'repsite_handler'
				));
				add_action('wp_ajax_nopriv_repsite_header', array(
					$this,
					'repsite_handler'
				));
                
                add_action( 'admin_menu', array($repsite_settings,'repsite_add_admin_menu') );
                add_action( 'admin_init', array($repsite_settings,'repsite_settings_init') );

			}
			else
			{
				// front end
				add_action('wp_enqueue_scripts', array(
					$this,
					'front_scripts'
				) , 10);
				add_filter('option_siteurl', array(
					$this,
					'replace_siteurl'
				));
				add_filter('option_home', array(
					$this,
					'replace_siteurl'
				));
                
                if(array_key_exists("add_to_header",$this->options))
                     add_action( 'et_html_top_header', array(
                        $this,
                        'et_header_top_hook' 
                    ));
                add_shortcode( 'repsite', array($this,'repsite_shortcode') );
			}
			add_action('widgets_init', array(
				$this,
				'register_widget'
			));
            
            
           
		}
        
		public static function init()
		{
			if (!self::$instance) self::$instance = new self;
			return self::$instance;
		}
        
		public function replace_siteurl($val)
        {
            //make all generated links keep distname as the subdomain
			return '//' . $_SERVER['HTTP_HOST'];
        }
        
		public function register_widget()
		{
			register_widget('Repsite_Widget');
		}
        
		public function front_scripts()
		{
			wp_enqueue_script('repsite_aregolife_script', plugins_url('public/js/repsite-aregolife.js?', __FILE__) , array(
				'jquery'
			) , self::version, false);
			$title_nonce = wp_create_nonce('repsite_nonce');
			wp_localize_script('repsite_aregolife_script', 'repsite_ajax', array(
				'ajax_url' => admin_url('admin-ajax.php') ,
				'nonce' => $title_nonce
			));
			wp_enqueue_style('repsite_aregolife_style', plugins_url('public/css/repsite-aregolife.css', __FILE__) , array() , self::version, 'all');
		}
        
		public function repsite_handler()
		{
			$username = esc_html($_POST['username']);
			$url = $this->options["firestorm_website_url"];
			$data = [];
			if ($username)
			{
				$data["username"] = $username;
                $data['firestormcart'] = $url;
				$response = wp_remote_get($url . $username, array(
					'timeout' => 30
				)); //Get distributor information from FireStorm

				$body = wp_remote_retrieve_body($response);

                //parse HTML
				libxml_use_internal_errors(true);
				$dom = new DOMDocument;
				$dom->loadHTML($body);
                
				$repInfo = $dom->getElementById("ctl00_ReplicatedWebsiteInfoDiv");
				if ($repInfo)
				{
					$name = $dom->getElementById("ReplicatedFullDealerName")->textContent;
					$data["name"] = $name;
					$img = $repInfo->getElementsByTagName('img') [0]->getAttribute('src');
					$data["img"] = str_replace("..", $url . 'membertoolsdotnet', $img);
					$distid = substr($img, strpos($img, "DealerID=") + 9);
					$data["distid"] = $distid;
					$email = $dom->getElementById("ReplicatedEmail");
					if ($email)
					{
						$email = $email->getElementsByTagName('a') [0]->textContent;
						$data["email"] = $email;
					}
                    $phone = $dom->getElementById("ReplicatedWorkPhone");
					if ($phone)
					{
						$phone = $phone->textContent;
						$data["phone"] = trim(str_replace("Phone:","",$phone),"\xC2\xA0\n");
					}   
				}
				else
				{ //no info
					$data["error"] = true;
				}
			}
			else
			{ // no username
				$data["error"] = true;
			}
			wp_send_json(json_encode($data));
		}
        
        
        
        function et_header_top_hook($top_header) {
            
            $guestOf = $this->options["guest_of"];
            $email = $this->options["email"];
            $phone = $this->options["phone"];
			$search = $this->options["html_search_value"];
            
            $str ='<div id="repsite-header" class="repsite"><span class="repsite-photo"></span><span class="repsite-info"><span class="repsite-guest-of"><span class="repsite-guestOf" data-text="' . esc_html($guestOf) . '"></span><span class="repsite-name"></span></span><span class="repsite-contact-info"><span class="repsite-phone" data-text="' . esc_html($phone) . '"></span><span class="repsite-email" data-text="' . esc_html($email) . '"></span></span></span></div>';
            
			echo str_replace($search, $str . $search , $top_header);
        }

        function repsite_shortcode($atts){
            $a = shortcode_atts( array(
		'field' => '',
		'title' => '',
	), $atts );
            
            $str = "";
            
            if ($a['field'] == 'name'){
                if($a['title'])
                    $str .= '<span class="repsite-guestOf" data-text="' . esc_html($a['title']) . '"></span>';
                $str .= '<span class="repsite-name"></span>';
            }
            if ($a['field'] == 'email'){
                    $str .= '<span class="repsite-email" data-text="' . esc_html($a['title']) . '"></span>';
            }
            if ($a['field'] == 'phone'){
                    $str .= '<span class="repsite-phone" data-text="' . esc_html($a['title']) . '"></span>';
            }
            if ($a['field'] == 'image'){
                    $str .= '<span class="repsite-photo"></span>';
            }
            
            
            return $str;
        }
        
	}
	$Repsite_Aregolife = Repsite_Aregolife::init();
}
