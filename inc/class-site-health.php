<?php
/**
 * Adds checks about energy used and the carbon footprint of the homepage in Site Health Status 
 * and sustainability-related data under the info tab.
 *
 * @package carbon-footprint
 * @since 1.0
 */

namespace CarbonFootprint;

class SiteHealth {

    private $carbon_report = null;

    /**
     * Add the tests and the info/data to Site Health and set the carbon report.
     *
     * @access public
     * @since 1.0
     */
    public function __construct() {

        // add the tests to status tab
        add_filter( 'site_status_tests', array( $this, 'add_tests' ) );
        // add the data to the info tab
        add_filter( 'debug_information', array( $this, 'get_carbon_footprint_info' ) );
        add_filter( 'debug_information', array( $this, 'get_server_info' ) );

        // add tests to wp_ajax actions to do test async
        add_action( 'wp_ajax_health-check-carbonfootprint-hosting', array( $this, 'get_test_hosting' ) );
        add_action( 'wp_ajax_health-check-carbonfootprint-footprint', array( $this, 'get_test_footprint' ) );
    }

    /**
     * Get the carbon report.
     *
     * @access public
     * @since 1.0
     */
    public function get_carbon_report() {

        if ( ! $this->carbon_report ) {

            // get the report if it wasn't yet
            $this->carbon_report = carbonfootprint_get_website_carbon_report();
        }
    }

    /**
     * Adds tests to Site Health.
     *
     * @access public
     * @since 1.0
     *
     * @param array $tests Site Health Tests.
     * @return array $tests Site Health Tests.
     */
    public function add_tests( $tests ) {

        // is your hosting green?
        $tests['async']['carbonfootprint_hosting'] = array(
            'label'             => __( 'The energy used by the hosting', 'carbon-footprint' ),
            'test'              => 'carbonfootprint-hosting',
            'async_direct_test' => array( $this, 'get_test_hosting' )
        );

        // is your homepage sustainable?
        $tests['async']['carbonfootprint_footprint'] = array(
            'label'             => __( 'The carbon footprint of the homepage', 'carbon-footprint' ),
            'test'              => 'carbonfootprint-footprint',
            'async_direct_test' => array( $this, 'get_test_footprint' )
        );

        return $tests;
    }

    /**
     * Tests if hosting is green.
     *
     * @access public
     * @since 1.0
     *
     * @return array $result Site Health test result.
     */
    public function get_test_hosting() {

        $result = array(
            'label'       => __( 'Determine whether your site uses renewable energy', 'carbon-footprint' ),
            'status'      => 'recommended',
            'badge'       => array(
                'label' => __( 'Sustainability', 'carbon-footprint' ),
                'color' => 'orange',
            ),
            'description' => sprintf(
                '<p>%s</p>',
                esc_html__( 'The test did not succeed. Please note that your site needs to be public to be tested. If it is, then something must have gone wrong... Consider testing your site manually to learn more about the carbon footprint of your website.', 'carbon-footprint' )
            ),
            'actions'     => sprintf(
                '<p><a href="%1$s" target="_blank">%2$s</a></p>',
                esc_url( 'https://www.websitecarbon.com' ),
                esc_html__( 'Test your site', 'carbon-footprint' )
            ),
            'test'        => 'carbonfootprint_hosting',
        );

        $this->get_carbon_report();
    
        if ( is_array( $this->carbon_report ) ) {

            $result['label'] = __( 'Your website runs on bog standard energy', 'carbon-footprint' );
            $result['description'] = sprintf(
                '<p>%s</p>',
                esc_html__( 'Oh no, it looks like your website does not run on renewable energy (or your host is not listed in the Green Hosting Directory). You could save around 10% of carbon emissions by using renewable energy. Please consider informing your hosting provider or switching to another host.', 'carbon-footprint' )
            );
            $result['actions'] = sprintf(
                '<p><a href="%1$s" target="_blank">%2$s</a></p>',
                esc_url( 'https://www.thegreenwebfoundation.org/directory/' ),
                esc_html__( 'Check out green hosting providers', 'carbon-footprint' )
            );

            // if good
            if ( $this->carbon_report['green'] == 'true' ) {

                $result['label'] = __( 'Your website runs on renewable energy', 'carbon-footprint' );
                $result['status'] = 'good';
                $result['badge']['color'] = 'green';
                $result['description'] = sprintf(
                    '<p>%s</p>',
                    esc_html__( 'Nice! You save around 10% of carbon emissions by using renewable energy to run your website. The other part of the emission is determined by other parameters, such as the size of your web pages, the network and the end user devices.', 'carbon-footprint' )
                );
                $result['actions'] = '';
            }
        }
    
        wp_send_json_success( $result );
    }

