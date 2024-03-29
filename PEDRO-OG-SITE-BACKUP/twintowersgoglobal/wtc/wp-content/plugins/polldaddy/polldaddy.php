<?php /**/ ?><?php

/*
Plugin Name: PollDaddy Polls
Description: Create and manage PollDaddy polls and ratings in WordPress
Author: Automattic, Inc.
Author URL: http://automattic.com/
Version: 1.8.8
*/

// You can hardcode your PollDaddy PartnerGUID (API Key) here
//define( 'WP_POLLDADDY__PARTNERGUID', '12345...' );

class WP_PollDaddy {
	var $errors;
	var $base_url;
	var $is_admin;
	var $is_author;
	var $scheme;
	var $version;      
	var $polldaddy_client_class;
	var $polldaddy_clients;
	var $id;
	var $multiple_accounts;
	var $user_code;
	var $rating_user_code;
	
	function WP_PollDaddy(){
    $this ->__construct();
  }

  function __construct() {
    global $current_user;
    $this->errors = new WP_Error;
    $this->scheme = 'https';
    $this->version = '1.8.8';
    $this->multiple_accounts = true;   
    $this->polldaddy_client_class = 'api_client';
    $this->polldaddy_clients = array();
		$this->is_admin = (bool) current_user_can('manage_options');
		$this->is_author = true;
    $this->id = (int) $current_user->ID;
    $this->user_code = null;
    $this->rating_user_code = null;	
  }
   
	function &get_client( $api_key, $userCode = null ) {
		if ( isset( $this->polldaddy_clients[$api_key] ) ) {
			if ( !is_null( $userCode ) ) 
				$this->polldaddy_clients[$api_key]->userCode = $userCode;
			return $this->polldaddy_clients[$api_key];
		}
		require_once WP_POLLDADDY__POLLDADDY_CLIENT_PATH;
		$this->polldaddy_clients[$api_key] = $this->config_client( new $this->polldaddy_client_class( $api_key, $userCode ) );
		return $this->polldaddy_clients[$api_key];
	}
	
	function config_client( $client ){
    return $client;
  }

	function admin_menu() { 
		if ( !defined( 'WP_POLLDADDY__PARTNERGUID' ) ) {
			$guid = get_option( 'polldaddy_api_key' );
			if ( !$guid || !is_string( $guid ) )
				$guid = false;
			define( 'WP_POLLDADDY__PARTNERGUID', $guid );
		}
		
		if ( !WP_POLLDADDY__PARTNERGUID ) {   			
			if ( function_exists( 'add_object_page' ) ) // WP 2.7+
  			$hook = add_object_page( __( 'Ratings', 'polldaddy' ), __( 'Ratings', 'polldaddy' ), 'edit_posts', 'ratings', array( &$this, 'api_key_page' ), "{$this->base_url}polldaddy.png" );
  		else
  			$hook = add_management_page( __( 'Ratings', 'polldaddy' ), __( 'Ratings', 'polldaddy' ), 'edit_posts', 'ratings', array( &$this, 'api_key_page' ) );
  		
  		add_action( "load-$hook", array( &$this, 'api_key_page_load' ) );
  
  		if ( function_exists( 'add_object_page' ) ) // WP 2.7+
  			$hook = add_object_page( __( 'Polls', 'polldaddy' ), __( 'Polls', 'polldaddy' ), 'edit_posts', 'polls', array( &$this, 'api_key_page' ), "{$this->base_url}polldaddy.png" );
  		else
  			$hook = add_management_page( __( 'Polls', 'polldaddy' ), __( 'Polls', 'polldaddy' ), 'edit_posts', 'polls', array( &$this, 'api_key_page' ) );
  		
  		add_action( "load-$hook", array( &$this, 'api_key_page_load' ) );
			if ( ( empty( $_GET['page'] ) || 'polls' != $_GET['page'] ) && ( empty( $_GET['page'] ) || 'ratings' != $_GET['page'] ) )
				add_action( 'admin_notices', create_function( '', 'echo "<div class=\"error\"><p>" . sprintf( "You need to <a href=\"%s\">input your PollDaddy.com account details</a>.", "edit.php?page=polls" ) . "</p></div>";' ) );
			return false;
		}                  
		
		if ( function_exists( 'add_object_page' ) ) // WP 2.7+
			$hook = add_object_page( __( 'Ratings', 'polldaddy' ), __( 'Ratings', 'polldaddy' ), 'edit_posts', 'ratings', array( &$this, 'management_page' ), "{$this->base_url}polldaddy.png" );
		else
			$hook = add_management_page( __( 'Ratings', 'polldaddy' ), __( 'Ratings', 'polldaddy' ), 'edit_posts', 'ratings', array( &$this, 'management_page' ) );
		
		add_action( "load-$hook", array( &$this, 'management_page_load' ) );

		if ( function_exists( 'add_object_page' ) ) // WP 2.7+
			$hook = add_object_page( __( 'Polls', 'polldaddy' ), __( 'Polls', 'polldaddy' ), 'edit_posts', 'polls', array( &$this, 'management_page' ), "{$this->base_url}polldaddy.png" );
		else
			$hook = add_management_page( __( 'Polls', 'polldaddy' ), __( 'Polls', 'polldaddy' ), 'edit_posts', 'polls', array( &$this, 'management_page' ) );
		
		add_action( "load-$hook", array( &$this, 'management_page_load' ) );
		
		if ( $this->is_admin ) { 
		  add_submenu_page( 'ratings', __( 'Ratings &ndash; Settings', 'polldaddy' ), __( 'Settings', 'polldaddy' ), 'edit_posts', 'ratings', array( &$this, 'management_page' ) );
		  add_submenu_page( 'ratings', __( 'Ratings &ndash; Reports', 'polldaddy' ), __( 'Reports', 'polldaddy' ), 'edit_posts', 'ratings&amp;action=reports', array( &$this, 'management_page' ) );
		}
		else{ 
		  add_submenu_page( 'ratings', __( 'Ratings &ndash; Reports', 'polldaddy' ), __( 'Reports', 'polldaddy' ), 'edit_posts', 'ratings', array( &$this, 'management_page' ) );
    }
		
		add_submenu_page( 'polls', __( 'Polls', 'polldaddy' ), __( 'Edit', 'polldaddy' ), 'edit_posts', 'polls', array( &$this, 'management_page' ) );
		
    if ( $this->is_author ) {
      add_submenu_page( 'polls', __( 'Add New Poll', 'polldaddy' ), __( 'Add New', 'polldaddy' ), 'edit_posts', 'polls&amp;action=create-poll', array( &$this, 'management_page' ) );
		  add_submenu_page( 'polls', __( 'Custom Styles', 'polldaddy' ), __( 'Custom Styles', 'polldaddy' ), 'edit_posts', 'polls&amp;action=list-styles', array( &$this, 'management_page' ) );
		  add_submenu_page( 'polls', __( 'Options', 'polldaddy' ), __( 'Options', 'polldaddy' ), 'edit_posts', 'polls&amp;action=options', array( &$this, 'management_page' ) );
    }    

		add_action( 'media_buttons', array( &$this, 'media_buttons' ) );
	}

  function api_key_page_load() {
		if ( 'post' != strtolower( $_SERVER['REQUEST_METHOD'] ) || empty( $_POST['action'] ) || 'account' != $_POST['action'] )
			return false;

		check_admin_referer( 'polldaddy-account' );

		$polldaddy_email = stripslashes( $_POST['polldaddy_email'] );
		$polldaddy_password = stripslashes( $_POST['polldaddy_password'] );

		if ( !$polldaddy_email )
			$this->errors->add( 'polldaddy_email', __( 'Email address required', 'polldaddy' ) );

		if ( !$polldaddy_password )
			$this->errors->add( 'polldaddy_password', __( 'Password required', 'polldaddy' ) );

		if ( $this->errors->get_error_codes() )
			return false;

		$details = array( 
			'uName' => get_bloginfo( 'name' ),
			'uEmail' => $polldaddy_email,
			'uPass' => $polldaddy_password,
			'partner_userid' => $this->id
		);
		if ( function_exists( 'wp_remote_post' ) ) { // WP 2.7+
			$polldaddy_api_key = wp_remote_post( $this->scheme . '://api.polldaddy.com/key.php', array(
				'body' => $details
			) );
			if ( is_wp_error( $polldaddy_api_key ) ) {
				$this->errors = $polldaddy_api_key;
				return false;
			}
			$polldaddy_api_key = wp_remote_retrieve_body( $polldaddy_api_key );
		} else {
			$fp = fsockopen(
				'api.polldaddy.com',
				80,
				$err_num,
				$err_str,
				3
			);

			if ( !$fp ) {
				$this->errors->add( 'connect', __( "Can't connect to PollDaddy.com", 'polldaddy' ) );
				return false;
			}

			if ( function_exists( 'stream_set_timeout' ) )
				stream_set_timeout( $fp, 3 );

			global $wp_version;

			$request_body = http_build_query( $details, null, '&' );

			$request  = "POST /key.php HTTP/1.0\r\n";
			$request .= "Host: api.polldaddy.com\r\n";
			$request .= "User-agent: WordPress/$wp_version\r\n";
			$request .= 'Content-Type: application/x-www-form-urlencoded; charset=' . get_option('blog_charset') . "\r\n";
			$request .= 'Content-Length: ' . strlen( $request_body ) . "\r\n";

			fwrite( $fp, "$request\r\n$request_body" );

			$response = '';
			while ( !feof( $fp ) )
				$response .= fread( $fp, 4096 );
			fclose( $fp );
			list($headers, $polldaddy_api_key) = explode( "\r\n\r\n", $response, 2 );
		}

		if ( !$polldaddy_api_key ) {
			$this->errors->add( 'polldaddy_password', __( 'Invalid Account', 'polldaddy' ) );
			return false;
		}

		update_option( 'polldaddy_api_key', $polldaddy_api_key );

		$polldaddy = $this->get_client( $polldaddy_api_key );
		$polldaddy->reset();
		if ( !$polldaddy->get_usercode( $this->id ) ) {
			$this->parse_errors( $polldaddy );
			$this->errors->add( 'GetUserCode', __( 'Account could not be accessed.  Are your email address and password correct?', 'polldaddy' ) );
			return false;
		}

		return true;
	}

	function parse_errors( &$polldaddy ) {
		if ( $polldaddy->errors )
			foreach ( $polldaddy->errors as $code => $error )
				$this->errors->add( $code, $error );
		if ( isset( $this->errors->errors[4] ) ) {
			$this->errors->errors[4] = array( sprintf( __( 'Obsolete PollDaddy User API Key:  <a href="%s">Sign in again to re-authenticate</a>', 'polldaddy' ), add_query_arg( array( 'action' => 'signup', 'reaction' => empty( $_GET['action'] ) ? false : $_GET['action'] ) ) ) );
			$this->errors->add_data( true, 4 );
		}
	}

	function print_errors() {
		if ( !$error_codes = $this->errors->get_error_codes() )
			return;
?>

<div class="error">

<?php

		foreach ( $error_codes as $error_code ) :
			foreach ( $this->errors->get_error_messages( $error_code ) as $error_message ) :
?>

	<p><?php echo $this->errors->get_error_data( $error_code ) ? $error_message : wp_specialchars( $error_message ); ?></p>

<?php
			endforeach;
		endforeach;
		
		$this->errors = new WP_Error;
?>

</div>
<br class="clear" />

<?php
	}

	function api_key_page() {
    $this->print_errors();
?>

<div class="wrap">

	<h2><?php _e( 'PollDaddy Account', 'polldaddy' ); ?></h2>

	<p><?php printf( __( 'Before you can use the PollDaddy plugin, you need to enter your <a href="%s">PollDaddy.com</a> account details.', 'polldaddy' ), 'http://polldaddy.com/' ); ?></p>

	<form action="" method="post">
		<table class="form-table">
			<tbody>
				<tr class="form-field form-required">
					<th valign="top" scope="row">
						<label for="polldaddy-email"><?php _e( 'PollDaddy Email Address', 'polldaddy' ); ?></label>
					</th>
					<td>
						<input type="text" name="polldaddy_email" id="polldaddy-email" aria-required="true" size="40" />
					</td>
				</tr>
				<tr class="form-field form-required">
					<th valign="top" scope="row">
						<label for="polldaddy-password"><?php _e( 'PollDaddy Password', 'polldaddy' ); ?></label>
					</th>
					<td>
						<input type="password" name="polldaddy_password" id="polldaddy-password" aria-required="true" size="40" />
					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit">
			<?php wp_nonce_field( 'polldaddy-account' ); ?>
			<input type="hidden" name="action" value="account" />
			<input type="hidden" name="account" value="import" />
			<input type="submit" value="<?php echo attribute_escape( __( 'Submit', 'polldaddy' ) ); ?>" />
		</p>
	</form>
</div>

<?php
	}

	function media_buttons() {
		$title = __( 'Add Poll', 'polldaddy' );
		echo "<a href='admin.php?page=polls&amp;iframe&amp;TB_iframe=true' onclick='return false;' id='add_poll' class='thickbox' title='$title'><img src='{$this->base_url}polldaddy.png' alt='$title' /></a>";
	}
	
	function set_api_user_code(){
    $polldaddy = $this->get_client( WP_POLLDADDY__PARTNERGUID );	
		$polldaddy->reset();
		
		if ( empty( $this->user_code ) ){
      $this->user_code = $polldaddy->get_usercode( $this->id );
    } 
  }
  
