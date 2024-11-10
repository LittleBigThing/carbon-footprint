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

	// get the homepage (we only test the homepage: awareness, and not additional load, remember?)
	$home_url = get_home_url();
	if ( ! $home_url ) return false;

	// if there is no transient, let’s set up those API calls
	// PageSpeed Insights API from Google
	$url_pagespeed_insights = add_query_arg(
		array(
			'url' => urlencode( $home_url ),
		),
		'https://pagespeedonline.googleapis.com/pagespeedonline/v5/runPagespeed?category=performance&strategy=desktop' // default to desktop? For now, same as in API
	);
	$request_pagespeed_insights = array(
		'url' => $url_pagespeed_insights
	);

	// Greencheck API from the Green Web Foundation (domain needed without scheme!)
	$request_green_web_foundation = array(
		'url' => 'https://api.thegreenwebfoundation.org/api/v3/greencheck/' . parse_url( $home_url, PHP_URL_HOST )
	);

	// make the first two requests using the Request API in WP (using multiple requests)
	$responses = WpOrg\Requests\Requests::request_multiple(
		array(
			$request_green_web_foundation,
			$request_pagespeed_insights
		),
		array(
			'timeout' => 30
		)
	);

	// set up an array to gather the data we need
	$data = [];
	foreach ( $responses as $response ) {

		if ( is_a( $response, 'WpOrg\Requests\Response' ) ) {

			$result = json_decode( $response->body );

			// is the site hosted green, check only if its not the lighthouse result
			if ( ! $result->lighthouseResult ) {

				if ( $result->green ) {

					$data['green'] = 1;
	
				} else {
	
					$data['green'] = 0;
				}
			}

			// get the page size
			if ( $result->lighthouseResult->audits->{'total-byte-weight'} ) {

				$data['bytes'] = (int) $result->lighthouseResult->audits->{'total-byte-weight'}->numericValue;
			}

		} else {

			return false;
		}
	}

	// Website Carbon API from Wholegrain Digital
	$request_website_carbon = add_query_arg(
		array(
			'bytes' => absint( $data['bytes'] ),
			'green' => absint( $data['green'] )
		),
		'https://api.websitecarbon.com/data'
	);

	// make a request to the Website Carbon API
	$response = wp_remote_get( $request_website_carbon );

	if ( is_array( $response ) && ! is_wp_error( $response ) ) {

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( array_key_exists( 'error', $body ) || empty( $body ) ) return false;

		// add page weight from Page Speed Insights
		$body['bytes'] = $data['bytes'];

		// add a timestamp to show when test was done
		$body['timestamp'] = current_time( 'timestamp' );

		// set a transient to limit hitting the API each time:
		// 1 week, the same as WP Site Health's cron. Note that the API caches the result for 1 day.
		set_transient( 'carbonfootprint_test', $body, WEEK_IN_SECONDS );

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
				'homepage_size'		=> absint( $body['bytes'] ),
				'rating'			=> sanitize_text_field( $body['rating'] )
			));

		} else {

			// delete the data when the homepage gets better
			delete_option( 'carbonfootprint_data' );
		}

		return $body;
	}

	return false;
}
