<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class jsSupportTicketServerCalls extends JS_SUPPORTTICKETUpdater{

	private static $jsst_server_url = 'https://jshelpdesk.com/setup/index.php';

	public static function jsstPluginUpdateCheck($jsst_token_arrray_json) {
		$jsst_args = array(
			'request' => 'pluginupdatecheck',
			'token' => $jsst_token_arrray_json,
			'domain' => site_url()
		);

		$jsst_url = self::$jsst_server_url . '?' . http_build_query( $jsst_args, '', '&' );
		$jsst_request = wp_remote_get($jsst_url);

		if ( is_wp_error( $jsst_request ) || wp_remote_retrieve_response_code( $jsst_request ) != 200 ) {
			$jsst_error_message = 'pluginupdatecheck case returned error';
			JSSTincluder::getJSModel('systemerror')->addSystemError($jsst_error_message);
			return false;
		}

		$jsst_response = wp_remote_retrieve_body( $jsst_request );
		$jsst_response = json_decode($jsst_response);

		if ( is_object( $jsst_response ) ) {
			return $jsst_response;
		} else {
			$jsst_error_message = 'pluginupdatecheck case returned data which was not correct';
			JSSTincluder::getJSModel('systemerror')->addSystemError($jsst_error_message);
			return false;
		}
	}

	public static function jsstPluginUpdateCheckFromCDN() {

		$jsst_url = "https://d265tox1ugu9o3.cloudfront.net/addonslatestversions.txt";
		$jsst_request = wp_remote_get($jsst_url);

		if ( is_wp_error( $jsst_request ) || wp_remote_retrieve_response_code( $jsst_request ) != 200 ) {
			$jsst_error_message = 'pluginupdatecheck cdn case returned error';
			JSSTincluder::getJSModel('systemerror')->addSystemError($jsst_error_message);
			return false;
		}

		$jsst_response = wp_remote_retrieve_body( $jsst_request );
		$jsst_response = json_decode($jsst_response);

		if ( is_object( $jsst_response ) ) {
			return $jsst_response;
		} else {
			$jsst_error_message = 'pluginupdatecheck cdn case returned data which was not correct';
			JSSTincluder::getJSModel('systemerror')->addSystemError($jsst_error_message);
			return false;
		}
	}

	public static function jsstGenerateToken($jsst_transaction_key,$jsst_addon_name) {
			$jsst_args = array(
				'request' => 'generatetoken',
				'transactionkey' => $jsst_transaction_key,
				'productcode' => $jsst_addon_name,
				'domain' => JSSTincluder::getJSModel('jssupportticket')->getSiteUrl()
			);

			$jsst_url = self::$jsst_server_url . '?' . http_build_query( $jsst_args, '', '&' );
			$jsst_request = wp_remote_get($jsst_url);
			if ( is_wp_error( $jsst_request ) || wp_remote_retrieve_response_code( $jsst_request ) != 200 ) {
				$jsst_error_message = 'generatetoken case returned error';
				JSSTincluder::getJSModel('systemerror')->addSystemError($jsst_error_message);
				return array('error'=>$jsst_error_message);
			}

			$jsst_response = wp_remote_retrieve_body( $jsst_request );
			$jsst_response = json_decode($jsst_response,true);

			if ( is_array( $jsst_response ) ) {
				return $jsst_response;
			} else {
				$jsst_error_message = 'generatetoken case returned data which was not correct';
				JSSTincluder::getJSModel('systemerror')->addSystemError($jsst_error_message);
				return array('error'=>$jsst_error_message);
			}
			return false;
		}


	public static function jsstGetLatestVersions() {
		$jsst_args = array(
				'request' => 'getlatestversions'
			);
		$jsst_request = wp_remote_get( 'https://jshelpdesk.com/appsys/addoninfo/index.php' . '?' . http_build_query( $jsst_args, '', '&' ) );

		if ( is_wp_error( $jsst_request ) || wp_remote_retrieve_response_code( $jsst_request ) != 200 ) {
			$jsst_error_message = 'getlatestversions case returned error';
			JSSTincluder::getJSModel('systemerror')->addSystemError($jsst_error_message);
			return false;
		}

		$jsst_response = wp_remote_retrieve_body( $jsst_request );
		// $jsst_response = array();
		// $jsst_response['js-support-ticket-agent'] = '1.1.0';
		// $jsst_response['js-support-ticket-actions'] = '1.1.0';
		// $jsst_response['js-support-ticket-announcement'] = '1.1.0';
		// $jsst_response['js-support-ticket-feedback'] = '1.1.0';

		$jsst_response = json_decode($jsst_response,true);
		if ( is_array( $jsst_response ) ) {
			return $jsst_response;
		} else {
			$jsst_error_message = 'getlatestversions case returned data which was not correct';
			JSSTincluder::getJSModel('systemerror')->addSystemError($jsst_error_message);
			return false;
		}
	}

	public static function jsstPluginInformation( $jsst_args ) {
		$jsst_defaults = array(
			'request'        => 'plugininformation',
			'plugin_slug'    => '',
			'version'        => '',
			'token'    => '',
			'domain'          => site_url()
		);

		$jsst_args    = wp_parse_args( $jsst_args, $jsst_defaults );
		$jsst_request = wp_remote_get( 'https://jshelpdesk.com/appsys/addoninfo/index.php' . '?' . http_build_query( $jsst_args, '', '&' ) );

		if ( is_wp_error( $jsst_request ) || wp_remote_retrieve_response_code( $jsst_request ) != 200 ) {
			$jsst_error_message = 'plugininformation case returned data error';
			JSSTincluder::getJSModel('systemerror')->addSystemError($jsst_error_message);
			return false;
		}
		$jsst_response = wp_remote_retrieve_body( $jsst_request );

		$jsst_response = json_decode($jsst_response);

		if ( is_object( $jsst_response ) ) {
			return $jsst_response;
		} else {
			$jsst_error_message = 'plugininformation case returned data which is not correct';
			JSSTincluder::getJSModel('systemerror')->addSystemError($jsst_error_message);
			return false;
		}
	}
}