    /**
     * Tests footprint of the website's homepage
     *
     * @access public
     * @since 1.0
     *
     * @return array $result Site Health test result.
     */
    public function get_test_footprint() {

        $result = array(
            'label'       => __( 'Estimate the carbon footprint of your homepage', 'carbon-footprint' ),
            'status'      => 'recommended',
            'badge'       => array(
                'label' => __( 'Sustainability', 'carbon-footprint' ),
                'color' => 'orange',
            ),
            'description' => sprintf(
                '<p>%s</p>',
                esc_html__( 'The footprint of your homepage could not be determined. Please consider testing your site manually if you want to learn more about the carbon footprint of your website.', 'carbon-footprint' )
            ),
            'actions'     => sprintf(
                '<p><a href="%1$s" target="_blank">%2$s</a></p>',
                esc_url( 'https://www.websitecarbon.com' ),
                esc_html__( 'Test your site', 'carbon-footprint' )
            ),
            'test'        => 'carbonfootprint_footprint',
        );

        $this->get_carbon_report();
    
        if ( is_array( $this->carbon_report ) ) {

            $cleaner_than = floatval( $this->carbon_report['cleanerThan'] );

            // set a basic explanation about the test
            $base_description = esc_html__( 'Websites have an actual carbon footprint, because the energy needed to power them often comes from fossil fuels. ', 'carbon-footprint' );

            // if great
            if ( $cleaner_than > 0.9 ) {

                $result['label'] = sprintf(
                    __( 'Your homepage is cleaner than %s of web pages tested', 'carbon-footprint' ),
                    number_format_i18n( $cleaner_than * 100 ) . '%'
                );
                $result['status'] = 'good';
                $result['badge']['color'] = 'green';
                $result['description'] = sprintf(
                    '<p>%s</p>',
                    $base_description . esc_html__( 'Your homepage is doing great, however, when compared to other pages tested. Keep up the good work!', 'carbon-footprint' )
                );
                $result['actions'] = '';

            // if good
            } elseif ( $cleaner_than <= 0.9 && $cleaner_than > 0.5 ) {

                $result['label'] = sprintf(
                    __( 'Your homepage is cleaner than %s of web pages tested', 'carbon-footprint' ),
                    number_format_i18n( $cleaner_than * 100 ) . '%'
                );
                $result['status'] = 'good';
                $result['badge']['color'] = 'green';
                $result['description'] = sprintf(
                    '<p>%s</p>',
                    $base_description . esc_html__( 'Your homepage is doing quite good when compared to other pages tested. It might be doing even better with the information below.', 'carbon-footprint' )
                );
                $result['actions'] = '';

            // if it could be better
            } elseif ( $cleaner_than <= 0.5 && $cleaner_than > 0.1 ) {

                $result['label'] = sprintf(
                    __( 'Your homepage is dirtier than %s of web pages tested', 'carbon-footprint' ),
                    number_format_i18n( (1 - $cleaner_than) * 100 ) . '%'
                );
                $result['description'] = sprintf(
                    '<p>%s</p>',
                    $base_description . esc_html__( 'Your homepage has a relatively large footprint when compared to other pages tested. Learn how to improve it using the information below.', 'carbon-footprint' )
                );
                $result['actions'] = '';

            // if it is very bad
            // Note: make this threshold filterable?
            } elseif ( $cleaner_than <= 0.1 ) {

                $result['label'] = sprintf(
                    __( 'Your homepage is dirtier than %s of web pages tested', 'carbon-footprint' ),
                    number_format_i18n( (1 - $cleaner_than) * 100 ) . '%'
                );
                $result['status'] = 'critical';
                $result['badge']['color'] = 'red';
                $result['description'] = sprintf(
                    '<p>%s</p>',
                    $base_description . esc_html__( 'Your homepage has a large footprint when compared to other pages tested. It could be much lower. Learn how to improve it using the information below.', 'carbon-footprint' )
                );
                $result['actions'] = '';
            }
        }

        // add link to resources on how to improve website sustainability
        $result['actions'] .= sprintf(
            '<p><a href="%1$s" target="_blank">%2$s<span class="screen-reader-text">%3$s</span>
            <span aria-hidden="true" class="dashicons dashicons-external"></span></a></p><p><a href="%4$s" target="_blank">%5$s<span class="screen-reader-text">%3$s</span>
            <span aria-hidden="true" class="dashicons dashicons-external"></span></a></p>',
            esc_url( 'https://sustainablewebdesign.org/strategies/' ),
            esc_html__( 'Find out how to lower the footprint of your website', 'carbon-footprint' ),
            esc_html__( '(opens in a new tab)', 'carbon-footprint' ),
            esc_url( 'https://www.websitecarbon.com' ),
            esc_html__( 'Check other pages of your website with the Website Carbon Calculator', 'carbon-footprint' )
        );
    
        wp_send_json_success( $result );
    }

