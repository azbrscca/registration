<?php

  class Request {
    public $errors = array();
    public $method;
    public $orgId = 0;
    public $parameters;
    public $url_elements = array();

    public function __construct() {

      $this->method = $_SERVER[ 'REQUEST_METHOD' ];

      if ( !empty( $_SERVER[ 'REQUEST_URI' ] ) ) {
        $uri = $_SERVER[ 'REQUEST_URI' ];
        if ( strpos( $_SERVER[ 'REQUEST_URI' ], '?' ) !== false ) {
          $uri = substr( $_SERVER[ 'REQUEST_URI' ], 1, strpos( $_SERVER[ 'REQUEST_URI' ], '?' ) - 1 );
        }
        $this->url_elements = array_filter( explode( '/', $uri ), 'strlen' );
      }

      while ( 
        ( sizeof( $this->url_elements ) > 0 ) &&
        ( ( $token = array_shift( $this->url_elements ) ) != serviceRoot ) ) 
      {}
        
      $this->parseIncomingParams();

      // initialise json as default format
      $this->format = 'json';
      if ( isset( $this->parameters[ 'format' ] ) ) {
        $this->format = $this->parameters[ 'format' ];
      }
      return true;
    }

    public function parseIncomingParams() {
        $parameters = array();

        switch( $this->method ) {
        
          case 'GET':
            if (isset($_SERVER['QUERY_STRING'])) {
              parse_str($_SERVER['QUERY_STRING'], $parameters);
            }
            //$parameters = $this->sanitizeArray( $parameters );

            break;
            
          case 'POST':
            $parameters = $this->sanitizeArray( $_POST );
            break;
        } 
        $this->parameters = $parameters;
    }

    private function sanitizeArray( $dirtyArray ) {
      $cleanArray = array();
      foreach( array_keys( $dirtyArray ) as $key ) {
        if ( is_array( $dirtyArray[ $key ] ) ) {
          $cleanArray[ $key ] = self::sanitizeArray( $dirtyArray[ $key ] );
        } else {
          $cleanArray[ $key ] = self::sanitizeString( $dirtyArray[ $key ] );
        }
      }
      return $cleanArray;
    }
      
    private function sanitizeString( $dirty ) {
      $clean = mb_convert_encoding( $dirty, 'UTF-8', 'UTF-8' );
      $clean = stripcslashes( $clean );
      $clean = htmlentities( $clean, ENT_QUOTES, 'UTF-8' );
      return $clean;
    }
}
