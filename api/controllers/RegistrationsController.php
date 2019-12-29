<?php
  class RegistrationsController extends BaseController {
  
    public function getAction( $request ) {

      if ( empty( $request->url_elements ) ) {
        return array( 'error' => "Invalid route." );
      } else {

        switch ( array_shift( $request->url_elements ) ) {
          case "event":

            if ( empty( $request->url_elements ) ) {
              return array( 'error' => "Invalid route." );
            } else {          
              $id = intval( array_shift( $request->url_elements ) );
              return $this->event( $id, $request->parameters );
            }

          break;
          
          case "previous";
          
            if ( !array_key_exists( "entrant_id", $request->parameters ) ||
                 !array_key_exists( "organization_id", $request->parameters ) ) {
              return array( 'error' => "Invalid route." );
            } else {          
              return $this->findPrevious(
                intval( $request->parameters[ 'entrant_id' ] ),
                intval( $request->parameters[ 'organization_id' ] ) );
            }

          break;

          default:
            return array( 'error' => "Invalid route." );
          break;
        }
      }
    }

    private function event( $id, $parameters ) {
    
      $q = new Query( "events" );
      $event = $q->selectById( $id );
      $admin = json_decode( $event[ 'admin' ], true );
      
      $workers_key = ( !empty( $admin[ 'work_positions' ] ) ? $admin[ 'work_positions'] : $event[ 'organization_id' ] );
      $q = new Query( "work_positions" );
      $items =
        $q->addWhere( 'organization_id', $workers_key )
          ->select();    
      $work_positions = array();
      foreach( $items as $item ) {
        $work_positions[ $item[ 'id' ] ] = $item;
      }          

      $q = new Query( "registrations" );
      $q->addWhere( "event_id", $id )
        ->joinOn( "car" )
        ->joinOn( "entrant" );

      $sort = false;
      if ( array_key_exists( "order", $parameters ) ) {
        if ( Database::hasColumn( "registrations", $parameters[ 'order' ] ) ) {
          $q->addOrder( $parameters[ 'order' ] );
        } else {
          $sort = true;
        }
      }
      
      $registrations = $q->select();

      foreach( $registrations as $index => $registration ) {
        for ( $i=1; $i<4; $i++ ) {
          if ( array_key_exists( $registration[ 'work_pos_'.$i ], $work_positions ) ) {
            $registrations[ $index ][ 'work_pos_'.$i."_name" ] =
              $work_positions[ $registration[ 'work_pos_'.$i ] ][ 'name' ];
          } else {
            $registrations[ $index ][ 'work_pos_'.$i."_name" ] = "Any";
          }
        }
      }

      if ( $sort ) {
        switch( $parameters[ 'order' ] ) {
          case 'entrant_name':
            usort( $registrations, array( "Functions", "sortByEntrantName" ) );
          break;
        }
      }

      return $registrations;
    }
    
    private function findPrevious( $entrantId, $organizationId ) {
    
      $q = new Query( "registrations" );
      $q->addWhere( "entrant_id", $entrantId )
        ->joinOn( "entrant" )
        ->joinOn( "event" )
        ->addWhereOnJoin( "events", "organization_id", $organizationId )
        ->addOrderOnJoin( "events", "date", "desc" );
        
      $r = $q->selectOne();
      if ( !empty( $r ) ) {
        $q = new Query( "registrations" );
        $r = $q->selectById( $r[ 'id' ] );
        unset( $r[ 'id' ] );
        unset( $r[ 'entrant_id' ] );
        unset( $r[ 'event_id' ] );
        unset( $r[ 'payment_id' ] );
        unset( $r[ 'date_created' ] );
        unset( $r[ 'date_modified' ] );
      }
      return $r;
    }
    
  }
?>
