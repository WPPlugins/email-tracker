<?php
/*
Plugin Name: Email Tracker
Plugin URI:  wordpress.org/plugins/email-tracker/
Description: Email Tracker is a wordpress plugin that lets you know if the emails you have sent have been read or not. 
Version:     1.3.3
Author:      Prashant Baldha
Text Domain: email-tracker
Domain Path: /languages
Author URI:  https://profiles.wordpress.org/pmbaldha#content-plugins
License:     GPL2
Email Tracker is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
Email Tracker is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with Email Tracker. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define( 'EMTR_BASE_FILE_PATH', __FILE__ );
define( 'EMTR_TEXT_DOMAIN', 'email-tracker' );
/*
 * includes  all functions which required by plugin
 */
require_once( __DIR__.DIRECTORY_SEPARATOR.'et-functions.php' );

require_once( __DIR__.DIRECTORY_SEPARATOR.'template-redirect.php' );
require_once( __DIR__.DIRECTORY_SEPARATOR.'libs'.DIRECTORY_SEPARATOR.'SingletonFactory.php' ); 

if(is_admin()){    
	/*
	 * Back-end (visible for admin only)
	*/
	require_once( __DIR__.DIRECTORY_SEPARATOR.'et-admin.php' );
}

/**
 * This is databse version which is useful when we upgrade plugin
 * 	
 */ 
define( 'EMTR_DB_VERSION', '1.1' );
define( 'EMTR_VERSION', '1.3.1' );



/*
* Upgrade Script
*/
if (version_compare(get_option( 'emtr_db_version' ) , EMTR_DB_VERSION, '<')) { 
	add_action('admin_init', 'emtr_plugin_activate', 20);
}

/**
 * This function and hook set default value of setting
 * This function will run when plugin will activated	
 */ 