	function management_page_load() {		
		wp_reset_vars( array( 'page', 'action', 'poll', 'style', 'rating', 'id' ) );
		global $plugin_page, $page, $action, $poll, $style, $rating, $id, $wp_locale; 		
    
    $this->set_api_user_code();

		if ( empty( $this->user_code ) && $page == 'polls' ){
      $action = 'signup';
    }		

		require_once WP_POLLDADDY__POLLDADDY_CLIENT_PATH;

		wp_enqueue_script( 'polls', "{$this->base_url}polldaddy.js", array( 'jquery', 'jquery-ui-sortable' ), $this->version );
		wp_enqueue_script( 'polls-common', "{$this->base_url}common.js", array(), $this->version );
		
		if( $page == 'polls' ) {		  
		  if ( !$this->is_author && in_array( $action, array( 'edit', 'edit-poll', 'create-poll', 'edit-style', 'create-style', 'list-styles', 'options', 'update-options', 'import-account' ) ) ) {//check user privileges has access to action
		    $action = '';
		  }
		  
			switch ( $action ) :
				case 'edit' :
				case 'edit-poll' :
				case 'create-poll' :
					wp_enqueue_script( 'polls-style', "http://i.polldaddy.com/js/poll-style-picker.js", array(), $this->version );
					
					if ( $action == 'create-poll' )
						$plugin_page = 'polls&amp;action=create-poll';
						
					break;
				case 'edit-style' :
				case 'create-style' :
					wp_enqueue_script( 'polls-style', "http://i.polldaddy.com/js/style-editor.js", array(), $this->version.mktime() );
					wp_enqueue_script( 'polls-style-color', "http://i.polldaddy.com/js/jquery/jscolor.js", array(), $this->version );
					wp_enqueue_style( 'polls', "{$this->base_url}style-editor.css", array(), $this->version );
					$plugin_page = 'polls&amp;action=list-styles';
					break;
				case 'list-styles' :
					$plugin_page = 'polls&amp;action=list-styles';
					break; 
				case 'options' :
        case 'update-options' : 
        case 'import-account' :
					$plugin_page = 'polls&amp;action=options';
					break;
			endswitch;
		} elseif( $page == 'ratings' ) {
		  if ( !$this->is_admin && !in_array( $action, array( 'reports', 'delete' ) ) ) {//check user privileges has access to action
		    $action = 'reports';
		  }
			switch ( $action ) :
				case 'delete' :
				case 'reports' :
					$plugin_page = 'ratings&amp;action=reports';
					break;
				default :	
					wp_enqueue_script( 'rating-text-color', "http://i.polldaddy.com/js/jquery/jscolor.js", array(), $this->version );
					wp_enqueue_script( 'ratings', 'http://i.polldaddy.com/ratings/rating.js', array(), $this->version );
					wp_localize_script( 'polls-common', 'adminRatingsL10n', array(
						'star_colors' => __( 'Star Colors', 'polldaddy' ), 'star_size' =>  __( 'Star Size', 'polldaddy' ),
				   		'nero_type' => __( 'Nero Type', 'polldaddy' ), 'nero_size' => __( 'Nero Size', 'polldaddy' ),	) );
			endswitch;
		}	
		
		wp_enqueue_script( 'admin-forms' );
		add_thickbox();

		wp_enqueue_style( 'polls', "{$this->base_url}polldaddy.css", array( 'global', 'wp-admin' ), $this->version );
		if ( isset($wp_locale->text_direction) && 'rtl' == $wp_locale->text_direction ) 
			wp_enqueue_style( 'polls-rtl', "{$this->base_url}polldaddy-rtl.css", array( 'global', 'wp-admin' ), $this->version );
		add_action( 'admin_body_class', array( &$this, 'admin_body_class' ) );

		add_action( 'admin_notices', array( &$this, 'management_page_notices' ) );

		$query_args = array();
		$args = array();
		
		$allowedtags = array(
			'a' => array(
				'href' => array (),
				'title' => array (),
				'target' => array ()),
			'img' => array(
				'alt' => array (),
				'align' => array (),
				'border' => array (),
				'class' => array (),
				'height' => array (),
				'hspace' => array (),
				'longdesc' => array (),
				'vspace' => array (),
				'src' => array (),
				'width' => array ()),
			'abbr' => array(
				'title' => array ()),
			'acronym' => array(
				'title' => array ()),
			'b' => array(),
			'blockquote' => array(
				'cite' => array ()),
			'cite' => array (),
			'em' => array (), 
			'i' => array (),
			'q' => array( 
				'cite' => array ()),
			'strike' => array(),
			'strong' => array()
		);

		$is_POST = 'post' == strtolower( $_SERVER['REQUEST_METHOD'] );

		if( $page == 'polls' ) {
			switch ( $action ) :
			case 'signup' : // sign up for first time
			case 'account' : // reauthenticate
			case 'import-account' : // reauthenticate
				if ( !$is_POST )
					return;   

				check_admin_referer( 'polldaddy-account' );

				if ( $new_args = $this->management_page_load_signup() )
					$query_args = array_merge( $query_args, $new_args );
				if ( $this->errors->get_error_codes() )
					return false;
					
				$query_args['message'] = 'imported-account';

				wp_reset_vars( array( 'action' ) );
				if ( !empty( $_GET['reaction'] ) )
					$query_args['action'] = $_GET['reaction'];
				elseif ( !empty( $_GET['action'] ) && 'account' == $_GET['action'] )
					$query_args['action'] = $_GET['action'];
				else
					$query_args['action'] = false;
				break;
			  
			case 'delete' :
				if ( empty( $poll ) )
					return;

				if ( is_array( $poll ) )
					check_admin_referer( 'action-poll_bulk' );
				else
					check_admin_referer( "delete-poll_$poll" );

				$polldaddy = $this->get_client( WP_POLLDADDY__PARTNERGUID, $this->user_code );

				foreach ( (array) $_REQUEST['poll'] as $poll_id ) {
					$polldaddy->reset();
					$poll_object = $polldaddy->get_poll( $poll );

					if ( !$this->can_edit( $poll_object ) ) {
						$this->errors->add( 'permission', __( 'You are not allowed to delete this poll.', 'polldaddy' ) );
						return false;
					}

					// Send Poll Author credentials
					if ( !empty( $poll_object->_owner ) && $this->id != $poll_object->_owner ) {
						$polldaddy->reset();
						if ( !$userCode = $polldaddy->get_usercode( $poll_object->_owner ) ) { 
							$this->errors->add( 'no_usercode', __( 'Invalid Poll Author', 'polldaddy' ) );
						}
						$polldaddy->userCode = $userCode;
					}

					$polldaddy->reset();
					$polldaddy->delete_poll( $poll_id );
				}

				$query_args['message'] = 'deleted';
				$query_args['deleted'] = count( (array) $poll );
				break;
			case 'open' :
				if ( empty( $poll ) )
					return;

				if ( is_array( $poll ) )
					check_admin_referer( 'action-poll_bulk' );
				else
					check_admin_referer( "open-poll_$poll" );

				$polldaddy = $this->get_client( WP_POLLDADDY__PARTNERGUID, $this->user_code );

				foreach ( (array) $_REQUEST['poll'] as $poll_id ) {
					$polldaddy->reset();
					$poll_object = $polldaddy->get_poll( $poll );

					if ( !$this->can_edit( $poll_object ) ) {
						$this->errors->add( 'permission', __( 'You are not allowed to open this poll.', 'polldaddy' ) );
						return false;
					}

					// Send Poll Author credentials
					if ( !empty( $poll_object->_owner ) && $this->id != $poll_object->_owner ) {
						$polldaddy->reset();
						if ( !$userCode = $polldaddy->get_usercode( $poll_object->_owner ) ) { 
							$this->errors->add( 'no_usercode', __( 'Invalid Poll Author', 'polldaddy' ) );
						}
						$polldaddy->userCode = $userCode;
					}

					$polldaddy->reset();
					$polldaddy->open_poll( $poll_id );
				}

				$query_args['message'] = 'opened';
				$query_args['opened'] = count( (array) $poll );
				break;
			case 'close' :
				if ( empty( $poll ) )
					return;

				if ( is_array( $poll ) )
					check_admin_referer( 'action-poll_bulk' );
				else
					check_admin_referer( "close-poll_$poll" );

				$polldaddy = $this->get_client( WP_POLLDADDY__PARTNERGUID, $this->user_code );

				foreach ( (array) $_REQUEST['poll'] as $poll_id ) {
					$polldaddy->reset();
					$poll_object = $polldaddy->get_poll( $poll );

					if ( !$this->can_edit( $poll_object ) ) {
						$this->errors->add( 'permission', __( 'You are not allowed to close this poll.', 'polldaddy' ) );
						return false;
					}

					// Send Poll Author credentials
					if ( !empty( $poll_object->_owner ) && $this->id != $poll_object->_owner ) {
						$polldaddy->reset();
						if ( !$userCode = $polldaddy->get_usercode( $poll_object->_owner ) ) { 
							$this->errors->add( 'no_usercode', __( 'Invalid Poll Author', 'polldaddy' ) );
						}
						$polldaddy->userCode = $userCode;
					}

					$polldaddy->reset();
					$polldaddy->close_poll( $poll_id );
				}

				$query_args['message'] = 'closed';
				$query_args['closed'] = count( (array) $poll );
				break;
			case 'edit-poll' : // TODO: use polldaddy_poll
				if ( !$is_POST || !$poll = (int) $poll )
					return;

				check_admin_referer( "edit-poll_$poll" );

				$polldaddy = $this->get_client( WP_POLLDADDY__PARTNERGUID, $this->user_code );
				$polldaddy->reset();

				$poll_object = $polldaddy->get_poll( $poll );
				$this->parse_errors( $polldaddy );

				if ( !$this->can_edit( $poll_object ) ) {
					$this->errors->add( 'permission', __( 'You are not allowed to edit this poll.', 'polldaddy' ) );
					return false;
				}

				// Send Poll Author credentials
				
				if ( !empty( $poll_object->_owner ) && $this->id != $poll_object->_owner ) {
					$polldaddy->reset();
					if ( !$userCode = $polldaddy->get_usercode( $poll_object->_owner ) ) {	
						$this->errors->add( 'no_usercode', __( 'Invalid Poll Author', 'polldaddy' ) );
					}
					$this->parse_errors( $polldaddy );
					$polldaddy->userCode = $userCode;
				}

				if ( !$poll_object ) 
					$this->errors->add( 'GetPoll', __( 'Poll not found', 'polldaddy' ) );

				if ( $this->errors->get_error_codes() )
					return false;

				$poll_data = get_object_vars( $poll_object );
				foreach ( $poll_data as $key => $value )
					if ( '_' === $key[0] )
						unset( $poll_data[$key] );

				foreach ( array( 'multipleChoice', 'randomiseAnswers', 'otherAnswer', 'sharing' ) as $option ) {
					if ( isset( $_POST[$option] ) && $_POST[$option] )
						$poll_data[$option] = 'yes';
					else
						$poll_data[$option] = 'no';
				}

				$blocks = array( 'off', 'cookie', 'cookieip' );
				if ( isset( $_POST['blockRepeatVotersType'] ) && in_array( $_POST['blockRepeatVotersType'], $blocks ) )
					$poll_data['blockRepeatVotersType'] = $_POST['blockRepeatVotersType'];

				$results = array( 'show', 'percent', 'hide' );
				if ( isset( $_POST['resultsType'] ) && in_array( $_POST['resultsType'], $results ) )
					$poll_data['resultsType'] = $_POST['resultsType'];
				$poll_data['question'] = stripslashes( $_POST['question'] );

				if ( empty( $_POST['answer'] ) || !is_array( $_POST['answer'] ) )
					$this->errors->add( 'answer', __( 'Invalid answers', 'polldaddy' ) );

				$answers = array();
				foreach ( $_POST['answer'] as $answer_id => $answer ) {
					if ( !$answer = trim( stripslashes( $answer ) ) )
						continue;
						
					$args['text'] = wp_kses( $answer, $allowedtags );
					
					if ( is_numeric( $answer_id ) )
						$answers[] = polldaddy_poll_answer( $args, $answer_id );
					else
						$answers[] = polldaddy_poll_answer( $args );
				}

				if ( 2 > count( $answers ) )
					$this->errors->add( 'answer', __( 'You must include at least 2 answers', 'polldaddy' ) );

				if ( $this->errors->get_error_codes() )
					return false;

				$poll_data['answers'] = $answers;
				
				$poll_data['question'] = wp_kses( $poll_data['question'], $allowedtags );
				
				if ( isset ( $_POST['styleID'] ) ){
					if ( $_POST['styleID'] == 'x' ){
						$this->errors->add( 'UpdatePoll', __( 'Please choose a poll style', 'polldaddy' ) );
						return false;
					}
				}
				$poll_data['styleID'] = (int) $_POST['styleID'];
				$poll_data['choices'] = (int) $_POST['choices'];
				
				if ( $poll_data['blockRepeatVotersType'] == 'cookie' ){
      		if( isset( $_POST['cookieip_expiration'] ) )
      			$poll_data['blockExpiration'] = (int) $_POST['cookieip_expiration'];
      	} elseif ( $poll_data['blockRepeatVotersType'] == 'cookieip' ){
      		if( isset( $_POST['cookieip_expiration'] ) )
      			$poll_data['blockExpiration'] = (int) $_POST['cookieip_expiration'];
      	}

				$polldaddy->reset();

				$update_response = $polldaddy->update_poll( $poll, $poll_data );

				$this->parse_errors( $polldaddy );

				if ( !$update_response )
					$this->errors->add( 'UpdatePoll', __( 'Poll could not be updated', 'polldaddy' ) );

				if ( $this->errors->get_error_codes() )
					return false;

				$query_args['message'] = 'updated';
				if ( isset($_POST['iframe']) )
					$query_args['iframe'] = '';
				break;
			case 'create-poll' :
				if ( !$is_POST )
					return;

				check_admin_referer( 'create-poll' );

				$polldaddy = $this->get_client( WP_POLLDADDY__PARTNERGUID, $this->user_code );
				$polldaddy->reset();

				$answers = array();
				foreach ( $_POST['answer'] as $answer ){
					if ( !$answer = trim( stripslashes( $answer ) ) )
						continue;

					$args['text'] = wp_kses( $answer, $allowedtags );

					$answers[] = polldaddy_poll_answer( $args );
				}

				if ( !$answers )
					return false;

				$poll_data = _polldaddy_poll_defaults();
				
				foreach ( array( 'multipleChoice', 'randomiseAnswers', 'otherAnswer', 'sharing' ) as $option ) {
					if ( isset( $_POST[$option] ) && $_POST[$option] )
						$poll_data[$option] = 'yes';
					else
						$poll_data[$option] = 'no';
				}

				$blocks = array( 'off', 'cookie', 'cookieip' );
				if ( isset( $_POST['blockRepeatVotersType'] ) && in_array( $_POST['blockRepeatVotersType'], $blocks ) )
					$poll_data['blockRepeatVotersType'] = $_POST['blockRepeatVotersType'];

				$results = array( 'show', 'percent', 'hide' );
				if ( isset( $_POST['resultsType'] ) && in_array( $_POST['resultsType'], $results ) )
					$poll_data['resultsType'] = $_POST['resultsType'];

				$poll_data['answers'] = $answers;
				
				$poll_data['question'] = stripslashes( $_POST['question'] );
				$poll_data['question'] = wp_kses( $poll_data['question'], $allowedtags );
				
				if ( isset ( $_POST['styleID'] ) ){
					if ( $_POST['styleID'] == 'x' ){
				        $this->errors->add( 'UpdatePoll', __( 'Please choose a poll style', 'polldaddy' ) );
				        return false;
					}
				}
				$poll_data['styleID'] = (int) $_POST['styleID'];
				$poll_data['choices'] = (int) $_POST['choices']; 
				
				if ( $poll_data['blockRepeatVotersType'] == 'cookie' ){
      		if( isset( $_POST['cookieip_expiration'] ) )
      			$poll_data['blockExpiration'] = (int) $_POST['cookieip_expiration'];
      	} elseif ( $poll_data['blockRepeatVotersType'] == 'cookieip' ){
      		if( isset( $_POST['cookieip_expiration'] ) )
      			$poll_data['blockExpiration'] = (int) $_POST['cookieip_expiration'];
      	}
				
				$poll = $polldaddy->create_poll( $poll_data );
				$this->parse_errors( $polldaddy );

				if ( !$poll || empty( $poll->_id ) )
					$this->errors->add( 'CreatePoll', __( 'Poll could not be created', 'polldaddy' ) );

				if ( $this->errors->get_error_codes() )
					return false;

				$query_args['message'] = 'created';
				$query_args['action'] = 'edit-poll';
				$query_args['poll'] = $poll->_id;
				if ( isset($_POST['iframe']) )
					$query_args['iframe'] = '';
				break;
			case 'delete-style' :
				if ( empty( $style ) )
					return;

				if ( is_array( $style ) )
					check_admin_referer( 'action-style_bulk' );
				else
					check_admin_referer( "delete-style_$style" );

				$polldaddy = $this->get_client( WP_POLLDADDY__PARTNERGUID, $this->user_code );

				foreach ( (array) $_REQUEST['style'] as $style_id ) {
					$polldaddy->reset();
					$polldaddy->delete_style( $style_id );
				}

				$query_args['message'] = 'deleted-style';
				$query_args['deleted'] = count( (array) $style );
				break;
			case 'edit-style' :
				if ( !$is_POST || !$style = (int) $style )
					return;
				
				check_admin_referer( "edit-style$style" );

				$polldaddy = $this->get_client( WP_POLLDADDY__PARTNERGUID, $this->user_code );
				$polldaddy->reset();

				$style_data = _polldaddy_style_defaults();

				if ( isset($_POST['style-title'] ) )
					$style_data['title'] = stripslashes( trim ( (string) $_POST['style-title'] ) ); 

				if ( isset($_POST['CSSXML'] ) )
					$style_data['css'] = urlencode( stripslashes( trim ( (string) $_POST['CSSXML'] ) ) );
					
				if ( isset($_REQUEST['updatePollCheck'] ) && $_REQUEST['updatePollCheck'] == 'on' )
    				$style_data['retro'] = 1;

				$update_response = $polldaddy->update_style( $style, $style_data );

				$this->parse_errors( $polldaddy );

				if ( !$update_response )
					$this->errors->add( 'UpdateStyle', __( 'Style could not be updated', 'polldaddy' ) );

				if ( $this->errors->get_error_codes() )
					return false;

				$query_args['message'] = 'updated-style';
				if ( isset($_POST['iframe']) )
					$query_args['iframe'] = '';
				break;
			case 'create-style' :
				if ( !$is_POST )
					return;
				
				check_admin_referer( 'create-style' );

				$polldaddy = $this->get_client( WP_POLLDADDY__PARTNERGUID, $this->user_code );
				$polldaddy->reset();

				$style_data = _polldaddy_style_defaults();

				if ( isset($_POST['style-title'] ) )
					$style_data['title'] = stripslashes( strip_tags( trim ( (string) $_POST['style-title'] ) ) ); 

				if ( isset($_POST['CSSXML'] ) )
					$style_data['css'] = urlencode( stripslashes( trim ( (string) $_POST['CSSXML'] ) ) );

				$style = $polldaddy->create_style( $style_data );
				$this->parse_errors( $polldaddy );
				
				if ( !$style || empty( $style->_id ) )
					$this->errors->add( 'CreateStyle', __( 'Style could not be created', 'polldaddy' ) );

				if ( $this->errors->get_error_codes() )
					return false;

				$query_args['message'] = 'created-style';
				$query_args['action'] = 'edit-style';
				$query_args['style'] = $style->_id;
				if ( isset($_POST['iframe']) )
					$query_args['iframe'] = '';
				break;
			case 'update-options' :
			  if ( !$is_POST )
					return;
				
				check_admin_referer( 'polldaddy-account' );
				
				$polldaddy = $this->get_client( WP_POLLDADDY__PARTNERGUID, $this->user_code );
				$polldaddy->reset();

				$poll_defaults = _polldaddy_poll_defaults();
				
				$user_defaults = array();
        
        foreach( array( "multipleChoice", "randomiseAnswers", "otherAnswer", "sharing", "resultsType", "styleID", "blockRepeatVotersType", "blockExpiration" ) as $option ){
          if ( isset( $poll_defaults[$option] ) && $poll_defaults[$option] )
						$user_defaults[$option] = $poll_defaults[$option];    
        }
				
				foreach ( array( 'multipleChoice', 'randomiseAnswers', 'otherAnswer', 'sharing' ) as $option ) {
					if ( isset( $_POST[$option] ) && $_POST[$option] )
						$user_defaults[$option] = 'yes';
					else
						$user_defaults[$option] = 'no';
				}

				$results = array( 'show', 'percent', 'hide' );
				if ( isset( $_POST['resultsType'] ) && in_array( $_POST['resultsType'], $results ) )
					$user_defaults['resultsType'] = $_POST['resultsType'];   
				
				if ( isset ( $_POST['styleID'] ) ){
					$user_defaults['styleID'] = (int) $_POST['styleID'];
				} 

				$blocks = array( 'off', 'cookie', 'cookieip' );
				if ( isset( $_POST['blockRepeatVotersType'] ) && in_array( $_POST['blockRepeatVotersType'], $blocks ) )
					$user_defaults['blockRepeatVotersType'] = $_POST['blockRepeatVotersType'];
				
      	if( isset( $_POST['blockExpiration'] ) )
      		$user_defaults['blockExpiration'] = (int) $_POST['blockExpiration'];
      	
      	$polldaddy->update_poll_defaults( 0, $user_defaults );
				
				$this->parse_errors( $polldaddy );  
				if ( $this->errors->get_error_codes() )
					return false;
        
        $query_args['message'] = 'updated-options';
        break; 
			default :
				return;
			endswitch;
		} elseif( $page == 'ratings' ) {
			
			switch ( $action ) :
			case 'delete' :
				if ( empty( $id ) )
					return;
				if ( empty( $rating ) )
					return;
					
				$polldaddy = $this->get_client( WP_POLLDADDY__PARTNERGUID, $this->rating_user_code );
					
				if ( is_array( $rating ) ) {
					check_admin_referer( 'action-rating_bulk' );
					
					foreach( $rating as $key => $value ){
						$polldaddy->reset();
						$polldaddy->delete_rating_result( $id, $value );
					}
				} else {
					check_admin_referer( "delete-rating_$rating" );
					
					$polldaddy->delete_rating_result( $id, $rating );
				}

				if ( isset( $_REQUEST['filter'] ) )
					$query_args['filter'] = $_REQUEST['filter'];
				if ( isset( $_REQUEST['change-report-to'] ) )
					$query_args['change-report-to'] = $_REQUEST['change-report-to'];
				$query_args['message'] = 'deleted-rating';
				$query_args['deleted'] = count( (array) $rating );
				break;
			default :				
				return;
			endswitch;
		}
		
		wp_redirect( add_query_arg( $query_args, wp_get_referer() ) );
		exit;
	}

	function management_page_load_signup() {
		switch ( $_POST['account'] ) :
		case 'import' :
		  return $this->import_account();
			break;
		default :
			return;
		endswitch;
	}
	
	function import_account(){
    $polldaddy = $this->get_client( WP_POLLDADDY__PARTNERGUID );
		$polldaddy->reset();
		$email = trim( stripslashes( $_POST['polldaddy_email'] ) );
		$password = trim( stripslashes( $_POST['polldaddy_password'] ) );
    
    if ( !is_email( $email ) )
			$this->errors->add( 'polldaddy_email', __( 'Email address required', 'polldaddy' ) );

		if ( !$password )
			$this->errors->add( 'polldaddy_password', __( 'Password required', 'polldaddy' ) );

		if ( $this->errors->get_error_codes() )
			return false;

		if ( $usercode = $polldaddy->initiate( $email, $password, $this->id ) ) {
		  $this->user_code = $usercode;
    } else {	 
			$this->parse_errors( $polldaddy );
			$this->errors->add( 'import-account', __( 'Account could not be imported.  Are your email address and password correct?', 'polldaddy' ) );			
			return false;
    }      		
  }

	function admin_body_class( $class ) {
		if ( isset( $_GET['iframe'] ) )
			$class .= 'poll-preview-iframe ';
		if ( isset( $_GET['TB_iframe'] ) )
			$class .= 'poll-preview-iframe-editor ';
		return $class;
	}

	function management_page_notices( $message = false ) { 		
		switch ( (string) @$_GET['message'] ) :
		case 'deleted' :
			$deleted = (int) $_GET['deleted'];
			if ( 1 == $deleted )
				$message = __( 'Poll deleted.', 'polldaddy' );
			else
				$message = sprintf( __ngettext( '%s Poll Deleted.', '%s Polls Deleted.', $deleted ), number_format_i18n( $deleted ) );
			break;
		case 'opened' :
			$opened = (int) $_GET['opened'];
			if ( 1 == $opened )
				$message = __( 'Poll opened.', 'polldaddy' );
			else
				$message = sprintf( __ngettext( '%s Poll Opened.', '%s Polls Opened.', $opened ), number_format_i18n( $opened ) );
			break;
		case 'closed' :
			$closed = (int) $_GET['closed'];
			if ( 1 == $closed )
				$message = __( 'Poll closed.', 'polldaddy' );
			else
				$message = sprintf( __ngettext( '%s Poll Closed.', '%s Polls Closed.', $closed ), number_format_i18n( $closed ) );
			break;
		case 'updated' :
			$message = __( 'Poll updated.', 'polldaddy' );
			break;
		case 'created' :
			$message = __( 'Poll created.', 'polldaddy' );
			if ( isset( $_GET['iframe'] ) )
				$message .= ' <input type="button" class="button polldaddy-send-to-editor" value="' . attribute_escape( __( 'Send to Editor', 'polldaddy' ) ) . '" />';
			break;
		case 'updated-style' :
			$message = __( 'Custom Style updated.', 'polldaddy' );
			break;
		case 'created-style' :
			$message = __( 'Custom Style created.', 'polldaddy' );
			break;
		case 'deleted-style' :
			$deleted = (int) $_GET['deleted'];
			if ( 1 == $deleted )
				$message = __( 'Custom Style deleted.', 'polldaddy' );
			else
				$message = sprintf( __ngettext( '%s Style Deleted.', '%s Custom Styles Deleted.', $deleted ), number_format_i18n( $deleted ) );
			break;
		case 'imported-account' :
			$message = __( 'Account Imported.', 'polldaddy' );
			break;
		case 'updated-options' :
			$message = __( 'Options Updated.', 'polldaddy' );
			break;
		case 'deleted-rating' :
			$deleted = (int) $_GET['deleted'];
			if ( 1 == $deleted )
				$message = __( 'Rating deleted.', 'polldaddy' );
			else
				$message = sprintf( __ngettext( '%s Rating Deleted.', '%s Ratings Deleted.', $deleted ), number_format_i18n( $deleted ) );
			break;
		endswitch;

		$is_POST = 'post' == strtolower( $_SERVER['REQUEST_METHOD'] );

		if ( $is_POST ) {
			switch ( $GLOBALS['action'] ) :
			case 'create-poll' :
				$message = __( 'Error: An error has occurred;  Poll not created.', 'polldaddy' );
				break;
			case 'edit-poll' :
				$message = __( 'Error: An error has occurred;  Poll not updated.', 'polldaddy' );
				break;
			case 'account' :
				if ( 'import' == $_POST['account'] )
					$message = __( 'Error: An error has occurred;  Account could not be imported.  Perhaps your email address or password is incorrect?', 'polldaddy' );
				else
					$message = __( 'Error: An error has occurred;  Account could not be created.', 'polldaddy' );
				break;
			endswitch;
		}

		if ( !$message )
			return;
?>
		<div class='updated'><p><?php echo $message; ?></p></div>
<?php
		$this->print_errors();
	}

	function management_page() {
		global $page, $action, $poll, $style, $rating; 
		$poll = (int) $poll;
		$style = (int) $style;
		$rating = wp_specialchars( $rating );
?>

	<div class="wrap" id="manage-polls">

<?php
	if( $page == 'polls' ) { 		  
		if ( !$this->is_author && in_array( $action, array( 'edit', 'edit-poll', 'create-poll', 'edit-style', 'create-style', 'list-styles', 'options', 'update-options', 'import-account' ) ) ) {//check user privileges has access to action
	    $action = '';
	  }
		switch ( $action ) :
		case 'signup' :
		case 'account' :
			$this->signup();
			break;
		case 'preview' :
?>

		<h2 id="preview-header"><?php
    if( $this->is_author )
      printf( __( 'Poll Preview (<a href="%s">Edit Poll</a>, <a href="%s">List Polls</a>)', 'polldaddy' ),
			 clean_url( add_query_arg( array( 'action' => 'edit', 'poll' => $poll, 'message' => false ) ) ),
			 clean_url( add_query_arg( array( 'action' => false, 'poll' => false, 'message' => false ) ) ));
    else
      printf( __( 'Poll Preview (<a href="%s">List Polls</a>)', 'polldaddy'), clean_url( add_query_arg( array( 'action' => false, 'poll' => false, 'message' => false ) ) ) ); ?></h2>

<?php
			echo do_shortcode( "[polldaddy poll=$poll cb=1]" );
			break;
		case 'results' :
?>

		<h2><?php 
    if( $this->is_author )
      printf( __( 'Poll Results (<a href="%s">Edit Poll</a>)', 'polldaddy' ), clean_url( add_query_arg( array( 'action' => 'edit', 'poll' => $poll, 'message' => false ) ) ) ); 
    else
      printf( __( 'Poll Results (<a href="%s">List Polls</a>)', 'polldaddy'), clean_url( add_query_arg( array( 'action' => false, 'poll' => false, 'message' => false ) ) ) ); ?></h2>

<?php
			$this->poll_results_page( $poll );
			break;
		case 'edit' :
		case 'edit-poll' :
?>

		<h2><?php printf( __('Edit Poll (<a href="%s">List Polls</a>)', 'polldaddy'), clean_url( add_query_arg( array( 'action' => false, 'poll' => false, 'message' => false ) ) ) ); ?></h2>

<?php

			$this->poll_edit_form( $poll );
			break;
		case 'create-poll' :
?>

		<h2><?php printf( __('Create Poll (<a href="%s">List Polls</a>)', 'polldaddy'), clean_url( add_query_arg( array( 'action' => false, 'poll' => false, 'message' => false ) ) ) ); ?></h2>

<?php
			$this->poll_edit_form();
			break;
		case 'list-styles' :
?>

		<h2><?php 
    if( $this->is_author )
      printf( __('Custom Styles (<a href="%s">Add New</a>)', 'polldaddy'), clean_url( add_query_arg( array( 'action' => 'create-style', 'poll' => false, 'message' => false ) ) ) ); 
    else
      _e('Custom Styles', 'polldaddy'); ?></h2>

<?php
			$this->styles_table();
			break;
		case 'edit-style' :
?>

		<h2><?php printf( __('Edit Style (<a href="%s">List Styles</a>)', 'polldaddy'), clean_url( add_query_arg( array( 'action' => 'list-styles', 'style' => false, 'message' => false, 'preload' => false ) ) ) ); ?></h2>

<?php

			$this->style_edit_form( $style );
			break;
		case 'create-style' :
?>

		<h2><?php printf( __('Create Style (<a href="%s">List Styles</a>)', 'polldaddy'), clean_url( add_query_arg( array( 'action' => 'list-styles', 'style' => false, 'message' => false, 'preload' => false ) ) ) ); ?></h2>

<?php
			$this->style_edit_form();
			break;
		case 'options' :
    case 'import-account' :
    case 'update-options' :		  
		  $this->plugin_options();
      break;
		default :

?>

		<h2 id="poll-list-header"><?php 
    if( $this->is_author )
      printf( __( 'Polls (<a href="%s">Add New</a>)', 'polldaddy' ), clean_url( add_query_arg( array('action' => 'create-poll','poll' => false,'message' => false) ) ) );
    else
      _e( 'Polls', 'polldaddy'); ?></h2>

<?php 
			$this->polls_table( isset( $_GET['view'] ) && 'user' == $_GET['view'] ? 'user' : 'blog' );
		endswitch;
	} elseif( $page == 'ratings' ) {
	  if ( !$this->is_admin && !in_array( $action, array( 'delete', 'reports' ) ) ) {//check user privileges has access to action
		  $action = 'reports';
		}
	   
		switch ( $action ) :
		case 'delete' :
		case 'reports' :
			$this->rating_reports();
			break;
		case 'update-rating' :
			$this->update_rating();
			$this->rating_settings( $action );
			break;
		default :
			$this->rating_settings();
		endswitch;
	}
?>

	</div>

<?php

	}

