<?php
  class EventsController extends BaseController {

    private function eventStatus( &$event ) {

      $reg_open = strtotime( $event[ 'registration_open' ] );
      $reg_close = strtotime( $event[ 'registration_close' ] );
      $now = time();
  
      if ( $now > $event[ 'registration_close_ts' ] ) {
      $event[ 'status' ] = "closed";
      } else if ( $now > $event[ 'registration_open_ts' ] ) {
        $event[ 'status' ] = "open";
      } else if ( $now < $event[ 'date_ts' ] ) {
        $event[ 'status' ] = "will open";
      } else {
        $event[ 'status' ] = "unknown";
      }
    }
  
    private function registrationCount( &$event, $index ) {
      $q = new Query( "registrations" );
      $event[ 'comp_count' ] = $q->addWhere( 'event_id', $event[ 'id' ] )->count();
      $event[ 'to_count' ] = $q->addWhere( 'time_only_reg', 1 )->count();
    }

    public function getAction( $request ) {

      if ( empty( $request->url_elements ) ) {
        return array( 'error' => "Invalid route." );
      } else {

        if ( ( $request->orgId != apiMasterId ) &&
              empty( $request->parameters[ 'organization_id' ] ) ) {
          $request->parameters[ 'organization_id' ] = $request->orgId;
        }

        switch ( array_shift( $request->url_elements ) ) {
        
          case "active":
            return $this->active( $request->parameters );
          break;

          case "calendar":
            if ( empty( $request->url_elements ) ) {
              return array( 'error' => "Invalid route." );
            } else {
              $year = array_shift( $request->url_elements );
              return $this->calendar( $year, $request->parameters );
            }
          break;

          case "past":
            return $this->past( $request->parameters );
          break;

          case "upcoming":
            $open = !empty( $request->url_elements ) &&
                    ( "open" == array_shift( $request->url_elements ) );
            return $this->upcoming( $request->parameters, $open );
          break;

          default:
            return array( 'error' => "Invalid route." );
          break;
        }
      }
    }

    private function active( $parameters ) {

      $q = new Query( "events" );
      $q->addWhere( "registration_open", date( "Y-m-d H:i:s", time() ), "<=" )
        ->addOrder( "date", "desc" )
        ->joinOn( "organization", array( "name" ) )
        ->joinOn( "site", array( "name" ) )
        ->timestamps( true );

      if ( array_key_exists( 'organization_id', $parameters ) ) {
        $q->addWhere( "organization_id" ,intval( $parameters[ 'organization_id' ] ) );
      }

      if ( array_key_exists( 'public', $parameters ) ) {
        $q->addWhere( "public", intval( $parameters[ 'public' ] ) );
      }

      if ( array_key_exists( 'limit', $parameters ) &&
           is_numeric( $parameters[ 'limit' ] ) ) {
        $q->setLimit( $parameters[ 'limit' ] );
      }
      
      $events = $q->select();    

      array_walk( $events, array( $this, 'eventStatus' ) );
      array_walk( $events, array( $this, 'registrationCount' ) );

      return $events;
    }  

    private function calendar( $year, $parameters ) {

      $q = new Query( "events" );
      $q->addWhere( "date", $year, "=", "year" )
        ->addOrder( "date" )
        ->joinOn( "organization", array( "name" ) )
        ->joinOn( "site", array( "name" ) )
        ->timestamps( true );

      if ( array_key_exists( 'organization_id', $parameters ) ) {
        $q->addWhere( "organization_id" ,intval( $parameters[ 'organization_id' ] ) );
      }

      if ( array_key_exists( 'public', $parameters ) ) {
        $q->addWhere( "public", intval( $parameters[ 'public' ] ) );
      }

      if ( array_key_exists( 'limit', $parameters ) &&
           is_numeric( $parameters[ 'limit' ] ) ) {
        $q->setLimit( $parameters[ 'limit' ] );
      }
      
      $events = $q->select();    

      array_walk( $events, array( $this, 'eventStatus' ) );
      array_walk( $events, array( $this, 'registrationCount' ) );

      return $events;
    }  

    private function past( $parameters ) {
    
      $q = new Query( "events" );
      $q->addWhere( "date", date( "Y-m-d", time() ), "<" )
        ->addOrder( "date", "desc" )
        ->joinOn( "organization", array( "name" ) )
        ->joinOn( "site", array( "name" ) )
        ->timestamps( true );

      if ( array_key_exists( 'organization_id', $parameters ) ) {
        $q->addWhere( "organization_id" ,intval( $parameters[ 'organization_id' ] ) );
      }

      if ( array_key_exists( 'public', $parameters ) ) {
        $q->addWhere( "public" ,intval( $parameters[ 'public' ] ) );
      }

      if ( array_key_exists( 'limit', $parameters ) &&
           is_numeric( $parameters[ 'limit' ] ) ) {
        $q->setLimit( $parameters[ 'limit' ] );
      }
      $events = $q->select();    

      array_walk( $events, array( $this, 'eventStatus' ) );
      array_walk( $events, array( $this, 'registrationCount' ) );
   
      return $events;
    }  

    private function upcoming( $parameters, $open = false ) {

      $q = new Query( "events" );
      $q->addWhere( "date", date( "Y-m-d", time() ), ">=" )
        ->addOrder( "date" )
        ->joinOn( "organization", array( "name" ) )
        ->joinOn( "site", array( "name" ) )
        ->timestamps( true );

      if ( $open ) {

        $q->addWhere( "registration_open", date( "Y-m-d G:i:s", time() ), "<=" )
          ->addWhere( "registration_close", date( "Y-m-d", time() ), ">=" );
      }


      if ( array_key_exists( 'organization_id', $parameters ) ) {
        $q->addWhere( "organization_id" ,intval( $parameters[ 'organization_id' ] ) );
      }

      if ( array_key_exists( 'public', $parameters ) ) {
        $q->addWhere( "public" ,intval( $parameters[ 'public' ] ) );
      }

      if ( array_key_exists( 'limit', $parameters ) ) {
        $q->setLimit( intval( $parameters[ 'limit' ] ) );
      }
      
      $events = $q->select();    
      
      array_walk( $events, array( $this, 'eventStatus' ) );
      array_walk( $events, array( $this, 'registrationCount' ) );
   
      return $events;
    }  
  
  }
  
?>