function emtr_create_tables()
{
	global $wpdb;
	$tableName = emtr_get_table_name('email');
	
	if( $wpdb->get_var("SHOW TABLES LIKE '$tableName'") != $tableName ) {
		$wpdb->query("CREATE TABLE IF NOT EXISTS `$tableName` (
					  `email_id` int(11) NOT NULL AUTO_INCREMENT,
					  `date_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
					  `to` varchar(255) NOT NULL DEFAULT '',
					  `subject` varchar(200) NOT NULL DEFAULT '',
					  `message` text,
					  `message_plain` text,
					  `headers` text,
					  `attachments` text,
					  PRIMARY KEY (`email_id`),
					  KEY `to` (`to`),
					  KEY `subject` (`subject`)
					);");	
					// DEFAULT CHARSET=".$wpdb->charset." COLLATE=".$wpdb->collate
	}
	
	
	$tableName = emtr_get_table_name('track_email_open_log');
	if( $wpdb->get_var("SHOW TABLES LIKE '$tableName'") != $tableName ) {
		$wpdb->query("CREATE TABLE IF NOT EXISTS `$tableName` (
				  `trkemail_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `trkemail_email_id` int(10) unsigned NOT NULL COMMENT 'FK (emtr_mail => email_id)',
				  `trkemail_date_time` datetime NOT NULL,
				  `trkemail_http_user_agent` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
				  `trkemail_ip_address` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
				  `trkemail_tacked_by` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Image' COMMENT '[Image] , [Link]',
				  PRIMARY KEY (`trkemail_id`),
				  KEY `trkemail_email_id` (`trkemail_email_id`),
  				  KEY `trkemail_date_time` (`trkemail_date_time`)
				) ;");	
				//DEFAULT CHARSET=".$wpdb->charset." COLLATE=".$wpdb->collate."
	}
				
				
	$tableName = emtr_get_table_name('track_email_open_log');
	if( $wpdb->get_var("SHOW TABLES LIKE '$tableName'") != $tableName ) {
		$wpdb->query("
			CREATE TABLE IF NOT EXISTS `$tableName` (
			  `trklink_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `trklink_user_id` int(10) unsigned NOT NULL COMMENT '(FK user_master => user_id)',
			  `trklink_email_id` int(10) unsigned NOT NULL COMMENT 'FK (lead_mail => email_id)',
			  `trklink_link` varchar(255) NOT NULL,
			  PRIMARY KEY (`trklink_id`),
			  KEY `trklink_user_id` (`trklink_user_id`),
			  KEY `trklink_email_id` (`trklink_email_id`)
			);");	
					
		/*
		 ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
		*/		
	}
	
	
	$tableName = emtr_get_table_name('track_email_link_master');
	if( $wpdb->get_var("SHOW TABLES LIKE '$tableName'") != $tableName ) {
		$wpdb->query("
			CREATE TABLE IF NOT EXISTS `$tableName` (
			  `trklink_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `trklink_user_id` int(10) unsigned NOT NULL COMMENT '(FK user_master => user_id)',
			  `trklink_email_id` int(10) unsigned NOT NULL COMMENT 'FK (lead_mail => email_id)',
			  `trklink_link` varchar(255) NOT NULL,
			  PRIMARY KEY (`trklink_id`),
			  KEY `trklink_user_id` (`trklink_user_id`),
			  KEY `trklink_email_id` (`trklink_email_id`)
			);");	
	}
		
		
	
	$tableName = emtr_get_table_name('track_email_link_click_log');
	if( $wpdb->get_var("SHOW TABLES LIKE '$tableName'") != $tableName ) {
		$wpdb->query("
				CREATE TABLE IF NOT EXISTS `$tableName` (
		  `trklinkclick_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `trklinkclick_trklink_id` int(10) unsigned NOT NULL COMMENT 'FK (track_email_link_master  => trklink_id)',
		   `trklinkclick_email_id` int(20) unsigned NOT NULL DEFAULT '0',

		  `trklinkclick_date_time` datetime NOT NULL,
		  `trklinkclick_http_user_agent` varchar(255) NOT NULL,
		  `trklinkclick_ip_address` varchar(20) NOT NULL,
		  PRIMARY KEY (`trklinkclick_id`),
		  KEY `trklinkclick_trklink_id` (`trklinkclick_trklink_id`),
  			KEY `trklinkclick_email_id` (`trklinkclick_email_id`)
		);");	
	}
	

	update_option( 'emtr_db_version', EMTR_DB_VERSION );
	update_option( 'emtr_version', EMTR_VERSION );	

	//For add rewite rule of email tracking			
	flush_rewrite_rules();	
	
}
	 
function emtr_plugin_activate( $network_wide ) {
	if (function_exists( 'is_multisite' ) && is_multisite() &&  $network_wide ) {
		global $wpdb;
		$old_blog =  $wpdb->blogid;
		//Get all blog ids
		$blogids =  $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
		foreach ( $blogids as $blog_id ) {
			switch_to_blog($blog_id);
			//Create database table if not exists
			emtr_create_tables();
		}
		switch_to_blog( $old_blog );
		return;
	}	
	//Create database table if not exists
	emtr_create_tables();
	
}
register_activation_hook( __FILE__, 'emtr_plugin_activate');

function emtr_email_before_send( $orig_email ) {
	global $wpdb;
	global $emtr_is_plain;
	if( $emtr_is_plain !== false ) {
		$orig_email['message'] = nl2br($orig_email['message']);
		// The Regular Expression filter
		//$orig_email['message'] = emtr_url_to_link( $orig_email['message'] );
	}	
	
	$email = $orig_email;
	$email_db_data = array(
		'to'          	=> emtr_extract_email_field( $email['to'] ),
		'subject'     	=> $email['subject'],
		'message'     	=> $email['message'],
		'message_plain' => emtr_convet_html_to_text( $email['message'] ),
		'headers'     	=> emtr_extract_email_field($email['headers']),
		'attachments' 	=> emtr_extract_attachments( $email['attachments'] ),
		'date_time' 	=> gmdate( 'Y-m-d H:i:s' ),
	);	
	$ret = $wpdb->insert(
						emtr_get_table_name('email'), 
						$email_db_data, 
						array(
							'%s', 
							'%s', 
							'%s', 
							'%s', 
							'%s', 
							'%s', 
							'%s', 
						)
	
	);	
	
	if( $ret  ) {
		$email_id = $wpdb->insert_id;
		
		
		$orig_email['message'] = EMTR_Model::TrackEmail()->email_link_replace( $orig_email['message'], $email_id, get_current_user_id() );
	
		$orig_email['message'] .=  EMTR_Model::TrackEmail()->get_track_code( $email_id );
		
		
		
	}
	
	
	//Debug Message
	if( defined(  'WP_DEBUG' ) && WP_DEBUG ) {
		$str = __('To :', EMTR_TEXT_DOMAIN).htmlspecialchars( $email_db_data['to'] ) .'<br>'.
				__('Subject :', EMTR_TEXT_DOMAIN).$email['subject'].'<br>'.
				__('Headers :', EMTR_TEXT_DOMAIN).htmlspecialchars( $email_db_data['headers']  ).'<br>'.
				__('Attachments :', EMTR_TEXT_DOMAIN). nl2br( $email_db_data['attachments'] ).'<br>'.
				__('Message :', EMTR_TEXT_DOMAIN).$orig_email['message'].'<br>';
		
		$str = '<b>To :</b><br/>'.htmlspecialchars( $email_db_data['to'] ) .'<br><br>
							<b>Subject :</b><br/>'.$email_db_data['subject'].'<br><br>';
					
					
		$str .= '<b>'.__('Date :', EMTR_TEXT_DOMAIN).'</b><br/>'.get_date_from_gmt($email_db_data['date_time'] ,'F j, Y g:i A').emtr_relative_time(get_date_from_gmt( $email_db_data['date_time'] )).'<br><br>';
		
		if( !empty($email_db_data['headers']) ) {
			$str .= '<b>'.__('Headers :', EMTR_TEXT_DOMAIN).'</b><br/>'.nl2br( $email_db_data['headers']  ).'<br><br>';
		}
		if( !empty($email_db_data['attachments']) ) {
			$arr_attachments = explode( ',\n', $email_db_data['attachments'] );
			
			$str_attach = '';
			foreach( $arr_attachments as $key=>$attach ) {
				$str_attach .= '<a href="'.WP_CONTENT_URL.$attach.'" target="_blank">'.WP_CONTENT_URL.$attach.'</a>';
				if( $key != count($arr_attachments) - 1 ) {
					$str_attach .= ',<br/>';
				}
			}
			$str .= '<b>'.__('Attachments :', EMTR_TEXT_DOMAIN).'</b><br/>'. $str_attach .'<br><br>';
		}
			
		$str .= '<b>'.__('Message :', EMTR_TEXT_DOMAIN).'</b><br/>'. $orig_email['message'] .'<br><br>';		
				
		$upload_dir = wp_upload_dir();
		$email_dir = $upload_dir['basedir']. DIRECTORY_SEPARATOR . "email_output" ;
		
		wp_mkdir_p($email_dir);		   
		file_put_contents( $email_dir.DIRECTORY_SEPARATOR.'email-'.date('Y-m-d-H-i-s-').microtime().'.html', $str );
	}
	
	return $orig_email;
}
add_filter( 'wp_mail', 'emtr_email_before_send', 9999999999 );

	
function emtr_extract_email_field( $email_field ) {
	return is_array( $email_field ) ? implode( ',\n', $email_field ) : $email_field;
}

/**
 * Filter the mail content type.
 */
add_filter( 'wp_mail_content_type', 'emtr_set_html_mail_content_type' );
function emtr_set_html_mail_content_type( $content_type ) {	
	global $emtr_is_plain;
	if( $content_type == 'text/plain' ) {
		$emtr_is_plain = true;
    	return 'text/html';
	}
	else {
		$emtr_is_plain = false;
		return $content_type;
	}
}


function emtr_custom_rewrite_basic( $wp_rewrite ) {
	$new_rules = array('^track/e/(o|l)/([^/]+).*$' =>  'index.php?main_action=track-email.php&action=$matches[1]&pk=$matches[2]');
	
	$wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
}
add_action('generate_rewrite_rules', 'emtr_custom_rewrite_basic');

function query_vars($public_query_vars) {
	$public_query_vars[] = "main_action";
    $public_query_vars[] = "action";
    $public_query_vars[] = "pk";
    return $public_query_vars;
}
add_filter('query_vars', 'query_vars');
 
add_action( 'parse_request', 'emtr_parse_request' );
function emtr_parse_request( &$wp )
{
    if ( array_key_exists( 'main_action', $wp->query_vars ) ) {
		
		set_query_var( 'action', $wp->query_vars['action'] );
		set_query_var( 'pk', $wp->query_vars['pk'] ); 
		
        include( __DIR__ . DIRECTORY_SEPARATOR. 'track-email.php' );
        exit();
    }
}



add_filter( 'retrieve_password_message', 'emtr_retrieve_password_message', 10, 2 );
function emtr_retrieve_password_message( $message, $key ){
	$message = str_replace(array('<','>'),"",$message);
    return $message;
}


/*
Code taken from Add Plain Text Email
Plugin URI: http://dannyvankooten.com/wordpress-plugins/mailchimp-for-wordpress/
Description: Adds a text/plain email to text/html emails to decrease the chance of emails being tagged as spam.
Version: 1.1.2
Author: Danny van Kooten
Author URI: http://dannyvanKooten.com
*/
class Emtr_Mailer {

	/**
	 * @var string
	 */
	protected $previous_altbody;

	/**
	 * Add hooks
	 */
	public function add_hooks() {
		// add action so function actually runs
		add_action('phpmailer_init', array( $this, 'set_plaintext_body' ) );
	}

	/**
	 * @param PHPMailer $phpmailer
	 */
	public function set_plaintext_body( $phpmailer ) {

		// don't run if sending plain text email already
		if( $phpmailer->ContentType === 'text/plain' ) {
			return;
		}

		// don't run if altbody is set (by other plugin)
		if( ! empty( $phpmailer->AltBody ) && $phpmailer->AltBody !== $this->previous_altbody ) {
			return;
		}

		// set AltBody
		$text_message = $this->strip_html_tags( $phpmailer->Body );
		$phpmailer->AltBody = $text_message;
		$this->previous_altbody = $text_message;
	}

	/**
	 * Remove HTML tags, including invisible text such as style and
	 * script code, and embedded objects.  Add line breaks around
	 * block-level tags to prevent word joining after tag removal.
	 */
	private function strip_html_tags( $text ) {
	    $text = preg_replace(
	        array(
	          // Remove invisible content
	            '@<head[^>]*?>.*?</head>@siu',
	            '@<style[^>]*?>.*?</style>@siu',
	            '@<script[^>]*?.*?</script>@siu',
	            '@<object[^>]*?.*?</object>@siu',
	            '@<embed[^>]*?.*?</embed>@siu',
	            '@<noscript[^>]*?.*?</noscript>@siu',
	            '@<noembed[^>]*?.*?</noembed>@siu',
		        '@\t+@siu',
		        '@\n+@siu'
	        ),
	        '',
	        $text );

		// replace certain elements with a line-break
		$text = preg_replace(
			array(
				'@</?((div)|(h[1-9])|(/tr)|(p)|(pre))@iu'
			),
			"\n\$0",
			$text );

		// replace other elements with a space
		$text = preg_replace(
			array(
				'@</((td)|(th))@iu'
			),
			" \$0",
			$text );

		// strip all remaining HTML tags
	    $text = strip_tags( $text );

		// trim text
		$text = trim( $text );

		return $text;
	}

}

$Emtr_Mailer = new Emtr_Mailer;
$Emtr_Mailer->add_hooks();