<?php
  class Functions {

  	public static function decodeJSONField( &$item, $key, $field ) {
  	  $item[ $field ] = json_decode( $item[ $field ] );
  	}

		private static function removePrefix( &$item, $index, $start) {
		  $item = substr( $item, $start );
		}

    public static function listFiles( $dir, $ext = "*", $recursive = false ) {
      $files = glob( rootDir.$dir."/*".$ext );
			array_walk( $files, array( "Functions", "removePrefix" ), strlen( rootDir ) );

			if ( $recursive ) {
			  $subdirs = glob( rootDir.$dir."/*", GLOB_ONLYDIR );
			  array_walk( $subdirs, array( "Functions", "removePrefix" ), strlen( rootDir ) );
				foreach( $subdirs as $subdir ) {
				  $files = array_merge( $files, Functions::listFiles( $subdir, $ext, true ) );
				}
			}

			return $files;
    }

    public static function cleanArray( $item ) {
      if ( is_array( $item ) ) {
        $item = array_map( array( 'Functions', 'cleanArray' ), $item );
      } else {
        $item = mb_convert_encoding( $item, 'UTF-8', 'UTF-8' );
        $item = stripcslashes( $item );
        $item = htmlentities( $item, ENT_QUOTES, 'UTF-8' );
      }
      return $item;
    }

    public static function cleanName( $dirty_name ) {
      $clean_name = trim( $dirty_name );
      $clean_name = ucfirst( $clean_name );
      $clean_name = preg_replace( "/&#039;/", "'", $clean_name );
      $clean_name = html_entity_decode( $clean_name );
      return $clean_name;
    }

    public static function decodeArray( $item ) {
      if ( is_array( $item ) ) {
        $item = array_map( array( 'Functions', 'decodeArray' ), $item );
      } else {
        $item = html_entity_decode( $item );
      }
      return $item;
    }
      
    public static function randomString( $length = 16 ) {
      $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
      $string = "";
      for( $i=0; $i<$length; $i++ ) {
        $string .= $chars[(mt_rand(1, strlen($chars)))-1];
      }
      return $string;
    }

    public static function sortByEntrantName( $one, $two ) {
      if ( strtolower( $one[ 'entrant_name_last' ] ) == strtolower( $two[ 'entrant_name_last' ] ) ) {
        return strcmp( strtolower( $one[ 'entrant_name_first' ] ), strtolower( $two[ 'entrant_name_first' ] ) );
      } else {
        return strcmp( strtolower( $one[ 'entrant_name_last' ] ), strtolower( $two[ 'entrant_name_last' ] ) );
      }
    }

    public static function stripslashesDeep( $item ) {
      if ( is_array( $item ) ) {
        $item = array_map( array( 'Functions', 'stripslashesDeep' ), $item );
      } else {
        $item = stripslashes( $item );
      }
      return $item;
    }


  } // class Functions
?>
