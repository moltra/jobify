<?php
class JobifyPlugin_Scripts {
  public function run() {
    add_action( 'wp_enqueue_scripts', function()
    {
      wp_register_script( 'jobifyPlugin-indeed', 'https://gdc.indeed.com/ads/apiresults.js', array(), false, false );
      wp_register_script( 'jobifyPlugin-geolocation', plugin_dir_url( JobifyPlugin_PLUGIN ) . 'js' . DIRECTORY_SEPARATOR . 'geolocation.js', array( 'jquery' ), '1.1.0', false );
      wp_register_script( 'jobifyPlugin-tracker', plugin_dir_url( JobifyPlugin_PLUGIN ) . 'js' . DIRECTORY_SEPARATOR . 'tracker.js', array( 'jquery' ), '1.3.3', false );

      wp_localize_script( 'jobifyPlugin-geolocation', 'JobifyPlugin', array(
        'ajaxurl'  => admin_url( 'admin-ajax.php' ),
        'security' => wp_create_nonce( 'jobifyPlugin' )
      ));
    });

    add_action( 'wp_ajax_jobifyPlugin_get_jobs', function() {
      check_ajax_referer( 'jobifyPlugin', 'security' );

      // Get the jobs
      $job_options = jobifyPlugin_job_args( $_POST['params'] );

      // Location
      if ( ! empty( $_POST['params']['lat'] ) && ! empty( $_POST['params']['lng'] ) ) {
        $job_options['lat'] = $_POST['params']['lat'];
        $job_options['lng'] = $_POST['params']['lng'];
      } elseif ( ! empty( $_POST['params']['location'] ) ) {
        $job_options['location'] = $args['location'];
      }

      $jobs = jobifyPlugin_get_jobs( $job_options );

      if ( count( $jobs ) > 0 )
      {
        shuffle( $jobs );
        $jobs = array_slice( $jobs, 0, $job_options['limit'] );
      }

      echo json_encode( $jobs );

      die();
    } );
  }
}