<?php
  class Session {
  
    public static function checkCookie() {
/*
      if ( !empty( $_COOKIE[ 'registration-login' ] ) ) {
        $cookie = json_decode( preg_replace( "/\\\\/", "", $_COOKIE[ 'registration-login' ] ), true );

				if ( !empty( $cookie[ 'userId' ] ) && is_numeric( $cookie[ 'userId' ] ) ) {

					$q = new Query( "entrants" );
					$user = $q->selectById( intval( $cookie[ 'userId' ] ) );
	
					if ( !empty( $user ) && !empty( $cookie[ 'created' ] ) ) {
						$data = sha1( $user[ 'username' ].$user[ 'password' ].$cookie[ 'created' ] );
						if ( !empty( $cookie[ 'data' ] ) && ( $cookie[ 'data' ] == $data ) ) {

							$_SESSION[ 'id'  ] = session_id();
							unset( $user[ 'password' ] );
							$_SESSION[ 'user' ] = $user;

							$q = new Query( "organizations" );
							$_SESSION[ 'organization' ] = $q->selectById( $user[ 'organization_id' ] );
	
						}
					}          
				}
			}
*/
      return false;
    }
  
    public static function checkLogin( $redirectOnFailure = false ) {

      $loggedIn = false;

      if ( !empty( $_SESSION ) &&
           !empty( $_SESSION[ 'user_id' ] ) &&
           is_numeric( $_SESSION[ 'user_id' ] ) ) {
            
				$q = new Query( "sessions" );
				$session = 
					$q->addWhere( "entrant_id", $_SESSION[ 'user_id' ] )
						->addWhere( "session_id", session_id() )
						->addWhere( "remote_addr", $_SERVER[ 'REMOTE_ADDR' ] )
						->selectOne();
	
				if ( !empty( $session ) ) {
					$hashData = array(
						'entrant_id' => $session[ 'entrant_id' ],
						'remote_addr' => $session[ 'remote_addr' ],
						'salt' => $session[ 'salt' ],
						'session_id' => $session[ 'session_id' ]
					);
					
					if ( sha1( json_encode( $hashData ) ) == 
							 $_SESSION[ 'data' ] ) {
						$loggedIn = true;
					}
				}
//			} else {
//			  $loggedIn = self::checkCookie();
			}

  	  if ( !$loggedIn && $redirectOnFailure ) {
        header( "Location: ".baseHref );    
      }
      return $loggedIn;
    }
    
    public static function checkAccess( $user, $org ) {

      $granted = false;
      
      $uriTokens = array();
      if ( !empty( $_SERVER[ 'SCRIPT_FILENAME' ] ) ) {
        $uri = $_SERVER[ 'SCRIPT_FILENAME' ];
        if ( strpos( $_SERVER[ 'SCRIPT_FILENAME' ], '?' ) !== false ) {
          $uri = substr( $_SERVER[ 'SCRIPT_FILENAME' ], 1, strpos( $_SERVER[ 'REQUEST_URI' ], '?' ) - 1 );
        }
        $uri = substr( $uri, strlen( rootDir ) );
        $uriTokens = array_filter( explode( '/', $uri ), 'strlen' );
      }

      $section = array_shift( $uriTokens );

      if ( !empty( $uriTokens ) ) {
      
				switch( $section ) {
				
				  case 'forms':
				  case 'org':

            if ( !empty( $org ) ) {
              $privs = json_decode( $org[ 'privileges' ], true );
              $target = array_shift( $uriTokens );
              switch ( $target ) {
                case "api.html":
                  $granted = array_key_exists( 'apiaccess', $privs ) &&
                              ( $privs[ 'apiaccess' ] == "true" );
                break;
                default:
                  $granted = true;
                break;
              }
            } else {
              $granted = false;
            }

			  	  break;
	
 			 	  case 'system':
			 	   $granted = !empty( $user ) && ( $user[ 'user_type_id' ] == 9 );
			 	   break;		
	
			 	 default:
			 	   $granted = false;
	 		 	  break;
				}

	  	}
      
      if ( !$granted ) {
        header( "Location: ".baseHref );    
      } else {
        //echo "allowed<br/>";
      }
    }

    public static function clearLogin() {

      setcookie( "registration-login", "", time() - 60*60*6, "/" );
      setcookie( "registration-username", "", time() - 60*60*6, "/" );
      
      if ( !empty( $_SESSION ) ) {
				$q = new Query( "sessions" );
				$sessions =
					$q->addWhere( 'entrant_id', $_SESSION[ 'user_id' ] )
						->addWhere( 'remote_addr', $_SERVER[ 'REMOTE_ADDR' ] )
	//          ->addWhere( 'session_id', session_id() )
						->only( 'id' )
						->select();
				foreach( $sessions as $session ) {
					$q = new Query( "sessions" );
					$q->deleteById( $session[ 'id' ] );
				}
			}

      $_SESSION = array();
      return session_destroy();
    }

    public static function grantablePrivs() {
			$privs = array(
				"org/categories.html",
				"org/groups.html",
				"org/index.html",
				"org/positions.html",
				"org/privileges.html",
				"org/series.html",
				"org/sites.html",
				"org/events/edit.html",
				"org/events/index.html",
				"org/events/list.html",
				"org/events/metrics.html",
				"org/events/metrics_excel.html",
				"org/events/payments.html",
				"org/events/results.html",
				"org/events/configure/five.html",
				"org/events/configure/four.html",
				"org/events/configure/index.html",
				"org/events/configure/one.html",
				"org/events/configure/six.html",
				"org/events/configure/three.html",
				"org/events/configure/two.html",
      );
    }
  }
?>
