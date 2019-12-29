<?php
  class FormsController extends BaseController {
  
    public function getAction( $request ) {

      if ( empty( $request->url_elements ) ) {
        return array( 'error' => "Invalid route." );
      } else {

        switch ( array_shift( $request->url_elements ) ) {

          case "event":
            if ( empty( $request->url_elements ) ) {
              return array( 'error' => "Invalid route." );
            }
            
            $id = array_shift( $request->url_elements );
            if ( !is_numeric( $id ) ) {
              return array( 'error' => "Invalid event ID." );
            } else {
              return $this->formList( "event", $id, $request->parameters );
            }
          break;

          default:
            return array( 'error' => "Invalid route." );
          break;
        }
      }
    }

    private function formList( $key, $value, $parameters ) {

      $q = new Query( "entry_forms" );
      $q->joinOn( "entrant" )
        ->joinOn( "registration" )
        ->joinOn( "scca_class" )
        ->joinOn( "comp_category" )
        ->joinOn( "work_position" );

      $q->addWhere( $key."_id", $value );

      if ( array_key_exists( "order_by", $parameters ) ) {
        $fields = preg_split( '/,/', $parameters[ 'order_by' ] );
        foreach( $fields as $field ) {
          $q->addOrder( $field );
        }
      }
      return $q->select();
    }  
  
  }
?>
