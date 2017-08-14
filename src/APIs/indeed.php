<?php
/**
 * Include the Indeed PHP API
 */
require_once JobifyPlugin_ROOT . 'src' . DIRECTORY_SEPARATOR . 'indeed.php';

jobifyPlugin_addAPI( array(
  'key'          => 'indeed',
  'title'        => __( 'Indeed', 'jobifyPlugin' ),
  'logo'         => plugins_url( 'img/indeed.jpg' , JobifyPlugin_PLUGIN ),
  // Since 1.4.0
  'requirements' => array(
    'location'    => __( 'Indeed API requires a location to be provided.', 'jobifyPlugin' ),
    'attribution' => sprintf( __( 'Indeed attribution link required (see <a href="%s">%s</a> for more information).', 'jobifyPlugin' ), 'https://ads.indeed.com/jobroll/xmlfeed', 'https://ads.indeed.com/jobroll/xmlfeed' ),
    'geolocation' => __( 'Supports geolocation if enabled.', 'jobifyPlugin' )
  ),
  'getJobs'      => function( $args )
  {
    // Create the returned jobs array
    $jobs = array();

    // Get JobifyPlugin settings
    $settings = jobifyPlugin_settings();

    // Set the Indeed publisher number
    $indeed_publisher_number = ( ! empty ( $settings['indeed_publisher_number'] ) ) ? $settings['indeed_publisher_number'] : '9769494768160125';

    // Check cache for results
    $results = wp_cache_get( 'jobs-indeed-' . jobifyPlugin_string( $args ), 'jobifyPlugin' );
    if ( false === $results )
    {
      // Query the Indeed PHP API
      $client = new Indeed( $indeed_publisher_number );
      $params   = array(
        'userip'    => jobifyPlugin_get_ip(),
        'useragent' => $_SERVER['HTTP_USER_AGENT'],
        'format'    => 'json',
        'raw'       => false,
        'start'     => 0,
        'highlight' => 0,
        'filter'    => 1,
        'latlong'   => 1,
        //'co'      => 'us'
      );

      $params['q']       = ( ! empty( $args['keyword'] ) ) ? $args['keyword'] : '';
      $params['radius']  = ( ! empty( $args['indeed_radius'] ) ) ? $args['indeed_radius'] : '25';
      $params['sort']    = ( ! empty( $args['indeed_sort'] ) ) ? $args['indeed_sort'] : 'relevance';
      $params['limit']   = ( ! empty( $args['indeed_limit'] ) ) ? $args['indeed_limit'] : 10;
      $params['fromage'] = ( ! empty( $args['indeed_fromage'] ) ) ? $args['indeed_fromage'] : '';


      // Location
      if ( ! empty( $args['lat'] ) && ! empty( $args['lng'] ) ) {
        $location = jobifyPlugin_get_location( $args['lat'] . ',' .  $args['lng'] );
        if ( count( $location ) > 0 )
        {
          $params['l'] = $location[3];
        }
      } elseif ( ! empty( $_POST['params']['location'] ) ) {
        $params['l'] = $args['location'];
      }

      $results = $client->search( $params );
      if ( ! empty( $results['error'] ) )
      {
        // API error
        $jobs[] = array(
          'error'  => __( '<b>Indeed API Error:</b> ', 'jobifyPlugin' ) . $results['error']
        );
      }
      else
      {
        // Save results to cache
        wp_cache_set( 'jobs-indeed-' . jobifyPlugin_string( $args ), $results, 'jobifyPlugin', 43200 ); // Half a day
        if ( count( $results['results'] ) > 0 ) {
          foreach ( $results['results'] as $key => $ary ) {
            // Add job to array
            $jobs[] = array(
              'portal'   => 'indeed',
              'title'    => ( ! empty( $ary['jobtitle'] ) ) ? $ary['jobtitle'] : false,
              'company'  => ( ! empty( $ary['company'] ) ) ? $ary['company'] : false,
              //'company_logo' => ( ! empty( $obj->company_logo ) ) ? $obj->company_logo : false,
              'company_url'   => ( ! empty( $obj->company_url ) ) ? $obj->company_url : false,
              'city'     => ( ! empty( $ary['city'] ) ) ? $ary['city'] : false,
              'state'    => ( ! empty( $ary['state'] ) ) ? $ary['state'] : false,
              'country'  => ( ! empty( $ary['country'] ) ) ? $ary['country'] : false,
              'desc'     => ( ! empty( $ary['snippet'] ) ) ? $ary['snippet'] : false,
              'app_url'  => ( ! empty( $ary['url'] ) ) ? $ary['url'] : false,
              'lat'      => ( ! empty( $ary['latitude'] ) ) ? $ary['latitude'] : false,
              'long'     => ( ! empty( $ary['longitude'] ) ) ? $ary['longitude'] : false,
              'date'     => ( ! empty( $ary['date'] ) ) ? $ary['date'] : false,
              'location' => ( ! empty( $ary['formattedLocationFull'] ) ) ? $ary['formattedLocationFull'] : false,
              'custom'   => array(
                'onmousedown'           => ( ! empty( $ary['onmousedown'] ) ) ? $ary['onmousedown'] : false,
                'source'                => ( ! empty( $ary['source'] ) ) ? $ary['source'] : false,
                'sponsored'             => ( ! empty( $ary['sponsored'] ) ) ? $ary['sponsored'] : false,
                'expired'               => ( ! empty( $ary['expired'] ) ) ? $ary['expired'] : false,
                'indeedApply'           => ( ! empty( $ary['indeedApply'] ) ) ? $ary['indeedApply'] : false,
                'formattedRelativeTime' => ( ! empty( $ary['formattedRelativeTime'] ) ) ? $ary['formattedRelativeTime'] : false,
                'noUniqueUrl'           => ( ! empty( $ary['noUniqueUrl'] ) ) ? $ary['noUniqueUrl'] : false,
              )
              //'address'  => ( ! empty( $ary['address'] ) ) ? $ary['address'] : false,
              //'phone'  => ( ! empty( $ary['phone'] ) ) ? $ary['phone'] : false,
              //'email'  => ( ! empty( $ary['email'] ) ) ? $ary['email'] : false,
              //'type'  => ( ! empty( $ary['type'] ) ) ? $ary['type'] : false,
            );
          }
        }
      }
    }

    return $jobs;
  },
  'options' => array(
    array(
      'group' => array(
        array(
          'title'   => __( 'Radius', 'jobifyPlugin' ),
          'name'    => 'indeed_radius',
          'desc'    => __( 'Distance from search location.', 'jobifyPlugin' ),
          'default' => '25',
          'type'    => 'number'
        ),
        array(
          'title'   => __( 'Number of Days', 'jobifyPlugin' ),
          'name'    => 'indeed_fromage',
          'desc'    => __( 'Number of days back to search.', 'jobifyPlugin' ),
          'default' => '30',
          'type'    => 'number'
        ),
      )
    ),
    array(
      'title'   => __( 'Limit', 'jobifyPlugin' ),
      'name'    => 'indeed_limit',
      'desc'    => __( 'Max number of results from Indeed (Max. 25).', 'jobifyPlugin' ),
      'default' => '10',
      'type'    => 'number'
    ),
  )
));