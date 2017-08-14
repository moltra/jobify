<?php
/**
 * Security Note: Blocks direct access to the plugin PHP files.
 */
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function jobifyPlugin_settings() {
  if ( is_plugin_active_for_network( plugin_basename( JobifyPlugin_PLUGIN ) ) ) {
    // Network plugin settings.
    return (array) get_site_option( 'jobifyPlugin_settings' );
  }

  // Site plugin settings.
  return (array) get_option( 'jobifyPlugin_settings' );
}

function jobifyPlugin_addAPI( $args )
{
  global $jobifyPluginAPIs;

  $jobifyPluginAPIs[ $args['key'] ] = $args;
}


function jobifyPlugin_get_url() {
  $pageURL = 'http';

  if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) {
    $pageURL .= 's';
  }

  $pageURL .= '://';

  if ( '80' != $_SERVER['SERVER_PORT'] ) {
    $pageURL .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
  } else {
    $pageURL .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
  }

  return $pageURL;
}

function jobifyPlugin_get_ip() {
  $ipaddress = '';

  if ( getenv('HTTP_CLIENT_IP') ) {
    $ipaddress = getenv('HTTP_CLIENT_IP');
  } else if ( getenv('HTTP_X_FORWARDED_FOR') ) {
    $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
  } else if ( getenv('HTTP_X_FORWARDED') ) {
    $ipaddress = getenv('HTTP_X_FORWARDED');
  } else if ( getenv('HTTP_FORWARDED_FOR') ) {
    $ipaddress = getenv('HTTP_FORWARDED_FOR');
  } else if ( getenv('HTTP_FORWARDED') ) {
    $ipaddress = getenv('HTTP_FORWARDED');
  } else if ( getenv('REMOTE_ADDR') ) {
    $ipaddress = getenv('REMOTE_ADDR');
  } else {
    $ipaddress = 'UNKNOWN';
  }

  return $ipaddress;
}

function jobifyPlugin_admin_url() {
  if ( is_plugin_active_for_network( plugin_basename( JobifyPlugin_PLUGIN ) ) ) {
    $settings_url = network_admin_url( 'settings.php' );
  } else if ( home_url() != site_url() ) {
    $settings_url = home_url( '/wp-admin/' . 'options-general.php' );
  } else {
    $settings_url = admin_url( 'options-general.php' );
  }

  return $settings_url;
}

if ( ! function_exists( 'jobifyPlugin_parse' ) )
{
  function jobifyPlugin_parse( $string, $args )
  {
    $find   = array();
    $replace = array();
    foreach ( $args as $key => $value )
    {
      if ( is_array( $value ) )
      {
        foreach ( $value as $k => $v )
        {
          $find[]    = '[' . $k . ']';
          $replace[] = $v;
        }
      }
      else
      {
        $find[]    = '[' . $key . ']';
        $replace[] = $value;
      }
    }

    $string = str_replace( $find, $replace, $string );

    return $string;
  }
}

if ( ! function_exists( 'jobifyPlugin_get_location' ) )
{
  function jobifyPlugin_get_location( $latLng )
  {
    // Google Map Geocode API url
    $url = "http://maps.google.com/maps/api/geocode/json?latlng={$latLng}&sensor=false";

    // Get the JSON response
    $resp_json = file_get_contents( $url );

    // Decode the JSON
    $resp = json_decode( $resp_json, true );

    // response status will be 'OK', if able to geocode given address
    if ( 'OK' === $resp['status'] )
    {

      foreach ( $resp['results'][0]['address_components'] as $key => $ary )
      {
        if ( in_array( 'postal_code', $ary ['types'] ) )
        {
          $zip = $ary['long_name'];
        }
      }

      // Get the important data
      $lati              = $resp['results'][0]['geometry']['location']['lat'];
      $longi             = $resp['results'][0]['geometry']['location']['lng'];
      $formatted_address = $resp['results'][0]['formatted_address'];

      // Put the data in the array
      $data_arr = array();

      array_push(
          $data_arr,
              $lati,
              $longi,
              $formatted_address,
              $zip
          );

      return $data_arr;
    }
    else
    {
      return false;
    }
  }
}

if ( ! function_exists( 'jobifyPlugin_get_jobs' ) )
{
  function jobifyPlugin_get_jobs( $args )
  {
    // Get the available job portal APIs
    global $jobifyPluginAPIs;

    // Create the returned jobs array
    $jobs = array();

    // Loop through the requested job portals
    $portals = ( ! empty ( $args['portals'] ) ) ? $args['portals'] : array();
    if ( count( $portals ) > 0 )
    {
      foreach( $portals as $key => $portal )
      {
        if ( array_key_exists( $portal, $jobifyPluginAPIs ) )
        {
          // If portal exists, run the 'getJobs()' method
          unset( $args['portals'] );
          $jobs = array_merge( $jobs, $jobifyPluginAPIs[$portal]['getJobs']( $args ) );
        }
      }
    }

    return $jobs;
  }
}

