<?php
  function rallyx_ts_spreadsheet( $event, &$excelSheet ) {

    $row = 1;

    $excelSheet->setTitle( "Timing & Scoring" );

    $q = new Query( "comp_categories" );
    $categories =
      $q->addWhere( 'organization_id', $event[ 'organization_id' ] )
        ->select();
 
    foreach( $categories as $category ) {
      $excelSheet->setCellValue( 'A'.$row, $category[ 'name' ] );
      $excelSheet->getStyle( 'A'.$row )->getFont()->setBold( true );
      $excelSheet->mergeCells( 'A'.$row.':G'.$row );
      $row++;

      $excelSheet->setCellValue( 'A'.$row, "Last Name" );
      $excelSheet->setCellValue( 'B'.$row, "First Name" );
      $excelSheet->setCellValue( 'C'.$row, "Class" );
      $excelSheet->setCellValue( 'D'.$row, "Number" );
      $excelSheet->setCellValue( 'E'.$row, "Car" );
      $excelSheet->mergeCells( 'E'.$row.':G'.$row );

      for( $i=1; $i<7; $i++ ) {
        $excelSheet->setCellValue( getColIndex( $i+7 ).$row, "Run ".$i );
      }
      $excelSheet->setCellValue( getColIndex( $i+7 ).$row, "Total" );

      $row++;

      $q = new Query( "entry_forms" );
      $forms =
        $q->addWhere( "event_id", $event[ 'id' ] )
          ->addWhere( "comp_category_id", $category[ 'id' ] )
          ->addOrder( "name_last" )
          ->addOrder( "name_first" )
          ->select();

      foreach( $forms as $form ) {
        
        $q = new Query( "scca_rallyx_classes" );
        $scca_class = $q->selectById( $form[ 'scca_class_id' ] );

        $excelSheet->setCellValue( 'A'.$row, ucfirst( $form[ 'name_first' ] ) );
        $excelSheet->setCellValue( 'B'.$row, ucfirst( $form[ 'name_last' ] ) );
        $excelSheet->setCellValue( 'C'.$row, $scca_class[ "initials" ] );
        $excelSheet->setCellValue( 'D'.$row, $form[ 'car_number' ] );
        $excelSheet->setCellValue( 'E'.$row, $form[ 'year' ]." ".$form[ 'make' ]." ".$form[ 'model' ] );
        $excelSheet->mergeCells( 'E'.$row.':G'.$row );

        $row++;
      }

      $row+=2;
    }

    $excelSheet->getStyle( 'A1:G'.$row )->getAlignment()->setShrinkToFit( true );

  }
?>
