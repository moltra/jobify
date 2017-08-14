<?php
/**
 * Include the Careerjet's PHP API
 *
 * @since 1.4.0
 */
require_once JobifyPlugin_ROOT . 'src' . DIRECTORY_SEPARATOR . 'Careerjet_API.php';

jobifyPlugin_addAPI( array(
  'key'          => 'careerjet',
  'title'        => __( 'Careerjet', 'jobifyPlugin' ),
  'logo'         => plugins_url( 'img/careerjet.jpg' , JobifyPlugin_PLUGIN ),
  // Since 1.4.0
  'requirements' => array(
    'geolocation' => __( 'Supports geolocation if enabled.', 'jobifyPlugin' )
  ),
  'getJobs'      => function( $args )
  {
    // Create the returned jobs array
    $jobs = array();

    // Get JobifyPlugin settings
    $settings = jobifyPlugin_settings();

    // Set the Careerjet affiliate ID
    $careerjet_api_key = ( ! empty ( $settings['careerjet_api_key'] ) ) ? $settings['careerjet_api_key'] : 'b4a44bbbcaa7fe6bfd6039d1e864294e';

    // Set the Careerjet locale
    $careerjet_locale = ( ! empty ( $args['careerjet_locale'] ) ) ? $args['careerjet_locale'] : 'en_US';

    // Check cache for results
    $results = wp_cache_get( 'jobs-careerjet-' . jobifyPlugin_string( $args ), 'jobifyPlugin' );
    if ( false === $results )
    {
      // Query the Careerjet PHP API
      $careerjet = new Careerjet_API( $careerjet_locale );

      $params = array(
        'page'  => 1,
        'affid' => $careerjet_api_key
      );

      $params['keywords']       = ( ! empty( $args['keyword'] ) ) ? $args['keyword'] : '';

      // Location
      if ( ! empty( $args['lat'] ) && ! empty( $args['lng'] ) ) {
        $location = jobifyPlugin_get_location( $args['lat'] . ',' .  $args['lng'] );
        if ( count( $location ) > 0 )
        {
          $params['location'] = $location[3];
        }
      } elseif ( ! empty( $args['location'] ) ) {
        $params['location'] = $args['location'];
      }

      $results = $careerjet->search( $params );
      if ( ! $results->type == 'JOBS' )
      {
        // API error
        $jobs[] = array(
          'error'  => __( '<b>Careerjet API Error:</b> ', 'jobifyPlugin' ) . ' Invalid result type: ' . $results->type
        );
      }
      else
      {
        // Save results to cache
        wp_cache_set( 'jobs-careerjet-' . jobifyPlugin_string( $args ), $results, 'jobifyPlugin', 43200 ); // Half a day
        if ( ! empty( $results->jobs ) && count( $results->jobs ) > 0 ) {
          foreach ( $results->jobs as $key => $obj ) {
            // Add job to array
            $jobs[] = array(
              'portal'   => 'careerjet',
              'title'    => ( ! empty( $obj->title ) ) ? $obj->title : false,
              'company'  => ( ! empty( $obj->company ) ) ? $obj->company : false,
              //'company_logo' => ( ! empty( $obj->company_logo ) ) ? $obj->company_logo : false,
              //'company_url'   => ( ! empty( $obj->company_url ) ) ? $obj->company_url : false,
              //'city'     => ( ! empty( $ary['city'] ) ) ? $ary['city'] : false,
              //'state'    => ( ! empty( $ary['state'] ) ) ? $ary['state'] : false,
              //'country'  => ( ! empty( $ary['country'] ) ) ? $ary['country'] : false,
              'desc'     => ( ! empty( $obj->description ) ) ? $obj->description : false,
              'app_url'  => ( ! empty( $obj->url ) ) ? $obj->url : false,
              //'lat'      => ( ! empty( $ary['latitude'] ) ) ? $ary['latitude'] : false,
              //'long'     => ( ! empty( $ary['longitude'] ) ) ? $ary['longitude'] : false,
              'date'     => ( ! empty( $obj->date ) ) ? $obj->date : false,
              'location' => ( ! empty( $obj->locations ) ) ? $obj->locations : false,
              'custom'   => array(
                //'onmousedown'           => ( ! empty( $ary['onmousedown'] ) ) ? $ary['onmousedown'] : false,
                //'source'                => ( ! empty( $ary['source'] ) ) ? $ary['source'] : false,
                //'sponsored'             => ( ! empty( $ary['sponsored'] ) ) ? $ary['sponsored'] : false,
                //'expired'               => ( ! empty( $ary['expired'] ) ) ? $ary['expired'] : false,
                //'indeedApply'           => ( ! empty( $ary['indeedApply'] ) ) ? $ary['indeedApply'] : false,
                //'formattedRelativeTime' => ( ! empty( $ary['formattedRelativeTime'] ) ) ? $ary['formattedRelativeTime'] : false,
                //'noUniqueUrl'           => ( ! empty( $ary['noUniqueUrl'] ) ) ? $ary['noUniqueUrl'] : false,
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
      'title'   => __( 'Careerjet Locale', 'jobifyPlugin' ),
      'name'    => 'careerjet_locale',
      'desc'    => __( 'Select your Careerjet locale.', 'jobifyPlugin' ),
      'default' => 'en_US',
      'type'    => 'select',
      'options' => array(
        'cs_CZ' => __( 'Czech Republic', 'jobifyPlugin' ),
        'da_DK' => __( 'Denmark', 'jobifyPlugin' ),
        'de_AT' => __( 'Austria', 'jobifyPlugin' ),
        'de_CH' => __( 'Switzerland (DE)', 'jobifyPlugin' ),
        'de_DE' => __( 'Germany', 'jobifyPlugin' ),
        'en_AE' => __( 'United Arab Emirates', 'jobifyPlugin' ),
        'en_AU' => __( 'Australia', 'jobifyPlugin' ),
        'en_CA' => __( 'Canada (EN)', 'jobifyPlugin' ),
        'en_CN' => __( 'China (EN)', 'jobifyPlugin' ),
        'en_HK' => __( 'Hong Kong', 'jobifyPlugin' ),
        'en_IE' => __( 'Ireland', 'jobifyPlugin' ),
        'en_IN' => __( 'India', 'jobifyPlugin' ),
        'en_MY' => __( 'Malaysia', 'jobifyPlugin' ),
        'en_NZ' => __( 'New Zealand', 'jobifyPlugin' ),
        'en_OM' => __( 'Oman', 'jobifyPlugin' ),
        'en_PH' => __( 'Philippines', 'jobifyPlugin' ),
        'en_PK' => __( 'Pakistan', 'jobifyPlugin' ),
        'en_QA' => __( 'Qatar', 'jobifyPlugin' ),
        'en_SG' => __( 'Singapore', 'jobifyPlugin' ),
        'en_GB' => __( 'United Kingdom', 'jobifyPlugin' ),
        'en_US' => __( 'United States', 'jobifyPlugin' ),
        'en_ZA' => __( 'South Africa', 'jobifyPlugin' ),
        'en_TW' => __( 'Taiwan', 'jobifyPlugin' ),
        'en_VN' => __( 'Vietnam (EN)', 'jobifyPlugin' ),
        'es_AR' => __( 'Argentina', 'jobifyPlugin' ),
        'es_BO' => __( 'Bolivia', 'jobifyPlugin' ),
        'es_CL' => __( 'Chile', 'jobifyPlugin' ),
        'es_CR' => __( 'Costa Rica', 'jobifyPlugin' ),
        'es_DO' => __( 'Dominican Republic', 'jobifyPlugin' ),
        'es_EC' => __( 'Ecuador', 'jobifyPlugin' ),
        'es_ES' => __( 'Spain', 'jobifyPlugin' ),
        'es_GT' => __( 'Guatemala', 'jobifyPlugin' ),
        'es_MX' => __( 'Mexico', 'jobifyPlugin' ),
        'es_PA' => __( 'Panama', 'jobifyPlugin' ),
        'es_PE' => __( 'Peru', 'jobifyPlugin' ),
        'es_PR' => __( 'Puerto Rico', 'jobifyPlugin' ),
        'es_PY' => __( 'Paraguay', 'jobifyPlugin' ),
        'es_UY' => __( 'Uruguay', 'jobifyPlugin' ),
        'es_VE' => __( 'Venezuela', 'jobifyPlugin' ),
        'fi_FI' => __( 'Finland', 'jobifyPlugin' ),
        'fr_CA' => __( 'Canada (FR)', 'jobifyPlugin' ),
        'fr_BE' => __( 'Belgium (FR)', 'jobifyPlugin' ),
        'fr_CH' => __( 'Switzerland (FR)', 'jobifyPlugin' ),
        'fr_FR' => __( 'France', 'jobifyPlugin' ),
        'fr_LU' => __( 'Luxembourg', 'jobifyPlugin' ),
        'fr_MA' => __( 'Morocco', 'jobifyPlugin' ),
        'hu_HU' => __( 'Hungary', 'jobifyPlugin' ),
        'it_IT' => __( 'Italy', 'jobifyPlugin' ),
        'ja_JP' => __( 'Japan', 'jobifyPlugin' ),
        'ko_KR' => __( 'Korea', 'jobifyPlugin' ),
        'nl_BE' => __( 'Belgium (NL)', 'jobifyPlugin' ),
        'nl_NL' => __( 'Netherlands', 'jobifyPlugin' ),
        'no_NO' => __( 'Norway', 'jobifyPlugin' ),
        'pl_PL' => __( 'Poland', 'jobifyPlugin' ),
        'pt_PT' => __( 'Portugal', 'jobifyPlugin' ),
        'pt_BR' => __( 'Brazil', 'jobifyPlugin' ),
        'ru_RU' => __( 'Russia', 'jobifyPlugin' ),
        'ru_UA' => __( 'Ukraine (RU)', 'jobifyPlugin' ),
        'sv_SE' => __( 'Sweden', 'jobifyPlugin' ),
        'sk_SK' => __( 'Slovakia', 'jobifyPlugin' ),
        'tr_TR' => __( 'Turkey', 'jobifyPlugin' ),
        'uk_UA' => __( 'Ukraine (UK)', 'jobifyPlugin' ),
        'vi_VN' => __( 'Vietnam (VI)', 'jobifyPlugin' ),
        'zh_CN' => __( 'China (ZH)', 'jobifyPlugin' )
      ),
    ),
  )
));