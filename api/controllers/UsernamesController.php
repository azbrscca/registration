<?php
  class UsernamesController extends BaseController {
  
    public function getAction( $request ) {

      if ( empty( $request->url_elements ) ) {
        return array( 'error' => "Invalid route." );
      } else {
        switch ( array_shift( $request->url_elements ) ) {

          case "available":

            if (in_array("q", array_keys($request->parameters))) {

              $q = new Query( "entrants" );
              $q->addWhere( "username", $request->parameters['q']);
              return array( 'r' => $q->count() );

            } else {
              return array( 'error' => "No query argument provided." );              
            }

          break;

          default:
            return array( 'error' => "Invalid route." );
          break;
        }
      }
    }  
  }
?>