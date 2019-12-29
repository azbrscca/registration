<?php
  function svsort_forms( $a, $b ) {

    $catA = $a[ 'comp_category_id' ];
    $catB = $b[ 'comp_category_id' ];

    if ( ( $catA == 0 ) xor ( $catB == 0 ) ) {
      return ( $catA == 0 ? 1 : -1 );
    } else {
      if ( $a[ 'name_last' ] == $b[ 'name_last' ] ) {
        return strcmp( $a[ 'name_first' ], $b[ 'name_first' ] );
      } else {
        return strcmp( $a[ 'name_last' ], $b[ 'name_last' ] );
      }
    }
  }

  function forms_as_xml( $event, $order, $type ) {

    $privs = json_decode( $event[ 'organization_privileges' ], true );
    if ( $privs[ 'configuration' ] == "true" ) {
      $config = json_decode( $event[ 'configuration' ], true );
      foreach( $config as $key => $value ) {
        $event[ $key ] = $value;
      }
    }

    /* ordering:
      1. Alphabetical by Last Name
      2. Alphabetical by First Name
      3. Run Order
    */

    $q = new Query( "entry_forms" );
    $q->addWhere( "event_id", $event[ 'id' ] )
      ->joinOn( 'entrant', array( "dob" ) );

    switch ( $order ) {
    
      case 3:
        $q->addOrder( "run_group" )
          ->addOrder( "position" );
        break;
 
     case 2:
        $q->addOrder( "name_first" )
          ->addOrder( "name_last" );
        break;

      case 1:
      default:
        $q->addOrder( "name_last" )
          ->addOrder( "name_first" );
        break;
    }

    /* form type
      1. Combined Competition & TO
      2. Separate Competition & TO
      3. Separate, Competitions followed by TOs
    */
    
    switch( $type ) {

      case 3:
      default:
        $forms = $q->select();
	      usort( $forms, "svsort_forms" );
        break;

      case 2:
//        echo "Separate Competition & TO<br/>";
        $forms = $q->select();
        break;
        
      case 1:
      default:
 //       echo "Combined Competition & TO<br/>";      
        $forms = $q->addWhere( "comp_category_id", 0, "!=" )
                   ->select();
        break;
    }


    $simple_xml = new SimpleXMLElement("<event></event>");
    $metadata = $simple_xml->addChild("metadata");
    $entryFees = $metadata->addChild("entryFees");

    $entry_fees = json_decode( $event[ 'organization_entry_fees' ], true );
    foreach( array_keys($entry_fees) as $key) {
      $entryFees->addChild($key, $entry_fees[$key]);
    }

    $simple_xml->addAttribute( 'date', $event[ 'date' ] );
    $simple_xml->addAttribute( 'formatteddate', date( "D. M. d, Y", strtotime( $event[ 'date' ] ) ) );

    $simple_xml->addAttribute( 'organization', $event[ 'organization_name' ] );
    $simple_xml->addAttribute( 'orgshortname', strtolower( $event[ 'organization_shortname' ] ) );

    $simple_xml->addAttribute( 'site', $event[ 'site_name' ] );

    $event_ts = strtotime( $event[ 'date' ] );

    $forms_xml = $simple_xml->addChild( "forms");

    foreach( $forms as $form ) {

      $dob_ts = strtotime( $form[ 'entrant_dob' ] );
      $minor = ( ( ( $event_ts - $dob_ts ) / 31556926 ) < 18 ) ? "Yes" : "No";

      $form_xml = $forms_xml->addChild( "form");
      $form_xml->addAttribute( 'priority', $form[ 'priority' ] );
      $form_xml->addAttribute( 'registration_id', $form[ 'registration_id' ] );

      $q = new Query( "registrations" );
      $registration = $q->selectById( $form[ 'registration_id' ] );
      
      $entrant_xml = $form_xml->addChild( "entrant" );
      $entrant_xml->addAttribute( 'id', $form[ 'entrant_id' ] );
      
      $name_xml = $entrant_xml->addChild( "name" );
      $name_xml->addChild( "firstname", ucfirst( $form[ 'name_first' ] ) );
      $name_xml->addChild( "lastname", ucfirst( $form[ 'name_last' ] ) );

      $entrant_xml->addChild( "dob", $form[ 'entrant_dob' ] );
      $entrant_xml->addChild( "minor", $minor );

      $address_xml = $entrant_xml->addChild( "address" );
      $address_xml->addChild( "street", $form[ 'address_street' ] );
      $address_xml->addChild( "city", $form[ 'address_city' ] );
      $address_xml->addChild( "state", $form[ 'address_state' ] );
      $address_xml->addChild( "zipcode", $form[ 'address_zip' ] );
      
      $entrant_xml->addChild( "email", $form[ 'email' ] );

      $contact_xml = $entrant_xml->addChild( "emergencycontact" );
      $contact_xml->addChild( "name", $form[ 'emer_name' ] );
      $contact_xml->addChild( "phonenumber", $form[ 'emer_phone' ] );
      
      $phone_xml = $entrant_xml->addChild( "phonenumbers" );
      $phone_xml->addChild( "home", $form[ 'phone_home' ] );
      $phone_xml->addChild( "work", $form[ 'phone_work' ] );
      
      $scca_xml = $entrant_xml->addChild( "sccamembership" );
      $scca_xml->addChild( "number", $form[ 'scca_number' ] );
 
     
//     if ( strtotime( $event[ 'date' ] ) > strtotime( $form[ 'scca_date' ] ) ) {
       $scca_xml->addChild( "status", "(".$form[ 'scca_date' ].")" );
//      } else {
//       $scca_xml->addChild( "status", "Expires on ".$form[ 'scca_date' ] );
//      }
     
      $entrant_xml->addChild( "sponsor", $form[ 'club' ] );

      $reg_xml = $form_xml->addChild( "registration" );
      $reg_xml->addChild( "car-number", $form[ 'car_number' ] );
      $reg_xml->addChild( "codriver", $form[ 'codriver' ] );

      $q = new Query( "payments" );
      $payment = $q->addWhere( "event_id", $event[ 'id' ] )
                   ->addWhere( "entrant_id", $form[ 'entrant_id' ] )
                   ->selectOne();
      $reg_xml->addChild( "payment", empty( $payment  ) ? "" : "Paid Online: $".number_format( $payment[ 'amount' ], 2 ) );

      $comp_xml = $form_xml->addChild( "competition" );
      $car_xml = $comp_xml->addChild( "car" );

      $car_xml->addChild( "year", $form[ 'year' ] );
      $car_xml->addChild( "make", $form[ 'make' ] );
      $car_xml->addChild( "model", $form[ 'model' ] );
      $car_xml->addChild( "color", $form[ 'color' ] );
      $car_xml->addChild( "engine", $form[ 'engine' ] );
      $car_xml->addChild( "tire_brand", $form[ 'tire_brand' ] );
      $car_xml->addChild( "tire_size", $form[ 'tire_size' ] );
      $car_xml->addChild( "tire_type", ( $form[ 'tire_type' ] == 1 ) ? 'Street' : 'Competition' );
//      $car_xml->addChild( "modifications", $form[ 'perf_mods' ] );

      switch( $event[ 'event_type_id' ] ) {
        case 2: // rallycross
          $q = new Query( "scca_rallyx_classes" );
	  break;

	case 1: // autocross
	default:
          $q = new Query( "scca_classes" );
	  break;
      }
      $scca_class = $q->selectById( $form[ 'scca_class_id' ] );
      $car_xml->addChild( "class", $scca_class[ 'initials' ] );

      $q = new Query( "comp_categories" );
      $comp_category = $q->selectById( $form[ 'comp_category_id' ] );
      $car_xml->addChild( "category", ( isset( $comp_category[ "name" ] ) ? $comp_category[ 'name' ] : "TO" ) );

      $car_number = "Not Assigned";
      $work_assign = "Not Assigned";


      if ( ( $privs[ 'configuration' ] == "true" ) &&
           ( $form[ 'run_group' ] != 0 ) &&
           ( $form[ 'position' ] != 0 ) ) {
        $car_number = chr( $form[ 'run_group' ] + 64 ).$form[ 'position' ];
        if ( preg_match( "/^Novice/", $comp_category[ 'name' ] ) ) {
          $car_number .= "N";
        }

        $q = new Query( "work_positions" );
        $work_position = $q->selectById( $form[ 'work_position_id' ] );
        $work_assign = chr( $form[ 'work_group' ] + 64 ).", ".$work_position[ 'name' ];

      }

      $comp_xml->addChild( "car-number", $car_number );
      $comp_xml->addChild( "work-assign", $work_assign );

      if ( $type == 1 ) {

        if ( $registration[ 'time_only_reg' ] == 1 ) {
    
          $q = new Query( "entry_forms" );
          $to_form = $q->addWhere( "event_id", $event[ 'id' ] )
                       ->addWhere( "entrant_id", $form[ 'entrant_id' ] )
                       ->addWhere( "comp_category_id", 0 )
                       ->selectOne();

          $to_xml = $form_xml->addChild( "time-only" );
          $to_car_xml = $to_xml->addChild( "car" );

          if ( ( $registration[ 'car_id' ] == $registration[ 'to_car_id' ] ) ||
               ( $registration[ 'to_car_id' ] == 0 ) ) {
            
            $to_car_xml->addAttribute( "competition-car", "true" );

          } else {

            $to_car_xml->addChild( "year", $to_form[ 'year' ] );
            $to_car_xml->addChild( "make", $to_form[ 'make' ] );
            $to_car_xml->addChild( "model", $to_form[ 'model' ] );
            $to_car_xml->addChild( "color", $to_form[ 'color' ] );
            $to_car_xml->addChild( "engine", $to_form[ 'engine' ] );
            $to_car_xml->addChild( "tire_brand", $to_form[ 'tire_brand' ] );
            $to_car_xml->addChild( "tire_size", $to_form[ 'tire_size' ] );
            $to_car_xml->addChild( "tire_type", ( $to_form[ 'tire_type' ] == 1 ) ? 'Street' : 'Competition' );               

            $q = new Query( "scca_classes" );
            $scca_class = $q->selectById( $to_form[ 'scca_class_id' ] );
            $to_car_xml->addChild( "class", $scca_class[ 'initials' ] );

          }

          $car_number = "Not Assigned";
          $work_assign = "Not Assigned";

          if ( ( $privs[ 'configuration' ] == "true" ) &&
               ( $to_form[ 'run_group' ] != 0 ) &&
               ( $to_form[ 'position' ] != 0 ) ) {
            $car_number = chr( $to_form[ 'run_group' ] + 64 ).$to_form[ 'position' ];
            
            $q = new Query( "work_positions" );
            $work_position = $q->selectById( $to_form[ 'work_position_id' ] );
            $work_assign = chr( $to_form[ 'work_group' ] + 64 ).", ".$work_position[ 'name' ];

          }

          $to_xml->addChild( "car-number", $car_number );
          $to_xml->addChild( "work-assign", $work_assign );

        }
      }
   
    }
    // Header('Content-type: text/xml');
    // echo $simple_xml->asXML();
    $xsl_file = rootDir."forms/xsl/".strtolower( $event[ 'organization_shortname' ] ).".xsl";
    $simple_xsl = simplexml_load_file( $xsl_file );
    $xslt = new XSLTProcessor();
    $xslt->importStylesheet( $simple_xsl );
    echo $xslt->transformToXml( $simple_xml );
  }
?>