	function polls_table( $view = 'blog' ) {
		$page = 1;
		if ( isset( $_GET['paged'] ) )
			$page = absint($_GET['paged']);
		$polldaddy = $this->get_client( WP_POLLDADDY__PARTNERGUID, $this->user_code );
		$polldaddy->reset();
		
		if( !$this->is_author )
      $view = '';     
    
		if ( 'user' == $view )
			$polls_object = $polldaddy->get_polls( ( $page - 1 ) * 10 + 1, $page * 10 );
		else
			$polls_object = $polldaddy->get_polls_by_parent_id( ( $page - 1 ) * 10 + 1, $page * 10 );
		$this->parse_errors( $polldaddy );
		$this->print_errors();
		$polls = & $polls_object->poll;
		if( isset( $polls_object->_total ) )
			$total_polls = $polls_object->_total;
		else
			$total_polls = count( $polls );
		$class = '';

		$page_links = paginate_links( array(
			'base' => add_query_arg( 'paged', '%#%' ),
			'format' => '',
			'total' => ceil( $total_polls / 10 ),
			'current' => $page
		) );

    if( $this->is_author ){ ?>
		<ul class="subsubsub">
			<li><a href="<?php echo clean_url( add_query_arg( array( 'view' => false, 'paged' => false ) ) ); ?>"<?php if ( 'blog' == $view ) echo ' class="current"'; ?>><?php _e( "All Blog's Polls", 'polldaddy' ); ?></a> | </li>
			<li><a href="<?php echo clean_url( add_query_arg( array( 'view' => 'user', 'paged' => false ) ) ); ?>"<?php if ( 'user' == $view ) echo ' class="current"'; ?>><?php _e( "All My Polls", 'polldaddy' ); ?></a></li>
		</ul>
	  <?php } ?>
		<form method="post" action="">
<?php	if( $this->is_author ){ ?>
		<div class="tablenav">
			<div class="alignleft">
				<select name="action">
					<option selected="selected" value=""><?php _e( 'Actions', 'polldaddy' ); ?></option>
					<option value="delete"><?php _e( 'Delete', 'polldaddy' ); ?></option>
					<option value="close"><?php _e( 'Close', 'polldaddy' ); ?></option>
					<option value="open"><?php _e( 'Open', 'polldaddy' ); ?></option>
				</select>
				<input class="button-secondary action" type="submit" name="doaction" value="<?php _e( 'Apply', 'polldaddy' ); ?>" />
				<?php wp_nonce_field( 'action-poll_bulk' ); ?>
			</div>
			<div class="tablenav-pages"><?php echo $page_links; ?></div>
		</div>
		<br class="clear" />
<?php } ?>
		<table class="widefat">
			<thead>
				<tr>
          <th id="cb" class="manage-column column-cb check-column" scope="col" /><?php if( $this->is_author ){ ?><input type="checkbox" /><?php } ?></th>
					<th id="title" class="manage-column column-title" scope="col"><?php _e( 'Poll', 'polldaddy' ); ?></th>
					<th id="votes" class="manage-column column-vote num" scope="col"><?php _e( 'Votes', 'polldaddy' ); ?></th>
					<th id="date" class="manage-column column-date" scope="col"><?php _e( 'Created', 'polldaddy' ); ?></th>
				</tr>
			</thead>
			<tbody>

<?php
		if ( $polls ) : 		  
			foreach ( $polls as $poll ) :
				$poll_id = (int) $poll->_id;
				
				$poll->___content = trim( strip_tags( $poll->___content ) );
        if( strlen( $poll->___content ) == 0 ){
        	$poll->___content = '-- empty HTML tag --';
        }				
				
				$poll_closed = (int) $poll->_closed;

				if ( $this->is_author and $this->can_edit( $poll ) ) {  
					$edit_link = clean_url( add_query_arg( array( 'action' => 'edit', 'poll' => $poll_id, 'message' => false ) ) ); 
				  $delete_link = clean_url( wp_nonce_url( add_query_arg( array( 'action' => 'delete', 'poll' => $poll_id, 'message' => false ) ), "delete-poll_$poll_id" ) );
				  $open_link = clean_url( wp_nonce_url( add_query_arg( array( 'action' => 'open', 'poll' => $poll_id, 'message' => false ) ), "open-poll_$poll_id" ) );
				  $close_link = clean_url( wp_nonce_url( add_query_arg( array( 'action' => 'close', 'poll' => $poll_id, 'message' => false ) ), "close-poll_$poll_id" ) );
        }
				else {
				  $edit_link = false; 
				  $delete_link = false;
				  $open_link = false;
				  $close_link = false;
        }             					

				$class = $class ? '' : ' class="alternate"';
				$results_link = clean_url( add_query_arg( array( 'action' => 'results', 'poll' => $poll_id, 'message' => false ) ) );
				$preview_link = clean_url( add_query_arg( array( 'action' => 'preview', 'poll' => $poll_id, 'message' => false ) ) ); //, 'iframe' => '', 'TB_iframe' => 'true' ) ) );
				list($poll_time) = explode( '.', $poll->_created );
				$poll_time = strtotime( $poll_time );
?>
				<tr<?php echo $class; ?>>
					<th class="check-column" scope="row"><?php if( $this->is_author and $this->can_edit( $poll ) ){ ?><input type="checkbox" value="<?php echo (int) $poll_id; ?>" name="poll[]" /><?php } ?></th>
					<td class="post-title column-title">
<?php	if ( $edit_link ) { ?>
						<strong><a class="row-title" href="<?php echo $edit_link; ?>"><?php echo wp_specialchars( $poll->___content ); ?></a></strong>
						<div class="row-actions">
						<span class="edit"><a href="<?php echo $edit_link; ?>"><?php _e( 'Edit', 'polldaddy' ); ?></a> | </span>
<?php	} else { ?>
						<strong><?php echo wp_specialchars( $poll->___content ); ?></strong>
						<div class="row-actions">
<?php	} ?>
						<span class="results"><a href="<?php echo $results_link; ?>"><?php _e( 'Results', 'polldaddy' ); ?></a> | </span>
<?php	if ( $delete_link ) { ?>
						<span class="delete"><a class="delete-poll delete" href="<?php echo $delete_link; ?>"><?php _e( 'Delete', 'polldaddy' ); ?></a> | </span>
<?php	} 
      if ( $poll_closed == 2 ) { 
          if ( $open_link ) { ?> 
						<span class="open"><a class="open-poll" href="<?php echo $open_link; ?>"><?php _e( 'Open', 'polldaddy' ); ?></a> | </span>	
<?php } } else {  
          if ( $close_link ) { ?>
						<span class="close"><a class="close-poll" href="<?php echo $close_link; ?>"><?php _e( 'Close', 'polldaddy' ); ?></a> | </span>
<?php } } ?>
<?php if ( isset( $_GET['iframe'] ) ) { ?>
						<span class="view"><a href="<?php echo $preview_link; ?>"><?php _e( 'Preview', 'polldaddy' ); ?></a> | </span>
						<span class="editor">
							<a href="#" class="polldaddy-send-to-editor"><?php _e( 'Send to editor', 'polldaddy' ); ?></a>
							<input type="hidden" class="polldaddy-poll-id hack" value="<?php echo (int) $poll_id; ?>" /> |
						</span>
<?php } else { ?>
						<span class="view"><a class="thickbox" href="<?php echo $preview_link; ?>"><?php _e( 'Preview', 'polldaddy' ); ?></a> | </span>
<?php } ?>
					<span class="shortcode"><a href="#" class="polldaddy-show-shortcode"><?php _e( 'Share-Embed', 'polldaddy' ); ?></a></span>
<?php $this->poll_table_add_option( $poll_id ); ?>
          	</div>
          </td>
                                        <td class="poll-votes column-vote num"><?php echo number_format_i18n( $poll->_responses ); ?></td>
                                        <td class="date column-date"><abbr title="<?php echo date( __('Y/m/d g:i:s A', 'polldaddy'), $poll_time ); ?>"><?php echo date( __('Y/m/d', 'polldaddy'), $poll_time ); ?></abbr></td>
                                </tr>
                                <tr class="polldaddy-shortcode-row" style="display: none;">
                                        <td colspan="4">
                                                <h4><?php _e( 'WordPress Shortcode', 'polldaddy' ); ?></h4>
                                                <input type="text" readonly="readonly" style="width: 175px;" onclick="this.select();" value="[polldaddy poll=<?php echo (int) $poll_id; ?>]"/>

                                                <h4><?php _e( 'JavaScript', 'polldaddy' ); ?></h4>
                                                <pre>&lt;script type="text/javascript" language="javascript"
  src="http://static.polldaddy.com/p/<?php echo (int) $poll_id; ?>.js"&gt;&lt;/script&gt;
&lt;noscript&gt;
 &lt;a href="http://polldaddy.com/poll/<?php echo (int) $poll_id; ?>/"&gt;<?php echo trim( strip_tags( $poll->___content ) ); ?>&lt;/a&gt;&lt;br/&gt;
 &lt;span style="font:9px;"&gt;(&lt;a href="http://www.polldaddy.com"&gt;polls&lt;/a&gt;)&lt;/span&gt;
&lt;/noscript&gt;</pre>
<h4><?php _e( 'Short URL (Good for Twitter etc.)', 'polldaddy' ); ?></h4>
<input type="text" readonly="readonly" style="width: 175px;" onclick="this.select();" value="http://poll.fm/<?php echo base_convert( $poll_id, 10, 36 ); ?>"/>
<h4><?php _e( 'Facebook URL', 'polldaddy' ); ?></h4>
<input type="text" readonly="readonly" style="width: 175px;" onclick="this.select();" value="http://poll.fm/f/<?php echo base_convert( $poll_id, 10, 36 ); ?>"/>
					</td>
				</tr>

<?php
			endforeach;
		elseif ( $total_polls ) : // $polls
?>

				<tr>
					<td colspan="4"><?php printf( __( 'What are you doing here?  <a href="%s">Go back</a>.', 'polldaddy' ), clean_url( add_query_arg( 'paged', false ) ) ); ?></td>
				</tr>

<?php
		else : // $polls
?>

				<tr>
					<td colspan="4"><?php 
          if( $this->is_author )
            printf( __( 'No polls yet.  <a href="%s">Create one</a>', 'polldaddy' ), clean_url( add_query_arg( array( 'action' => 'create-poll' ) ) ) ); 
          else
            _e( 'No polls yet.', 'polldaddy' ); ?></td>
				</tr>
<?php		endif; // $polls ?>

			</tbody>
		</table>
		<?php $this->poll_table_extra(); ?>
		</form>
		<div class="tablenav">
			<div class="tablenav-pages"><?php echo $page_links; ?></div>
		</div>
		<br class="clear" />
		<script language="javascript">
		jQuery( document ).ready(function(){ 
			plugin = new Plugin( {
				delete_rating: '<?php _e( 'Are you sure you want to delete the rating for "%s"?','polldaddy'); ?>',
				delete_poll: '<?php _e( 'Are you sure you want to delete "%s"?','polldaddy'); ?>',
				delete_answer: '<?php _e( 'Are you sure you want to delete this answer?','polldaddy'); ?>',
				delete_answer_title: '<?php _e( 'delete this answer','polldaddy'); ?>',
				standard_styles: '<?php _e( 'Standard Styles','polldaddy'); ?>',
				custom_styles: '<?php _e( 'Custom Styles','polldaddy'); ?>'
			} );
		});
		</script>

<?php
	}
	
	function poll_table_add_option(){}
	
	function poll_table_extra(){}

