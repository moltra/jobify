<?php
class JobifyPlugin_Widgets {
  public function run() {
    add_action( 'widgets_init', function(){
      register_widget( 'JobsWidget\JobsWidget' );
    });

    add_action('admin_enqueue_scripts', function( $hook )
      {
        if ( $hook != 'widgets.php' )
          return;

        wp_enqueue_style( 'jobifyPlugin', plugins_url( 'css/widgets.css' , JobifyPlugin_PLUGIN ) );
      });
  }
}