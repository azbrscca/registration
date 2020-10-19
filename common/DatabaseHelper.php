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

  } // class DatabaseHelper
?>
