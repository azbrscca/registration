<?php	
  function announcer_spreadsheet( $event, &$excelSheet ) {

    $excelSheet->getColumnDimension('A')->setWidth( 4 );
    $excelSheet->getColumnDimension('B')->setWidth( 17 );
    $excelSheet->getColumnDimension('C')->setWidth( 6 );
    $excelSheet->getColumnDimension('D')->setWidth( 44 );

    $excelSheet->setTitle( "Announcer" );

    $row = 1;

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

      $excelSheet->setCellValue( 'A'.$row, "Announcer, Run Group ".chr( $run_group + 64 )." - ".date( "M. d, Y", $event[ 'date_ts' ] ) );
      $excelSheet->getStyle( 'A'.$row )->getFont()->setBold( true );
      $excelSheet->getStyle( 'A'.$row )->getFont()->setSize( 18 );
      $excelSheet->getStyle( 'A'.$row )->getAlignment()->setHorizontal( PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
      $excelSheet->mergeCells( 'A'.$row.':D'.$row );

      $row++;

      $q = new Query( "entry_forms" );
      $forms = $q->addWhere( "event_id", $event[ 'id' ] )
                 ->addWhere( "run_group", $run_group )
                 ->addOrder( "position" )
                 ->select();
      $form_ndx = 0;
      for( $position = 1; $position <= $positions_to_use; $position++ ) {

        if ( array_key_exists( $form_ndx, $forms ) &&
             ( $forms[ $form_ndx ][ 'position' ] == $position ) ) {

          $comp_category_name = "";
          if ( $forms[ $form_ndx ][ 'comp_category_id' ] == 0 ) {
            $comp_category_name = "TO";
          } else {
            $q = new Query( "comp_categories" );
            $comp_category = $q->selectById( $forms[ $form_ndx ][ 'comp_category_id' ] );
            $comp_category_name = $comp_category[ 'name' ];
          }
          $q = new Query( "scca_classes" );
          $scca_class = $q->selectById( $forms[ $form_ndx ][ 'scca_class_id' ] );

          $excelSheet->setCellValue( 'A'.$row, chr( $run_group + 64 ).$position );
          $excelSheet->setCellValue( 'B'.$row,
            clean_name( $forms[ $form_ndx ][ 'name_first' ] )." "
           .clean_name( $forms[ $form_ndx ][ 'name_last' ] ) );

          if ( $forms[ $form_ndx ][ 'scca_number' ] != "" ) {
            $excelSheet->setCellValue( 'C'.$row, "SCCA" );
          } else {
            $excelSheet->setCellValue( 'C'.$row, "WKND" );
          }

          $excelSheet->setCellValue( 'D'.$row, "[".$scca_class[ 'initials' ]."-".clean_name( $comp_category_name )."] "
                .$forms[ $form_ndx ][ 'year' ]." "
                .ucfirst( $forms[ $form_ndx ][ 'make' ] )."/"
                .ucfirst( $forms[ $form_ndx ][ 'model' ] )." "
                .ucfirst( $forms[ $form_ndx ][ 'color' ] ) );

          $form_ndx++;

        } else {

          $excelSheet->setCellValue( 'A'.$row, chr( $run_group + 64 ).$position );
          $excelSheet->setCellValue( 'B'.$row, "" );
          $excelSheet->setCellValue( 'C'.$row, "" );
          $excelSheet->setCellValue( 'D'.$row, "" );
        }
        $row++;
      }

      // Skip a couple blank rows for readability
      $row += 2;
      
      // Page break after each run group
	  $excelSheet->setBreak( 'A'.($row++), PHPExcel_Worksheet::BREAK_ROW );      
    }

    $excelSheet->getStyle( 'A1:D'.$row )->getAlignment()->setShrinkToFit( true );

  }
?>
