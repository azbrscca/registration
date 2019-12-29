<?php
  function blank_worker_row( &$excelSheet, $row, $run_group, $position ) {
    //echo "blank row for ".$run_group." ".$position."<br/>";
    $excelSheet->setCellValue( 'A'.$row, chr( $run_group + 64 ).$position );
  }

  function workers_spreadsheet( $event, &$excelSheet ) {

    $excelSheet->getColumnDimension('A')->setWidth( 4 );
    $excelSheet->getColumnDimension('B')->setWidth( 17 );
    $excelSheet->getColumnDimension('C')->setWidth( 12 );
    $excelSheet->getColumnDimension('D')->setWidth( 27 );

    $excelSheet->setTitle( "Workers" );

    $row = 1;

    $config = json_decode( $event[ 'configuration' ], true );

    $positions_array = array();

    for( $run_group = 1; $run_group <= $event[ 'run_groups' ]; $run_group++ ) {
    
      $q = new Query( "entry_forms" );
      $forms = $q->addWhere( "event_id", $event[ 'id' ] )
                 ->addWhere( "run_group", $run_group )
                 ->addOrder( "position" ) 
                 ->select(); 
      $last_position = $forms[ sizeof( $forms ) - 1 ][ 'position' ];
      $empty_positions = $last_position - sizeof( $forms );
      $positions_to_use = max( sizeof( $forms ), $last_position );
      if ( $empty_positions < 5 ) {
        $positions_to_use += ( 5 - $empty_positions );
      } 
      
      array_push( $positions_array, $positions_to_use );
    } 
    
    $positions_to_use = max( $positions_array );

    for( $run_group = 1; $run_group <= $event[ 'run_groups' ]; $run_group++ ) {

      $excelSheet->setCellValue( 'A'.$row, "Workers, Run Group ".chr( $run_group + 64 )." - ".date( "M. d, Y", $event[ 'date_ts' ] ) );
      $excelSheet->getStyle( 'A'.$row )->getFont()->setBold( true );
      $excelSheet->getStyle( 'A'.$row )->getFont()->setSize( 18 );
      $excelSheet->getStyle( 'A'.$row )->getAlignment()->setHorizontal( PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
      $excelSheet->mergeCells( 'A'.$row.':E'.$row );

      $row++;
      
      $q = new Query( "entry_forms" );
      $forms = $q->addWhere( "event_id", $event[ 'id' ] )
                 ->addWhere( "work_group", $run_group )
                 ->addOrder( "position" )
                 ->addOrder( "run_group" )
                 ->joinOn( "registration" )
                 ->select();
      $form_ndx = 0;

      $last_pos = 1;
      foreach( $forms as $form ) {

        while ( ( $last_pos + 1 ) < min( $form[ 'position' ], $positions_to_use ) ) {
          $last_pos++;
          //echo "current position ".$form[ 'run_group' ].$form[ 'position' ]."<br/>";
          blank_worker_row( $excelSheet, $row++, $form[ 'run_group' ], $last_pos );
        }


        $excelSheet->setCellValue( 'A'.$row, chr( $form[ 'run_group' ] + 64 ).$form[ 'position' ] );

        $r = new Query( "comp_categories" );
        $comp_category = $r->selectById( $form[ 'registration_comp_category_id' ] );

        $name = clean_name( $form[ 'name_first' ] )." "
          .clean_name( $form[ 'name_last' ] );

        if ( preg_match( "/^Novice*/i", $comp_category[ 'name' ] ) ) {
          $name .= " (N)";
        }

        $excelSheet->setCellValue( 'B'.$row, $name );

        $q = new Query( "work_positions" );
        $work_pos = $q->selectById( $form[ 'work_position_id' ] );
        $excelSheet->setCellValue( 'C'.$row, $work_pos[ 'name' ] );

        $thisCol = "";
        for( $ndx=1; $ndx<=3; $ndx++ ) {
          $key = 'work_pos_'.$ndx;
          if ( $form[ "registration_".$key ] == 0 ) {
            $thisCol .= "None ";
          } else {
            $q = new Query( "work_positions" );
            $work_pos = $q->selectById( $form[ "registration_".$key ] );
            $thisCol .= $work_pos[ 'name' ]." ";
          }
        }
          
        $excelSheet->setCellValue( 'D'.$row, $thisCol );

        $form_ndx++;

        $row++;
        $last_pos = $form[ 'position' ];
      }

      while ( $last_pos < $positions_to_use ) {
        $last_pos++;
        blank_worker_row( $excelSheet, $row++, $form[ 'run_group' ], $last_pos );
      }

      // Skip a couple blank rows for readability
      $row += 2;
      
      // Page break after each run group
	    $excelSheet->setBreak( 'A'.($row++), PHPExcel_Worksheet::BREAK_ROW );      
    }


    $excelSheet->getStyle( 'A1:E'.$row )->getAlignment()->setShrinkToFit( true );

  }
?>