if ( ! function_exists( 'jobifyPlugin_string' ) )
{
  function jobifyPlugin_string( $ary )
  {
    $string = '';

    foreach( $ary as $key => $value )
    {
      if ( is_array( $value ) ) continue;

      $string .= strip_tags(trim(strtolower(str_replace(array(
        ' '
      ),
      array(
        '__'
      ), $value))));
    }

    return $string;
  }
}

if ( ! function_exists( 'jobifyPlugin_powered_by' ) )
{
  function jobifyPlugin_powered_by()
  {
    $strings = array(
      sprintf(
        __( 'Powered by <a href="%s" target="_blank">JobifyPlugin</a>.', 'jobifyPlugin' ),
        'https://benmarshall.me/jobifyPlugin?utm_source=jobifyPlugin%20plugin&utm_medium=powered%20by&utm_campaign=jobifyPlugin&utm_content=' . urlencode( 'Powered by JobifyPlugin' )
      ),
      sprintf(
        __( 'Powered by <a href="%s" target="_blank">WordPress JobifyPlugin</a>.', 'jobifyPlugin' ),
        'https://benmarshall.me/jobifyPlugin?utm_source=jobifyPlugin%20plugin&utm_medium=powered%20by&utm_campaign=jobifyPlugin&utm_content=' . urlencode( 'Powered by WordPress JobifyPlugin' )
      ),
      sprintf(
        __( 'Jobs aggregated by <a href="%s" target="_blank">JobifyPlugin</a>.', 'jobifyPlugin' ),
        'https://benmarshall.me/jobifyPlugin?utm_source=jobifyPlugin%20plugin&utm_medium=powered%20by&utm_campaign=jobifyPlugin&utm_content=' . urlencode( 'Jobs aggregated by JobifyPlugin' )
      ),
      sprintf(
        __( 'Jobs by <a href="%s" target="_blank">JobifyPlugin</a>.', 'jobifyPlugin' ),
        'https://benmarshall.me/jobifyPlugin?utm_source=jobifyPlugin%20plugin&utm_medium=powered%20by&utm_campaign=jobifyPlugin&utm_content=' . urlencode( 'Jobs by JobifyPlugin' )
      ),
      sprintf(
        __( 'Aggregated by <a href="%s" target="_blank">JobifyPlugin</a>.', 'jobifyPlugin' ),
        'https://benmarshall.me/jobifyPlugin?utm_source=jobifyPlugin%20plugin&utm_medium=powered%20by&utm_campaign=jobifyPlugin&utm_content=' . urlencode( 'Aggregated by JobifyPlugin' )
      ),
      sprintf(
        __( 'Aggregated by <a href="%s" target="_blank">WordPress JobifyPlugin</a>.', 'jobifyPlugin' ),
        'https://benmarshall.me/jobifyPlugin?utm_source=jobifyPlugin%20plugin&utm_medium=powered%20by&utm_campaign=jobifyPlugin&utm_content=' . urlencode( 'Aggregated by WordPress JobifyPlugin' )
      ),
      sprintf(
        __( '<a href="%s" target="_blank">WordPress job plugin</a> by JobifyPlugin.', 'jobifyPlugin' ),
        'https://benmarshall.me/jobifyPlugin?utm_source=jobifyPlugin%20plugin&utm_medium=powered%20by&utm_campaign=jobifyPlugin&utm_content=' . urlencode( 'WordPress job plugin by JobifyPlugin' )
      ),
      sprintf(
        __( '<a href="%s" target="_blank">WordPress Job Board</a> by JobifyPlugin.', 'jobifyPlugin' ),
        'https://benmarshall.me/jobifyPlugin?utm_source=jobifyPlugin%20plugin&utm_medium=powered%20by&utm_campaign=jobifyPlugin&utm_content=' . urlencode( 'WordPress Job Board by JobifyPlugin' )
      ),
    );

    echo '<p>' . $strings[rand(0, (count( $strings ) - 1))] . '</p>';
  }
}