    /**
     * Adds carbon footprint data to Site Health info.
     *
     * @since 1.0
     *
     * @param array $debug_info Site Health Tests.
     * @return array $debug_info Site Health Tests.
     */
    public function get_carbon_footprint_info( $debug_info ) {

        $this->get_carbon_report();

        if ( $this->carbon_report === false ) return $debug_info;

        // Energy source
        $energy = esc_html__( 'The type of energy used to run your website could not be determined', 'carbon-footprint' );
        if ( is_array( $this->carbon_report ) ) {

            if ( $this->carbon_report['green'] == 'true' ) {

                $energy = esc_html__( 'Renewable energy', 'carbon-footprint' );
            
            } else {

                $energy = esc_html__( 'Bug standard energy', 'carbon-footprint' );
            }
        }

        // Homepage size
        $homepage_size = number_format_i18n( round( $this->carbon_report['bytes'] / 1000 ) ); // or use adjusted bytes (new vs. returning visitors accounted for)?

        // Carbon emission
        if ( $this->carbon_report['green'] == 'true' ) {

            $carbon_emission = number_format_i18n( round( $this->carbon_report['statistics']['co2']['renewable']['grams'], 2 ), 2 );

        } else {

            $carbon_emission = number_format_i18n( round( $this->carbon_report['statistics']['co2']['grid']['grams'], 2 ), 2 );
        }

        // Energy used
        // TODO

        // add the information
        $debug_info['carbonfootprint'] = array(
            'label'    => __( 'Carbon Footprint', 'carbon-footprint' ),
            'fields'   => array(
                'energy_source' => array(
                    'label'    => __( 'Energy source server', 'carbon-footprint' ),
                    'value'   => $energy,
                ),
                'homepage_size' => array(
                    'label'    => __( 'Homepage size', 'carbon-footprint' ),
                    'value'   => sprintf(
                        esc_html__( '%s KB', 'carbon-footprint' ),
                        $homepage_size
                    ),
                ),
                'emission' => array(
                    'label'    => __( 'Carbon emission', 'carbon-footprint' ),
                    'value'   => sprintf(
                        esc_html__( '%s g (grams) of CO2 per pageview for the homepage', 'carbon-footprint' ),
                        $carbon_emission
                    ),
                ),
                /*'energy_use' => array(
                    'label'    => __( 'Energy use', 'carbon-footprint' ),
                    'value'   => sprintf(
                        esc_html__( '%s g (grams) of CO2 per pageview', 'carbon-footprint' ),
                        $carbon_emission
                    ),
                ),*/
            ),
        );
    
        return $debug_info;
    }

    /**
     * Adds information about the hosting to the server section of Site Health.
     *
     * @since 1.0
     *
     * @param array $debug_info Site Health Tests.
     * @return array $debug_info Site Health Tests.
     */
    public function get_server_info( $debug_info ) {

        $this->get_carbon_report();

        // set default
        $energy = esc_html__( 'The type of energy used to run your website could not be determined', 'carbon-footprint' );

        if ( is_array( $this->carbon_report ) ) {

            if ( $this->carbon_report['green'] == 'true' ) {

                $energy = esc_html__( 'Renewable energy', 'carbon-footprint' );
            
            } else {

                $energy = esc_html__( 'Bug standard energy', 'carbon-footprint' );
            }
        }

        $debug_info['wp-server']['fields']['energy_source'] = array(
            'label'    => __( 'Energy source', 'carbon-footprint' ),
            'value'   => $energy
        );

        return $debug_info;
    }
}

new \CarbonFootprint\SiteHealth();