	function poll_edit_form( $poll_id = 1 ) {
		$poll_id = (int) $poll_id;

		$polldaddy = $this->get_client( WP_POLLDADDY__PARTNERGUID, $this->user_code );
		$polldaddy->reset();

		$is_POST = 'post' == strtolower( $_SERVER['REQUEST_METHOD'] );

		if ( $poll_id ) {
			$poll = $polldaddy->get_poll( $poll_id );
			$this->parse_errors( $polldaddy );

			if ( !$this->can_edit( $poll ) ) {
				$this->errors->add( 'permission', __( 'You are not allowed to edit this poll.', 'polldaddy' ) );
			}
			
			if( $poll_id == 1 ){
        $poll->answers = array();
        $poll_id = 0;
      }
			  
		} else {        
			$poll = polldaddy_poll( array(), null, false );   
		}

		$question = $is_POST ? attribute_escape( stripslashes( $_POST['question'] ) ) : attribute_escape( $poll->question );

		$this->print_errors();
?>

<form action="" method="post">
<div id="poststuff"><div id="post-body" class="has-sidebar has-right-sidebar">

<div class="inner-sidebar" id="side-info-column">
	<div id="submitdiv" class="postbox">
		<h3><?php _e( 'Publish', 'polldaddy' ); ?></h3>
		<div class="inside">
			<div id="major-publishing-actions">
				<p id="publishing-action">
					<?php wp_nonce_field( $poll_id ? "edit-poll_$poll_id" : 'create-poll' ); ?>
					<input type="hidden" name="action" value="<?php echo $poll_id ? 'edit-poll' : 'create-poll'; ?>" />
					<input type="hidden" class="polldaddy-poll-id" name="poll" value="<?php echo $poll_id; ?>" />
					<input type="submit" class="button-primary" value="<?php echo attribute_escape( __( 'Save Poll', 'polldaddy' ) ); ?>" />

<?php if ( isset( $_GET['iframe'] ) && $poll_id ) : ?>

					<input type="button" class="button polldaddy-send-to-editor" value="<?php echo attribute_escape( __( 'Send to Editor', 'polldaddy' ) ); ?>" />

<?php endif; ?>

				</p>
				<br class="clear" />
			</div>
		</div>
	</div>

	<div class="postbox">
		<h3><?php _e( 'Poll results', 'polldaddy' ); ?></h3>
		<div class="inside">
			<ul class="poll-options">

<?php
			foreach ( array( 'show' => __( 'Show results to voters', 'polldaddy' ), 'percent' => __( 'Only show percentages', 'polldaddy' ), 'hide' => __( 'Hide all results', 'polldaddy' ) ) as $value => $label ) :
				if ( $is_POST )
					$checked = $value === $_POST['resultsType'] ? ' checked="checked"' : '';
				else
					$checked = $value === $poll->resultsType ? ' checked="checked"' : '';
?>

				<li>
				<label for="resultsType-<?php echo $value; ?>"><input type="radio"<?php echo $checked; ?> value="<?php echo $value; ?>" name="resultsType" id="resultsType-<?php echo $value; ?>" /> <?php echo wp_specialchars( $label ); ?></label>
				</li>

<?php			endforeach; ?>

			</ul>
		</div>
	</div>

	<div class="postbox">
		<h3><?php _e( 'Block repeat voters', 'polldaddy' ); ?></h3>
		<div class="inside">
			<ul class="poll-options">

<?php
			foreach ( array( 'off' => __( "Don't block repeat voters", 'polldaddy' ), 'cookie' => __( 'Block by cookie (recommended)', 'polldaddy' ), 'cookieip' => __( 'Block by cookie and by IP address', 'polldaddy' ) ) as $value => $label ) :
				if ( $is_POST )
					$checked = $value === $_POST['blockRepeatVotersType'] ? ' checked="checked"' : '';
				else
					$checked = $value === $poll->blockRepeatVotersType ? ' checked="checked"' : '';
?>

				<li>
					<label for="blockRepeatVotersType-<?php echo $value; ?>"><input class="block-repeat" type="radio"<?php echo $checked; ?> value="<?php echo $value; ?>" name="blockRepeatVotersType" id="blockRepeatVotersType-<?php echo $value; ?>" /> <?php echo wp_specialchars( $label ); ?></label>
				</li>

<?php			endforeach; ?>

			</ul>
										
			<span style="margin:6px 6px 8px;" id="cookieip_expiration_label"><label><?php _e( 'Expires: ', 'polldaddy' ); ?></label></span>
			<select id="cookieip_expiration" name="cookieip_expiration" style="width: auto;<?php echo $poll->blockRepeatVotersType == 'off' ? 'display:none;' : ''; ?>">
				<option value="0" <?php echo (int) $poll->blockExpiration == 0 ? 'selected' : ''; ?>><?php _e( 'Never', 'polldaddy' ); ?></option>
				<option value="3600" <?php echo (int) $poll->blockExpiration == 3600 ? 'selected' : ''; ?>><?php printf( __('%d hour', 'polldaddy'), 1 ); ?></option>
				<option value="10800" <?php echo (int) $poll->blockExpiration == 10800 ? 'selected' : ''; ?>><?php printf( __('%d hours', 'polldaddy'), 3 ); ?></option>
				<option value="21600" <?php echo (int) $poll->blockExpiration == 21600 ? 'selected' : ''; ?>><?php printf( __('%d hours', 'polldaddy'), 6 ); ?></option>
				<option value="43200" <?php echo (int) $poll->blockExpiration == 43200 ? 'selected' : ''; ?>><?php printf( __('%d hours', 'polldaddy'), 12 ); ?></option>
				<option value="86400" <?php echo (int) $poll->blockExpiration == 86400 ? 'selected' : ''; ?>><?php printf( __('%d day', 'polldaddy'), 1 ); ?></option>
				<option value="604800" <?php echo (int) $poll->blockExpiration == 604800 ? 'selected' : ''; ?>><?php printf( __('%d week', 'polldaddy'), 1 ); ?></option>
				<option value="2419200" <?php echo (int) $poll->blockExpiration == 2419200 ? 'selected' : ''; ?>><?php printf( __('%d month', 'polldaddy'), 1 ); ?></option>
			</select>
			<p><?php _e( 'Note: Blocking by cookie and IP address can be problematic for some voters.', 'polldaddy'); ?></p>
		</div>
	</div>
</div>


<div id="post-body-content" class="has-sidebar-content">

	<div id="titlediv">
		<div id="titlewrap">
			<input type="text" autocomplete="off" id="title" value="<?php echo $question; ?>" tabindex="1" size="30" name="question" />
		</div>
	</div>

	<div id="answersdiv" class="postbox">
		<h3><?php _e( 'Answers', 'polldaddy' ); ?></h3>

		<div id="answerswrap" class="inside">
		<ul id="answers">
<?php
		$a = 0;
		$answers = array();
		if ( $is_POST && $_POST['answer'] ) {
			foreach( $_POST['answer'] as $answer_id => $answer )
				$answers[attribute_escape($answer_id)] = attribute_escape( stripslashes($answer) );
		} elseif ( isset( $poll->answers->answer ) ) {
			foreach ( $poll->answers->answer as $answer )
				$answers[(int) $answer->_id] = attribute_escape( $answer->text );
		}

		foreach ( $answers as $answer_id => $answer ) :
			$a++;
			$delete_link = clean_url( wp_nonce_url( add_query_arg( array( 'action' => 'delete-answer', 'poll' => $poll_id, 'answer' => $answer_id, 'message' => false ) ), "delete-answer_$answer_id" ) );
?>

			<li>
				<span class="handle" title="<?php echo attribute_escape( 'click and drag to move' ); ?>">&#x2195;</span>
				<div><input type="text" autocomplete="off" id="answer-<?php echo $answer_id; ?>" value="<?php echo $answer; ?>" tabindex="2" size="30" name="answer[<?php echo $answer_id; ?>]" /></div>
				<a href="<?php echo $delete_link; ?>" class="delete-answer delete" title="<?php echo attribute_escape( 'delete this answer' ); ?>">&times;</a>
			</li>

<?php
		endforeach;

		while ( 3 - $a > 0 ) :
			$a++;
?>

			<li>
				<span class="handle" title="<?php echo attribute_escape( 'click and drag to move' ); ?>">&#x2195;</span>
				<div><input type="text" autocomplete="off" value="" tabindex="2" size="30" name="answer[new<?php echo $a; ?>]" /></div>
				<a href="#" class="delete-answer delete" title="<?php echo attribute_escape( 'delete this answer' ); ?>">&times;</a>
			</li>

<?php
		endwhile;
?>

		</ul>

		<p id="add-answer-holder">
			<button class="button"><?php echo wp_specialchars( __( 'Add another', 'polldaddy' ) ); ?></button>
		</p>

		<ul id="answer-options">

<?php
		foreach ( array( 'multipleChoice' => __( 'Multiple choice', 'polldaddy' ), 'randomiseAnswers' => __( 'Randomize answer order', 'polldaddy' ), 'otherAnswer' => __( 'Allow other answers', 'polldaddy' ), 'sharing' => __( "'Share This' link", 'polldaddy' ) ) as $option => $label ) :
			if ( $is_POST )
				$checked = 'yes' === $_POST[$option] ? ' checked="checked"' : '';
			else
				$checked = 'yes' === $poll->$option ? ' checked="checked"' : '';
?>

			<li>
				<label for="<?php echo $option; ?>"><input type="checkbox"<?php echo $checked; ?> value="yes" id="<?php echo $option; ?>" name="<?php echo $option; ?>" /> <?php echo wp_specialchars( $label ); ?></label>
			</li>

<?php		endforeach; ?>

		</ul>
		<?php 
			if ( $is_POST )
				$style = 'yes' === $_POST['multipleChoice'] ? 'display:block;' : 'display:none;';
			else
				$style = 'yes' === $poll->multipleChoice ? 'display:block;' : 'display:none;';
		?>
		<div id="numberChoices" name="numberChoices" style="padding-left:15px;<?php echo $style; ?>">
			<p>Number of choices: <select name="choices" id="choices"><option value="0">No Limit</option>
				<?php				
				if ( $is_POST )
					$choices = (int) $_POST['choices'];
				else
					$choices = (int) $poll->choices;

				if( $a > 1 ) :
					for( $i=2; $i<=$a; $i++ ) :
						$selected = $i == $choices ? 'selected="true"' : '';
						echo "<option value='$i' $selected>$i</option>";
					endfor;
				endif; ?>
				</select>
			</p>
		</div>
		</div>
	</div>

	<div id="design" class="postbox">

<?php	$style_ID = (int) ( $is_POST ? $_POST['styleID'] : $poll->styleID );	

		$iframe_view = false;
		if ( isset($_GET['iframe']) )
			$iframe_view = true;
		
		$options = array(
			101 => __( 'Aluminum Narrow','polldaddy'),
			102 => __( 'Aluminum Medium','polldaddy'),
			103 => __( 'Aluminum Wide','polldaddy'),
			104 => __( 'Plain White Narrow','polldaddy'),
			105 => __( 'Plain White Medium','polldaddy'),
			106 => __( 'Plain White Wide','polldaddy'),
			107 => __( 'Plain Black Narrow','polldaddy'),
			108 => __( 'Plain Black Medium','polldaddy'),
			109 => __( 'Plain Black Wide','polldaddy'),
			110 => __( 'Paper Narrow','polldaddy'),
			111 => __( 'Paper Medium','polldaddy'),
			112 => __( 'Paper Wide','polldaddy'),
			113 => __( 'Skull Dark Narrow','polldaddy'),
			114 => __( 'Skull Dark Medium','polldaddy'),
			115 => __( 'Skull Dark Wide','polldaddy'),
			116 => __( 'Skull Light Narrow','polldaddy'),
			117 => __( 'Skull Light Medium','polldaddy'),
			118 => __( 'Skull Light Wide','polldaddy'),
			157 => __( 'Micro','polldaddy'),
			119 => __( 'Plastic White Narrow','polldaddy'),
			120 => __( 'Plastic White Medium','polldaddy'),
			121 => __( 'Plastic White Wide','polldaddy'),
			122 => __( 'Plastic Grey Narrow','polldaddy'),
			123 => __( 'Plastic Grey Medium','polldaddy'),
			124 => __( 'Plastic Grey Wide','polldaddy'),
			125 => __( 'Plastic Black Narrow','polldaddy'),
			126 => __( 'Plastic Black Medium','polldaddy'),
			127 => __( 'Plastic Black Wide','polldaddy'),
			128 => __( 'Manga Narrow','polldaddy'),
			129 => __( 'Manga Medium','polldaddy'),
			130 => __( 'Manga Wide','polldaddy'),
			131 => __( 'Tech Dark Narrow','polldaddy'),
			132 => __( 'Tech Dark Medium','polldaddy'),
			133 => __( 'Tech Dark Wide','polldaddy'),
			134 => __( 'Tech Grey Narrow','polldaddy'),
			135 => __( 'Tech Grey Medium','polldaddy'),
			136 => __( 'Tech Grey Wide','polldaddy'),
			137 => __( 'Tech Light Narrow','polldaddy'),
			138 => __( 'Tech Light Medium','polldaddy'),
			139 => __( 'Tech Light Wide','polldaddy'),
			140 => __( 'Working Male Narrow','polldaddy'),
			141 => __( 'Working Male Medium','polldaddy'),
			142 => __( 'Working Male Wide','polldaddy'),
			143 => __( 'Working Female Narrow','polldaddy'),
			144 => __( 'Working Female Medium','polldaddy'),
			145 => __( 'Working Female Wide','polldaddy'),
			146 => __( 'Thinking Male Narrow','polldaddy'),
			147 => __( 'Thinking Male Medium','polldaddy'),
			148 => __( 'Thinking Male Wide','polldaddy'),
			149 => __( 'Thinking Female Narrow','polldaddy'),
			150 => __( 'Thinking Female Medium','polldaddy'),
			151 => __( 'Thinking Female Wide','polldaddy'),
			152 => __( 'Sunset Narrow','polldaddy'),
			153 => __( 'Sunset Medium','polldaddy'),
			154 => __( 'Sunset Wide','polldaddy'),
			155 => __( 'Music Medium','polldaddy'),
			156 => __( 'Music Wide','polldaddy')
		);
		
		$polldaddy->reset();
		$styles = $polldaddy->get_styles();

		$show_custom = false;
		if( !empty( $styles ) && !empty( $styles->style ) && count( $styles->style ) > 0 ){
			foreach( (array) $styles->style as $style ){
				$options[ (int) $style->_id ] = $style->title;	
			}
			$show_custom = true;
		}			

		if ( $style_ID > 18 ){
			$standard_style_ID = 0;
			$custom_style_ID = $style_ID;
		}
		else{
			$standard_style_ID = $style_ID;
			$custom_style_ID = 0;
		}		
?>

		<h3><?php _e( 'Design', 'polldaddy' ); ?></h3>
		<input type="hidden" name="styleID" id="styleID" value="<?php echo $style_ID ?>">
		<div class="inside">
			<?php if ( $iframe_view ){ ?>
			<div id="design_standard" style="padding:0px;">
				<div class="hide-if-no-js">
					<table class="pollStyle">
						<thead>
							<tr>
								<th>
									<div style="display:none;">
										<input type="radio" name="styleTypeCB" id="regular" onclick="javascript:pd_build_styles( 0 );"/>
									</div>
								</th>
							</tr>
						</thead>
						<tr>
							<td class="selector">
								<table class="st_selector">
									<tr>
										<td class="dir_left">
											<a href="javascript:pd_move('prev');" style="width: 1em;display: block;font-size: 4em;text-decoration: none;">&#171;</a>
										</td>
										<td class="img"><div class="st_image_loader"><div id="st_image" onmouseover="st_results(this, 'show');" onmouseout="st_results(this, 'hide');"></div></div></td>
										<td class="dir_right">
											<a href="javascript:pd_move('next');" style="width: 1em;display: block;font-size: 4em;text-decoration: none;">&#187;</a>
										</td>
									</tr>
									<tr>
										<td></td>
										<td class="counter">
											<div id="st_number"></div>
										</td>
										<td></td>
									</tr>
									<tr>
										<td></td>
										<td class="title">
											<div id="st_name"></div>
										</td>
										<td></td>
									</tr>
									<tr>
										<td></td>
										<td>
											<div id="st_sizes"></div>
										</td>
										<td></td>
									</tr>
									<tr>
										<td colspan="3">
											<div id="st_description"></div>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</div>

				<p class="empty-if-js" id="no-js-styleID">
					<select id="styleID" name="styleID">

				<?php 	foreach ( $options as $styleID => $label ) :
						$selected = $styleID == $style_ID ? ' selected="selected"' : ''; ?>
						<option value="<?php echo (int) $styleID; ?>"<?php echo $selected; ?>><?php echo wp_specialchars( $label ); ?></option>
				<?php 	endforeach; ?>

					</select>
				</p>				
			</div>
			<?php if ( $show_custom ){ ?>
			<div id="design_custom">
				<p class="hide-if-no-js">
					<table class="pollStyle">
						<thead>
							<tr>
								<th>
									<div style="display:none;">
										<?php $disabled = $show_custom == false ? ' disabled="true"' : ''; ?>
										<input type="radio" name="styleTypeCB" id="custom" onclick="javascript:pd_change_style(_$('customSelect').value);" <?php echo $disabled; ?>></input>
										<label onclick="javascript:pd_change_style(_$('customSelect').value);"><?php _e( 'Custom Style', 'polldaddy' ); ?></label>
									</div>
								</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td class="customSelect">
									<table>
										<tr>
											<td><?php $hide = $show_custom == true ? ' style="display:block;"' : ' style="display:none;"'; ?>
											<select id="customSelect" name="customSelect" onclick="pd_change_style(this.value);" <?php echo $hide ?>>
												<?php 	$selected = $custom_style_ID == 0 ? ' selected="selected"' : ''; ?>
														<option value="x"<?php echo $selected; ?>><?php _e( 'Please choose a custom style...', 'polldaddy' ); ?></option>
												<?php 	if( $show_custom) : foreach ( (array)$styles->style as $style ) :
														$selected = $style->_id == $custom_style_ID ? ' selected="selected"' : ''; ?>
														<option value="<?php echo (int) $style->_id; ?>"<?php echo $selected; ?>><?php echo wp_specialchars( $style->title ); ?></option>
												<?php	endforeach; endif; ?>
											</select>
											<div id="styleIDErr" class="formErr" style="display:none;"><?php _e( 'Please choose a style.', 'polldaddy' ); ?></div></td>
										</tr>
										<tr>
											<td><?php $extra = $show_custom == false ? __( 'You currently have no custom styles created.', 'polldaddy') : ''; ?>
												<p><?php echo $extra ?></p>
												<p><?php printf( __( 'Did you know we have a new editor for building your own custom poll styles? Find out more <a href="%s" target="_blank">here</a>.', 'polldaddy' ), 'http://support.polldaddy.com/custom-poll-styles/' ); ?></p>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
				</p>
			</div>
			<div id="design_options">
				<a href="#" class="polldaddy-show-design-options"><?php _e( 'Custom Styles', 'polldaddy' ); ?></a>
			</div>
			<?php }}else{?>
				<div class="design_standard">
					<div class="hide-if-no-js">
					<table class="pollStyle">
						<thead>
							<tr>
								<th class="cb">
									<input type="radio" name="styleTypeCB" id="regular" onclick="javascript:pd_build_styles( 0 );"/>
								</th>
								<th>
									<label for="skin" onclick="javascript:pd_build_styles( 0 );"><?php _e( 'PollDaddy Style', 'polldaddy' ); ?></label>
								</th>
								<th/>
								<th class="cb">
									<?php $disabled = $show_custom == false ? ' disabled="true"' : ''; ?>
									<input type="radio" name="styleTypeCB" id="custom" onclick="javascript:pd_change_style(_$('customSelect').value);" <?php echo $disabled; ?>></input>
								</th>
								<th>
									<label onclick="javascript:pd_change_style(_$('customSelect').value);"><?php _e( 'Custom Style', 'polldaddy' ); ?></label>
								</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td/>
								<td class="selector">
									<table class="st_selector">
										<tr>
											<td class="dir_left">
												<a href="javascript:pd_move('prev');" style="width: 1em;display: block;font-size: 4em;text-decoration: none;">&#171;</a>
											</td>
											<td class="img"><div class="st_image_loader"><div id="st_image" onmouseover="st_results(this, 'show');" onmouseout="st_results(this, 'hide');"></div></div></td>
											<td class="dir_right">
												<a href="javascript:pd_move('next');" style="width: 1em;display: block;font-size: 4em;text-decoration: none;">&#187;</a>
											</td>
										</tr>
										<tr>
											<td></td>
											<td class="counter">
												<div id="st_number"></div>
											</td>
											<td></td>
										</tr>
										<tr>
											<td></td>
											<td class="title">
												<div id="st_name"></div>
											</td>
											<td></td>
										</tr>
										<tr>
											<td></td>
											<td>
												<div id="st_sizes"></div>
											</td>
											<td></td>
										</tr>
										<tr>
											<td colspan="3">
												<div id="st_description"></div>
											</td>
										</tr>
									</table>
								</td>
								<td width="100"></td>
								<td/>
								<td class="customSelect">
									<table>
										<tr>
											<td><?php $hide = $show_custom == true ? ' style="display:block;"' : ' style="display:none;"'; ?>
											<select id="customSelect" name="customSelect" onclick="pd_change_style(this.value);" <?php echo $hide ?>>
												<?php 	$selected = $custom_style_ID == 0 ? ' selected="selected"' : ''; ?>
														<option value="x"<?php echo $selected; ?>><?php _e( 'Please choose a custom style...', 'polldaddy'); ?></option>
												<?php 	if( $show_custom) : foreach ( (array)$styles->style as $style ) :
														$selected = $style->_id == $custom_style_ID ? ' selected="selected"' : ''; ?>
														<option value="<?php echo (int) $style->_id; ?>"<?php echo $selected; ?>><?php echo wp_specialchars( $style->title ); ?></option>
												<?php	endforeach; endif;?>
											</select>
											<div id="styleIDErr" class="formErr" style="display:none;"><?php _e( 'Please choose a style.', 'polldaddy'); ?></div></td>
										</tr>
										<tr>
											<td><?php $extra = $show_custom == false ? __( 'You currently have no custom styles created.', 'polldaddy' ) : ''; ?>
												<p><?php echo $extra ?></p>
												<p><?php printf( __( 'Did you know we have a new editor for building your own custom poll styles? Find out more <a href="%s" target="_blank">here</a>.', 'polldaddy' ), 'http://support.polldaddy.com/custom-poll-styles/' ); ?></p>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
					</div>
					<p class="empty-if-js" id="no-js-styleID">
						<select id="styleID" name="styleID">

					<?php 	foreach ( $options as $styleID => $label ) :
							$selected = $styleID == $style_ID ? ' selected="selected"' : ''; ?>
							<option value="<?php echo (int) $styleID; ?>"<?php echo $selected; ?>><?php echo wp_specialchars( $label ); ?></option>
					<?php 	endforeach; ?>

						</select>
					</p>
				</div>	
			<?php } ?>
			<script language="javascript">
			jQuery( document ).ready(function(){ 
				plugin = new Plugin( {
					delete_rating: '<?php _e( 'Are you sure you want to delete the rating for "%s"?','polldaddy'); ?>',
					delete_poll: '<?php _e( 'Are you sure you want to delete "%s"?','polldaddy'); ?>',
					delete_answer: '<?php _e( 'Are you sure you want to delete this answer?','polldaddy'); ?>',
					delete_answer_title: '<?php _e( 'delete this answer','polldaddy'); ?>',
					standard_styles: '<?php _e( 'Standard Styles','polldaddy'); ?>',
					custom_styles: '<?php _e( 'Custom Styles','polldaddy'); ?>'
				} );
			});
			</script>
			<script language="javascript">
			current_pos = 0;			
			
			for( var key in styles_array ) {
				var name = styles_array[key].name;
				
				switch( name ){
					case 'Aluminum':
						styles_array[key].name = '<?php _e( 'Aluminum', 'polldaddy' ); ?>';
						break;
					case 'Plain White':
						styles_array[key].name = '<?php _e( 'Plain White', 'polldaddy' ); ?>';
						break;
					case 'Plain Black':
						styles_array[key].name = '<?php _e( 'Plain Black', 'polldaddy' ); ?>';
						break;
					case 'Paper':
						styles_array[key].name = '<?php _e( 'Paper', 'polldaddy' ); ?>';
						break;
					case 'Skull Dark':
						styles_array[key].name = '<?php _e( 'Skull Dark', 'polldaddy' ); ?>';
						break;
					case 'Skull Light':
						styles_array[key].name = '<?php _e( 'Skull Light', 'polldaddy' ); ?>';
						break;
					case 'Micro':
						styles_array[key].name = '<?php _e( 'Micro', 'polldaddy' ); ?>';
						styles_array[key].n_desc = '<?php _e( 'Width 150px, the micro style is useful when space is tight.', 'polldaddy' ); ?>';
						break;
					case 'Plastic White':
						styles_array[key].name = '<?php _e( 'Plastic White', 'polldaddy' ); ?>';
						break;
					case 'Plastic Grey':
						styles_array[key].name = '<?php _e( 'Plastic Grey', 'polldaddy' ); ?>';
						break;
					case 'Plastic Black':
						styles_array[key].name = '<?php _e( 'Plastic Black', 'polldaddy' ); ?>';
						break;
					case 'Manga':
						styles_array[key].name = '<?php _e( 'Manga', 'polldaddy' ); ?>';
						break;
					case 'Tech Dark':
						styles_array[key].name = '<?php _e( 'Tech Dark', 'polldaddy' ); ?>';
						break;
					case 'Tech Grey':
						styles_array[key].name = '<?php _e( 'Tech Grey', 'polldaddy' ); ?>';
						break;
					case 'Tech Light':
						styles_array[key].name = '<?php _e( 'Tech Light', 'polldaddy' ); ?>';
						break;
					case 'Working Male':
						styles_array[key].name = '<?php _e( 'Working Male', 'polldaddy' ); ?>';
						break;
					case 'Working Female':
						styles_array[key].name = '<?php _e( 'Working Female', 'polldaddy' ); ?>';
						break;
					case 'Thinking Male':
						styles_array[key].name = '<?php _e( 'Thinking Male', 'polldaddy' ); ?>';
						break;
					case 'Thinking Female':
						styles_array[key].name = '<?php _e( 'Thinking Female', 'polldaddy' ); ?>';
						break;
					case 'Sunset':
						styles_array[key].name = '<?php _e( 'Sunset', 'polldaddy' ); ?>';
						break;
					case 'Music':
						styles_array[key].name = '<?php _e( 'Music', 'polldaddy' ); ?>';
						break;
				}
			}
			pd_map = {
				wide : '<?php _e( 'Wide', 'polldaddy' ); ?>',
				medium : '<?php _e( 'Medium', 'polldaddy' ); ?>',
				narrow : '<?php _e( 'Narrow', 'polldaddy' ); ?>',
				style_desc_wide : '<?php _e( 'Width: 630px, the wide style is good for blog posts.', 'polldaddy' ); ?>',
				style_desc_medium : '<?php _e( 'Width: 300px, the medium style is good for general use.', 'polldaddy' ); ?>',
				style_desc_narrow : '<?php _e( 'Width 150px, the narrow style is good for sidebars etc.', 'polldaddy' ); ?>',
				style_desc_micro : '<?php _e( 'Width 150px, the micro style is useful when space is tight.', 'polldaddy' ); ?>'
			}
			pd_build_styles( current_pos );
			<?php if( $style_ID > 0 && $style_ID <= 1000 ){ ?>
			pd_pick_style( <?php echo $style_ID ?> );
			<?php }else{ ?>
			pd_change_style( <?php echo $style_ID ?> );
			<?php } ?>
			</script>
		</div>
	
	</div>

</div>
</div></div>
</form>
<br class="clear" />

<?php
	}

	function poll_results_page( $poll_id ) {
		$polldaddy = $this->get_client( WP_POLLDADDY__PARTNERGUID, $this->user_code );
		$polldaddy->reset();

		$results = $polldaddy->get_poll_results( $poll_id );
?>

		<table class="poll-results widefat">
			<thead>
				<tr>
					<th scope="col" class="column-title"><?php _e( 'Answer', 'polldaddy' ); ?></th>
					<th scope="col" class="column-vote"><?php _e( 'Votes', 'polldaddy' ); ?></th>
				</tr>
			</thead>
			<tbody>

<?php
		$class = '';
		foreach ( $results->answers as $answer ) :
			$answer->text = trim( strip_tags( $answer->text ) );
            if( strlen( $answer->text ) == 0 ){
            	$answer->text = '-- empty HTML tag --';
            }
			
			$class = $class ? '' : ' class="alternate"';
			$content = $results->others && 'Other answer...' === $answer->text ? sprintf( __( 'Other (<a href="%s">see below</a>)', 'polldaddy' ), '#other-answers-results' ) : wp_specialchars( $answer->text );

?>

				<tr<?php echo $class; ?>>
					<th scope="row" class="column-title"><?php echo $content; ?></th>
					<td class="column-vote">
						<div class="result-holder">
							<span class="result-bar" style="width: <?php echo number_format( $answer->_percent, 2 ); ?>%;">&nbsp;</span>
							<span class="result-total alignleft"><?php echo number_format_i18n( $answer->_total ); ?></span>
							<span class="result-percent alignright"><?php echo number_format_i18n( $answer->_percent ); ?>%</span>
						</div>
					</td>
				</tr>
<?php
		endforeach;
?>

			</tbody>
		</table>

<?php

		if ( !$results->others )
			return;
?>

		<table id="other-answers-results" class="poll-others widefat">
			<thead>
				<tr>
					<th scope="col" class="column-title"><?php _e( 'Other Answer', 'polldaddy' ); ?></th>
					<th scope="col" class="column-vote"><?php _e( 'Votes', 'polldaddy' ); ?></th>
				</tr>
			</thead>
			<tbody>

<?php
		$class = '';
		$others = array_count_values( $results->others );
		arsort( $others );
		foreach ( $others as $other => $freq ) :
			$class = $class ? '' : ' class="alternate"';
?>

				<tr<?php echo $class; ?>>
					<th scope="row" class="column-title"><?php echo wp_specialchars( $other ); ?></th>
					<td class="column-vote"><?php echo number_format_i18n( $freq ); ?></td>
				</tr>
<?php
		endforeach;
?>

			</tbody>
		</table>

<?php
	}
	
	function styles_table() {
		$polldaddy = $this->get_client( WP_POLLDADDY__PARTNERGUID, $this->user_code );
		$polldaddy->reset();
		
		$styles_object = $polldaddy->get_styles();
			
		$this->parse_errors( $polldaddy );
		$this->print_errors();
		$styles = & $styles_object->style;
		$class = '';
		$styles_exist = false;
		
		foreach ( (array)$styles as $style ) :
			if( (int) $style->_type == 1 ):
				$styles_exist = true;
				break;
			endif;
		endforeach;
?>

		<form method="post" action="">
		<div class="tablenav">
			<div class="alignleft">
				<select name="action">
					<option selected="selected" value=""><?php _e( 'Actions', 'polldaddy' ); ?></option>
					<option value="delete-style"><?php _e( 'Delete', 'polldaddy' ); ?></option>
				</select>
				<input class="button-secondary action" type="submit" name="doaction" value="<?php _e( 'Apply', 'polldaddy' ); ?>" />
				<?php wp_nonce_field( 'action-style_bulk' ); ?>
			</div>
			<div class="tablenav-pages"></div>
		</div>
		<br class="clear" />
		<table class="widefat">
			<thead>
				<tr>
					<th id="cb" class="manage-column column-cb check-column" scope="col" /><input type="checkbox" /></th>
					<th id="title" class="manage-column column-title" scope="col"><?php _e( 'Style', 'polldaddy' ); ?></th>
					<th id="date" class="manage-column column-date" scope="col"><?php _e( 'Last Modified', 'polldaddy' ); ?></th>
				</tr>
			</thead>
			<tbody>

<?php
		if ( $styles_exist ) :
			foreach ( $styles as $style ) :
				if( (int) $style->_type == 1 ):
					$style_id = (int) $style->_id;			

					$class = $class ? '' : ' class="alternate"';
					$edit_link = clean_url( add_query_arg( array( 'action' => 'edit-style', 'style' => $style_id, 'message' => false ) ) );
					$delete_link = clean_url( wp_nonce_url( add_query_arg( array( 'action' => 'delete-style', 'style' => $style_id, 'message' => false ) ), "delete-style_$style_id" ) );
					list($style_time) = explode( '.', $style->date );
					$style_time = strtotime( $style_time );
	?>

					<tr<?php echo $class; ?>>
						<th class="check-column" scope="row"><input type="checkbox" value="<?php echo (int) $style_id; ?>" name="style[]" /></th>
						<td class="post-title column-title">
	<?php					if ( $edit_link ) : ?>
							<strong><a class="row-title" href="<?php echo $edit_link; ?>"><?php echo wp_specialchars( $style->title ); ?></a></strong>
							<span class="edit"><a href="<?php echo $edit_link; ?>"><?php _e( 'Edit', 'polldaddy' ); ?></a> | </span>
	<?php					else : ?>
							<strong><?php echo wp_specialchars( $style->title ); ?></strong>
	<?php					endif; ?>

							<span class="delete"><a class="delete-poll delete" href="<?php echo $delete_link; ?>"><?php _e( 'Delete', 'polldaddy' ); ?></a></span>
						</td>
						<td class="date column-date"><abbr title="<?php echo date( __('Y/m/d g:i:s A', 'polldaddy'), $style_time ); ?>"><?php echo date( __('Y/m/d', 'polldaddy'), $style_time ); ?></abbr></td>
					</tr>

	<?php
				endif;
			endforeach;
		else : // $styles
?>

				<tr>
					<td colspan="4"><?php printf( __( 'No custom styles yet.  <a href="%s">Create one</a>', 'polldaddy' ), clean_url( add_query_arg( array( 'action' => 'create-style' ) ) ) ); ?></td>
				</tr>
<?php		endif; // $styles ?>

			</tbody>
		</table>
		</form>
		<div class="tablenav">
			<div class="tablenav-pages"></div>
		</div>
		<br class="clear" />

<?php
	}
		
