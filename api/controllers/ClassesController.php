<?php
  class ClassesController extends BaseController {
  
    public function getAction( $request ) {

      if ( empty( $request->url_elements ) ) {
        $q = new Query( "scca_classes" );
        $q->addOrder( "date_effective", "desc" );

        return $q->select();
      } else {

        switch ( array_shift( $request->url_elements ) ) {
          case "types":
              $q = new Query( "class_type" );
              return $q->select();
          break;
          case "year":
            if ( empty( $request->url_elements ) ) {

              $lookup = array();

              $q = new Query( "scca_classes" );
              $q->addOrder( "date_effective", "desc" );
              $rows = $q->distinct( "date_effective" );
              foreach( $rows as $row ) {
                $year = date( "Y", strtotime( $row[ 'date_effective' ] ) );
                if ( !array_key_exists( $year, $lookup ) ) {
                  $q = new Query( "scca_classes" );
                  $q->only( array( "name", "initials", "pax", "type" ) )
                    ->addOrder( "id", "asc" )
                    ->addWhere( "date_effective", $year, "=", "year" );
                  $lookup[ $year ] = $q->select();
                }
              }
              return $lookup;

            } else if ( !is_numeric( $request->url_elements[ 0 ] ) ) {
              return array( 'error' => "Invalid year specified." );
            } else {
              $year = intval( array_shift( $request->url_elements ) );
              $q = new Query( "scca_classes" );
              $q->addWhere( "date_effective", $year, "=", "year" )
                ->addOrder('id', 'asc');
              $classes = $q->select();
              return $classes;
            }
                 
          break;

          default:
            return array( 'error' => "Invalid route." );
          break;
        }
      }
    }
  
  }
?>
