<?php
  require_once "ConnectionFactory.php";

  class Database {

    private static $loaded = false;
    private static $debug = false, $jsonFile, $name, $prefix;
    private static $tables = array();

    public static function getColumns( $table ) {
      $table = self::$prefix.$table;
      if ( array_key_exists( $table, self::$tables ) ) {
        return array_keys( self::$tables[ $table ] );
      }
    }

    public static function getColumnType( $table, $column ) {

      if ( !preg_match( "/^".self::$prefix."/", $table ) ) {
        $table = self::$prefix.$table;
      }
      if ( array_key_exists( $table, self::$tables ) &&
           array_key_exists( $column, self::$tables[ $table ] ) ) {
        return self::$tables[ $table ][ $column ][ 'type' ];
      } else {
        return null;
      }
    }
    
    public static function getPrefix() {
      return self::$prefix;
    }
    
    public static function getTables() {
      return array_keys( self::$tables );
    }
    
    public static function hasColumn( $table, $column ) {

      $table = self::$prefix.$table;
      return ( array_key_exists( $table, self::$tables ) &&
               array_key_exists( $column, self::$tables[ $table ] ) );
    }

    public static function hasTable( $table ) {
      return in_array( self::$prefix.$table, array_keys( self::$tables ) );
    } 

    public static function isLoaded() {
      return self::$loaded;
    }
    
    public static function isNullable( $table, $column ) {
      $table = self::$prefix.$table;
      return self::$tables[ $table ][ $column ][ 'nullable' ];
    }
    
    public static function loadCols( $conn, $table ) {
 
      $cols = array();
      $query = "show columns from ".$table; //." from ".self::$name;

      $result = $conn->query( $query );
      while( $row = $result->fetch_assoc() ) {
        $col = array(
                 'default' => $row[ 'Default' ],
                 'nullable' => ( $row[ 'Null' ] == "YES" ),
                 'type' => $row[ 'Type' ],
               );
        $cols[ $row[ 'Field' ] ] = $col;
        if ( self::$debug ) {
          echo " &nbsp; &nbsp; Column '".$row[ 'Field' ]."': ";
          print_r( $cols[ $row[ 'Field' ] ] );
          echo "<br/>";
        }
      }
      $result->close();
      return $cols;
    } 
 
    public static function loadTables( $conn, $loadCols = true, $forceReload = false ) {

      $factory = ConnectionFactory::getFactory();

      self::$jsonFile = $factory->getJsonFile();
      self::$name = $factory->getName();
      self::$prefix = $factory->getPrefix();

      if ( file_exists( self::$jsonFile ) && !$forceReload ) {

        $jsonSize = filesize( self::$jsonFile );
        $handle = fopen( self::$jsonFile, 'r' );
        $jsonString = fread( $handle, $jsonSize );
        self::$tables = json_decode( $jsonString, true );
        self::$loaded = true;
      }

      if ( !self::$loaded || $forceReload ) {
    
        self::$tables = array();
        $query = "show tables";
        $result = $conn->query( $query );
        while( $row = $result->fetch_array( MYSQLI_ASSOC ) ) {
          $table = $row[ 'Tables_in_'.self::$name ];
          if ( self::$debug ) {
            echo "Loading table '".$table."' in database '".self::$name."'<br/>";
          }
          self::$tables[ $table ] = ( $loadCols ? self::loadCols( $conn, $table ) : array() );
          if ( self::$debug ) {
            echo "<br/>";
          }
        }
        $result->close();
        self::$loaded = true;

        $handle = fopen( self::$jsonFile, 'w' );
        fwrite( $handle, json_encode( self::$tables ) );
        fclose( $handle );


      } else if ( self::$debug ) {
        echo "Database already loaded.<br/><br/>";
      }
    }
    
    public static function setDebug( $debug ) {
      self::$debug = $debug;
    }

  }
?>
