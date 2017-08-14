"use strict";

( function( $ ) {
  var JobifyPluginTracker = function() {

    let methods = {};

    let jobs = $( ".jobifyPluginJobs" );
    jobs.each( function() {
      let element = this;

    });

    $( "body" ).on( "click", ".jobifyPluginJob a", function( e ) {
      let portal  = $( this ).closest( '.jobifyPluginJob' ).data( 'portal' ),
          id      = $( this ).closest( '.jobifyPluginJob' ).data( 'id' );

      if ( "indeed" === portal ) {
        indeed_clk( this, id );
      }
    });
  };

  var JobifyPluginTracker = new JobifyPluginTracker();
})( jQuery );