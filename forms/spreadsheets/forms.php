<?php
  function forms_spreadsheet( $event, &$excelSheet, $order = 0 ) {

    $config = json_decode( $event[ 'configuration' ], true );
    if ( !empty( $config ) ) {
      foreach( $config as $key => $value ) {
        $event[ $key ] = $value;
      }
    }

    $col = 0;
    $row = 1;

    $excelSheet->setTitle( "Entry Forms" );
                      
    $q = new Query( "entry_forms" );
    $q->addWhere( "event_id", $event[ 'id' ] );

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

    $forms = $q->joinOn( "registration" )->select();

    $column_titles = array(
      "First Name", "Last Name", "Name", "Address", "City", "State", "Zip", "E-Mail",
      "Emergency Contact", "Emergency Phone", "Phone(h)", "Phone(w)", "SCCA Number", "SCCA Expiration",
      "Car Number", "Car", "Color", "Engine", "Tire Manufacturer", "Tire Size", "Street Tires?", "Sponsor", "Entry",
      "Class", "Category", "Group", "Position", "Number", "Work Assignment",
      "Codriver", "Paid Online" );
    
    if ( $order == 3 ) {
      $more_titles = array(
        "TO Car", "TO Class", "TO Number", "TO Work Assignment"
      );

      $column_titles = array_merge( $column_titles, $more_titles );
    }
    
    for( $ndx=0; $ndx < sizeof( $column_titles ); $ndx++ ) {
      $excelSheet->setCellValue( getColIndex( $col++ ).$row, $column_titles[ $ndx ] );
    }
    $row++;
    
    foreach( $forms as $form ) {
      $col = 0;

      $excelSheet->setCellValue( getColIndex( $col++ ).$row, clean_name( $form[ 'name_first' ] ) );
      $excelSheet->setCellValue( getColIndex( $col++ ).$row, clean_name( $form[ 'name_last' ] ) );
      $excelSheet->setCellValue( getColIndex( $col++ ).$row,
        clean_name( $form[ 'name_first' ] )." "
       .clean_name( $form[ 'name_last' ] ) );
      $excelSheet->setCellValue( getColIndex( $col++ ).$row, $form[ 'address_street' ] );
      $excelSheet->setCellValue( getColIndex( $col++ ).$row, $form[ 'address_city' ] );
      $excelSheet->setCellValue( getColIndex( $col++ ).$row, strtoupper( $form[ 'address_state' ] ) );
      $excelSheet->setCellValue( getColIndex( $col++ ).$row, $form[ 'address_zip' ] );
      $excelSheet->setCellValue( getColIndex( $col++ ).$row, $form[ 'email' ] );
      $excelSheet->setCellValue( getColIndex( $col++ ).$row, clean_name( $form[ 'emer_name' ] ) );
      $excelSheet->setCellValue( getColIndex( $col++ ).$row, $form[ 'emer_phone' ] );
      $excelSheet->setCellValue( getColIndex( $col++ ).$row, $form[ 'phone_home' ] );
      $excelSheet->setCellValue( getColIndex( $col++ ).$row, $form[ 'phone_work' ] );
      $excelSheet->setCellValue( getColIndex( $col++ ).$row, $form[ 'scca_number' ] );
      $excelSheet->setCellValue( getColIndex( $col++ ).$row, $form[ 'scca_date' ] );
      $excelSheet->setCellValue( getColIndex( $col++ ).$row, $form[ 'car_number' ] );
      $excelSheet->setCellValue( getColIndex( $col++ ).$row, $form[ 'year' ]." ".$form[ 'make' ]." ".$form[ 'model' ] );
      $excelSheet->setCellValue( getColIndex( $col++ ).$row, $form[ 'color' ] );
      $excelSheet->setCellValue( getColIndex( $col++ ).$row, $form[ 'engine' ] );
      $excelSheet->setCellValue( getColIndex( $col++ ).$row, $form[ 'tire_brand' ] );
      $excelSheet->setCellValue( getColIndex( $col++ ).$row, $form[ 'tire_size' ] );
      
      if ( $form[ 'tire_type' ] == 1 ) {
        $excelSheet->setCellValue( getColIndex( $col++ ).$row, "Yes" );
      } else {
        $excelSheet->setCellValue( getColIndex( $col++ ).$row, "No" );
      }
      
      $excelSheet->setCellValue( getColIndex( $col++ ).$row, $form[ 'club' ] );
      $q = new Query( "comp_categories" );
      $comp_category = $q->selectById( $form[ 'comp_category_id' ] );
      if ( !isset( $comp_category[ "name" ] ) ) {
        $comp_category = array( "id" => 0, "name" => "TO" );
      }

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
      $excelSheet->setCellValue( getColIndex( $col++ ).$row, $form[ 'priority' ] );
      $excelSheet->setCellValue( getColIndex( $col++ ).$row, $scca_class[ "initials" ] );

      if ( !empty( $comp_category[ 'initials' ] ) ) {
        $excelSheet->setCellValue( getColIndex( $col++ ).$row, $comp_category[ "initials" ] );
      } else {
        $excelSheet->setCellValue( getColIndex( $col++ ).$row, $comp_category[ "name" ] );
      }

      if ( ( $form[ 'run_group' ] != 0 ) &&
           ( $form[ 'position' ] != 0 ) ) {
        $excelSheet->setCellValue( getColIndex( $col++ ).$row, chr( $form[ 'run_group' ] + 64 ) );
        $excelSheet->setCellValue( getColIndex( $col++ ).$row, $form[ 'position' ] );
        $number = chr( $form[ 'run_group' ] + 64 ).$form[ 'position' ];

        if ( preg_match( "/^Novice/", $comp_category[ 'name' ] ) ) {
          $number .= "N";
        }
        
        $excelSheet->setCellValue( getColIndex( $col++ ).$row, $number );
        $q = new Query( "work_positions" );
        $work_position = $q->selectById( $form[ 'work_position_id' ] );
        $excelSheet->setCellValue( getColIndex( $col++ ).$row,
          chr( $form[ 'work_group' ] + 64 ).", ".$work_position[ "name" ] );
      } else {
        $excelSheet->setCellValue( getColIndex( $col++ ).$row, "" );
        $excelSheet->setCellValue( getColIndex( $col++ ).$row, "" );
        $excelSheet->setCellValue( getColIndex( $col++ ).$row, "" );
        $excelSheet->setCellValue( getColIndex( $col++ ).$row, "" );
      }

      $excelSheet->setCellValue( getColIndex( $col++ ).$row, preg_replace( "/&#039;/", "'", $form[ 'codriver' ] ) );

      $q = new Query( "payments" );
      $payment = $q->addWhere( "event_id", $event[ 'id' ] )
                   ->addWhere( "entrant_id", $form[ 'entrant_id' ] )
                   ->selectOne();
      if ( !empty( $payment ) ) {
        $excelSheet->setCellValue( getColIndex( $col++ ).$row, "Paid Online: $".number_format( $payment[ 'amount' ], 2 ) );
      } else {
        $excelSheet->setCellValue( getColIndex( $col++ ).$row, "" );
      }

      if ( $order == 3 ) {

        if ( $form[ 'registration_time_only_reg' ] == 1 ) {

	  $q = new Query( "entry_forms" );
          $to_form = 
	    $q->addWhere( "event_id", $event[ 'id' ] )
	      ->addWhere( "entrant_id", $form[ 'entrant_id' ] )
	      ->addWhere( "comp_category", 0 )
              ->joinOn( "registration" )
	      ->selectOne();

          if ( ( $form[ 'registration_car_id' ] == $form[ 'registration_to_car_id' ] ) ||
               ( $form[ 'registration_to_car_id' ] == 0 ) ) {
            $excelSheet->setCellValue( getColIndex( $col++ ).$row, "Same as Competition" );
            $excelSheet->setCellValue( getColIndex( $col++ ).$row, $scca_class[ 'initials' ]."/TO" );
          } else {
            $to_car = $to_form[ 'year' ]." ".$to_form[ 'make' ]." ".$to_form[ 'model' ].", ".$to_form[ 'color' ];
            $excelSheet->setCellValue( getColIndex( $col++ ).$row, $to_car );

            $q = new Query( "scca_classes" );
            $scca_class = $q->selectById( $to_form[ 'scca_class_id' ] );
            $excelSheet->setCellValue( getColIndex( $col++ ).$row, $scca_class[ 'initials' ]."/TO" );          
          }

          if ( ( $to_form[ 'run_group' ] != 0 ) && ( $to_form[ 'position' ] != 0 ) ) {
            $excelSheet->setCellValue( getColIndex( $col++ ).$row, chr( $to_form[ 'run_group' ] + 64 ).$to_form[ 'position' ] );
          } else {
            $excelSheet->setCellValue( getColIndex( $col++ ).$row, "" );
          }
        
	  $q = new Query( "work_positions" );
          $work_position = $q->selectById( $to_form[ 'work_position_id' ] );
          $excelSheet->setCellValue( getColIndex( $col++ ).$row, chr( $to_form[ 'work_group' ] + 64 ).", ".$work_position[ 'name' ] );
        }
      }
      
      $row++;
    }

  }
?>
