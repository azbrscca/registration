<?php
  require_once "../../../db/Query.php";
  
  function assignWorkers( $event ) {

    $admin = json_decode( $event[ 'admin' ], true );
    $config = json_decode( $event[ 'configuration' ], true );
    $workers = ( empty( $admin[ 'work_positions' ] ) ? $event[ 'organization_id' ] : $admin[ 'work_positions' ] );

    $q = new Query( "work_positions" );
    $workPositions = $q->addWhere( 'organization_id', $workers )
                       ->addOrder( "name" )
                       ->select();
    
    $defaultWp = array();
    if ( $event[ 'default_work_pos' ] != 0 ) {
      $q->addWhere( 'id', $event[ 'default_work_pos' ] );
    } else {
      $q->addWhere( 'name', "Course" );
    }
    $defaultWp = $q->selectOne();

    for( $g = 1; $g <= $event[ 'run_groups' ]; $g++ ) {

      $q = new Query( "entry_forms" );
      $entryForms = $q->addWhere( 'event_id', $event[ 'id' ] )
                      ->addWhere( 'run_group', $g )
                      ->joinOn( 'registration' )
                      ->addOrder( 'priority' )
                      ->select();

      $groupWp = array();
      foreach( $workPositions as $wp ) {
        $wp[ 'filled' ] = 0;
        $groupWp[ $wp[ 'id' ] ] = $wp;
      }
            
      foreach( $entryForms as $entryForm ) {

        $assigned = false;

        for( $opt = 1; $opt <= 3 && !$assigned; $opt++ ) {
          $current = $entryForm[ 'registration_work_pos_'.$opt ];

          if ( $current != 0 ) {
      
            $filled = intval( $groupWp[ $current ][ 'filled' ] );
            $maximum = intval( $groupWp[ $current ][ 'maximum' ] );
        
            $assigned = ( ( $maximum == 0 ) || ( $filled < $maximum ) );
        
            if ( $assigned ) {
              $groupWp[ $current ][ 'filled' ]++;

              $values = array(
                'id' => $entryForm[ 'id' ],
                'work_group' => $config[ 'work_order' ][ $entryForm[ 'run_group' ] ], 
                'work_position_id' => $current,
              );
              $q->updateById( $values );
            }
          }
        }
        
        if ( !$assigned && !empty( $defaultWp ) ) {
          $groupWp[ $defaultWp[ 'id' ] ][ 'filled' ]++;

          $values = array(
            'id' => $entryForm[ 'id' ],
            'work_group' => $config[ 'work_order' ][ $entryForm[ 'run_group' ] ], 
            'work_position_id' => $defaultWp[ 'id' ],
          );
          $q->updateById( $values );
        }

      }
    }
  }
  
  function buildGroups( $event ) {

    $q = new Query( "registrations" );
    $registrations = $q->addWhere( 'event_id', $event[ 'id' ] )
               ->joinOn( 'entrant' )
               ->joinOn( 'car' )
               ->addOrder( 'date_created' )
               ->select();
  
    $q = new Query( "entry_forms" );
    $q->addWhere( 'event_id', $event[ 'id' ] );

    if ( $q->count() > 0 ) {
      $q->delete();
    }
    createForms( $registrations );
               
    $deferredForms = array();
    $entryForms = $q->addWhere( 'comp_category_id', 0, "!=" )
                    ->joinOn( 'registration' )
                    ->addOrder( 'date_created' )->select();
  
    $groupMax = $event[ 'run_group_size' ];
    $runGroups = array();
    for( $i=1; $i <= $event[ 'run_groups' ]; $i++ ) {
      $runGroups[ $i ] = array();
    }

    $config = json_decode( $event[ 'configuration' ], true );
    $categories = array();
    if ( array_key_exists( 'categories', $config ) ) {
      foreach( $config[ 'categories' ] as $c ) {
        if ( array_key_exists( 'classes', $c ) ) {
          $categories[ $c[ 'id' ] ] = $c[ 'classes' ];
        } else if ( array_key_exists( 'run_group', $c ) ) {
          $categories[ $c[ 'id' ] ] = $c[ 'run_group' ];
        }
      }
    }

    foreach( $entryForms as $entryForm ) {
      
      $compPosition = array(
        'run_group' => 0,
        'position' => 0,
      );
      $toPosition = array(
        'run_group' => 0,
        'position' => 0,
      );
        
      $timeOnly = ( $entryForm[ 'registration_time_only_reg' ] == 1 );
        
      if ( array_key_exists( $entryForm[ 'comp_category_id' ], $categories ) ) {

        $id = $entryForm[ 'comp_category_id' ];
        
        if ( is_array( $categories[ $id ] ) ) {
          
          foreach( $categories[ $id ] as $sccaClass ) {
            if ( $entryForm[ 'scca_class_id' ] == $sccaClass[ 'id' ] ) {
              $compPosition[ 'run_group' ] = $sccaClass[ 'run_group' ];
              $compPosition[ 'position' ] = 1;
              while( array_key_exists( $compPosition[ 'position' ],
                                       $runGroups[ $compPosition[ 'run_group' ] ] ) ) {
                $compPosition[ 'position' ]++;
              }
              $runGroups[ $compPosition[ 'run_group' ] ] [ $compPosition[ 'position' ] ] = true;

              break;
            }
          }

        } else if ( is_numeric( $categories[ $id ] ) ) {

          $compPosition[ 'run_group' ] = intval( $categories[ $id ] );
          $compPosition[ 'position' ] = 1;
          while( array_key_exists( $compPosition[ 'position' ],
                   $runGroups[ $compPosition[ 'run_group' ] ] ) ) {
                  $compPosition[ 'position' ]++;
          }
          $runGroups[ $compPosition[ 'run_group' ] ] [ $compPosition[ 'position' ] ] = true;
        }

        if ( $timeOnly ) {
      
          $toGroup = 
            minimum_size_to_group(
              $runGroups,
              $config[ 'to_groups' ],
              $compPosition[ 'run_group' ],
              $config[ 'work_order' ][ $compPosition[ 'run_group' ] ]
          );

          $toPosition[ 'run_group' ] = $toGroup;
          $runGroups[ $toGroup ]++;

          if ( $toPosition[ 'run_group' ] == ( $compPosition[ 'run_group' ] + 1 ) ) {

            if ( array_key_exists( $compPosition[ 'position' ],
                   $runGroups[ $toPosition[ 'run_group' ] ] ) ) {
              $compPosition[ 'position' ]++;
              while( array_key_exists( $compPosition[ 'position' ],
                       $runGroups[ $toPosition[ 'run_group' ] ] ) ) {
                      $compPosition[ 'position' ]++;
              }
            }
            $toPosition[ 'position' ] = $compPosition[ 'position' ];

          } else {
            $toPosition[ 'position' ] = 1;
            while( array_key_exists( $toPosition[ 'position' ],
                     $runGroups[ $toPosition[ 'run_group' ] ] ) ) {
              $toPosition[ 'position' ]++;
            }
          }
        }
      
      } else {
    
        $confirm = false;
        for( $desired = 1; $desired < 4 && $compPosition[ 'run_group' ] == 0; $desired++ ) {

          $current = $entryForm[ 'registration_run_group_'.$desired ];

          if ( $current == 0 ) {
            $desired = 4;

          } else if ( ( $current == $event[ 'run_groups' ] ) && $timeOnly ) {
//            array_push( $logs, $entrant_log.
//                 ": requested time only heat, cannot run competition heat in last group (".chr( $current + 64 ).").<br/>" ;

          } else if ( !in_array( $current, $config[ 'comp_groups' ] ) ) {
//            array_push( $logs, $entrant_log.
//                 ": perferred run group, ".chr( $current + 64 )." is not a competition heat.<br/>" );

          } else if ( sizeof( $runGroups[ $current ] ) >= $groupMax ) {
//              array_push( $logs, $entrant_log.
//                 ": perferred run group, ".chr( $current + 64 ).", is full.<br/>" );

          } else {
            
            // if the entrant is running TOs
            // first make sure their TO assignment won't upset the balance of the run groups
            $confirm = true;

            if ( $timeOnly ) {

              $confirm = false;
              $toPosition[ 'run_group' ] =
                minimum_size_to_group( $runGroups, $config[ 'to_groups' ], $current, $config[ 'work_order' ][ $current ] );

              if ( sizeof( $runGroups[ $toPosition[ 'run_group' ] ] ) < $groupMax ) {
                $confirm = true;
              } else {
//              array_push( $logs, $entryForm[ 'name_first' ]." ".$entryForm[ 'name_last' ].
//                       " (".$priority."/".sizeof( $registrations ).") ".
//                       ": time only heat is full.<br/>" );
              }

            }

            if ( $confirm ) {

              $compPosition[ 'run_group' ] = $current;
              $compPosition[ 'position' ] = 1;
              while( array_key_exists( $compPosition[ 'position' ],
                     $runGroups[ $compPosition[ 'run_group' ] ] ) ) {
                $compPosition[ 'position' ]++;
              }
              
              if ( $timeOnly ) {
              
                if ( $toPosition[ 'run_group' ] == ( $compPosition[ 'run_group' ] + 1 ) ) {
    
                  if ( array_key_exists( $compPosition[ 'position' ],
                        $runGroups[ $toPosition[ 'run_group' ] ] ) ) {
                    $compPosition[ 'position' ]++;
                    while( array_key_exists( $compPosition[ 'position' ],
                             $runGroups[ $toPosition[ 'run_group' ] ] ) ) {
                      $compPosition[ 'position' ]++;
                    }
                  }
                  $toPosition[ 'position' ] = $compPosition[ 'position' ];
    
                } else {
                  $toPosition[ 'position' ] = 1;
                  while( array_key_exists( $toPosition[ 'position' ],
                           $runGroups[ $toPosition[ 'run_group' ] ] ) ) {
                    $toPosition[ 'position' ]++;
                  }
                }

                $runGroups[ $compPosition[ 'run_group' ] ] [ $compPosition[ 'position' ] ] = true;
                $runGroups[ $toPosition[ 'run_group' ] ] [ $toPosition[ 'position' ] ] = true;

              } else {
                $runGroups[ $compPosition[ 'run_group' ] ] [ $compPosition[ 'position' ] ] = true;
              }        


            }
          }
        }
          
        if ( !$confirm ) {
          array_push( $deferredForms, $entryForm );
        }
    
      }

      // update

      if ( $compPosition[ 'run_group' ] != 0 ) {
        $compPosition[ 'work_group' ] =
          $config[ 'work_order' ][ $compPosition[ 'run_group' ] ];
      }

      $q = new Query( "entry_forms" );
      $compPosition[ 'id' ] = $entryForm[ 'id' ];
      $q->updateById( $compPosition );
    
      if ( $timeOnly ) {
        $toPosition[ 'id' ] = ( intval( $entryForm[ 'id' ] ) + 1 );
        if ( $toPosition[ 'run_group' ] != 0 ) {
          $toPosition[ 'work_group' ] =
            $config[ 'work_order' ][ $toPosition[ 'run_group' ] ];
        }
        $q->updateById( $toPosition );
      }
      
      }  // foreach( $entryForms as $entryForm )

      foreach( $deferredForms as $entryForm ) {

        $compPosition = array(
          'run_group' => 0,
          'position' => 0,
        );
        $toPosition = array(
          'run_group' => 0,
          'position' => 0,
        );
        
        $timeOnly = ( $entryForm[ 'registration_time_only_reg' ] == 1 );

        $compPosition[ 'run_group' ] = minimum_size_comp_group( $runGroups, $config[ 'comp_groups' ], $timeOnly );
        $compPosition[ 'position' ] = 1;
        while( array_key_exists( $compPosition[ 'position' ],
                 $runGroups[ $compPosition[ 'run_group' ] ] ) ) {
          $compPosition[ 'position' ]++;
        }

        if ( $timeOnly ) {

          $toPosition[ 'run_group' ] = minimum_size_to_group( $runGroups, $config[ 'to_groups' ], $compPosition[ 'run_group' ], $config[ 'work_order' ][ $compPosition[ 'run_group' ] ] );

          if ( $toPosition[ 'run_group' ] == ( $compPosition[ 'run_group' ] + 1 ) ) {

            if ( array_key_exists( $compPosition[ 'position' ],
                   $runGroups[ $toPosition[ 'run_group' ] ] ) ) {
              $compPosition[ 'position' ]++;
              while( array_key_exists( $compPosition[ 'position' ],
                       $runGroups[ $toPosition[ 'run_group' ] ] ) ) {
                $compPosition[ 'position' ]++;
              }
            }
            $toPosition[ 'position' ] = $compPosition[ 'position' ];

          } else {

          $toPosition[ 'position' ] = 1;
          while( array_key_exists( $toPosition[ 'position' ],
                   $runGroups[ $toPosition[ 'run_group' ] ] ) ) {
            $toPosition[ 'position' ]++;
          }
          $runGroups[ $toPosition[ 'run_group' ] ] [ $toPosition[ 'position' ] ] = true;

        }

        $runGroups[ $compPosition[ 'run_group' ] ] [ $compPosition[ 'position' ] ] = true;
        $runGroups[ $toPosition[ 'run_group' ] ] [ $toPosition[ 'position' ] ] = true;

      } else {
        $runGroups[ $compPosition[ 'run_group' ] ] [ $compPosition[ 'position' ] ] = true;
      }

      // update
      $q = new Query( "entry_forms" );
      $compPosition[ 'id' ] = $entryForm[ 'id' ];
      $compPosition[ 'work_group' ] =
        $config[ 'work_order' ][ $compPosition[ 'run_group' ] ];
      $q->updateById( $compPosition );
    
      if ( $timeOnly ) {
        $toPosition[ 'id' ] = ( intval( $entryForm[ 'id' ] ) + 1 );
        $toPosition[ 'work_group' ] =
          $config[ 'work_order' ][ $toPosition[ 'run_group' ] ];
        $q->updateById( $toPosition );
      }

    } // foreach( $deferredForms as $entryForm )
  }
  
  function createForms( $registrations ) {
  
    $priority = 0;
    $q = new Query( "entry_forms" );
  
    foreach( $registrations as $registration ) {
      $priority++;

      if ( $registration[ 'entrant_scca_status' ] == 0 ) {
        $registration[ 'entrant_scca_number' ] = "";
        $registration[ 'entrant_scca_date' ] = "0000-00-00";
      }

      $entryForm = array(
        'entrant_id' => $registration[ 'entrant_id' ],
        'event_id' => $registration[ 'event_id' ],
        'registration_id' => $registration[ 'id' ],
        'priority' => $priority,
        'name_first' => ucfirst( strtolower( $registration[ 'entrant_name_first' ] ) ),
        'name_last' => ucfirst( strtolower( $registration[ 'entrant_name_last' ] ) ),
        'address_street' => $registration[ 'entrant_address_street' ],
        'address_city' => $registration[ 'entrant_address_city' ],
        'address_state' => $registration[ 'entrant_address_state' ],
        'address_zip' => $registration[ 'entrant_address_zip' ],
        'phone_home' => $registration[ 'entrant_phone_home' ],
        'phone_work' => $registration[ 'entrant_phone_work' ],
        'email' => $registration[ 'entrant_email' ],
        'club' => $registration[ 'entrant_club' ],
        'scca_number' => $registration[ 'entrant_scca_number' ],
        'scca_date' => $registration[ 'entrant_scca_date' ],
        'emer_name' => $registration[ 'entrant_emer_name' ],
        'emer_phone' => $registration[ 'entrant_emer_phone' ],
  
        'year' => $registration[ 'car_year' ],
        'make' => $registration[ 'car_make' ],
        'model' => $registration[ 'car_model' ],
        'color' => $registration[ 'car_color' ],
        'engine' => $registration[ 'car_engine' ],
        'tire_brand' => $registration[ 'car_tire_brand' ],
        'tire_type' => $registration[ 'car_tire_type' ],
        'tire_size' => $registration[ 'car_tire_size' ],
        'modifications' => $registration[ 'car_modifications' ],
        'scca_class_id' => $registration[ 'car_scca_class_id' ],

        'car_number' => $registration[ 'car_number' ],
  
        'codriver' => $registration[ 'codriver' ],
        'comp_category_id' => $registration[ 'comp_category_id' ],
      );
  
      $entryForm[ 'id' ] = $q->insertNew( $entryForm );
  
      if ( intval( $registration[ 'time_only_reg' ] ) == 1 ) {
  
        $entryForm[ 'comp_category_id' ] = 0;
    
        if ( $registration[ 'car_id' ] != $registration[ 'to_car_id' ] ) {
    
          $r = new Query( "cars" );
          $car = $r->selectById( $registration[ 'to_car_id' ] );
          if( !empty( $car ) ) {
            $to_form = array(
              'year' => $car[ 'year' ],
              'make' => $car[ 'make' ],
              'model' => $car[ 'model' ],
              'color' => $car[ 'color' ],
              'engine' => $car[ 'engine' ],
              'tire_brand' => $car[ 'tire_brand' ],
              'tire_type' => $car[ 'tire_type' ],
              'tire_size' => $car[ 'tire_size' ],
              'modifcations' => $car[ 'modifications' ],
              'scca_class_id' => $car[ 'scca_class_id' ],
            );
          }
        }

        $entryForm[ 'id' ] = $q->insertNew( $entryForm );

      }
    } // foreach( $registrations as $registration )
  }

  function find_open_position( $event, $run_group, $min = 1 ) {

    $position = $min;
    $where = "event_id = ".$event[ 'id' ].
            " and run_group = ".$run_group;

    $filled_positions = array();
    $entryForms = select( "entry_forms", $where, "position" );
    foreach( $entryForms as $entryForm  ) {
      array_push( $filled_positions, $entryForm[ 'position' ] );
    }
    while( in_array( $position, $filled_positions ) ) {
      $position++;
    }

    return $position;
  }

  function minimum_size_comp_group( $runGroups, $comp_groups, $running_tos ) {

    $array_keys = array_keys( $runGroups );
    $num_groups = sizeof( $runGroups );

    foreach( $array_keys as $key ) {

      // event setup must have allowed for the group to have competition runs
      if ( !in_array( $key, $comp_groups ) ) {
        unset( $runGroups[ $key ] );

      // entrant cannot run compeition heat in last group if also running time only heat
      } else if ( $running_tos && ( $key == $num_groups ) ) {
        unset( $runGroups[ $key ] );

      }
    }

    $array_keys = array_keys( $runGroups );
    $min_key = $array_keys[ 0 ];
    $min = $runGroups[ $min_key ];

    // if there is only one choice left, no need to find the smallest group
    if ( sizeof( $array_keys ) == 1 ) {
      return $min_key;
    }

    // if there are multiple options left, place in the smallest size group for balance
    foreach( array_keys( $runGroups ) as $key ) {
      if ( $runGroups[ $key ] < $min ) {
        $min_key = $key;
        $min = $runGroups[ $key ];
      }
    }

    return $min_key;
  }

  function minimum_size_to_group( $runGroups, $to_groups, $run_group, $comp_work_group ) {

    $array_keys = array_keys( $runGroups );

    foreach( $array_keys as $key ) {

      // event setup must have allowed for the group to have time only runs
      if ( !in_array( $key, $to_groups ) ) {
        unset( $runGroups[ $key ] );
//echo " 1 (".chr( $key + 64 ).") <br/>";
      // entrant cannot run time only heat before competition heat
      } else if ( $key <= $run_group ) {
        unset( $runGroups[ $key ] );
//echo " 2 (".chr( $key + 64 ).") <br/>";
      // entrant cannot run time only heat during work assignment for competition heat
      } else if ( $key == $comp_work_group ) {
        unset( $runGroups[ $key ] );
//echo " 3 (".chr( $key + 64 ).") <br/>";
      }
    }

    $array_keys = array_keys( $runGroups );
    $min_key = $array_keys[ 0 ];
    $min = $runGroups[ $min_key ];

    // if there is only one choice left, no need to find the smallest group
    if ( sizeof( $array_keys ) == 1 ) {
      return $min_key;
    }

    // if there are multiple options left, place in the smallest size group for balance
    foreach( array_keys( $runGroups ) as $key ) {
      if ( $runGroups[ $key ] < $min ) {
        $min_key = $key;
        $min = $runGroups[ $key ];
      }
    }

    return $min_key;
  }
?>
