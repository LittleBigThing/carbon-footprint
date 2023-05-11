<?php
/**
 * Adds a dashboard widget if homepage is too large.
 *
 * @package carbon-footprint
 * @since 0.5
 */

 /**
 * Adds dashboard widget.
 *
 * @since 0.5
 */
function carbonfootprint_add_dashboard_widget() {

    // Get the carbon data if present
    $carbonfootprint_data = get_option( 'carbonfootprint_data' );

    // if there is data, initiate the dashboard widget.
    if ( current_user_can( 'manage_options' ) && $carbonfootprint_data && floatval( $carbonfootprint_data['cleaner_than'] ) ) {

        wp_add_dashboard_widget( 'dashboard_carbonfootprint', __( 'Your homepage has a large carbon footprint', 'carbon-footprint' ), 'carbonfootprint_dashboard_content' );
    }
}
add_action( 'wp_dashboard_setup', 'carbonfootprint_add_dashboard_widget' );

/**
 * Outputs the content of the dashboard widget.
 *
 * @since 0.5
 */
function carbonfootprint_dashboard_content() {

    // Get the carbon data.
    $carbonfootprint_data = get_option( 'carbonfootprint_data' );

    // Get the data and the 'constants' that are needed.
    $cleaner_than = floatval( $carbonfootprint_data['clean_than'] );
    $carbon_emission = floatval( $carbonfootprint_data['carbon_emission'] );
    $homepage_size = absint( $carbonfootprint_data['homepage_size'] );
    // Set defaults.
    $average_page_size = 0.5;
    $monthly_page_views = 10000;

    // Start gathering the content:
    // The amount of CO2 emitted by the homepage.
    $content = sprintf(
        __( '<strong>%s grams of CO<sub>2</sub> is emitted each time your homepage is loaded.</strong>', 'carbon-footprint' ),
        number_format_i18n( round( $carbon_emission, 2 ), 2 )
    );

    // Compare it with the average.
    $content .= sprintf(
        __( ' That is more than %s times the average of 0.5 grams.', 'carbon-footprint' ),
        number_format_i18n( floor( $carbon_emission/$average_page_size ) )
    );

    // Put it in perspective with 10 000 monthly pageviews. Make this filterable?
    $content .= sprintf(
        __( ' For a website with %1$s monthly page views, that&lsquo;s %2$s kg CO2 per year, only for the homepage.', 'carbon-footprint' ),
        number_format_i18n( $monthly_page_views ),
        number_format_i18n( round( ($carbon_emission * $monthly_page_views * 12 / 1000), 2 ), 2 )
    );

    // Give the actual amount of data and encourage action.
    $content .= sprintf(
        __( '<br>This is due to the large amount of data (%s MB!) needed to view the page. You can reduce this amount by making clever choices and optimising your page.', 'carbon-footprint' ),
        number_format_i18n( round( $homepage_size / 1000000, 2 ), 2 )
    );

    echo '<p>' . $content . '</p>';

    // Let's start to make it better.
    printf(
        '<p><a class="button button-primary" href="%1$s" target="_blank" rel="noopener"><span class="screen-reader-text">%2$s</span>%3$s<span aria-hidden="true" class="dashicons dashicons-external"></span></a></p>',
        esc_url( 'https://sustainablewebdesign.org' ),
        esc_html__( 'Opens in new tab', 'carbon-footprint' ),
        esc_html__( 'Reduce your website&lsquo;s carbon footprint', 'carbon-footprint' )
    );
}