if ( ! function_exists( 'jobifyPlugin_job_result' ) )
{
  function jobifyPlugin_job_result( $tpl, $args )
  {
    if ( empty( $tpl ) )
    {
      $tpl = $this->default_settings['template'];
    }

    $job = '<div class="jobifyPluginJob" data-portal="' . esc_attr( $args['portal']) . '">' . jobifyPlugin_parse( $tpl, $args ) . '</div>';
    if ( ! empty( $args['portal'] ) && 'indeed' === $args['portal'] )
    {
      $indeedID = jobifyPlugin_between( $args['custom']['onmousedown'], "indeed_clk(this, '", "');" );
      $job = '<div class="jobifyPluginJob" data-portal="' . esc_attr( $args['portal']) . '" data-id="' . esc_attr( $indeedID ). '">' . jobifyPlugin_parse( $tpl, $args ) . '</div>';
    }
    return $job;
  }
}

if ( ! function_exists( 'jobifyPlugin_between' ) )
{
  function jobifyPlugin_between( $string, $start, $end )
  {
    $string = ' ' . $string;
    $ini = strpos( $string, $start );
    if ( $ini == 0 ) return '';
    $ini += strlen( $start );
    $len = strpos( $string, $end, $ini ) - $ini;

    return substr($string, $ini, $len);
  }
}

if ( ! function_exists( 'jobifyPlugin_job_args' ) )
{
  function jobifyPlugin_job_args( $args )
  {
    $jobArgs = array(
      'keyword'                 => ( ! empty( $args['keyword'] ) ) ? $args['keyword'] : false,
      'location'                => ( ! empty( $args['location'] ) ) ? $args['location'] : false,
      'geolocation'             => ( ! empty( $args['geolocation'] ) ) ? $args['geolocation'] : true,
      'powered_by'              => ( ! empty( $args['powered_by'] ) ) ? $args['powered_by'] : true,
      'portals'                 => ( ! empty( $args['portals'] ) ) ?  $args['portals'] : array( 'indeed', 'careerjet' ),

      'careerjet_locale'        => ( ! empty( $args['careerjet_locale'] ) ) ?  $args['careerjet_locale'] : 'en_US',
      'indeed_radius'           => ( ! empty( $args['indeed_radius'] ) ) ?  $args['indeed_radius'] : 25,
      'indeed_fromage'          => ( ! empty( $args['indeed_fromage'] ) ) ?  $args['indeed_fromage'] : 30,
      'indeed_limit'            => ( ! empty( $args['indeed_limit'] ) ) ?  $args['indeed_limit'] : 10,
      'githubjobs_fulltime'     => ( ! empty( $args['githubjobs_fulltime'] ) ) ?  $args['githubjobs_fulltime'] : 0,
      'usajobs_exclude_keyword' => ( ! empty( $args['usajobs_exclude_keyword'] ) ) ?  $args['usajobs_exclude_keyword'] : '',
      'usajobs_limit'           => ( ! empty( $args['usajobs_limit'] ) ) ?  $args['usajobs_limit'] : 10,

      'limit'                   => ( ! empty( $args['limit'] ) ) ?  $args['limit'] : 10,
    );

    return $jobArgs;
  }
}

if ( ! function_exists( 'jobifyPlugin_open_container' ) )
{
  function jobifyPlugin_open_container( $args, $id )
  {
    return '<div class="jobifyPluginJobs"
              data-location="' . esc_attr( $args['location'] ) . '"
              data-geolocation="' . $args['geolocation'] . '"
              data-template="jobifyPlugin-' . $id . '"
              data-keyword="' . esc_attr( $args['keyword'] ) . '"
              data-apis="' . implode( '|', $args['portals'] ) . '"
              data-limit="' . $args['limit'] . '"

              data-careerjet-locale="' . esc_attr( $args['careerjet_locale'] ) . '"
              data-githubjobs-fulltime="' . esc_attr( $args['githubjobs_fulltime'] ) . '"
              data-indeed-radius="' . esc_attr( $args['indeed_radius'] ) . '"
              data-indeed-fromage="' . esc_attr( $args['indeed_fromage'] ) . '"
              data-indeed-limit="' . esc_attr( $args['indeed_limit'] ) . '"
              data-usajobs-exclude-keyword="' . esc_attr( $args['usajobs_exclude_keyword'] ) . '"
              data-usajobs-limit="' . esc_attr( $args['usajobs_limit'] ) . '"
            >';
  }
}


function jobifyPlugin_indeed_attribution()
{
  wp_enqueue_script( 'jobifyPlugin-indeed' );

  echo '<p class="jobifyPlugin__indeed-attribution">' . sprintf( __( '<span id=indeed_at><a href="%s">jobs</a> by <a
    href="%s" title="Job Search"><img
    src="%s" style="border: 0;
    vertical-align: middle; display: inline" alt="Indeed job search"></a></span>', 'jobifyPlugin' ), 'http://www.indeed.com/', 'http://www.indeed.com/', '//www.indeed.com/p/jobsearch.gif' ) . '</p>';
}