	function style_edit_form( $style_id = 105 ) {		
		$style_id = (int) $style_id;

		$polldaddy = $this->get_client( WP_POLLDADDY__PARTNERGUID, $this->user_code );
		$polldaddy->reset();

		$is_POST = 'post' == strtolower( $_SERVER['REQUEST_METHOD'] );
		
		if ( $style_id ) {
			$style = $polldaddy->get_style( $style_id );
			$this->parse_errors( $polldaddy );
		} else {
			$style = polldaddy_style( array(), null, false );
		}

		$style->css = trim( urldecode( $style->css ) );

		if ( $start = stripos( $style->css, '<data>' ) )
			$style->css = substr( $style->css, $start );

		$style->css = addslashes( $style->css );

		$preload_style_id = 0;
		$preload_style = null;

		if ( isset ( $_REQUEST['preload'] ) )
		{
			$preload_style_id = (int) $_REQUEST['preload'];

			if ( $preload_style_id > 1000 || $preload_style_id < 100 )
				$preload_style_id = 0;
			
			if ( $preload_style_id > 0 ) {
				$polldaddy->reset();
				$preload_style = $polldaddy->get_style( $preload_style_id );
				$this->parse_errors( $polldaddy );
			}
			
			$preload_style->css = trim( urldecode( $preload_style->css ) );

			if ( $start = stripos( $preload_style->css, '<data>' ) )
				$preload_style->css = substr( $preload_style->css, $start );

			$style->css = addslashes( $preload_style->css );
		}
		
		$this->print_errors();
		
		echo '<script language="javascript">var CSSXMLString = "' . $style->css .'";</script>';
	?>

	<form action="" method="post">
	<div id="poststuff">
		<div id="post-body">
			<br/>
			<table width="100%">
				<tr>
					<td colspan="2">
						<table width="100%">
							<tr>
								<td valign="middle" width="8%">
									<label class="CSSE_title_label"><?php _e( 'Style Name', 'polldaddy' ); ?></label>
								</td>
								<td>
									<div id="titlediv" style="margin:0px;">
										<div id="titlewrap">
											<input type="text" autocomplete="off" id="title" value="<?php echo $style_id > 1000 ? $style->title : ''; ?>" tabindex="1" size="30" name="style-title"></input>
										</div>
									</div>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td width="13%">
						<label class="CSSE_title_label"><?php _e( 'Preload Basic Style', 'polldaddy' ); ?></label>
					</td>
					<td>
						<div class="CSSE_preload">				
							<select id="preload_value">
								<option value="0"></option>
								<option value="102"><?php _e( 'Aluminum', 'polldaddy' ); ?></option>
								<option value="105"><?php _e( 'Plain White', 'polldaddy' ); ?></option>
								<option value="108"><?php _e( 'Plain Black', 'polldaddy' ); ?></option>
								<option value="111"><?php _e( 'Paper', 'polldaddy' ); ?></option>
								<option value="114"><?php _e( 'Skull Dark', 'polldaddy' ); ?></option>
								<option value="117"><?php _e( 'Skull Light', 'polldaddy' ); ?></option>
								<option value="157"><?php _e( 'Micro', 'polldaddy' ); ?></option>
							</select>
							<a tabindex="4" id="style-preload" href="javascript:preload_pd_style();" class="button"><?php echo attribute_escape( __( 'Load Style', 'polldaddy' ) ); ?></a>
						</div>
					</td>
				</tr>
				<tr>
					<td width="13%">
						<p><?php _e( 'Choose a part to edit...', 'polldaddy' ); ?></p>
					</td>
					<td>
						<select id="styleName" onchange="renderStyleEdit(this.value);">
							<option value="pds-box" selected="selected"><?php _e( 'Poll Box', 'polldaddy' ); ?></option>
							<option value="pds-question-top"><?php _e( 'Question', 'polldaddy' ); ?></option>
							<option value="pds-answer-group"><?php _e( 'Answer Group', 'polldaddy' ); ?></option>
							<option value="pds-answer-input"><?php _e( 'Answer Check', 'polldaddy' ); ?></option>
							<option value="pds-answer"><?php _e( 'Answers', 'polldaddy' ); ?></option>
							<option value="pds-textfield"><?php _e( 'Other Input', 'polldaddy' ); ?></option>
							<option value="pds-vote-button"><?php _e( 'Vote Button', 'polldaddy' ); ?></option>
							<option value="pds-link"><?php _e( 'Links', 'polldaddy' ); ?></option>											
							<option value="pds-answer-feedback"><?php _e( 'Result Background', 'polldaddy' ); ?></option>
							<option value="pds-answer-feedback-bar"><?php _e( 'Result Bar', 'polldaddy' ); ?></option>
							<option value="pds-totalvotes-inner"><?php _e( 'Total Votes', 'polldaddy' ); ?></option>
						</select>
					</td>
				</tr>
			</table>
			<table width="100%">
				<tr>
					<td valign="top">
						<table class="CSSE_main">
							<tr>
								<td class="CSSE_main_l" valign="top">
									<div class="off" id="D_Font">
										<a href="javascript:CSSE_changeView('Font');" id="A_Font" class="Aoff"><?php _e( 'Font', 'polldaddy' ); ?></a>
									</div>
									<div class="on" id="D_Background">
										<a href="javascript:CSSE_changeView('Background');" id="A_Background" class="Aon"><?php _e( 'Background', 'polldaddy' ); ?></a>
									</div>
									<div class="off" id="D_Border">
										<a href="javascript:CSSE_changeView('Border');" id="A_Border" class="Aoff"><?php _e( 'Border', 'polldaddy' ); ?></a>
									</div>
									<div class="off" id="D_Margin">
										<a href="javascript:CSSE_changeView('Margin');" id="A_Margin" class="Aoff"><?php _e( 'Margin', 'polldaddy' ); ?></a>
									</div>
									<div class="off" id="D_Padding">
										<a href="javascript:CSSE_changeView('Padding');" id="A_Padding" class="Aoff"><?php _e( 'Padding', 'polldaddy' ); ?></a>
									</div>
									<div class="off" id="D_Scale">
										<a href="javascript:CSSE_changeView('Scale');" id="A_Scale" class="Aoff"><?php _e( 'Width', 'polldaddy' ); ?></a>
									</div>
									<div class="off" id="D_Height">
										<a href="javascript:CSSE_changeView('Height');" id="A_Height" class="Aoff"><?php _e( 'Height', 'polldaddy' ); ?></a>
									</div>
								</td>
								<td class="CSSE_main_r" valign="top">
									<table class="CSSE_sub">
										<tr>
											<td class="top"/>
										</tr>
										<tr>
											<td class="mid">
	<!-- Font Table -->
												<table class="CSSE_edit" id="editFont" style="display:none;">
													<tr>
														<td width="85"><?php _e( 'Font Size', 'polldaddy' ); ?>:</td>
														<td>
															<select id="font-size" onchange="bind(this);">
																<option value="6px">6px</option>
																<option value="8px">8px</option>
																<option value="9px">9px</option>
																<option value="10px">10px</option>
																<option value="11px">11px</option>
																<option value="12px">12px</option>
																<option value="13px">13px</option>
																<option value="14px">14px</option>
																<option value="15px">15px</option>
																<option value="16px">16px</option>
																<option value="18px">18px</option>
																<option value="20px">20px</option>
																<option value="24px">24px</option>
																<option value="30px">30px</option>
																<option value="36px">36px</option>
															</select>
														</td>
													</tr>
													<tr>
														<td><?php _e( 'Font Size', 'polldaddy' ); ?></td>
														<td>
															<select id="font-family" onchange="bind(this);">
																<option value="Arial">Arial</option>
																<option value="Comic Sans MS">Comic Sans MS</option>
																<option value="Courier">Courier</option>
																<option value="Georgia">Georgia</option>
																<option value="Lucida Grande">Lucida Grande</option>
																<option value="Trebuchet MS">Trebuchet MS</option>
																<option value="Times">Times</option>
																<option value="Verdana">Verdana</option>
															</select>
														</td>
													</tr>
													<tr>
														<td><?php _e( 'Color', 'polldaddy' ); ?> (#hex):</td>
														<td>
															<input type="text" maxlength="11" id="color" class="elmColor jscolor-picker" onblur="bind(this);" style="float:left;"/>
														</td>
													</tr>
													<tr>
														<td><?php _e( 'Bold', 'polldaddy' ); ?>:</td>
														<td>
															<input type="checkbox" id="font-weight" value="bold" onclick="bind(this);"/>
														</td>
													</tr>
													<tr>
														<td><?php _e( 'Italic', 'polldaddy' ); ?>:</td>
														<td>
															<input type="checkbox" id="font-style" value="italic" onclick="bind(this);"/>
														</td>
													</tr>
													<tr>
														<td><?php _e( 'Underline', 'polldaddy' ); ?>:</td>
														<td>
															<input type="checkbox" id="text-decoration" value="underline" onclick="bind(this);"/>
														</td>
													</tr>
													<tr>
														<td><?php _e( 'Line Height', 'polldaddy' ); ?>:</td>
														<td>
															<select id="line-height" onchange="bind(this);">
																<option value="6px">6px</option>
																<option value="8px">8px</option>
																<option value="9px">9px</option>
																<option value="10px">10px</option>
																<option value="11px">11px</option>
																<option value="12px">12px</option>
																<option value="13px">13px</option>
																<option value="14px">14px</option>
																<option value="15px">15px</option>
																<option value="16px">16px</option>
																<option value="18px">18px</option>
																<option value="20px">20px</option>
																<option value="24px">24px</option>
																<option value="30px">30px</option>
																<option value="36px">36px</option>
															</select>
														</td>
													</tr>
													<tr>
														<td><?php _e( 'Align', 'polldaddy' ); ?>:</td>
														<td>
															<select id="text-align" onchange="bind(this);">
																<option value="left"><?php _e( 'Left', 'polldaddy' ); ?></option>
																<option value="center"><?php _e( 'Center', 'polldaddy' ); ?></option>
																<option value="right"><?php _e( 'Right', 'polldaddy' ); ?></option>
															</select>
														</td>
													</tr>
												</table>
	<!-- Background Table -->
												<table class="CSSE_edit" id="editBackground" style="display:none;">
													<tr>
														<td width="85"><?php _e( 'Color', 'polldaddy' ); ?> (#hex):</td>
														<td>
															<input type="text" maxlength="11" id="background-color" class="elmColor jscolor-picker" onblur="bind(this);"/>
														</td>
													</tr>
													<tr>
														<td><?php _e( 'Image URL', 'polldaddy' ); ?>: <a href="http://support.polldaddy.com/custom-poll-styles/" class="noteLink" title="<?php _e( 'Click here for more information', 'polldaddy' ); ?>">(?)</a></td>
														<td>
															<input type="text" id="background-image" onblur="bind(this);"/>
														</td>
													</tr>
													<tr>
														<td><?php _e( 'Image Repeat', 'polldaddy' ); ?>:</td>
														<td>
															<select id="background-repeat" onchange="bind(this);">
																<option value="repeat"><?php _e( 'repeat', 'polldaddy' ); ?></option>
																<option value="no-repeat"><?php _e( 'no-repeat', 'polldaddy' ); ?></option>
																<option value="repeat-x"><?php _e( 'repeat-x', 'polldaddy' ); ?></option>
																<option value="repeat-y"><?php _e( 'repeat-y', 'polldaddy' ); ?></option>
															</select>
														</td>
													</tr>
													<tr>
														<td><?php _e( 'Image Position', 'polldaddy' ); ?>:</td>
														<td>
															<select id="background-position" onchange="bind(this);">
																<option value="left top"><?php _e( 'left top', 'polldaddy' ); ?></option>
																<option value="left center"><?php _e( 'left center', 'polldaddy' ); ?></option>
																<option value="left bottom"><?php _e( 'left bottom', 'polldaddy' ); ?></option>
																<option value="center top"><?php _e( 'center top', 'polldaddy' ); ?></option>
																<option value="center center"><?php _e( 'center center', 'polldaddy' ); ?></option>
																<option value="center bottom"><?php _e( 'center bottom', 'polldaddy' ); ?></option>
																<option value="right top"><?php _e( 'right top', 'polldaddy' ); ?></option>
																<option value="right center"><?php _e( 'right center', 'polldaddy' ); ?></option>
																<option value="right bottom"><?php _e( 'right bottom', 'polldaddy' ); ?></option>
															</select>
														</td>
													</tr>
												</table>
	<!-- Border Table -->
												<table class="CSSE_edit" id="editBorder" style="display:none;">
													<tr>
														<td width="85"><?php _e( 'Width', 'polldaddy' ); ?>:</td>
														<td>
															<select id="border-width" onchange="bind(this);">
																<option value="0px">0px</option>
																<option value="1px">1px</option>
																<option value="2px">2px</option>
																<option value="3px">3px</option>
																<option value="4px">4px</option>
																<option value="5px">5px</option>
																<option value="6px">6px</option>
																<option value="7px">7px</option>
																<option value="8px">8px</option>
																<option value="9px">9px</option>
																<option value="10px">10px</option>
																<option value="11px">11px</option>
																<option value="12px">12px</option>
																<option value="13px">13px</option>
																<option value="14px">14px</option>
																<option value="15px">15px</option>
																<option value="16px">16px</option>
																<option value="17px">17px</option>
																<option value="18px">18px</option>
																<option value="19px">19px</option>
																<option value="20px">20px</option>
																<option value="21px">21px</option>
																<option value="22px">22px</option>
																<option value="23px">23px</option>
																<option value="24px">24px</option>
																<option value="25px">25px</option>
																<option value="26px">26px</option>
																<option value="27px">27px</option>
																<option value="28px">28px</option>
																<option value="29px">29px</option>
																<option value="30px">30px</option>
															</select>
														</td>
													</tr>
													<tr>
														<td><?php _e( 'Style', 'polldaddy' ); ?>:</td>
														<td>
															<select id="border-style" onchange="bind(this);">
																<option value="none"><?php _e( 'none', 'polldaddy' ); ?></option>
																<option value="solid"><?php _e( 'solid', 'polldaddy' ); ?></option>
																<option value="dotted"><?php _e( 'dotted', 'polldaddy' ); ?></option>
																<option value="dashed"><?php _e( 'dashed', 'polldaddy' ); ?></option>
																<option value="double"><?php _e( 'double', 'polldaddy' ); ?></option>
																<option value="groove"><?php _e( 'groove', 'polldaddy' ); ?></option>
																<option value="inset"><?php _e( 'inset', 'polldaddy' ); ?></option>
																<option value="outset"><?php _e( 'outset', 'polldaddy' ); ?></option>
																<option value="ridge"><?php _e( 'ridge', 'polldaddy' ); ?></option>
																<option value="hidden"><?php _e( 'hidden', 'polldaddy' ); ?></option>
															</select>
														</td>
													</tr>
													<tr>
														<td><?php _e( 'Color', 'polldaddy' ); ?> (#hex):</td>
														<td>
															<input type="text" maxlength="11" class="elmColor jscolor-picker" id="border-color" onblur="bind(this);"/>
														</td>
													</tr>
													<tr>
														<td width="85"><?php _e( 'Rounded Corners', 'polldaddy' ); ?>:</td>
														<td>
															<select id="border-radius" onchange="bind(this);">
																<option value="0px">0px</option>
																<option value="1px">1px</option>
																<option value="2px">2px</option>
																<option value="3px">3px</option>
																<option value="4px">4px</option>
																<option value="5px">5px</option>
																<option value="6px">6px</option>
																<option value="7px">7px</option>
																<option value="8px">8px</option>
																<option value="9px">9px</option>
																<option value="10px">10px</option>
																<option value="11px">11px</option>
																<option value="12px">12px</option>
																<option value="13px">13px</option>
																<option value="14px">14px</option>
																<option value="15px">15px</option>
																<option value="16px">16px</option>
																<option value="17px">17px</option>
																<option value="18px">18px</option>
																<option value="19px">19px</option>
																<option value="20px">20px</option>
																<option value="21px">21px</option>
																<option value="22px">22px</option>
																<option value="23px">23px</option>
																<option value="24px">24px</option>
																<option value="25px">25px</option>
																<option value="26px">26px</option>
																<option value="27px">27px</option>
																<option value="28px">28px</option>
																<option value="29px">29px</option>
																<option value="30px">30px</option>
															</select>
															<br/>
															<?php _e( 'Not supported in Internet Explorer.', 'polldaddy' ); ?>
														</td>
													</tr>
												</table>
	<!-- Margin Table -->
												<table class="CSSE_edit" id="editMargin" style="display:none;">
													<tr>
														<td width="85"><?php _e( 'Top', 'polldaddy' ); ?>: </td>
														<td>
															<select id="margin-top" onchange="bind(this);">
																<option value="0px">0px</option>
																<option value="1px">1px</option>
																<option value="2px">2px</option>
																<option value="3px">3px</option>
																<option value="4px">4px</option>
																<option value="5px">5px</option>
																<option value="6px">6px</option>
																<option value="7px">7px</option>
																<option value="8px">8px</option>
																<option value="9px">9px</option>
																<option value="10px">10px</option>
																<option value="11px">11px</option>
																<option value="12px">12px</option>
																<option value="13px">13px</option>
																<option value="14px">14px</option>
																<option value="15px">15px</option>
																<option value="16px">16px</option>
																<option value="17px">17px</option>
																<option value="18px">18px</option>
																<option value="19px">19px</option>
																<option value="20px">20px</option>
																<option value="21px">21px</option>
																<option value="22px">22px</option>
																<option value="23px">23px</option>
																<option value="24px">24px</option>
																<option value="25px">25px</option>
																<option value="26px">26px</option>
																<option value="27px">27px</option>
																<option value="28px">28px</option>
																<option value="29px">29px</option>
																<option value="30px">30px</option>
															</select>
														</td>
													</tr>
													<tr>
														<td><?php _e( 'Right', 'polldaddy' ); ?>:</td>
														<td>
															<select id="margin-right" onchange="bind(this);">
																<option value="0px">0px</option>
																<option value="1px">1px</option>
																<option value="2px">2px</option>
																<option value="3px">3px</option>
																<option value="4px">4px</option>
																<option value="5px">5px</option>
																<option value="6px">6px</option>
																<option value="7px">7px</option>
																<option value="8px">8px</option>
																<option value="9px">9px</option>
																<option value="10px">10px</option>
																<option value="11px">11px</option>
																<option value="12px">12px</option>
																<option value="13px">13px</option>
																<option value="14px">14px</option>
																<option value="15px">15px</option>
																<option value="16px">16px</option>
																<option value="17px">17px</option>
																<option value="18px">18px</option>
																<option value="19px">19px</option>
																<option value="20px">20px</option>
																<option value="21px">21px</option>
																<option value="22px">22px</option>
																<option value="23px">23px</option>
																<option value="24px">24px</option>
																<option value="25px">25px</option>
																<option value="26px">26px</option>
																<option value="27px">27px</option>
																<option value="28px">28px</option>
																<option value="29px">29px</option>
																<option value="30px">30px</option>
															</select>
														</td>
													</tr>
													<tr>
														<td><?php _e( 'Bottom', 'polldaddy' ); ?>:</td>
														<td>
															<select id="margin-bottom" onchange="bind(this);">
																<option value="0px">0px</option>
																<option value="1px">1px</option>
																<option value="2px">2px</option>
																<option value="3px">3px</option>
																<option value="4px">4px</option>
																<option value="5px">5px</option>
																<option value="6px">6px</option>
																<option value="7px">7px</option>
																<option value="8px">8px</option>
																<option value="9px">9px</option>
																<option value="10px">10px</option>
																<option value="11px">11px</option>
																<option value="12px">12px</option>
																<option value="13px">13px</option>
																<option value="14px">14px</option>
																<option value="15px">15px</option>
																<option value="16px">16px</option>
																<option value="17px">17px</option>
																<option value="18px">18px</option>
																<option value="19px">19px</option>
																<option value="20px">20px</option>
																<option value="21px">21px</option>
																<option value="22px">22px</option>
																<option value="23px">23px</option>
																<option value="24px">24px</option>
																<option value="25px">25px</option>
																<option value="26px">26px</option>
																<option value="27px">27px</option>
																<option value="28px">28px</option>
																<option value="29px">29px</option>
																<option value="30px">30px</option>
															</select>
														</td>
													</tr>
													<tr>
														<td><?php _e( 'Left', 'polldaddy' ); ?>:</td>
														<td>
															<select id="margin-left" onchange="bind(this);">
																<option value="0px">0px</option>
																<option value="1px">1px</option>
																<option value="2px">2px</option>
																<option value="3px">3px</option>
																<option value="4px">4px</option>
																<option value="5px">5px</option>
																<option value="6px">6px</option>
																<option value="7px">7px</option>
																<option value="8px">8px</option>
																<option value="9px">9px</option>
																<option value="10px">10px</option>
																<option value="11px">11px</option>
																<option value="12px">12px</option>
																<option value="13px">13px</option>
																<option value="14px">14px</option>
																<option value="15px">15px</option>
																<option value="16px">16px</option>
																<option value="17px">17px</option>
																<option value="18px">18px</option>
																<option value="19px">19px</option>
																<option value="20px">20px</option>
																<option value="21px">21px</option>
																<option value="22px">22px</option>
																<option value="23px">23px</option>
																<option value="24px">24px</option>
																<option value="25px">25px</option>
																<option value="26px">26px</option>
																<option value="27px">27px</option>
																<option value="28px">28px</option>
																<option value="29px">29px</option>
																<option value="30px">30px</option>
															</select>
														</td>
													</tr>
												</table>
	<!-- Padding Table -->
												<table class="CSSE_edit" id="editPadding" style="display:none;">
													<tr>
														<td width="85"><?php _e( 'Top', 'polldaddy' ); ?>:</td>
														<td>
															<select id="padding-top" onchange="bind(this);">
																<option value="0px">0px</option>
																<option value="1px">1px</option>
																<option value="2px">2px</option>
																<option value="3px">3px</option>
																<option value="4px">4px</option>
																<option value="5px">5px</option>
																<option value="6px">6px</option>
																<option value="7px">7px</option>
																<option value="8px">8px</option>
																<option value="9px">9px</option>
																<option value="10px">10px</option>
																<option value="11px">11px</option>
																<option value="12px">12px</option>
																<option value="13px">13px</option>
																<option value="14px">14px</option>
																<option value="15px">15px</option>
																<option value="16px">16px</option>
																<option value="17px">17px</option>
																<option value="18px">18px</option>
																<option value="19px">19px</option>
																<option value="20px">20px</option>
																<option value="21px">21px</option>
																<option value="22px">22px</option>
																<option value="23px">23px</option>
																<option value="24px">24px</option>
																<option value="25px">25px</option>
																<option value="26px">26px</option>
																<option value="27px">27px</option>
																<option value="28px">28px</option>
																<option value="29px">29px</option>
																<option value="30px">30px</option>
															</select>
														</td>
													</tr>
													<tr>
														<td><?php _e( 'Right', 'polldaddy' ); ?>:</td>
														<td>
															<select id="padding-right" onchange="bind(this);">
																<option value="0px">0px</option>
																<option value="1px">1px</option>
																<option value="2px">2px</option>
																<option value="3px">3px</option>
																<option value="4px">4px</option>
																<option value="5px">5px</option>
																<option value="6px">6px</option>
																<option value="7px">7px</option>
																<option value="8px">8px</option>
																<option value="9px">9px</option>
																<option value="10px">10px</option>
																<option value="11px">11px</option>
																<option value="12px">12px</option>
																<option value="13px">13px</option>
																<option value="14px">14px</option>
																<option value="15px">15px</option>
																<option value="16px">16px</option>
																<option value="17px">17px</option>
																<option value="18px">18px</option>
																<option value="19px">19px</option>
																<option value="20px">20px</option>
																<option value="21px">21px</option>
																<option value="22px">22px</option>
																<option value="23px">23px</option>
																<option value="24px">24px</option>
																<option value="25px">25px</option>
																<option value="26px">26px</option>
																<option value="27px">27px</option>
																<option value="28px">28px</option>
																<option value="29px">29px</option>
																<option value="30px">30px</option>
															</select>
														</td>
													</tr>
													<tr>
														<td><?php _e( 'Bottom', 'polldaddy' ); ?>:</td>
														<td>
															<select id="padding-bottom" onchange="bind(this);">
																<option value="0px">0px</option>
																<option value="1px">1px</option>
																<option value="2px">2px</option>
																<option value="3px">3px</option>
																<option value="4px">4px</option>
																<option value="5px">5px</option>
																<option value="6px">6px</option>
																<option value="7px">7px</option>
																<option value="8px">8px</option>
																<option value="9px">9px</option>
																<option value="10px">10px</option>
																<option value="11px">11px</option>
																<option value="12px">12px</option>
																<option value="13px">13px</option>
																<option value="14px">14px</option>
																<option value="15px">15px</option>
																<option value="16px">16px</option>
																<option value="17px">17px</option>
																<option value="18px">18px</option>
																<option value="19px">19px</option>
																<option value="20px">20px</option>
																<option value="21px">21px</option>
																<option value="22px">22px</option>
																<option value="23px">23px</option>
																<option value="24px">24px</option>
																<option value="25px">25px</option>
																<option value="26px">26px</option>
																<option value="27px">27px</option>
																<option value="28px">28px</option>
																<option value="29px">29px</option>
																<option value="30px">30px</option>
															</select>
														</td>
													</tr>
													<tr>
														<td><?php _e( 'Left', 'polldaddy' ); ?>:</td>
														<td>
															<select id="padding-left" onchange="bind(this);">
																<option value="0px">0px</option>
																<option value="1px">1px</option>
																<option value="2px">2px</option>
																<option value="3px">3px</option>
																<option value="4px">4px</option>
																<option value="5px">5px</option>
																<option value="6px">6px</option>
																<option value="7px">7px</option>
																<option value="8px">8px</option>
																<option value="9px">9px</option>
																<option value="10px">10px</option>
																<option value="11px">11px</option>
																<option value="12px">12px</option>
																<option value="13px">13px</option>
																<option value="14px">14px</option>
																<option value="15px">15px</option>
																<option value="16px">16px</option>
																<option value="17px">17px</option>
																<option value="18px">18px</option>
																<option value="19px">19px</option>
																<option value="20px">20px</option>
																<option value="21px">21px</option>
																<option value="22px">22px</option>
																<option value="23px">23px</option>
																<option value="24px">24px</option>
																<option value="25px">25px</option>
																<option value="26px">26px</option>
																<option value="27px">27px</option>
																<option value="28px">28px</option>
																<option value="29px">29px</option>
																<option value="30px">30px</option>
															</select>
														</td>
													</tr>
												</table>
	<!-- Scale Table -->
												<table class="CSSE_edit" id="editScale" style="display:none;">
													<tr>
														<td width="85"><?php _e( 'Width', 'polldaddy' ); ?> (px):  <a href="http://support.polldaddy.com/custom-poll-styles/" class="noteLink" title="<?php _e( 'Click here for more information', 'polldaddy' ); ?>">(?)</a></td>
														<td>
															<input type="text" maxlength="4" class="elmColor" id="width" onblur="bind(this);"/>
														</td>
													</tr>
													<tr>
														<td width="85"></td>
														<td>
															<?php _e( 'If you change the width of the<br/> poll you may also need to change<br/> the width of your answers.', 'polldaddy' ); ?>
														</td>
													</tr>
												</table>

	<!-- Height Table -->
												<table class="CSSE_edit" id="editHeight" style="display:none;">
													<tr>
														<td width="85"><?php _e( 'Height', 'polldaddy' ); ?> (px):</td>
														<td>
															<input type="text" maxlength="4" class="elmColor" id="height" onblur="bind(this);"/>
														</td>
													</tr>
												</table>
											</td>
										</tr>
										<tr>
											<td class="btm"/>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
					<td width="10"> </td>
					<td valign="top">
						<div style="overflow-x:auto;width:633px;">
							<!-- POLL XHTML START -->
									<div class="pds-box" id="pds-box">
										<div class="pds-box-outer">
											<div class="pds-box-inner">
												<div class="pds-box-top">
													<div class="pds-question">
														<div class="pds-question-outer">
															<div class="pds-question-inner">
																<div class="pds-question-top" id="pds-question-top"><?php _e( 'Do you mostly use the internet at work, in school or at home?', 'polldaddy' ); ?></div>
															</div>
														</div>
													</div>
													<div>
							<!-- divAnswers -->
														<div id="divAnswers">
															<span id="pds-answer143974">

																<span class="pds-answer-group" id="pds-answer-group">
																	<span class="pds-answer-input" id="pds-answer-input">
																		<input type="radio" name="PDI_answer" value="1" id="p1" class="pds-checkbox"/>
																	</span>
																	<label for="p1" class="pds-answer" id="pds-answer"><span class="pds-answer-span"><?php _e( 'I use it in school.', 'polldaddy' ); ?></span></label>
																	<span class="pds-clear"></span>
																</span>

																<span class="pds-answer-group" id="pds-answer-group1">
																	<span class="pds-answer-input" id="pds-answer-input1">
																		<input type="radio" name="PDI_answer" value="2" id="p2" class="pds-checkbox"/>
																	</span>
																	<label for="p2" class="pds-answer" id="pds-answer1"><span class="pds-answer-span"><?php _e( 'I use it at home.', 'polldaddy' ); ?></span></label>
																	<span class="pds-clear"></span>
																</span>

																<span class="pds-answer-group" id="pds-answer-group2">
																	<span class="pds-answer-input" id="pds-answer-input2">
																		<input type="radio" name="PDI_answer" value="3" id="p3" class="pds-checkbox"/>
																	</span>
																	<label for="p3" class="pds-answer" id="pds-answer2"><span class="pds-answer-span"><?php _e( 'I use it every where I go, at work and home and anywhere else that I can!', 'polldaddy' ); ?></span></label>
																	<span class="pds-clear"></span>
																</span>

																<span class="pds-answer-group" id="pds-answer-group3">
																	<span class="pds-answer-input" id="pds-answer-input3">
																		<input type="radio" name="PDI_answer" value="4" id="p4" class="pds-checkbox"/>
																	</span>
																	<label for="p4" class="pds-answer" id="pds-answer3"><span class="pds-answer-span"><?php _e( 'Other', 'polldaddy' ); ?>:</span></label>
																	<span class="pds-clear"></span>
																	<span class="pds-answer-other">
																		<input type="text" name="PDI_OtherText1761982" id="pds-textfield" maxlength="80" class="pds-textfield"/>
																	</span>
																	<span class="pds-clear"></span>
																</span>

															</span>
															<br/>
															<div class="pds-vote" id="pds-links">
																<div class="pds-votebutton-outer">
																	<a href="javascript:renderStyleEdit('pds-answer-feedback');" id="pds-vote-button" style="display:block;float:left;" class="pds-vote-button"><span><?php _e( 'Vote', 'polldaddy' ); ?></span></a>
																	<span class="pds-links">
																		<div style="padding: 0px 0px 0px 15px; float:left;"><a href="javascript:renderStyleEdit('pds-answer-feedback');" class="pds-link" id="pds-link"><?php _e( 'View Results', 'polldaddy' ); ?></a></div>
																		<span class="pds-clear"></span>
																	</span>
																	<span class="pds-clear"></span>
																</div>
															</div>

														</div>
							<!-- End divAnswers -->
							<!-- divResults -->
														<div id="divResults">

															<div class="pds-answer-group" id="pds-answer-group4">
																<label for="PDI_feedback1" class="pds-answer" id="pds-answer4"><span class="pds-answer-text"><?php _e( 'I use it in school!', 'polldaddy' ); ?></span><xsl:text> </xsl:text><span class="pds-feedback-per"><strong>46%</strong></span><xsl:text> </xsl:text><span class="pds-feedback-votes"><?php printf( __( '(%d votes)', 'polldaddy' ), 620 ); ?></span></label>
																<span class="pds-clear"></span>
																<div id="pds-answer-feedback">
																	<div style="width:46%;" id="pds-answer-feedback-bar" class="pds-answer-feedback-bar"></div>
																</div>
																<span class="pds-clear"></span>
															</div>

															<div class="pds-answer-group" id="pds-answer-group5">
																<label for="PDI_feedback2" class="pds-answer" id="pds-answer5"><span class="pds-answer-text"><?php _e( 'I use it at home.', 'polldaddy' ); ?></span><xsl:text> </xsl:text><span class="pds-feedback-per"><strong>30%</strong></span><xsl:text> </xsl:text><span class="pds-feedback-votes"><?php printf( __( '(%d votes)', 'polldaddy' ), 400 ); ?></span></label>
																<span class="pds-clear"></span>
																<div id="pds-answer-feedback2">
																	<div style="width:46%;" id="pds-answer-feedback-bar2" class="pds-answer-feedback-bar"></div>
																</div>
																<span class="pds-clear"></span>
															</div>

															<div class="pds-answer-group" id="pds-answer-group6">
																<label for="PDI_feedback3" class="pds-answer" id="pds-answer6"><span class="pds-answer-text"><?php _e( 'I use it every where I go, at work and home and anywhere else that I can!', 'polldaddy' ); ?></span><xsl:text> </xsl:text><span class="pds-feedback-per"><strong>16%</strong></span><xsl:text> </xsl:text><span class="pds-feedback-votes"><?php printf( __( '(%d votes)', 'polldaddy' ), 220 ); ?></span></label>
																<span class="pds-clear"></span>
																<div id="pds-answer-feedback3">
																	<div style="width:16%;" id="pds-answer-feedback-bar3" class="pds-answer-feedback-bar"></div>
																</div>
																<span class="pds-clear"></span>
															</div>

															<div class="pds-answer-group" id="pds-answer-group7">
																<label for="PDI_feedback4" class="pds-answer" id="pds-answer7"><span class="pds-answer-text"><?php _e( 'Other', 'polldaddy' ); ?></span><xsl:text> </xsl:text><span class="pds-feedback-per"><strong>8%</strong></span><xsl:text> </xsl:text><span class="pds-feedback-votes"><?php printf( __( '(%d votes)', 'polldaddy' ), 110 ); ?></span></label>
																<span class="pds-clear"></span>
																<div id="pds-answer-feedback4">
																	<div style="width:8%;" id="pds-answer-feedback-bar4" class="pds-answer-feedback-bar"></div>
																</div>
																<span class="pds-clear"></span>
															</div>

														</div>
							<!-- End divResults -->
														<span class="pds-clear"></span>
														<div style="height: 10px;"></div>
														<div id="pds-totalvotes-inner"><?php _e( 'Total Votes', 'polldaddy' ); ?>: <strong>1,350</strong></div>
													</div>
													<div class="pds-vote" id="pds-links-back">
														<div class="pds-totalvotes-outer">
																<span class="pds-links-back">
																	<br/>
																	<a href="javascript:" class="pds-link" id="pds-link1"><?php _e( 'Comments', 'polldaddy' ); ?> <strong>(19)</strong></a> 
																	<xsl:text> </xsl:text>
																	<a href="javascript:renderStyleEdit('pds-box');" class="pds-link" id="pds-link2"><?php _e( 'Return To Poll', 'polldaddy' ); ?></a>
																	<span class="pds-clear"></span>
																</span>
																<span class="pds-clear"></span>
														</div>
													</div>
													</div>
											</div>
										</div>
									</div>
							<!-- POLL XHTML END -->
						</div>
					</td>
				</tr>
			</table>
			<div id="editBox"></div>     			
			<p class="pds-clear"></p>   
			<p>
				<?php wp_nonce_field( $style_id > 1000 ? "edit-style$style_id" : 'create-style' ); ?>
				<input type="hidden" name="action" value="<?php echo $style_id > 1000 ? 'edit-style' : 'create-style'; ?>" />
				<input type="hidden" class="polldaddy-style-id" name="style" value="<?php echo $style_id; ?>" />
				<input type="submit" class="button-primary" value="<?php echo attribute_escape( __( 'Save Style', 'polldaddy' ) ); ?>" />  
				<?php if ( $style_id > 1000 ) { ?>
				<input name="updatePollCheck" id="updatePollCheck" type="checkbox"> <label for="updatePollCheck"><?php _e( 'Check this box if you wish to update the polls that use this style.' ); ?></label>
				<?php } ?>
			</p>
		</div>
	</div>		
	<textarea id="S_www" name="CSSXML" style="display:none;width: 1000px; height: 500px;" rows="10" cols="10"> </textarea>
	</form>
<script language="javascript">
	jQuery( document ).ready(function(){ 
		plugin = new Plugin( {
			delete_rating: '<?php _e( 'Are you sure you want to delete the rating for "%s"?','polldaddy'); ?>',
			delete_poll: '<?php _e( 'Are you sure you want to delete "%s"?','polldaddy'); ?>',
			delete_answer: '<?php _e( 'Are you sure you want to delete this answer?','polldaddy'); ?>',
			delete_answer_title: '<?php _e( 'delete this answer','polldaddy'); ?>',
			standard_styles: '<?php _e( 'Standard Styles','polldaddy'); ?>',
			custom_styles: '<?php _e( 'Custom Styles','polldaddy'); ?>'
		} );
	});
	pd_map = {
		thankyou : '<?php _e( 'Thank you for voting!', 'polldaddy' ); ?>',
		question : '<?php _e( 'Do you mostly use the internet at work, in school or at home?', 'polldaddy' ); ?>'
	}
</script>
<script type="text/javascript" language="javascript">window.onload = function() {
	var CSSXML;
	loadStyle();
	showResults( false );
	renderStyleEdit( _$('styleName').value );
}</script>
	<br class="clear" />

	<?php
	}
	
	function rating_settings(){
		global $action, $rating;
		$show_posts = $show_posts_index = $show_pages = $show_comments = $pos_posts = $pos_posts_index = $pos_pages = $pos_comments = 0;
		$show_settings = $rating_updated = ( $action == 'update-rating' ? true : false );
		$error = false;
		
		$settings_style = 'display: none;';
		if( $show_settings )
			$settings_style = 'display: block;';
		
		$rating_id = get_option( 'pd-rating-posts-id' );
		$report_type = 'posts';
		$updated = false;
      
		if ( isset( $rating ) ) {
			switch ( $rating ) :
				case 'pages':
					$report_type = 'pages';
					$rating_id = (int) get_option( 'pd-rating-pages-id' );
					break;				
				case 'comments':
					$report_type = 'comments';
					$rating_id = (int) get_option( 'pd-rating-comments-id' );
					break;
				case 'posts':
					$report_type = 'posts';
					$rating_id = (int) get_option( 'pd-rating-posts-id' );
					break;
			endswitch;
		}
      
		$new_type = 0;
		if ( $report_type == 'comments' )
			$new_type = 1;
		
		$blog_name = get_option( 'blogname' );
		
		if ( empty( $blog_name ) )
			$blog_name = 'WordPress Blog';      
		$blog_name .= ' - ' . $report_type; 
		
		$polldaddy = $this->get_client( WP_POLLDADDY__PARTNERGUID, $this->rating_user_code );
		$polldaddy->reset();     
		
		if ( empty( $rating_id ) ) {
			$pd_rating = $polldaddy->create_rating( $blog_name , $new_type );
			if ( !empty( $pd_rating ) ) {
				$rating_id = (int) $pd_rating->_id;
				update_option ( 'pd-rating-' . $report_type . '-id', $rating_id );
				update_option ( 'pd-rating-' . $report_type, 0 );
			}
		} else      	
			$pd_rating = $polldaddy->get_rating( $rating_id );
      
		if ( empty( $pd_rating ) || (int) $pd_rating->_id == 0 ) {
		
			if ( $polldaddy->errors ) {
				if( array_key_exists( 4, $polldaddy->errors ) ) { //Obsolete key
					$this->rating_user_code = '';
					update_option( 'pd-rating-usercode', '' );   			
					$this->set_api_user_code();  // get latest key
					
					$polldaddy = $this->get_client( WP_POLLDADDY__PARTNERGUID, $this->rating_user_code );
					$polldaddy->reset();
					$pd_rating = $polldaddy->get_rating( $rating_id ); //see it exists
				
					if ( empty( $pd_rating ) || (int) $pd_rating->_id == 0 ) { //if not then create a rating for blog       
						$polldaddy->reset();				
						$pd_rating = $polldaddy->create_rating( $blog_name , $new_type );
					}
				}  
			} 
						
			if( empty( $pd_rating ) ) { //something's up!
				echo '<div class="error"><p>'.sprintf(__('Sorry! There was an error creating your rating widget. Please contact <a href="%1$s" %2$s>PollDaddy support</a> to fix this.', 'polldaddy'), 'http://polldaddy.com/feedback/', 'target="_blank"') . '</p></div>';
				$error = true;
			} else {
				$rating_id = (int) $pd_rating->_id;
				update_option ( 'pd-rating-' . $report_type . '-id', $rating_id );
				update_option ( 'pd-rating-' . $report_type, 0 );
			
				switch ( $report_type ) :
					case 'posts':
						$show_posts = 0;
						break;				
					case 'pages':
						$show_pages = 0;
						break;				
					case 'comments':
						$show_comments = 0;
						break;
				endswitch;   
			}
		}
      
		if ( isset( $_POST[ 'pd_rating_action_type' ] ) ) {
		
			switch ( $_POST[ 'pd_rating_action_type' ]  ) :	
				case 'posts' :
					if ( isset( $_POST[ 'pd_show_posts' ] ) && (int) $_POST[ 'pd_show_posts' ] == 1 )
						$show_posts = get_option( 'pd-rating-posts-id' );
					
					update_option( 'pd-rating-posts', $show_posts );
					
					if ( isset( $_POST[ 'pd_show_posts_index' ] ) && (int) $_POST[ 'pd_show_posts_index' ] == 1 )
						$show_posts_index = get_option( 'pd-rating-posts-id' );
					
					update_option( 'pd-rating-posts-index', $show_posts_index );
					
					if ( isset( $_POST[ 'posts_pos' ] ) && (int) $_POST[ 'posts_pos' ] == 1 )
						$pos_posts = 1;
					
					update_option( 'pd-rating-posts-pos', $pos_posts ); 
					
					if ( isset( $_POST[ 'posts_index_pos' ] ) && (int) $_POST[ 'posts_index_pos' ] == 1 )
						$pos_posts_index = 1;
					
					update_option( 'pd-rating-posts-index-pos', $pos_posts_index );
					$rating_updated = true;
					break;
				
				case 'pages';
					if ( isset( $_POST[ 'pd_show_pages' ] ) && (int) $_POST[ 'pd_show_pages' ] == 1 )
						$show_pages = get_option( 'pd-rating-pages-id' );
					
					update_option( 'pd-rating-pages', $show_pages );
					
					if ( isset( $_POST[ 'pages_pos' ] ) && (int) $_POST[ 'pages_pos' ] == 1 )
						$pos_pages = 1;
					
					update_option( 'pd-rating-pages-pos', $pos_pages );
					$rating_updated = true;
					break;
					
				case 'comments':
					if ( isset( $_POST[ 'pd_show_comments' ] ) && (int) $_POST[ 'pd_show_comments' ] == 1 )
						$show_comments = get_option( 'pd-rating-comments-id' );
					
					update_option( 'pd-rating-comments', $show_comments );
					
					if ( isset( $_POST[ 'comments_pos' ] ) && (int) $_POST[ 'comments_pos' ] == 1 )
						$pos_comments = 1;
					
					update_option( 'pd-rating-comments-pos', $pos_comments );
					
					$rating_updated = true;
					break;            
			endswitch;
		}
      
		$show_posts       = (int) get_option( 'pd-rating-posts' );
		$show_pages       = (int) get_option( 'pd-rating-pages' );
		$show_comments    = (int) get_option( 'pd-rating-comments' );
		$show_posts_index = (int) get_option( 'pd-rating-posts-index' );
		
		$pos_posts        = (int) get_option( 'pd-rating-posts-pos' );
		$pos_pages        = (int) get_option( 'pd-rating-pages-pos' );
		$pos_comments     = (int) get_option( 'pd-rating-comments-pos' );
		$pos_posts_index  = (int) get_option( 'pd-rating-posts-index-pos' );
		
		if ( !empty( $pd_rating ) ) {
			$settings_text = $pd_rating->settings;
			$settings = json_decode( $settings_text );         
			$rating_type = 0;
		
			if( $settings->type == 'stars' )
				$rating_type = 0;
			else
				$rating_type = 1;
		
			if( empty( $settings->font_color ) )
				$settings->font_color = '#000000'; 
      	}?>
		<div class="wrap">
		  <h2><?php _e('Rating Settings', 'polldaddy'); ?></h2><?php 
			if ( $rating_updated )
				echo( '<div class="updated"><p>'.__('Rating updated', 'polldaddy').'</p></div>' );

			if ( !$error ) { ?>
      <div id="side-sortables"> 
        <div id="categorydiv" class="categorydiv">
          <ul id="category-tabs" class="category-tabs"><?php 
				$this_class = '';
				$posts_link = clean_url( add_query_arg( array( 'rating' => 'posts', 'message' => false ) ) );
				$pages_link = clean_url( add_query_arg( array( 'rating' => 'pages', 'message' => false ) ) );
				$comments_link = clean_url( add_query_arg( array( 'rating' => 'comments', 'message' => false ) ) );
				if ( $report_type == 'posts' )
					$this_class = ' class="tabs"';?>
            <li <?php echo( $this_class ); ?>><a tabindex="3" href="<?php echo $posts_link; ?>"><?php _e('Posts', 'polldaddy');?></a></li><?php
          $this_class = '';
          if ( $report_type == 'pages' )
            $this_class = ' class="tabs"';  ?>
            <li <?php echo( $this_class ); ?>><a tabindex="3" href="<?php echo $pages_link; ?>"><?php _e('Pages', 'polldaddy');?></a></li><?php
	    	    $this_class = '';
          if ( $report_type == 'comments' )
            $this_class = ' class="tabs"';  ?>
            <li <?php echo( $this_class ); ?>><a href="<?php echo $comments_link; ?>"><?php _e('Comments', 'polldaddy');?></a></li>
          </ul>
          <div class="tabs-panel" id="categories-all" style="background: #FFFFFF;height: auto; overflow: visible;">
            <form action="" method="post">
            <input type="hidden" name="pd_rating_action_type" value="<?php echo ( $report_type ); ?>" />
            <table class="form-table" style="width: normal;">
              <tbody><?php
          if ( $report_type == 'posts' ) { ?>
                <tr valign="top">
                  <td style="padding-left: 0px; padding-right: 0px; padding-top: 7px;">
                    <label for="pd_show_posts">
                      <input type="checkbox" name="pd_show_posts" id="pd_show_posts" <?php if( $show_posts > 0 ) echo( ' checked="checked" ' ); ?> value="1" /> <?php _e('Enable for blog posts', 'polldaddy');?>
                    </label>
                    <span id="span_posts">
                      <select name="posts_pos"><?php
            $select = array( __('Above each blog post', 'polldaddy') => '0', __('Below each blog post', 'polldaddy') => '1' );
            foreach( $select as $option => $value ) :
              $selected = '';
              if ( $value == $pos_posts )
                $selected = ' selected="selected"';
              echo ( '<option value="' . $value . '" ' . $selected . '>' . $option . '</option>' );
            endforeach;?>
                      </select>
                    </span>
                  </td>
                </tr>   
                <tr valign="top">
                  <td style="padding-left: 0px; padding-right: 0px; padding-top: 7px;">
                    <label for="pd_show_posts_index">
                      <input type="checkbox" name="pd_show_posts_index" id="pd_show_posts_index" <?php if( $show_posts_index > 0 ) echo( ' checked="checked" ' ); ?> value="1" /> <?php _e('Enable for front page', 'polldaddy');?>
                    </label>
                    <span id="span_posts">
                      <select name="posts_index_pos"><?php
            $select = array( __('Above each blog post', 'polldaddy') => '0', __('Below each blog post', 'polldaddy') => '1' );
            foreach( $select as $option => $value ) :
              $selected = '';
              if ( $value == $pos_posts_index )
                $selected = ' selected="selected"';
              echo ( '<option value="' . $value . '" ' . $selected . '>' . $option . '</option>' );
            endforeach;?>
                      </select>
                    </span>
                  </td>
                </tr><?php
          }
          if ( $report_type == 'pages' ) {?>
                <tr valign="top">
                  <td style="padding-left: 0px; padding-right: 0px;  padding-top: 7px;">
                    <label for="pd_show_pages">
                      <input type="checkbox" name="pd_show_pages" id="pd_show_pages" <?php if( $show_pages > 0 ) echo( ' checked="checked" ' ); ?> value="1" /> <?php _e('Enable for pages', 'polldaddy');?>
                    </label>                    
                    <span id="span_pages">
                      <select name="pages_pos"><?php
            $select = array( __('Above each page', 'polldaddy') => '0', __('Below each page', 'polldaddy') => '1' );
            foreach( $select as $option => $value ) :
              $selected = '';
              if ( $value == $pos_pages )
                $selected = ' selected="selected"';
              echo ( '<option value="' . $value . '" ' . $selected . '>' . $option . '</option>' );
            endforeach; ?>
                      </select>
                    </span>
                  </td>
                </tr><?php
          }
          if ( $report_type == 'comments' ) {?>
                <tr valign="top">
                  <td style="padding-left: 0px; padding-right: 0px; padding-top: 7px;">
                    <label for="pd_show_comments">
                      <input type="checkbox" name="pd_show_comments" id="pd_show_comments" <?php if(    $show_comments > 0 ) echo( ' checked="checked" ' ); ?> value="1" /> <?php _e('Enable for comments', 'polldaddy');?>
                    </label>                 
                    <span id="span_comments">
                      <select name="comments_pos"><?php
            $select = array( __('Above each comment', 'polldaddy') => '0', __('Below each comment', 'polldaddy') => '1' );
            foreach( $select as $option => $value ) :
              $selected = '';
              if ( $value == $pos_comments )
                $selected = ' selected="selected"';
              echo ( '<option value="' . $value . '" ' . $selected . '>' . $option . '</option>' );
            endforeach; ?>
                      </select>
                    </span>
                  </td>
                </tr><?php 
          } ?>
              </tbody>
            </table>
            <p class="submit">
              <input class="button-primary" type="submit" value="<?php esc_attr_e('Save Changes', 'polldaddy');?>" name="Submit" />
            </p><?php
          if ( $report_type == 'posts' ) {
            if( $show_posts > 0 || $show_posts_index > 0 )
              $show_settings = true;
          }
          if ( $report_type == 'pages' && $show_pages > 0 )
            $show_settings = true;
          if ( $report_type == 'comments' && $show_comments > 0 )
            $show_settings = true;
          if ( $show_settings == true )
            echo ( '<a href="javascript:" onclick="show_settings();">'.__('Advanced Settings', 'polldaddy').'</a>' );?>
          </form>
        </div>
      </div>
    </div>

    <?php if ( $show_settings == true ){ ?>
    <br />
    <form method="post" action="">
      <div id="poststuff" style="<?php echo( $settings_style ); ?>">
        <div  class="has-sidebar has-right-sidebar">
          <div class="inner-sidebar-ratings">
            <div class="postbox" id="submitdiv">
              <h3><?php _e('Save', 'polldaddy');?></h3>
              <div class="inside">
                <div id="major-publishing-actions">
                  <input type="hidden" name="type" value="<?php echo( $report_type ); ?>" />
                  <input type="hidden" name="rating_id" value="<?php echo( $rating_id ); ?>" />
                  <input type="hidden" name="action" value="update-rating" />
                  <p id="publishing-action">
                    <input type="submit" value="<?php _e('Save Changes', 'polldaddy');?>" class="button-primary"/>
                  </p>
                  <br class="clear"/>
                </div>
              </div>
            </div>
            <div class="postbox">
              <h3><?php _e('Preview', 'polldaddy');?></h3>
              <div class="inside">
                <p><?php _e('This is a demo of what your rating widget will look like', 'polldaddy'); ?>.</p>
                <p>
                  <div id="pd_rating_holder_1"></div>
                </p>
              </div>
            </div>
            <div class="postbox">
              <h3><?php _e('Customize Labels', 'polldaddy');?></h3>
              <div class="inside">
                <table>
                  <tr>
                    <td width="100" height="30"><?php _e('Vote', 'polldaddy');?></td>
                    <td><input onblur="pd_bind(this);" type="text" name="text_vote" id="text_vote" value="<?php echo empty( $settings->text_vote ) ? 'Vote' : wp_specialchars( $settings->text_vote ); ?>" maxlength="20" />
                  </tr>
                  <tr>
                    <td width="100" height="30"><?php _e('Votes', 'polldaddy');?></td>
                    <td><input onblur="pd_bind(this);" type="text" name="text_votes" id="text_votes" value="<?php echo( wp_specialchars( $settings->text_votes ) ); ?>" maxlength="20" />
                  </tr>
                  <tr>
                    <td height="30"><?php _e('Rate This', 'polldaddy');?></td>
                    <td><input onblur="pd_bind(this);" type="text" name="text_rate_this" id="text_rate_this" value="<?php echo( wp_specialchars( $settings->text_rate_this ) ); ?>" maxlength="20" />
                  </tr>
                  <tr>
                    <td height="30"><?php printf(__( '%d star', 'polldaddy' ), 1);?></td>
                    <td><input onblur="pd_bind(this);" type="text" name="text_1_star" id="text_1_star" value="<?php echo( wp_specialchars( $settings->text_1_star ) ); ?>" maxlength="20" />
                  </tr>
                  <tr>
                    <td height="30"><?php printf(__( '%d stars', 'polldaddy' ), 2);?></td>
                    <td><input onblur="pd_bind(this);" type="text" name="text_2_star" id="text_2_star" value="<?php echo( wp_specialchars( $settings->text_2_star ) ); ?>" maxlength="20" />
                  </tr>
                  <tr>
                    <td height="30"><?php printf(__( '%d stars', 'polldaddy' ), 3);?></td>
                    <td><input onblur="pd_bind(this);" type="text" name="text_3_star" id="text_3_star" value="<?php echo( wp_specialchars( $settings->text_3_star ) ); ?>" maxlength="20" />
                  </tr>
                  <tr>
                    <td height="30"><?php printf(__( '%d stars', 'polldaddy' ), 4);?></td>
                    <td><input onblur="pd_bind(this);" type="text" name="text_4_star" id="text_4_star" value="<?php echo( wp_specialchars( $settings->text_4_star ) ); ?>" maxlength="20" />
                  </tr>
                  <tr>
                    <td height="30"><?php printf(__( '%d stars', 'polldaddy' ), 5);?></td>
                    <td><input onblur="pd_bind(this);" type="text" name="text_5_star" id="text_5_star" value="<?php echo( wp_specialchars( $settings->text_5_star ) ); ?>" maxlength="20" />
                  </tr>
                  <tr>
                    <td height="30"><?php _e('Thank You', 'polldaddy');?></td>
                    <td><input onblur="pd_bind(this);" type="text" name="text_thank_you" id="text_thank_you" value="<?php echo( wp_specialchars( $settings->text_thank_you ) ); ?>" maxlength="20" />
                  </tr>
                  <tr>
                    <td height="30"><?php _e('Rate Up', 'polldaddy');?></td>
                    <td><input onblur="pd_bind(this);" type="text" name="text_rate_up" id="text_rate_up" value="<?php echo( wp_specialchars( $settings->text_rate_up ) ); ?>" maxlength="20" />
                  </tr>
                  <tr>
                    <td height="30"><?php _e('Rate Down', 'polldaddy');?></td>
                    <td><input onblur="pd_bind(this);" type="text" name="text_rate_down" id="text_rate_down" value="<?php echo( wp_specialchars( $settings->text_rate_down ) ); ?>" maxlength="20" />
                  </tr>
                </table>
              </div>
            </div>
          </div>
          <div id="post-body-content" class="has-sidebar-content">          
            <div class="postbox">
              <h3><?php _e('Rating Type', 'polldaddy');?></h3>
              <div class="inside">				
                <p><?php _e('Here you can choose how you want your rating to display. The 5 star rating is the most commonly used. The Nero rating is useful for keeping it simple.', 'polldaddy'); ?></p>
                  <ul>
                    <li style="display: inline;margin-right: 10px;">
                      <label for="stars"><?php
          $checked = '';
          if ( $settings->type == 'stars' )
            $checked = ' checked="checked"';?>
                        <input type="radio" onchange="pd_change_type( 0 );" <?php echo ( $checked ); ?> value="stars" id="stars" name="rating_type" />
                          <?php printf(__( '%d Star Rating', 'polldaddy' ), 5);?>
                        </label>
                    </li>
                    <li style="display: inline;">
                      <label><?php
          $checked = '';
          if ( $settings->type == 'nero' )
            $checked = ' checked="checked"';?>
                        <input type="radio" onchange="pd_change_type( 1 );" <?php echo( $checked ); ?> value="nero" id="nero" name="rating_type" />
                        <?php _e('Nero Rating', 'polldaddy' );?>
                      </label>									
                    </li>
                  </ul>
                </div>
            </div>
          <div class="postbox">
            <h3><?php _e('Rating Style', 'polldaddy');?></h3>
            <div class="inside">
              <table>
                <tr>
                  <td height="30" width="100" id="editor_star_size_text"><?php _e('Star Size', 'polldaddy');?></td>
                  <td>
                    <select name="size" id="size" onchange="pd_bind(this);"><?php
          $select = array( __('Small', 'polldaddy')." (16px)" => "sml", __('Medium', 'polldaddy')." (20px)" => "med", __('Large', 'polldaddy')." (24px)" => "lrg" );          
          foreach ( $select as $option => $value ) :
            $selected = '';
            if ( $settings->size == $value )
              $selected = ' selected="selected"';
            echo ( '<option value="' . $value . '" ' . $selected . '>' . $option . '</option>' . "\n" );
          endforeach;?>
                    </select>
                  </td>
                </tr>
                <tr>
                  <td height="30" id="editor_star_color_text"><?php echo 'bubu'; _e('Star Color', 'polldaddy');?></td>
                  <td>
                    <select name="star_color" id="star_color" onchange="pd_bind(this);" style="display: none;"><?php
          $select = array( __('Yellow', 'polldaddy') => "yellow", __('Red', 'polldaddy') => "red", __('Blue', 'polldaddy') => "blue", __('Green', 'polldaddy') => "green", __('Grey', 'polldaddy') => "grey" );
          foreach ( $select as $option => $value ) :
            $selected = '';
            if ( $settings->star_color == $value )
              $selected = ' selected="selected"';
            echo ( '<option value="' . $value . '" ' . $selected . '>' . $option . '</option>' . "\n" );
          endforeach;?>	
                    </select>
                    <select name="nero_style" id="nero_style" onchange="pd_bind(this);"  style="display: none;"><?php
          $select = array( __('Hand', 'polldaddy') => "hand" );
          foreach ( $select as $option => $value ) :
            $selected = '';
            if ( $settings->star_color == $value )
              $selected = ' selected="selected"';
            echo ( '<option value="' . $value . '" ' . $selected . '>' . $option . '</option>' . "\n" );
          endforeach;?>
                    </select>
                  </td>
                </tr>
                <tr>
                  <td height="30"><?php _e('Custom Image', 'polldaddy');?></td>
                  <td><input type="text" onblur="pd_bind(this);" name="custom_star" id="custom_star" value="<?php echo( clean_url( $settings->custom_star ) ); ?>" maxlength="200" />
                </tr>
              </table>
            </div>
          </div>
          <div class="postbox">
            <h3><?php _e('Text Layout & Font', 'polldaddy');?></h3>
            <div class="inside">
              <table>
                <tr>
                  <td width="100" height="30"><?php _e('Align', 'polldaddy');?></td>
                  <td>
                    <select id="font_align" onchange="pd_bind(this);" name="font_align"><?php
          $select = array( __('Left', 'polldaddy') => "left", __('Center', 'polldaddy') => "center", __('Right', 'polldaddy') => "right" );	
          foreach( $select as $option => $value ):
            $selected = '';
            if ( $settings->font_align == $value )
              $selected = ' selected="selected"';
            echo ( '<option value="' . $value . '" ' . $selected . '>' . $option . '</option>');
          endforeach;?>
                    </select>
                  </td>
                </tr>
                <tr>
                  <td height="30"><?php _e('Position', 'polldaddy');?></td>
                  <td>
                    <select name="font_position" onchange="pd_bind(this);" id="font_position"><?php
          $select = array( __('Top', 'polldaddy') => "top", __('Right', 'polldaddy') => "right", __('Bottom', 'polldaddy') => "bottom" );
          foreach( $select as $option => $value ) :
            $selected = '';
            if ( $settings->font_position == $value )
              $selected = ' selected="selected"';
            echo ( '<option value="' . $value . '" ' . $selected . '>' . $option . '</option>');
          endforeach;?>
                    </select>
                  </td>
                </tr>
                <tr>
                  <td height="30"><?php _e('Font', 'polldaddy');?></td>
                  <td>
                    <select name="font_family" id="font_family" onchange="pd_bind(this);"><?php
          $select = array( __('Inherit', 'polldaddy') => "", "Arial" => "arial", "Comic Sans MS" => "comic sans ms", "Courier" => "courier",  "Georgia" => "georgia", "Lucida Grande" => "lucida grande", "Tahoma" => "tahoma", "Times" => "times", "Trebuchet MS" => "trebuchet ms", "Verdana" => "verdana" );
          foreach( $select as $option => $value ) :                                               
            $selected = '';
            if ( $settings->font_family == $value )
              $selected = ' selected="selected"';
            echo ( '<option value="' . $value . '" ' . $selected . '>' . $option . '</option>' );
          endforeach;?>
                    </select>
                  </td>
                </tr>
                <tr>
                  <td height="30"><?php _e('Color', 'polldaddy');?></td>
                  <td><input type="text" onblur="pd_bind(this);" class="elmColor jscolor-picker" name="font_color" id="font_color" value="<?php echo( wp_specialchars( $settings->font_color ) ); ?>" maxlength="11" autocomplete="off"/>
                  </td>
                </tr>
                <tr>
                  <td><?php _e('Size', 'polldaddy');?></td>
                  <td>
                    <select name="font_size" id="font_size"  onchange="pd_bind(this);"><?php
          $select = array( __('Inherit', 'polldaddy') => "", "6px" => "6px", "8px" => "8px", "9px" => "9px", "10px" => "10px", "11px" => "11px", "12px" => "12px", "14px" => "14px", "16px" => "16px", "18px" => "18px", "20px" => "20px", "24px" => "24px", "30px" => "30px", "36px" => "36px", );
          foreach ( $select as $option => $value ) :
            $selected = '';
            if ( $settings->font_size == $value )
              $selected = ' selected="selected"';
            echo ( '<option value="' . $value . '" ' . $selected . '>' . $option . '</option>' . "\n" );                       
          endforeach;?>
                    </select>
                  </td>
                </tr>
                <tr>
                  <td height="30"><?php _e('Line Height', 'polldaddy');?></td>
                  <td>
                    <select name="font_line_height" id="font_line_height" onchange="pd_bind(this);"><?php
          $select = array( __('Inherit', 'polldaddy') => "", "6px" => "6px", "8px" => "8px", "9px" => "9px", "10px" => "10px", "11px" => "11px", "12px" => "12px", "14px" => "14px", "16px" => "16px", "18px" => "18px", "20px" => "20px", "24px" => "24px", "30px" => "30px", "36px" => "36px", );
          foreach ( $select as $option => $value ) :
            $selected = '';
            if ( $settings->font_line_height == $value )
              $selected = ' selected="selected"';    
            echo ( '<option value="' . $value . '" ' . $selected . '>' . $option . '</option>' . "\n" );                           
          endforeach; ?>
                    </select>
                  </td>
                </tr>
                <tr>
                  <td height="30"><?php _e('Bold', 'polldaddy');?></td>
                  <td><?php 
          $checked = '';
          if ( $settings->font_bold == 'bold' )
            $checked = ' checked="checked"';?>
                    <input type="checkbox" name="font_bold" onclick="pd_bind(this);" id="font_bold" value="bold" <?php echo( $checked ); ?> />
                  </td>
                </tr>
                <tr>
                  <td height="30"><?php _e('Italic', 'polldaddy');?></td><?php
          $checked = '';
          if( $settings->font_italic == 'italic' )
            $checked = ' checked="checked"';?>
                  <td><input type="checkbox" name="font_italic"  onclick="pd_bind(this);" id="font_italic" value="italic" <?php echo( $checked ); ?>/></td>
                </tr>
              </table>
            </div>
          </div>
          <?php            
            if ( $this->is_admin && $report_type == 'posts' ) {                   
            $exclude_post_ids = wp_specialchars( get_option( 'pd-rating-exclude-post-ids' ) ); ?>
            <div class="postbox">
              <h3><?php _e('Extra Settings', 'polldaddy');?></h3>
              <div class="inside">
                <table> 
                  <tr>
                    <td width="100" height="30"><?php _e('Rating ID', 'polldaddy');?></td>
                    <td>
                      <input type="text" name="polldaddy-post-rating-id" id="polldaddy-post-rating-id" value="<?php echo $rating_id; ?>" />
                    </td>
                    <td>
                      <span class="description">
                        <label for="polldaddy-post-rating-id"><?php _e( 'This is the rating ID used in posts', 'polldaddy' ); ?></label>
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <td width="100" height="30"><?php _e('Exclude Posts', 'polldaddy');?></td>
                    <td>
                      <input type="text" name="exclude-post-ids" id="exclude-post-ids" value="<?php echo $exclude_post_ids; ?>" />
                    </td>
                    <td>
                      <span class="description">
                        <label for="exclude-post-ids"><?php _e( 'Enter the Post IDs where you want to exclude ratings from. Please use a comma-delimited list, eg. 1,2,3', 'polldaddy' ); ?></label>
                      </span>
                    </td>
                  </tr>
                </table>
              </div>
            </div><?php
            } else if ( $this->is_admin && $report_type == 'pages' ) {                   
            $exclude_page_ids = wp_specialchars( get_option( 'pd-rating-exclude-page-ids' ) ); ?>
            <div class="postbox">
              <h3><?php _e('Extra Settings', 'polldaddy');?></h3>
              <div class="inside">
                <table> 
                  <tr>
                    <td width="100" height="30"><?php _e('Rating ID', 'polldaddy');?></td>
                    <td>
                      <input type="text" name="polldaddy-page-rating-id" id="polldaddy-page-rating-id" value="<?php echo $rating_id; ?>" />
                    </td>
                    <td>
                      <span class="description">
                        <label for="polldaddy-page-rating-id"><?php _e( 'This is the rating ID used in pages', 'polldaddy' ); ?></label>
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <td width="100" height="30"><?php _e('Exclude Pages', 'polldaddy');?></td>
                    <td>
                      <input type="text" name="exclude-page-ids" id="exclude-page-ids" value="<?php echo $exclude_page_ids; ?>" />
                    </td>
                    <td>
                      <span class="description">
                        <label for="exclude-page-ids"><?php _e( 'Enter the Page IDs where you want to exclude ratings from. Please use a comma-delimited list, eg. 1,2,3', 'polldaddy' ); ?></label>
                      </span>
                    </td>
                  </tr>
                </table>
              </div>
            </div
            <?php } else if ( $this->is_admin && $report_type == 'comments' ) { ?>
            <div class="postbox">
              <h3><?php _e('Extra Settings', 'polldaddy');?></h3>
              <div class="inside">
                <table> 
                  <tr>
                    <td width="100" height="30"><?php _e('Rating ID', 'polldaddy');?></td>
                    <td>
                      <input type="text" name="polldaddy-comment-rating-id" id="polldaddy-comment-rating-id" value="<?php echo $rating_id; ?>" />
                    </td>
                    <td>
                      <span class="description">
                        <label for="polldaddy-comment-rating-id"><?php _e( 'This is the rating ID used in comments', 'polldaddy' ); ?></label>
                      </span>
                    </td>
                  </tr>
                </table>
              </div>
            </div
            <?php } ?>
        </div>
      </div>
    </form>
	<script language="javascript">
	jQuery( document ).ready(function(){ 
		plugin = new Plugin( {
			delete_rating: '<?php _e( 'Are you sure you want to delete the rating for "%s"?','polldaddy'); ?>',
			delete_poll: '<?php _e( 'Are you sure you want to delete "%s"?','polldaddy'); ?>',
			delete_answer: '<?php _e( 'Are you sure you want to delete this answer?','polldaddy'); ?>',
			delete_answer_title: '<?php _e( 'delete this answer','polldaddy'); ?>',
			standard_styles: '<?php _e( 'Standard Styles','polldaddy'); ?>',
			custom_styles: '<?php _e( 'Custom Styles','polldaddy'); ?>'
		} );
	});
	</script>
    <script type="text/javascript">
    PDRTJS_settings = <?php echo ( $settings_text ); ?>;
    PDRTJS_settings.id = "1"; 
    PDRTJS_settings.unique_id = "xxx";
    PDRTJS_settings.title = "";
    PDRTJS_settings.override = "<?php echo( $rating_id ); ?>";
    PDRTJS_settings.permalink = "";
    PDRTJS_1 = new PDRTJS_RATING( PDRTJS_settings );
    pd_change_type( <?php echo ( $rating_type ) ?> );
    </script><?php 
      } ?>
    </div><?php 
    } // from if !error ?>
    </div><?php
  }
	       
  function update_rating(){
    $rating_type = 0;
    $rating_id = 0;
    $new_rating_id = 0;
    $type = 'post';
    $set = null;
    
    if( isset( $_REQUEST['rating_id'] ) ) 
      $rating_id = (int) $_REQUEST['rating_id'];
    
    if( isset( $_REQUEST['polldaddy-post-rating-id'] ) ) {
      $new_rating_id = (int) $_REQUEST['polldaddy-post-rating-id'];
      $type = 'posts';
    } 
    else if( isset( $_REQUEST['polldaddy-page-rating-id'] ) ) {   
      $new_rating_id = (int) $_REQUEST['polldaddy-page-rating-id']; 
      $type = 'pages';
    }
    else if( isset( $_REQUEST['polldaddy-comment-rating-id'] ) ) {  
      $new_rating_id = (int) $_REQUEST['polldaddy-comment-rating-id']; 
      $type = 'comments';
    } else{
      $new_rating_id = $rating_id;
    }        
    
    if( $rating_id > 0 && $rating_id == $new_rating_id ) {
      if( isset( $_REQUEST['rating_type'] ) && $_REQUEST['rating_type'] == 'stars' ) {
        $set->type = 'stars';
        $rating_type = 0;
        if( isset( $_REQUEST['star_color'] ) )
          $set->star_color = attribute_escape( $_REQUEST['star_color'] );
      } else {
        $set->type = 'nero';
        $rating_type = 1;
        if( isset( $_REQUEST['nero_style'] ) )
          $set->star_color = attribute_escape( $_REQUEST['nero_style'] );
      }
      
      $set->size             = wp_specialchars( $_REQUEST['size'], 1 );
      $set->custom_star      = wp_specialchars( clean_url( $_REQUEST['custom_star'] ) , 1 );
      $set->font_align       = wp_specialchars( $_REQUEST['font_align'], 1 );
      $set->font_position    = wp_specialchars( $_REQUEST['font_position'], 1 );
      $set->font_family      = wp_specialchars( $_REQUEST['font_family'], 1);
      $set->font_size        = wp_specialchars( $_REQUEST['font_size'], 1 );
      $set->font_line_height = wp_specialchars( $_REQUEST['font_line_height'], 1 );
      
      if ( isset( $_REQUEST['font_bold'] ) && $_REQUEST['font_bold'] == 'bold' )
        $set->font_bold = 'bold';
      else
        $set->font_bold = 'normal';
      
      if ( isset( $_REQUEST['font_italic'] ) && $_REQUEST['font_italic'] == 'italic' )
        $set->font_italic = 'italic';
      else
        $set->font_italic = 'normal';
      
      $set->text_vote	   = wp_specialchars( $_REQUEST['text_vote'], 1 );
      $set->text_votes     = wp_specialchars( $_REQUEST['text_votes'], 1 );
      $set->text_rate_this = wp_specialchars( $_REQUEST['text_rate_this'], 1 );
      $set->text_1_star    = wp_specialchars( $_REQUEST['text_1_star'], 1 );
      $set->text_2_star    = wp_specialchars( $_REQUEST['text_2_star'], 1 );
      $set->text_3_star    = wp_specialchars( $_REQUEST['text_3_star'], 1 );
      $set->text_4_star    = wp_specialchars( $_REQUEST['text_4_star'], 1 );
      $set->text_5_star    = wp_specialchars( $_REQUEST['text_5_star'], 1 );
      $set->text_thank_you = wp_specialchars( $_REQUEST['text_thank_you'], 1 );
      $set->text_rate_up   = wp_specialchars( $_REQUEST['text_rate_up'], 1 );
      $set->text_rate_down = wp_specialchars( $_REQUEST['text_rate_down'], 1 );
      $set->font_color     = wp_specialchars( $_REQUEST['font_color'], 1 );
      
      $settings_text = json_encode( $set );
      
      $polldaddy = $this->get_client( WP_POLLDADDY__PARTNERGUID, $this->rating_user_code );
      $polldaddy->reset();		
      $rating = $polldaddy->update_rating( $rating_id, $settings_text, $rating_type );
    }
    else if( $this->is_admin && $new_rating_id > 0 ){
      switch( $type ){
        case 'pages':
          update_option( 'pd-rating-pages-id', $new_rating_id );              
          if( (int) get_option( 'pd-rating-pages' ) > 0 )
            update_option( 'pd-rating-pages', $new_rating_id );
          break;
        case 'comments':
          update_option( 'pd-rating-comments-id', $new_rating_id );              
          if( (int) get_option( 'pd-rating-comments' ) > 0 )
            update_option( 'pd-rating-comments', $new_rating_id );
          break;
        case 'posts':
          update_option( 'pd-rating-posts-id', $new_rating_id );              
          if( (int) get_option( 'pd-rating-posts' ) > 0 )
            update_option( 'pd-rating-posts', $new_rating_id );
      }
    }
    
    if( $this->is_admin ) {
      if( $type=='posts' && isset( $_REQUEST['exclude-post-ids'] ) ) {
        $exclude_post_ids = $_REQUEST['exclude-post-ids'];  
        if( empty( $exclude_post_ids ) ){
          update_option( 'pd-rating-exclude-post-ids', '' );
        } else{       
          $post_ids = array();
          $ids = explode( ',', $exclude_post_ids );
          if( !empty( $ids ) ){
            foreach( (array) $ids as $id ){
              if( (int) $id > 0 )
                $post_ids[] = (int) $id;  
            }  
          }   
          if( !empty( $post_ids ) ){
            $exclude_post_ids = implode( ',', $post_ids );
            update_option( 'pd-rating-exclude-post-ids', $exclude_post_ids );
          }
        }
      }
      
      if( $type=='pages' && isset( $_REQUEST['exclude-page-ids'] ) ) {
        $exclude_page_ids = $_REQUEST['exclude-page-ids'];  
        if( empty( $exclude_page_ids ) ){
          update_option( 'pd-rating-exclude-page-ids', '' );
        } else{
          $page_ids = array();
          $ids = explode( ',', $exclude_page_ids );
          if( !empty( $ids ) ){
            foreach( (array) $ids as $id ){
              if( (int) $id > 0 )
                $page_ids[] = (int) $id;  
            }  
          }   
          if( !empty( $page_ids ) ){
            $exclude_page_ids = implode( ',', $page_ids );
            update_option( 'pd-rating-exclude-page-ids', $exclude_page_ids );
          }
        }  
      }   
    }     
}
	function rating_reports() {
    $polldaddy = $this->get_client( WP_POLLDADDY__PARTNERGUID, $this->rating_user_code );
    $rating_id = get_option( 'pd-rating-posts-id' );
    
    $report_type = 'posts';
    $period = '7';
    $show_rating = 0;
    
    if ( isset( $_REQUEST['change-report-to'] ) ){
      switch ( $_REQUEST['change-report-to'] ) :
        case 'pages':
        $report_type = 'pages';
        $rating_id = (int) get_option( 'pd-rating-pages-id' );
        break;
        
        case 'comments':
        $report_type = 'comments';
        $rating_id = get_option( 'pd-rating-comments-id' );
        break;
        
        case 'posts':
          $report_type = 'posts';
          $rating_id = get_option( 'pd-rating-posts-id' );
          break;
      endswitch;
    }

		if ( isset( $_REQUEST['filter'] ) &&  $_REQUEST['filter'] ){
			switch ( $_REQUEST['filter'] ) :
				case '1':
					$period = '1';
					break;

				case '7':
					$period = '7';
					break;

				case '31':
			        $period = '31';
					break;

				case '90':
					$period = '90';
					break;

				case '365':
					$period = '365';
					break;

				case 'all':
					$period = 'all';
					break;
			endswitch;
		}

		$page_size = 15;	
		$current_page = 1;

		if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'change-report' ){
			$current_page = 1;
		} else {
			if ( isset( $_REQUEST['paged'] ) ) {
				$current_page = (int) $_REQUEST['paged'];
				if ( $current_page == 0 )
					$current_page = 1;
			}
		}

		$start = ( $current_page * $page_size ) - $page_size;
		$end = $page_size;

		$response = $polldaddy->get_rating_results( $rating_id, $period, $start, $end );	
    
    $total = $total_pages = 0;
    $ratings = null;
    
    if( !empty($response) ){ 
      $ratings = $response->rating;
      $total = (int) $response->_total;
      $total_pages = ceil( $total / $page_size );    
    } 
    
		$page_links = paginate_links( array(
			'base'       => add_query_arg( array ('paged' => '%#%', 'change-report-to' => $report_type, 'filter' => $period ) ),
			'format'     => '',
			'prev_text'  => __('&laquo;', 'polldaddy'),
			'next_text'  => __('&raquo;', 'polldaddy'),
			'total'      => $total_pages,
			'current'    => $current_page
		));
	?>
		<div class="wrap">
			<h2><?php _e('Rating Reports', 'polldaddy');?> <span style="font-size: 16px;">(<?php echo ( $report_type ); ?>)</span></h2>
			<div class="clear"></div>
			<form method="post" action="admin.php?page=ratings&action=reports">
				<div class="tablenav">
					<div class="alignleft actions">
						<select name="action">
							<option selected="selected" value=""><?php _e( 'Actions', 'polldaddy' ); ?></option>
							<option value="delete"><?php _e( 'Delete', 'polldaddy' ); ?></option>
						</select>
						<input type="hidden" name="id" id="id" value="<?php echo (int) $rating_id; ?>" />
						<input class="button-secondary action" type="submit" name="doaction" value="<?php _e( 'Apply', 'polldaddy' ); ?>" />
						<?php wp_nonce_field( 'action-rating_bulk' ); ?>
						<select name="change-report-to"><?php
    $select = array( __('Posts', 'polldaddy') => "posts", __('Pages', 'polldaddy') => "pages", __('Comments', 'polldaddy') => "comments" );
    foreach ( $select as $option => $value ) :
        $selected = '';
        if ( $value == $report_type )
            $selected = ' selected="selected"';?>
        <option value="<?php echo $value; ?>" <?php echo $selected; ?>><?php echo $option; ?></option>
    <?php endforeach;  ?>
						</select>
            			<select name="filter"><?php
		$select = array( __('Last 24 hours', 'polldaddy') => "1", __('Last 7 days', 'polldaddy') => "7", __('Last 31 days', 'polldaddy') => "31", __('Last 3 months', 'polldaddy') => "90", __('Last 12 months', 'polldaddy') => "365", __('All time', 'polldaddy') => "all" );
		foreach ( $select as $option => $value ) :
			$selected = '';
			if ( $value == $period )
				$selected = ' selected="selected"';?>
        					<option value="<?php echo $value; ?>" <?php echo $selected; ?>><?php echo $option; ?></option>
    <?php endforeach; ?>
          				</select>
          				<input class="button-secondary action" type="submit" value="<?php _e('Filter', 'polldaddy');?>" />
					</div>
					<div class="alignright">
						<div class="tablenav-pages">
							<?php echo( $page_links ); ?>
						</div>	
					</div>
				</div>

			<table class="widefat"><?php
			if ( empty( $ratings ) ) { ?>
				<tbody>
					<tr>
						<td colspan="4"><?php printf(__('No ratings have been collected for your %s yet.', 'polldaddy'), $report_type); ?></td>
					</tr>
				</tbody><?php
			} else {  ?>
				<thead>
					<tr>
			 	 		<th scope="col" class="manage-column column-cb check-column" id="cb"><input type="checkbox"></th>
						<th scope="col" class="manage-column column-title" id="title"><?php _e('Title', 'polldaddy');?></th>
						<th scope="col" class="manage-column column-id" id="id"><?php _e('Unique ID', 'polldaddy');?></th>
						<th scope="col" class="manage-column column-date" id="date"><?php _e('Start Date', 'polldaddy');?></th>
						<th scope="col" class="manage-column column-vote num" id="votes"><?php _e('Votes', 'polldaddy');?></th>
						<th scope="col" class="manage-column column-rating num" id="rating"><?php _e('Average Rating', 'polldaddy');?></th>
					</tr>
				</thead>
				<tbody><?php
				$alt_counter = 0;
				$alt = '';

				foreach ( $ratings as $rating  ) :
					$delete_link = clean_url( wp_nonce_url( add_query_arg( array( 'action' => 'delete', 'id' => $rating_id, 'rating' => $rating->uid, 'change-report-to' => $report_type, 'message' => false ) ), "delete-rating_$rating->uid" ) );
					$alt_counter++;?>
					<tr <?php echo ( $alt_counter & 1 ) ? ' class="alternate"' : ''; ?>>
						<th class="check-column" scope="row"><input type="checkbox" value="<?php echo wp_specialchars( $rating->uid ); ?>" name="rating[]" /></th>
						<td class="post-title column-title">
							<strong><a href="<?php echo clean_url( $rating->permalink ); ?>"><?php echo strlen( wp_specialchars( $rating->title ) ) > 75 ? substr( wp_specialchars( $rating->title ), 0, 72 ) . '&hellip' : wp_specialchars( $rating->title ); ?></a></strong>
							<div class="row-actions">
							<?php if ( $delete_link ) { ?>
								<span class="delete"><a class="delete-rating delete" href="<?php echo $delete_link; ?>"><?php _e( 'Delete', 'polldaddy' ); ?></a></span>
							<?php } ?>
							</div>
						</td>
						<td class="column-id">
							<?php echo wp_specialchars( $rating->uid ); ?>
						</td>
						<td class="date column-date">
							<abbr title="<?php echo date( __('Y/m/d g:i:s A', 'polldaddy'), $rating->date ); ?>"><?php echo str_replace( '-', '/', substr( wp_specialchars( $rating->date ), 0, 10 ) ); ?></abbr>
						</td>
						<td class="column-vote num"><?php echo number_format( $rating->_votes ); ?></td>
						<td class="column-rating num"><table width="100%"><tr align="center"><td style="border:none;"><?php
					if ( $rating->_type == 0 ) {
						$avg_rating = $this->round( $rating->average_rating, 0.5 );?>
							<div style="width:100px"><?php
						$image_pos = '';
				
						for ( $c = 1; $c <= 5; $c++ ) :
							if ( $avg_rating > 0 ) {
								if ( $avg_rating < $c )
									$image_pos = 'bottom left';
								if ( $avg_rating == ( $c - 1 + 0.5 ) )
									$image_pos = 'center left';
							} ?>
								<div style="width: 20px; height: 20px; background: url(http://i.polldaddy.com/ratings/images/star-yellow-med.png) <?php echo $image_pos; ?>; float: left;"></div><?php 
						endfor; ?>
								<br class="clear" />
							</div><?php 
					} else { ?>
							<div>
								<div style="margin: 0px 0px 0px 20px; background: transparent url(http://i.polldaddy.com/images/rate-graph-up.png); width: 20px; height: 20px; float: left;"></div>
								<div style="float:left; line-height: 20px; padding: 0px 10px 0px 5px;"><?php echo number_format ( $rating->total1 );?></div>
								<div style="margin: 0px; background: transparent url(http://i.polldaddy.com/images/rate-graph-dn.png); width: 20px; height: 20px; float: left;"></div>
								<div style="float:left; line-height: 20px; padding: 0px 10px 0px 5px;"><?php echo number_format( $rating->total2 );?></div>
								<br class="clear" />
							</div><?php 
					} ?>
							</td></tr></table>
						</td>
					</tr><?php  
				endforeach;
				?>
				</tbody><?php 
			} ?>
			</table>
	    	<div class="tablenav">
	        	<div class="alignright">
	            	<div class="tablenav-pages">
	                	<?php echo $page_links; ?>
	            	</div>
	        	</div>
	    	</div>
			</form>		
		</div>
		<p></p>
	<script language="javascript">
	jQuery( document ).ready(function(){ 
		plugin = new Plugin( {
			delete_rating: '<?php _e( 'Are you sure you want to delete the rating for "%s"?','polldaddy'); ?>',
			delete_poll: '<?php _e( 'Are you sure you want to delete "%s"?','polldaddy'); ?>',
			delete_answer: '<?php _e( 'Are you sure you want to delete this answer?','polldaddy'); ?>',
			delete_answer_title: '<?php _e( 'delete this answer','polldaddy'); ?>',
			standard_styles: '<?php _e( 'Standard Styles','polldaddy'); ?>',
			custom_styles: '<?php _e( 'Custom Styles','polldaddy'); ?>'
		} );
	});
	</script><?php
	}
	
	function plugin_options() {
	  if ( isset( $_POST['polldaddy_email'] ) ){
      $account_email = attribute_escape( $_POST['polldaddy_email'] );
    }
    else{
      $polldaddy = $this->get_client( WP_POLLDADDY__PARTNERGUID, $this->user_code );
      $account = $polldaddy->get_account();
		  
		  if( !empty($account) )
		    $account_email = attribute_escape( $account->email );
		    
		  $polldaddy->reset();
		  $poll = $polldaddy->get_poll( 1 );
		  
		  $options = array(
			101 => __( 'Aluminum Narrow','polldaddy'),
			102 => __( 'Aluminum Medium','polldaddy'),
			103 => __( 'Aluminum Wide','polldaddy'),
			104 => __( 'Plain White Narrow','polldaddy'),
			105 => __( 'Plain White Medium','polldaddy'),
			106 => __( 'Plain White Wide','polldaddy'),
			107 => __( 'Plain Black Narrow','polldaddy'),
			108 => __( 'Plain Black Medium','polldaddy'),
			109 => __( 'Plain Black Wide','polldaddy'),
			110 => __( 'Paper Narrow','polldaddy'),
			111 => __( 'Paper Medium','polldaddy'),
			112 => __( 'Paper Wide','polldaddy'),
			113 => __( 'Skull Dark Narrow','polldaddy'),
			114 => __( 'Skull Dark Medium','polldaddy'),
			115 => __( 'Skull Dark Wide','polldaddy'),
			116 => __( 'Skull Light Narrow','polldaddy'),
			117 => __( 'Skull Light Medium','polldaddy'),
			118 => __( 'Skull Light Wide','polldaddy'),
			157 => __( 'Micro','polldaddy'),
			119 => __( 'Plastic White Narrow','polldaddy'),
			120 => __( 'Plastic White Medium','polldaddy'),
			121 => __( 'Plastic White Wide','polldaddy'),
			122 => __( 'Plastic Grey Narrow','polldaddy'),
			123 => __( 'Plastic Grey Medium','polldaddy'),
			124 => __( 'Plastic Grey Wide','polldaddy'),
			125 => __( 'Plastic Black Narrow','polldaddy'),
			126 => __( 'Plastic Black Medium','polldaddy'),
			127 => __( 'Plastic Black Wide','polldaddy'),
			128 => __( 'Manga Narrow','polldaddy'),
			129 => __( 'Manga Medium','polldaddy'),
			130 => __( 'Manga Wide','polldaddy'),
			131 => __( 'Tech Dark Narrow','polldaddy'),
			132 => __( 'Tech Dark Medium','polldaddy'),
			133 => __( 'Tech Dark Wide','polldaddy'),
			134 => __( 'Tech Grey Narrow','polldaddy'),
			135 => __( 'Tech Grey Medium','polldaddy'),
			136 => __( 'Tech Grey Wide','polldaddy'),
			137 => __( 'Tech Light Narrow','polldaddy'),
			138 => __( 'Tech Light Medium','polldaddy'),
			139 => __( 'Tech Light Wide','polldaddy'),
			140 => __( 'Working Male Narrow','polldaddy'),
			141 => __( 'Working Male Medium','polldaddy'),
			142 => __( 'Working Male Wide','polldaddy'),
			143 => __( 'Working Female Narrow','polldaddy'),
			144 => __( 'Working Female Medium','polldaddy'),
			145 => __( 'Working Female Wide','polldaddy'),
			146 => __( 'Thinking Male Narrow','polldaddy'),
			147 => __( 'Thinking Male Medium','polldaddy'),
			148 => __( 'Thinking Male Wide','polldaddy'),
			149 => __( 'Thinking Female Narrow','polldaddy'),
			150 => __( 'Thinking Female Medium','polldaddy'),
			151 => __( 'Thinking Female Wide','polldaddy'),
			152 => __( 'Sunset Narrow','polldaddy'),
			153 => __( 'Sunset Medium','polldaddy'),
			154 => __( 'Sunset Wide','polldaddy'),
			155 => __( 'Music Medium','polldaddy'),
			156 => __( 'Music Wide','polldaddy')
		);
  		
  		$polldaddy->reset();
  		$styles = $polldaddy->get_styles();
  		
  		if( !empty( $styles ) && !empty( $styles->style ) && count( $styles->style ) > 0 ){
  			foreach( (array) $styles->style as $style ){
  				$options[ (int) $style->_id ] = $style->title;	
  			}
  		}	
    }
		$this->print_errors(); 
  ?>
<div id="options-page" class="wrap">
  <div class="icon32" id="icon-options-general"><br/></div>
  <h2>
    <?php _e( 'Options', 'polldaddy' ); ?>
  </h2>
    <?php if( $this->is_admin || $this->multiple_accounts ) {?>
  <h3>
    <?php _e( 'PollDaddy Account Info', 'polldaddy' ); ?>
  </h3>
  <p>
  <?php _e( 'This is the PollDadddy account you currently have imported into your WordPress account', 'polldaddy' ); ?>.
  </p>
  <form action="" method="post">
    <table class="form-table">
      <tbody>
        <tr class="form-field form-required">
          <th valign="top" scope="row">
            <label for="polldaddy-email">
              <?php _e( 'PollDaddy Email Address', 'polldaddy' ); ?>
            </label>
          </th>
          <td>
            <input type="text" name="polldaddy_email" id="polldaddy-email" aria-required="true" size="40" value="<?php echo $account_email; ?>" />
          </td>
        </tr>
        <tr class="form-field form-required">
          <th valign="top" scope="row">
            <label for="polldaddy-password">
              <?php _e( 'PollDaddy Password', 'polldaddy' ); ?>
            </label>
          </th>
          <td>
            <input type="password" name="polldaddy_password" id="polldaddy-password" aria-required="true" size="40" />
          </td>
        </tr>
      </tbody>
    </table>
    <p class="submit">
      <?php wp_nonce_field( 'polldaddy-account' ); ?>
      <input type="hidden" name="action" value="import-account" />
      <input type="hidden" name="account" value="import" />
      <input type="submit" value="<?php echo attribute_escape( __( 'Import Account', 'polldaddy' ) ); ?>" />
    </p>
  </form>
  <br />
  <?php } ?>
  <h3>
    <?php _e( 'General Settings', 'polldaddy' ); ?>
  </h3>
  <form action="" method="post">
    <table class="form-table">
      <tbody>
        <tr valign="top">
          <th valign="top" scope="row">
            <label>
              <?php _e( 'Default poll settings', 'polldaddy' ); ?>
            </label>
          </th>
          <td>
            <fieldset>
              <legend class="screen-reader-text"><span>poll-defaults</span></legend><?php
				      $selected = '';
				      if ( $poll->multipleChoice == 'yes' )
					       $selected = 'checked="checked"';?>
              <label for="multipleChoice"><input type="checkbox" <?php echo $selected; ?> value="1" id="multipleChoice" name="multipleChoice"> <?php _e( 'Multiple Choice', 'polldaddy' ); ?></label>
              <br /><?php
              $selected = '';
				      if ( $poll->randomiseAnswers == 'yes' )
					       $selected = 'checked="checked"';?>
              <label for="randomiseAnswers"><input type="checkbox" <?php echo $selected; ?> value="1" id="randomiseAnswers" name="randomiseAnswers"> <?php _e( 'Randomise Answers', 'polldaddy' ); ?></label>
              <br /><?php
              $selected = '';
				      if ( $poll->otherAnswer == 'yes' )
					       $selected = 'checked="checked"';?>
              <label for="otherAnswer"><input type="checkbox" <?php echo $selected; ?> value="1" id="otherAnswer" name="otherAnswer"> <?php _e( 'Other Answer', 'polldaddy' ); ?></label>
              <br /><?php
				      $selected = '';
				      if ( $poll->sharing == 'yes' )
					       $selected = 'checked="checked"';?>
              <label for="sharing"><input type="checkbox" <?php echo $selected; ?> value="1" id="sharing" name="sharing"> <?php _e( 'Sharing', 'polldaddy' ); ?></label>
              <br />
              <label for="resultsType">
                <select id="resultsType" name="resultsType">
                  <option <?php echo $poll->resultsType == 'show' ? 'selected="selected"':''; ?> value="show"><?php _e('Show', 'polldaddy'); ?></option>
                  <option <?php echo $poll->resultsType == 'hide' ? 'selected="selected"':''; ?> value="hide"><?php _e('Hide', 'polldaddy'); ?></option>
                  <option <?php echo $poll->resultsType == 'percent' ? 'selected="selected"':''; ?> value="percent"><?php _e('Percentages', 'polldaddy'); ?></option>
                </select> <?php _e( 'Poll results', 'polldaddy' ); ?>
              </label>
              <br />
              <label for="styleID">
                <select id="styleID" name="styleID"><?php 	
                foreach ( (array) $options as $styleID => $label ) :
        						$selected = $styleID == $poll->styleID ? ' selected="selected"' : ''; ?>
        						<option value="<?php echo (int) $styleID; ?>"<?php echo $selected; ?>><?php echo wp_specialchars( $label ); ?></option><?php 	
                endforeach;?>
                </select> <?php _e( 'Poll style', 'polldaddy' ); ?>
              </label>
              <br />
              <label for="blockRepeatVotersType">
                <select id="poll-block-repeat" name="blockRepeatVotersType">
                  <option <?php echo $poll->blockRepeatVotersType == 'off' ? 'selected="selected"':''; ?> value="off"><?php _e('Off', 'polldaddy'); ?></option>
                  <option <?php echo $poll->blockRepeatVotersType == 'cookie' ? 'selected="selected"':''; ?> value="cookie"><?php _e('Cookie', 'polldaddy'); ?></option>
                  <option <?php echo $poll->blockRepeatVotersType == 'cookieip' ? 'selected="selected"':''; ?> value="cookieip"><?php _e('Cookie & IP address', 'polldaddy'); ?></option>
                </select> <?php _e( 'Block repeat voters', 'polldaddy' ); ?>
              </label>
              <br />
              <label for="blockExpiration">
                <select id="blockExpiration" name="blockExpiration">
                  <option value="0" <?php echo $poll->blockExpiration == 0 ? 'selected="selected"':''; ?>><?php _e('Never', 'polldaddy'); ?></option>
                  <option value="3600" <?php echo $poll->blockExpiration == 3600 ? 'selected="selected"':''; ?>><?php printf( __('%d hour', 'polldaddy'), 1 ); ?></option>
                  <option value="10800" <?php echo (int) $poll->blockExpiration == 10800 ? 'selected="selected"' : ''; ?>><?php printf( __('%d hours', 'polldaddy'), 3 ); ?></option>
          				<option value="21600" <?php echo (int) $poll->blockExpiration == 21600 ? 'selected="selected"' : ''; ?>><?php printf( __('%d hours', 'polldaddy'), 6 ); ?></option>
          				<option value="43200" <?php echo (int) $poll->blockExpiration == 43200 ? 'selected="selected"' : ''; ?>><?php printf( __('%d hours', 'polldaddy'), 12 ); ?></option>
          				<option value="86400" <?php echo (int) $poll->blockExpiration == 86400 ? 'selected="selected"' : ''; ?>><?php printf( __('%d day', 'polldaddy'), 1 ); ?></option>
          				<option value="604800" <?php echo (int) $poll->blockExpiration == 604800 ? 'selected="selected"' : ''; ?>><?php printf( __('%d week', 'polldaddy'), 1 ); ?></option>
          				<option value="2419200" <?php echo (int) $poll->blockExpiration == 2419200 ? 'selected="selected"' : ''; ?>><?php printf( __('%d month', 'polldaddy'), 1 ); ?></option>
                </select> <?php _e( 'Block expiration limit', 'polldaddy' ); ?>
              </label>
              <br />
            </fieldset>
          </td>
        </tr>
        <?php $this->plugin_options_add(); ?>
      </tbody>
    </table>
    <p class="submit">
      <?php wp_nonce_field( 'polldaddy-account' ); ?>
      <input type="hidden" name="action" value="update-options" />
      <input type="submit" value="<?php echo attribute_escape( __( 'Save Options', 'polldaddy' ) ); ?>" />
    </p>
  </form>
</div>
  <?php
  }
  
  function plugin_options_add(){}

	function round($number, $increments) {
		$increments = 1 / $increments;
		return ( round ( $number * $increments ) / $increments );
	}	

	function signup() {
		return $this->api_key_page();
	}

	function can_edit( &$poll ) {	
    if ( empty( $poll->_owner ) )
			return true;
			
		if ( $this->id == $poll->_owner )
			return true;

		return (bool) current_user_can( 'edit_others_posts' );
	}
}

require 'rating.php';
require 'polldaddy-org.php';