<?php
jobifyPlugin_addAPI( array(
  'key'     => 'jobifyPlugin',
  'title'   => __( 'JobifyPlugin', 'jobifyPlugin' ),
  'logo'    => plugins_url( 'img/jobifyPlugin.jpg' , JobifyPlugin_PLUGIN ),
  'getJobs' => function( $options ) {
    $settings = jobifyPlugin_settings();
    $jobs     = array();

    $results = wp_cache_get( 'jobs-jobifyPlugin', 'jobifyPlugin' );
    if ( false === $results )
    {
      $args = array(
        'post_type' => 'jobifyPlugin_posting'
      );
      $query = new WP_Query( $args );
      if ( $query->have_posts() )
      {
        $query->the_post();
        $city    = get_field( 'jobifyPlugin_city' );
        $state   = get_field( 'jobifyPlugin_state' );
        $zip     = get_field( 'jobifyPlugin_zip' );
        $country = get_field( 'jobifyPlugin_country' );

        $jobs[] = array(
          'title'    => get_the_title(),
          'company'  => get_field( 'jobifyPlugin_company' ),
          'city'     => $city,
          'state'    => $state,
          'zip'      => $zip,
          'country'  => $country,
          'desc'     => get_the_excerpt(),
          'url'      => get_field( 'jobifyPlugin_app_url' ),
          'location' => $city . ', ' . $state . ' ' . $zip
        );
      }
      wp_reset_postdata();

      wp_cache_set( 'jobs-jobifyPlugin', $results, 'jobifyPlugin', 43200 ); // Half a day
    }


    return $jobs;
  },
  'options' => array(
  )
));