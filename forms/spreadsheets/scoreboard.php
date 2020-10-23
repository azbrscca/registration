<?php
  function scoreboard_spreadsheet( $event, &$excelSheet ) {

    $excelSheet->getColumnDimension( 'A' )->setWidth( 22.22 );

    $excelSheet->setTitle( "Scoreboard" );

    $col = 0;
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

      $q = new Query( "entry_forms" );
      $forms = $q->addWhere( "event_id", $event[ 'id' ] )
                 ->addWhere( "run_group", $run_group )
                 ->addOrder( "position" )
                 ->select();
      $form_ndx = 0;
      for( $position = 1; $position <= $positions_to_use; $position++ ) {

        $excelSheet->getRowDimension( $row )->setRowHeight( 16.5 );
        $excelSheet->getRowDimension( $row+1 )->setRowHeight( 16.5 );
        $excelSheet->getRowDimension( $row+2 )->setRowHeight( 16.5 );
if ( $event[ 'organization_id' ] == 3 ) {
        $excelSheet->getRowDimension( $row+3 )->setRowHeight( 2 );
} else {
        $excelSheet->getRowDimension( $row+3 )->setRowHeight( 29 );
}
        if ( array_key_exists( $form_ndx, $forms ) &&
             ( $forms[ $form_ndx ][ 'position' ] == $position ) ) {

          $excelSheet->getStyle( getColIndex( $col ).$row )->getFont()->setBold( true );
          $excelSheet->setCellValue(
            getColIndex( $col ).$row++,
            chr( $run_group + 64 ).$position." "
           .clean_name( $forms[ $form_ndx ][ 'name_first' ] )." "
           .clean_name( $forms[ $form_ndx ][ 'name_last' ] ) );

          $excelSheet->setCellValue( getColIndex( $col ).$row++, $forms[ $form_ndx ][ 'year' ]." "
           .ucfirst( $forms[ $form_ndx ][ 'make' ] )."/"
           .ucfirst( $forms[ $form_ndx ][ 'model' ] )." "
           .ucfirst( $forms[ $form_ndx ][ 'color' ] ) );

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

          $excelSheet->setCellValue( getColIndex( $col ).$row++,$scca_class[ 'initials' ]."-".clean_name( $comp_category_name ) );
          $excelSheet->setCellValue( getColIndex( $col ).$row++, "" );

          $form_ndx++;

        } else {

          $excelSheet->getStyle( getColIndex( $col ).$row )->getFont()->setBold( true );
          $excelSheet->setCellValue( getColIndex( $col ).$row++, chr( $run_group + 64 ).$position );
          $excelSheet->setCellValue( getColIndex( $col ).$row++, "" );
          $excelSheet->setCellValue( getColIndex( $col ).$row++, "" );
          $excelSheet->setCellValue( getColIndex( $col ).$row++, "" );
        }
        
        if ( $position % 7 == 0 ) {
          $col++;
          $row = 1;
          $excelSheet->getColumnDimension( getColIndex( $col ) )->setWidth( 22.22 );
        }
      }	  
      $col++;
      $row = 1;
      $excelSheet->getColumnDimension( getColIndex( $col ) )->setWidth( 22.22 );
    }

    $excelSheet->getStyle( 'A1:BZ31' )->getAlignment()->setShrinkToFit( true );
  }
?>
