<?php
  class TimeController extends BaseController {

    public function getAction( $request ) {
      return array( 'time' => time() );
    }

    public function postAction( $request ) {
      return getAction( $request );
    }

    public function requiresAuth() { return false; }
  
  }
?>
