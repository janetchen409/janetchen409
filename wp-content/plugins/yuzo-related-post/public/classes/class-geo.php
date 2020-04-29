<?php
/*
This PHP class is free software: you can redistribute it and/or modify
the code under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

freegeoip.app provides a free IP gelocation API for software developers.
It uses a database of IP addresses that are associated to cities along with other
relevant information like time zone, latitude and longitude.
@see https://github.com/wp-plugins/ip-geo-block/blob/master/classes/class-ip-geo-block-api.php
*/
if( ! class_exists( 'geoPlugin' ) ){
class geoPlugin {

	//the geoPlugin server
	var $host 		= 'https://freegeoip.app/json/{IP}';

	//initiate the geoPlugin vars
	var $ip                     = null;
	var $city                   = null;
	var $region                 = null;
	var $regionCode             = null;
	var $regionName             = null;
	var $dmaCode                = null;
	var $countryCode            = null;
	var $countryName            = null;
	var $inEU                   = null;
	var $euVATrate              = false;
	var $continentCode          = null;
	var $continentName          = null;
	var $latitude               = null;
	var $longitude              = null;
	var $locationAccuracyRadius = null;
	var $timezone               = null;
	var $currencyCode           = null;

	public function __construct( $ip = null ) {
        $this->locate( $ip );
	}

	public function locate($ip = null) {

		global $_SERVER;

		if ( is_null( $ip ) ) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		$host = str_replace( '{IP}', $ip, $this->host );
		//$host = str_replace( '{CURRENCY}', $this->currency, $host );
		//$host = str_replace( '{LANG}', $this->lang, $host );

		$data = array();

		$response = $this->fetch($host);

		if( $response )
			$data = ($response);

		if(  ! empty( $data )  ){
			//set the geoPlugin vars
			$this->ip          = $ip;
			$this->countryCode = $data['country_code'];
			$this->countryName = $data['country_name'];
			$this->regionName  = $data['region_name'];
			$this->regionCode  = $data['region_code'];
			$this->city        = $data['city'];
			$this->timezone    = $data['time_zone'];
			$this->latitude    = $data['latitude'];
			$this->longitude   = $data['longitude'];
		}

	}

	public function fetch($host) {

		$response = wp_remote_get( $host );
		if ( is_wp_error( $response ) ) {
			return null;
		} else {
			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body, true );
			return $data;
		}

		return;
	}

}
}
?>