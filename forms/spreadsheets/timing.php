<?php
  function timing_spreadsheet( $event, &$excelSheet ) {

    $regBorder = array(
      'borders' => array(
        'allborders' => array(
          'style' => PHPExcel_Style_Border::BORDER_THIN
        )
      )
    );

    $altBorder = array(
  	  'borders' => array(
       'allborders' => array(
   	     'style' => PHPExcel_Style_Border::BORDER_THICK
       )
   		)
    );
	

    $excelSheet->getColumnDimension('A')->setWidth( 20 );
    $excelSheet->getColumnDimension('B')->setWidth( 8 );
    $excelSheet->getColumnDimension('C')->setWidth( 8 );
    $excelSheet->getColumnDimension('D')->setWidth( 8 );
    $excelSheet->getColumnDimension('E')->setWidth( 8 );
    $excelSheet->getColumnDimension('F')->setWidth( 8 );
    $excelSheet->getColumnDimension('G')->setWidth( 8 );

    $excelSheet->setTitle( "Timing" );

    $row = 1;

    $max_group_size = 0;
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

      $excelSheet->setCellValue( 'A'.$row, "Timing Instructions" );
      $excelSheet->mergeCells( 'A'.$row.':G'.$row );
      $excelSheet->getStyle( 'A'.$row )->getFont()->setBold( true );
      $excelSheet->getStyle( 'A'.$row++ )->getAlignment()->setHorizontal( PHPExcel_Style_Alignment::HORIZONTAL_CENTER );

      $excelSheet->mergeCells( 'A'.$row.':G'.$row );      
      $excelSheet->setCellValue( 'A'.$row++, "Raw: Write in the run time (in seconds) as shown by the timing system." );
      $excelSheet->mergeCells( 'A'.$row.':G'.$row );
      $excelSheet->setCellValue( 'A'.$row++, "Penalty: Write in the cone count as indicated by the spotter(s)." );
      $excelSheet->mergeCells( 'A'.$row.':G'.$row );
      $excelSheet->setCellValue( 'A'.$row++, "Total: Write in the raw time plus 2 seconds for each cone penalty. Write in" );
      $excelSheet->mergeCells( 'A'.$row.':G'.$row );
      $excelSheet->setCellValue( 'A'.$row++, "DNS for 'Did Not Start' or DNF for 'Did Not Finish' when applicable." );
      $excelSheet->mergeCells( 'A'.$row.':G'.$row );
      $excelSheet->getStyle( 'A'.$row )->getFont()->setBold( true );
      $excelSheet->setCellValue( 'A'.$row++, "Once a competitor has completed all of their runs, please circle their lowest total." );
      
      $excelSheet->setCellValue( 'A'.$row, "Example" );
      $excelSheet->mergeCells( 'A'.$row.':G'.$row );
      $excelSheet->getStyle( 'A'.$row )->getFont()->setBold( true );
      $excelSheet->getStyle( 'A'.$row++ )->getAlignment()->setHorizontal( PHPExcel_Style_Alignment::HORIZONTAL_CENTER );

      $excelSheet->setCellValue( 'A'.$row, "D2 Skipp Sudduth" );
      $excelSheet->getStyle( 'A'.$row )->getFont()->setBold( true );
      $excelSheet->setCellValue( 'A'.($row+1), "FS / Open" );
      $excelSheet->setCellValue( 'A'.($row+2), "1998 Audi S8 Quattro" );
      $excelSheet->setCellValue( 'A'.($row+1), "Dark Green" );

      $excelSheet->setCellValue( 'B'.$row, "Raw:" );
      $excelSheet->setCellValue( 'B'.($row+1), "Penalty:" );
      $excelSheet->setCellValue( 'B'.($row+2), "Total" );
      
      $raw = array();
      $penalty = array();
      $total = array();
      for( $i=0; $i<5; $i++ ) {
        $r = 42 + rand( 0, 1000 ) / 1000;
        $p = rand( 0, 3 );
        array_push( $raw, $r );
        array_push( $penalty, $p );
        array_push( $total, $r + 2 * $p );
      }

	    $excelSheet->getStyle('C'.$row.':G'.($row+2))->applyFromArray($regBorder);

	    $excelSheet->getRowDimension( $row )->setRowHeight( 22 );
	    $excelSheet->getRowDimension( $row+1 )->setRowHeight( 22 );
	    $excelSheet->getRowDimension( $row+2 )->setRowHeight( 22 );
	    $excelSheet->getRowDimension( $row+3 )->setRowHeight( 22 );

	    $excelSheet->getRowDimension( $row+4 )->setRowHeight( 2 );

      $excelSheet->getStyle( 'C'.$row.':G'.($row+2) )->getAlignment()->setHorizontal( PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
      $excelSheet->getStyle( 'C'.$row.':G'.($row+2) )->getAlignment()->setVertical( PHPExcel_Style_Alignment::VERTICAL_CENTER );


      $min = min( $total );
      $max = max( $total );
      for( $i=0; $i<5; $i++ ) {
		    $excelSheet->setCellValue( getColIndex( $i+2 ).$row, number_format( $raw[ $i ], 3 ) );
    		$excelSheet->setCellValue( getColIndex( $i+2 ).($row+1), $penalty[ $i ] );
    		if ( $total[ $i ] == $max ) {
    		  $excelSheet->setCellValue( getColIndex( $i+2 ).($row+2), "DNF" );
    		} else {
    		  $excelSheet->setCellValue( getColIndex( $i+2 ).($row+2), $total[ $i ] );
    		}
    		if ( $total[ $i ] == $min ) {
		      $excelSheet->getStyle( getColIndex($i+2).($row+2))->applyFromArray($altBorder);
		    }
      }
      $row +=5;

      $excelSheet->setCellValue( 'A'.$row, "Timing, Run Group ".chr( $run_group + 64 )." - ".date( "M. d, Y", $event[ 'date_ts' ] ) );
      $excelSheet->getStyle( 'A'.$row )->getFont()->setBold( true );
      $excelSheet->getStyle( 'A'.$row )->getFont()->setSize( 14 );
      $excelSheet->getStyle( 'A'.$row )->getAlignment()->setHorizontal( PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
      $excelSheet->mergeCells( 'A'.$row.':G'.$row );

      $row++;
      $q = new Query( "entry_forms" );
      $forms = $q->addWhere( "event_id", $event[ 'id' ] )
                 ->addWhere( "run_group", $run_group )
                 ->addOrder( "position" )
                 ->select();
      $form_ndx = 0;
      for( $position = 1; $position <= $positions_to_use; $position++ ) {

        $excelSheet->getStyle( 'A'.$row )->getFont()->setBold( true );

        if ( array_key_exists( $form_ndx, $forms ) &&
             ( $forms[ $form_ndx ][ 'position' ] == $position ) ) {

          $excelSheet->setCellValue( 'A'.$row,
            chr( $run_group + 64 ).$position. " "
           .clean_name( $forms[ $form_ndx ][ 'name_first' ] )." "
           .clean_name( $forms[ $form_ndx ][ 'name_last' ] ) );

          $q = new Query( "scca_classes" );
          $scca_class = $q->selectById( $forms[ $form_ndx ][ 'scca_class_id' ] );
          $thisCol = $scca_class[ 'initials' ];
          if ( $forms[ $form_ndx ][ 'comp_category_id' ] == 0 ) {
            $thisCol .= " / TO";
          } else {
            $q = new Query( "comp_categories" );
            $comp_category = $q->selectById( $forms[ $form_ndx ][ 'comp_category_id' ] );
            $thisCol .= " / ".clean_name( $comp_category[ 'name' ] );
          }
          $excelSheet->setCellValue( 'A'.($row+1), $thisCol );
          $excelSheet->setCellValue( 'A'.($row+2), $forms[ $form_ndx ][ 'year' ]." "
          .ucfirst( $forms[ $form_ndx ][ 'make' ] )."/"
          .ucfirst( $forms[ $form_ndx ][ 'model' ] ) );
          
          $excelSheet->setCellValue( 'A'.($row+3), ucfirst( $forms[ $form_ndx ][ 'color' ] ) );

          $form_ndx++;
          
        } else {
          $excelSheet->setCellValue( 'A'.$row, chr( $run_group + 64 ).$position );
        }

        $excelSheet->setCellValue( 'B'.$row, "Raw: " );
        $excelSheet->setCellValue( 'B'.($row+1), "Penalty: " );
        $excelSheet->setCellValue( 'B'.($row+2), "Total: " );

        $excelSheet->getStyle('C'.$row.':G'.($row+2))->applyFromArray($regBorder);
 
        $excelSheet->getRowDimension( $row )->setRowHeight( 22 );
        $excelSheet->getRowDimension( $row+1 )->setRowHeight( 22 );
        $excelSheet->getRowDimension( $row+2 )->setRowHeight( 22 );
        $excelSheet->getRowDimension( $row+3 )->setRowHeight( 22 );

        $excelSheet->getRowDimension( $row+4 )->setRowHeight( 2 );


        $row +=4;
        if ( ( ( $position + 2 ) % 7 ) == 0 ) {
          $excelSheet->setBreak( 'A'.$row, PHPExcel_Worksheet::BREAK_ROW );
        }
        $row++;

      }

      // Page break after each run group
	  $excelSheet->setBreak( 'A'.($row++), PHPExcel_Worksheet::BREAK_ROW );      
    }

    $excelSheet->getStyle( 'A1:G'.$row )->getAlignment()->setShrinkToFit( true );
    
    unset( $regBorder );
    unset( $altBorder );
  }
?>
