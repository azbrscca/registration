<?php
  function scoring_spreadsheet( $event, &$excelSheet ) {

    $col = 1;
    $row = 1;

    $excelSheet->setTitle( "Timing & Scoring" );

	$q = new Query( "entry_forms" );
	$forms = $q->addWhere( "event_id", $event[ 'id' ] )
			   ->addOrder( "run_group" )
			   ->addOrder( "position" )
         ->joinOn("registration")
			   ->select();

    $column_titles = array( "First Name", "Last Name", "SCCA #", 
                            "Class", "Category", "Car", "Car Color",
                            "Run Group", "Position",
                            "RG1", "RG2", "RG3", "Work Group",
                            "Work Assigned",
                            "Work Pref 1",
                            "Work Pref 2",
                            "Work Pref 3",
                            "Notes"
                         );

    for( $ndx=0; $ndx < sizeof( $column_titles ); $ndx++ ) {
      $excelSheet->setCellValue( getColIndex( $col++ ).$row, $column_titles[ $ndx ] );
    }
    $row++;

    foreach( $forms as $form ) {
      $col = 1;

      $lookup = array(
        'entrant' => $form['entrant_id'],
        'event' => $form['event_id'],
        'form' => $form['id'],
        'registration' => $form['registration_id']
      );

      $excelSheet->setCellValue( getColIndex( $col++ ).$row, clean_name( trim( $form[ 'name_first' ] ) ));
      $excelSheet->setCellValue( getColIndex( $col++ ).$row, clean_name( trim( $form[ 'name_last' ] ) ));
      $excelSheet->setCellValue( getColIndex( $col++ ).$row, $form['scca_number']);

      // $excelSheet->setCellValue( getColIndex( $col++ ).$row, json_encode($lookup));

      $q = new Query( "scca_classes" );
      $scca_class = $q->selectById( $form[ 'scca_class_id' ] );
      $excelSheet->setCellValue( getColIndex( $col++ ).$row, $scca_class[ "initials" ] );

      if ( $form[ 'comp_category_id' ] == 0 ) {
        $excelSheet->setCellValue( getColIndex( $col++ ).$row, "TO" );
      } else {
        $q = new Query( "comp_categories" );
        $comp_category = $q->selectById( $form[ 'comp_category_id' ] );
        if ( !empty( $comp_category[ 'initials' ] ) ) {
          $excelSheet->setCellValue( getColIndex( $col++ ).$row, $comp_category[ "initials" ] );
        } else {
          $excelSheet->setCellValue( getColIndex( $col++ ).$row, $comp_category[ "name" ] );
        }
      }

      $excelSheet->setCellValue( getColIndex( $col++ ).$row, $form[ 'year' ]." ".$form[ 'make' ]." ".$form[ 'model' ] );
      $excelSheet->setCellValue( getColIndex( $col++ ).$row, $form[ 'color' ] );

      $excelSheet->setCellValue( getColIndex( $col++ ).$row, chr( $form[ 'run_group' ] + 64 ) );
      $excelSheet->getStyle( getColIndex( $col ).$row )->getNumberFormat()->setFormatCode( '00' );
      $excelSheet->setCellValue( getColIndex( $col++ ).$row, $form[ 'position' ] );

      for( $ndx=1; $ndx<=3; $ndx++ ) {
        $data = $form[ 'registration_run_group_'.$ndx ];
        if ($data == 0) {
          $col++;
        } else {
          $excelSheet->setCellValue( getColIndex( $col++ ).$row, chr( $form[ 'registration_run_group_'.$ndx ] + 64 ) );
        }
      }
      $excelSheet->setCellValue( getColIndex( $col++ ).$row, chr( $form[ 'work_group' ] + 64 ) );

      $workQ = new Query( "work_positions" );
      $work_pos = $workQ->selectById( $form[ 'work_position_id' ] );
      $excelSheet->setCellValue( getColIndex( $col++ ).$row, $work_pos[ 'name' ] );
      for( $ndx=1; $ndx<=3; $ndx++ ) {
        $key = 'work_pos_'.$ndx;
        if ( $form[ "registration_".$key ] == 0 ) {
          $excelSheet->setCellValue( getColIndex( $col++ ).$row, "No Preference" );
        } else {
          $q = new Query( "work_positions" );
          $work_pos = $q->selectById( $form[ "registration_".$key ] );
          $excelSheet->setCellValue( getColIndex( $col++ ).$row, $work_pos[ 'name' ] );
        }
      }

      $excelSheet->setCellValue( getColIndex( $col++ ).$row, preg_replace('/[^a-zA-Z0-9.]+/i', '', $form[ 'registration_comments' ] ));

      $row++;
    }

    $excelSheet->getStyle( 'A1:'.getColIndex( $col++ ).$row )->getAlignment()->setShrinkToFit( true );

  }
?>
