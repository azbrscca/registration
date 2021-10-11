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
    
    public static function getEntrantRegPositionIncludingTimeOnly( $entrant_id, $event_id ) {
      $q = new Query( "registrations" );
      $q->addWhere( 'event_id', $event_id );
      $q->addOrder( 'id' );
      $event_registrations = $q->select();
      $regPosition = 0;
      
      for ( $i = 0; $i < count( $event_registrations ); $i++ ) {
        if ( $event_registrations[ $i ][ 'entrant_id' ] == $entrant_id ) {
          return ( $regPosition + 1 );
        }
        
        $regPosition = $regPosition + 1;
        if ( $event_registrations[ $i ][ 'time_only_reg' ] == 1 ) {
          $regPosition = $regPosition + 1;
        }
      }
      
      return 0;
    }
    
    public static function getEntrantEntryFee( $entrant_id, $event_id ) {
      $registration = array();
      $q = new Query( "registrations" );  
      $registration = $q->addWhere( 'event_id', $event_id )
                        ->addWhere( 'entrant_id', $entrant_id )
                        ->select();
      error_log( print_r( $registration, true ) );
      return $registration[ 'entry_fee' ];
    }

  } // class DatabaseHelper
?>
