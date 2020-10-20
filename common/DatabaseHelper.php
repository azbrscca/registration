<?php
  require_once "../db/Query.php";
  class DatabaseHelper {

    public static function isEntrantRegistered( $entrant_id, $event_id ) {
      $q = new Query( "registrations" );
      $q->addWhere( 'entrant_id', $entrant_id )
        ->addWhere( 'event_id', $event_id );
      $registered = ( $q->count() == 1 );
      return $registered;
    }
    
    public static function getEntrantRegPosition( $entrant_id, $event_id ) {
      $q = new Query( "registrations" );
      $q->addWhere( 'event_id', $event_id );
      $q->addOrder( 'id' );
      $event_registrations = $q->select();
      
      for ( $i = 0; $i < count( $event_registrations ); $i++ ) {
        if ( $event_registrations[ $i ][ 'entrant_id' ] == $entrant_id ) {
          return ( $i + 1 );
        }
      }
      
      return 0;
    }

  } // class DatabaseHelper
?>
