<?php
  class DbController extends BaseController {

    private static $allowed_get_tables = array(
      "cars",
      "comp_categories",
      "comp_groups",
      "discounts",
      "discount_codes",
      "entry_forms",
      "events",
      "event_types",
      "organizations",
      "payments",
      "registrations",
      "results",
      "scca_classes",
      "series",
      "series_events",
      "sites",
      "work_positions"
    );

    private $table = null;

    public function __construct( $table ) {
      $this->table = $table;
    }
    
    public function deleteAction( $request, $cascade = true ) {
    
      $q = new Query( $this->table );
      $return = 0;

      if ( !empty( $request->url_elements[ 0 ] ) ) {
        $id = $request->url_elements[ 0 ];
        if ( is_numeric( $id ) ) {
          $item = $q->selectById( intval( $id ) );
          if ( $item != null ) {
//            $fks = DbKeys::oneToMany( $this->table );
            $return = $q->deleteById( $id ); //, $fks );
          }
        }
      }
      return $return;
    }

    public function getAction( $request ) {

      $q = new Query( $this->table );
      $q->timestamps( true );


      if ( !in_array($this->table, self::$allowed_get_tables ) ) {
        return array();
      }

      foreach( array_keys( $request->parameters ) as $key ) {

        switch( $key ) {
        
          case 'limit':
            $q->setLimit( $request->parameters[ $key ] );
            unset( $request->parameters[ $key ] );
            break;
        
          case 'order':
            $columns = preg_split( "/,/", $request->parameters[ 'order' ] );
            foreach( $columns as $column ) {
              $order = "asc";

              if ( preg_match( "/-/", $column ) ) {
                $tokens = preg_split( "/-/", $column );
                $column = $tokens[ 0 ];
                $order = $tokens[ 1 ];
              }

              if ( Database::hasColumn( $this->table, $column ) ) {
                $q->addOrder( $column, $order );
              }
            }
            unset( $request->parameters[ $key ] );
            break;
        
        }
      }

      if ( !empty( $request->url_elements[ 0 ] ) ) {

        if ( $request->url_elements[ 0 ] == "count" ) {

          foreach( array_keys( $request->parameters ) as $key ) {
            if ( Database::hasColumn( $this->table, $key ) ) {
              $q->addWhere( $key, $request->parameters[ $key ] );
            }
          }
          return $q->count();
          
        } else if ( $request->url_elements[ 0 ] == "sum" ) {
          
          array_shift( $request->url_elements );          
          $column = array_shift( $request->url_elements );
          if ( Database::hasColumn( $this->table, $column ) ) {

            foreach( array_keys( $request->parameters ) as $key ) {
              if ( Database::hasColumn( $this->table, $key ) ) {
                $q->addWhere( $key, $request->parameters[ $key ] );
              }
            }
            return $q->sum( $column, "sum" );
          } else {
            return 0;
          }
          
        } else if ( Database::hasColumn( $this->table, $request->url_elements[ 0 ] ) ) {
        
          $column = array_shift( $request->url_elements );
        
          $rows = $q->only( $column )->addOrder( $column )->select();
          $options = array();
          foreach( $rows as $row ) {
            array_push( $options, $row[ $column ] );
          }
          return $options;

        } else {

          $id = array_shift( $request->url_elements );
          if ( is_numeric( $id ) ) {
            $item = $q->selectById( intval( $id ) );
            return $this->decodeArray( $item );
          }
        }
        
      } else {

        foreach( array_keys( $request->parameters ) as $key ) {
          if ( Database::hasColumn( $this->table, $key ) ) {
            $q->addWhere( $key, $request->parameters[ $key ] );
          }
        }
        $items = $q->select();
        return $this->decodeArray( $items );
      }

    }

    public function postAction( $request ) {

      if ( ( sizeof( $request->parameters ) == 1 ) &&
           array_key_exists( "_method", $request->parameters ) &&
           $request->parameters[ '_method' ] == "delete" ) {
        return $this->deleteAction( $request );
      }

      $data = $request->parameters;
      foreach( array_keys( $data ) as $key ) {
        if ( is_array( $data[ $key ] ) ) {
          $data[ $key ] = json_encode( $data[ $key ] );
        } else if ( !Database::hasColumn( $this->table, $key ) ) {
          unset( $data[ $key ] );
        }
      }
    
      $q = new Query( $this->table );

      if ( !empty( $request->url_elements[ 0 ] ) ) {
      
        $id = $request->url_elements[ 0 ];
        if ( is_numeric( $id ) ) {
          $item = $q->selectById( intval( $id ) );
          if ( $item != null ) {
            $data[ 'id' ] = $id;
            $data[ 'updated' ] = $q->updateById( $data );
            return $data;
          }
        }
        
      } else {       
        if ( !empty( $data[ 'id' ] ) ) {
          unset( $data[ 'id' ] );
        }
        $data[ 'id' ] = $q->insertNew( $data );
        return $data;
      }
    }
}
