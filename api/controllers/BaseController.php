<?php
  class BaseController {

    protected function decodeArray( $array ) {
      $decoded = array();
      foreach( array_keys( $array ) as $key ) {
        if ( is_array( $array[ $key ] ) ) {
          $decoded[ $key ] = $this->decodeArray( $array[ $key ] );
        } else {
          $decoded[ $key ] = htmlspecialchars_decode( $array[ $key ] );
        }
      }
      return $decoded;
    }
  
    public function requiresAuth() {
      return true;
    }
  }
?>
