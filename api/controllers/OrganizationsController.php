<?php
  class OrganizationsController extends BaseController {
  
    public function postAction( $request ) {

      if ( empty( $request->url_elements ) ) {
        return array( 'error' => "Invalid route." );
      } else {

        $id = array_shift( $request->url_elements );
        if ( !is_numeric( $id ) ) {
          return array( 'error' => "Invalid organization id" );
        }
        $q = new Query( "organizations" );
        $org = $q->selectById( $id );
        if ( empty( $org ) ) {
          return array( 'error' => "Invalid organization id" );
        }

        if ( empty( $request->url_elements ) ) {
          return array( 'error' => "Invalid route." );
        } else {
          switch( array_shift( $request->url_elements ) ) {
            case "apikey":
              $org[ 'apikey' ] = "reg-".Functions::randomString( 10 );
              $org[ 'updated' ] = $q->updateById( $org );
              return $org;
              break;

            default:
              return array( 'error' => "Invalid route." );
              break;
          }
        }
      }
    }
  
  }
?>
