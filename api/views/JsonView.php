<?php
  class JsonView extends BaseView {
    public function render($content, $callback = null) {
      header('Content-Type: application/json');
      $json = json_encode( $content );
      if ( $callback != null ) {
        $json = $callback.'('.$json.');';
      }
      echo $json;
      return true;
    }
  }
?>