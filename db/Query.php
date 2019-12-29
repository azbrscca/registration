<?php
  require_once "Database.php";

  class Query {

    private $conn;
    private $debug;
    private $table;
    private $timestamps = false;
    
    private $group_bys = array();
    private $orders = array();
    private $wheres = array();
    private $columns = array();
    
    public function __construct( $table, $debug = false ) {
    
      if ( empty( $table ) ) {
        return;
      }

      $factory = ConnectionFactory::getFactory();
      $this->conn = $factory->getConnection();
      $this->debug = $debug;
      $this->table = $table;

      if ( !Database::isLoaded() ) {
        Database::loadTables( $this->conn );
      }

      $this->columns[ Database::getPrefix().$this->table ] = Database::getColumns( $table );

      $this->limit = null;
      
      return $this;
    }

    public function addGroupBy( $column ) {

      if ( Database::hascolumn( $this->table, $column ) ) {
        array_push( $this->group_bys, Database::getPrefix().$this->table.".".$column );
      }

      return $this;
    }

    public function addOrder( $column, $order = 'asc', $castAs = '' ) {

      if ( Database::hasColumn( $this->table, $column ) ) {

        $orderBy = Database::getPrefix().$this->table.".".$column;
        if ( !empty( $castAs ) ) {
          $orderBy = "cast(".$orderBy." as ".$castAs.")";
        }
        if ( !preg_match( "/^(asc)|(desc)$/", $order ) ) {
          $order = "asc";
        }
        $orderBy .= " ".$order;
        array_push( $this->orders, $orderBy );
      }

      return $this;
    }

    public function addOrderOnJoin( $table, $column, $order = 'asc' ) { 

      if ( array_key_exists( Database::getPrefix().$table, $this->columns ) &&
           Database::hasColumn( $table, $column ) ) {

        if ( !preg_match( "/^(asc)|(desc)$/", $order ) ) {
          $order = 'asc';
        }      
        array_push( $this->orders, Database::getPrefix().$table.".".$column." ".$order );
      }
      
      return $this;
    }

    public function addWhere( $column, $value, $op = "=", $function = "" ) {

      if ( Database::hasColumn( $this->table, $column ) ) {
        switch ( Database::getColumnType( $this->table, $column ) ) {
          case 'date':
          case 'datetime':
          case 'longtext':
          case 'text':
          case 'tinytext':
            $value = "'".$this->conn->escape_string( $value )."'";
          break;
        }

        $column = Database::getPrefix().$this->table.".".$column;
        if ( !empty( $function ) ) {
          $column = $function."(".$column.")";
        }

        array_push( $this->wheres, $column." ".$op." ".$value );
      }

      return $this;
    }

    public function addWhereOnJoin( $table, $column, $value, $op = "=", $function = "" ) { 
    
      if ( array_key_exists( Database::getPrefix().$table, $this->columns ) &&
           Database::hasColumn( $table, $column ) ) {

        switch ( Database::getColumnType( $table, $column ) ) {
          case 'date':
          case 'datetime':
          case 'longtext':
          case 'text':
          case 'tinytext':
            $value = "'".$value."'";
          break;
        }

        $column = Database::getPrefix().$table.".".$column;
        if ( !empty( $function ) ) {
          $column = $function."(".$column.")";
        }

        array_push( $this->wheres, $column." ".$op." ".$value );
      }
        
      return $this;
    } 

    private function buildCols( &$columns, $key ) {
      array_walk( $columns, "prepend", $key );
    }

    private function cascadeDelete( $key, $id ) {
      $table = $key."s";
      $column = substr( $this->table, 0, -1 )."_id";

      if ( Database::hasTable( $table ) &&
           Database::hasColumn( $table, $column ) ) {

        $sql = "delete from ".Database::getPrefix().$table." where ".$column." = ".$id;
        if ( $this->debug ) {
          echo $sql."<br/><br/>\n\n";
        }
        $result = $this->conn->query( $sql );     
        return $this->conn->affected_rows;
      }
    }
    
    public function clearWheres() {
      unset( $this->wheres );
      $this->wheres = array();
      return $this;
    }

    public function count() {
      $rows = $this->execute( "count", $this->conn );
      return intval( $rows[ 0 ][ 'count' ] );
    } 
    
    public function timestamps( $value ) {
      $this->timestamps = $value;
      return $this;
    }

    public function delete() {
      $sql = "delete from ".Database::getPrefix().$this->table;

      if ( empty( $this->wheres ) ) {
        return -1; // prevent accidental deletion of an entire table
      } else {
        $sql .= " where ".implode( " and ", $this->wheres );
      }

      if ( $this->debug ) {
        echo $sql."<br/><br/>\n\n";
      }
      $result = $this->conn->query( $sql );     
      $deletedRows = $this->conn->affected_rows;

      return $deletedRows;
    }      
        
    public function deleteById( $id, $cascadeTo = array() ) {

      $sql = "delete from ".Database::getPrefix().$this->table." where id = ".$id." limit 1";
      if ( $this->debug ) {
        echo $sql."<br/><br/>\n\n";
      }
      $result = $this->conn->query( $sql );     
      $deletedRows = $this->conn->affected_rows;
      
      if ( ( $deletedRows == 1 ) &&
           !empty( $cascadeTo ) ) {
        foreach( $cascadeTo as $table ) {
          $this->cascadeDelete( $table, $id );
        }
      }
      return $deletedRows;
    }

    public function distinct( $column ) {
      $this->only( $column );
      return $this->execute( "distinct", $this->conn );
    } 
    
    public function exclude( $column, $table = null ) {

      if ( $table == null ) {
        $table = $this->table;
      } else {
        // should check this
      }
      
      $ndx = array_search( $column, $this->columns[  Database::getPrefix().$table ] );
      if ( is_int( $ndx ) ) {
        unset( $this->columns[ Database::getPrefix().$table ][ $ndx ] );
      }

      return $this;
    }

    public function execute( $type ) {

      $timeCols = array();

      switch ( $type ) {
        case 'count':
          $sql = "select count( * ) as count";
        break;
        
        case 'distinct':
          $tableCols = $this->columns[ Database::getPrefix().$this->table ];            
          array_walk( $tableCols, "prepend", Database::getPrefix().$this->table );
          $sql = "select distinct( ".implode( ",", $tableCols )." )";
        break;

        case 'select':        
          $sql = $type." ";

          $columns = array();
          foreach( array_keys( $this->columns ) as $table ) {
            $tableCols = $this->columns[ $table ];            

            if ( $this->timestamps ) {
              foreach( $tableCols as $column ) {
                if ( preg_match( "/^date/", Database::getColumnType( $table, $column ) ) ) {
//                  array_push( $timeCols, 'unix_timestamp( '.$table.'.'.$column.' ) as '.$column.'_ts' );
                  array_push( $timeCols, $column );
                }
              }
            }

            array_walk( $tableCols, "prepend", $table );
/*
            if ( $this->timestamps && !empty( $timeCols ) ) {
              $tableCols = array_merge( $tableCols, $timeCols );
            }
*/
            $columns = array_merge( $columns, $tableCols );
          }
          $sql .= implode( ",", $columns );
  
        break;
        
        case 'sum':
          $sql = "select ".$this->columns[ Database::getPrefix().$this->table ];
        break;
      }

      $sql .= " from ".implode( ",", array_keys( $this->columns ) );

      if ( !empty( $this->wheres ) ) {
        $sql .= " where ".implode( " and ", $this->wheres );
      }

      if ( !empty( $this->group_bys ) ) {
        $sql .= " group by ".implode( ", ", $this->group_bys );
      }

      if ( ( $type != "count" ) && ( $type != "sum" ) ) { 

        if ( !empty( $this->orders ) ) {
          $sql .= " order by ".implode( ", ", $this->orders );
        } else {
          // some defaults for ordering
          if ( Database::hasColumn( $this->table, 'id' ) ) {
            $sql .= " order by ".Database::getPrefix().$this->table.".id desc";
          } else if ( Database::hasColumn( $this->table, 'date_created' ) ) {
            $sql .= " order by ".Database::getPrefix().$this->table.".date_created desc";
          }
        }

        if ( $this->limit != null ) {
          $sql .= " limit ".$this->limit;
        }

      }

      if ( $this->debug ) {
        echo $sql."<br/><br/>\n\n";
      }

      $result = $this->conn->query( $sql );      

      $rows = array();
      while( $row = $result->fetch_assoc() ) {
        array_push( $rows, Functions::stripslashesDeep( $row ) );
      }
      $result->close();      

      if ( $this->timestamps ) {
		    array_walk( $rows, array( $this, 'generateTimestamps' ), $timeCols );
	    }

      return $rows;
    }

		private function generateTimestamps( &$item, $index, $cols ) {

      foreach( $item as $col => $val ) {
		    if ( in_array( $col, $cols ) ) {
          $item[ $col.'_ts' ] = strtotime( $val );
			  }
			}
		}
    
    public function getWheres() {
      return $this->wheres;
    }

    public function insertNew( $columns ) {
          
      if ( array_key_exists( 'id', $columns ) ) {
        unset( $columns[ 'id' ] );
      }

      if ( Database::hasColumn( $this->table, 'date_created' ) ) {
        $columns[ 'date_created' ] = date( "Y-m-d H:i:s", time() );
      }

      foreach( array_keys( $columns ) as $column ) {
      
        switch ( Database::getColumnType( $this->table, $column ) ) {
          case 'date':
          case 'datetime':
          case 'longtext':
          case 'text':
          case 'tinytext':
            // preg_replace('/[[:cntrl:]]/', '', $input); ?????
            $columns[ $column ] = "'".$this->conn->escape_string( trim( $columns[ $column ] ) )."'";
          break;        
        }
      }

      $sql = "insert into ".Database::getPrefix().$this->table." ( "
            .implode( ",", array_keys( $columns ) )." ) values ( "
            .implode( ",", array_values( $columns ) )." )";

      if ( $this->debug ) {
        echo $sql."<br/><br/>\n\n";
      }

      $result = $this->conn->query( $sql );     
      return $this->conn->insert_id;
    }

    public function joinOn( $column, $selectCols = array(), $joinCol = "id" ) {

      $joinTable = $this->pluralize( $column );
      $this->addWhere( $column."_id", Database::getPrefix().$joinTable.".".$joinCol );

      $joinColumns = array();

      foreach( Database::getColumns( $joinTable ) as $col ) {          
        if ( ( $col != $joinCol ) &&
             ( $col != "password" ) &&
             ( ( empty( $selectCols ) || in_array( $col, $selectCols ) ) )
           ) {
          array_push( $joinColumns, $col." as ".$column."_".$col );
        }
      }

      $this->columns[ Database::getPrefix().$joinTable ] = $joinColumns;
      return $this;
    } 

    public function only( $columns, $table = null ) {

      if ( $table == null ) {
        $table = $this->table;
      } else {
        // should check this
      }
      unset( $this->columns[ Database::getPrefix().$table ] );
      if ( is_array( $columns ) ) {
        $this->columns[ Database::getPrefix().$table ] = $columns;
      } else {
        $this->columns[ Database::getPrefix().$table ] = array( $columns );
      }

      return $this;
    }

    public function select() {
      return $this->execute( "select" );
    }

    public function selectById( $id ) {
      $this->setLimit( "1" );
      $this->addWhere( "id", $id );
      return $this->selectOne();
    }
        
    public function selectOne() {
      $this->setLimit( "1" );
      $rows = $this->select();
      if ( sizeof( $rows ) > 0 ) {
        return $rows[ 0 ];
      } else { 
        return array();
      }
    }
    
    public function setDebug( $debug ) {
      $this->debug = $debug;
      return $this;
    }

    public function setLimit( $limit ) {
      $this->limit = $limit;
      return $this;      
    }
    
    public function setWhereOp( $whereOp ) {
      if ( in_array( $whereOp, array( "and", "or" ) ) ) {
        $this->whereOp = $whereOp;
      }
      return $this;
    }

    public function setWheres( $wheres ) {
      $this->wheres = $wheres;
      return $this;
    }

    public function sum( $col ) {
      $this->columns[ Database::getPrefix().$this->table ] = "sum(".Database::getPrefix().$this->table.".".$col.") as sum";
      $rows = $this->execute( "sum", $this->conn );
      unset( $this->columns );
      return $rows[ 0 ][ 'sum' ];
    }
    
    public function updateById( $row, $quiet = false ) {

      $sets = array();

      foreach( array_keys( $row ) as $column ) {

        if ( Database::hasColumn( $this->table, $column ) ) {

          if ( ( $column != "id" ) && ( $column != "date_modified" ) ) {
          
            switch ( Database::getColumnType( $this->table, $column ) ) {
              case 'date':
              case 'datetime':
              case 'longtext':
              case 'text':
              case 'tinytext':
                // preg_replace('/[[:cntrl:]]/', '', $input); ?????
                $row[ $column ] = "'".$this->conn->escape_string( trim( $row[ $column ] ) )."'";
              break;
            }
            array_push( $sets, $column." = ".$row[ $column ] );
          }
        }
      }

      if ( !$quiet && Database::hasColumn( $this->table, "date_modified" ) ) {
        switch ( Database::getColumnType( $this->table, "date_modified" ) ) {
          case "datetime":
            array_push( $sets, "date_modified = '".date( "Y-m-d H:i:s", time() )."'" );
          break;
        }
      }
      
      if ( empty( $row[ 'id' ] ) ) {
        return 0;
      }
      
      $sql = "update ".Database::getPrefix().$this->table
            ." set ".implode( ", ", $sets )
            ." where id = ".$row[ 'id' ];

      if ( $this->debug ) {
        echo "update: ".$sql."<br/>";
      }

      $result = $this->conn->query( $sql );     
      return $this->conn->affected_rows;

    }    
    
    private function pluralize( $column ) {

      if ( preg_match( "/ss$/", $column ) ) {
        return $column."es";
      } else if ( preg_match( "/y$/", $column ) ) {
        return substr( $column, 0, -1 )."ies";
      } else {
        return $column."s";
      }
    }

  }

  function prepend( &$column, $key, $table ) {
    $column = $table.".".$column;
  }  

?>
