<?php
/**
 * Gets the website carbon report for the homepage from the Website Carbon Calculator (https://www.websitecarbon.com)
 * For more details on the API: https://api.websitecarbon.com
 *
 * @since 1.0
 *
 * @return array/bool
 */
function carbonfootprint_get_website_carbon_report() {

	// quit if this site is local and probably cannot be accessed
    if ( 'local' === wp_get_environment_type() ) return false;

	// if there is a transient, easy, get it and go! :-)
	$body = get_transient( 'carbonfootprint_test' );
	if ( $body ) return $body;

	// if there is no transient, let’s do the work
	$api_url = 'https://api.websitecarbon.com/site';
	$home_url = get_home_url();

	if ( ! $home_url ) return false;

	// build the query string and attach to the API url
	$test_url = add_query_arg( array(
		'url' => urlencode( $home_url ),
	), $api_url );

	// get the report
	$response = wp_remote_get(
		$test_url,
		array ( 'timeout' => 30 ) // 30 sec delay, lower it?
	);

	if ( is_array( $response ) && ! is_wp_error( $response ) ) {

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( array_key_exists( 'error', $body ) || empty( $body ) ) return false;

		// set a transient to limit hitting the API each time
		// 1 day, this is the same as the API uses to cache a request?
		set_transient( 'carbonfootprint_test', $body, DAY_IN_SECONDS );

		// save data to alert if homepage is humongous, see dashboard widget
        // Note: make this threshold filterable?
		if ( floatval( $body['cleanerThan'] ) <= 0.1 ) {

			// get the correct emission value depending on the 'greenness' of the server
			if ( $body['green'] == 'true' ) {
				$carbon_emission = $body['statistics']['co2']['renewable']['grams'];
			} else {
				$carbon_emission = $body['statistics']['co2']['grid']['grams'];
			}

			// save data as an array of options
			update_option( 'carbonfootprint_data', array(
				'cleaner_than'		=> floatval( $body['cleanerThan'] ),
				'carbon_emission'	=> floatval( $carbon_emission ),
				'homepage_size'		=> absint( $body['bytes'] )
			));

		} else {

			// delete the data when the homepage gets better
			delete_option( 'carbonfootprint_data' );
		}

		return $body;
	}

	return false;
}
