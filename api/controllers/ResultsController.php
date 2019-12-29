<?php
  class ResultsController extends BaseController {

    private function sortPoints( $one, $two ) {
      return $one[ 'points' ] < $two[ 'points' ];
    }

    private function sortSeries( $one, $two ) {
      return ( strtotime( $one[ 'event_date' ] ) > strtotime( $two[ 'event_date' ] ) ) ? -1 : 1;
    }

    private function sortSeriesAsc( $one, $two ) {
      return ( strtotime( $one[ 'event_date' ] ) < strtotime( $two[ 'event_date' ] ) ) ? -1 : 1;
    }

    private function listResults( $listType ) {

      switch ( $listType ) {
        case "available":
          $list = array();
          
          $q = new Query( "series" );
          $series = $q->addOrder( "date", "desc" )->select();
          foreach( $series as $s ) {
            if ( $s[ 'results' ] == 1 ) {
              $s[ 'type' ] = "series";
              $s[ 'label' ] = $s[ 'name' ]." Series";
              array_push( $list, $s );
            }
            
            $q = new Query( "series_events" );
            $events =
              $q->addWhere( 'series_id', $s[ 'id' ] )
                ->joinOn( 'event' )
                ->select();
            usort( $events, array( 'ResultsController', 'sortSeries' ) );
            foreach( $events as $e ) {
              if ( $e[ 'event_results' ] == 1 ) {
                $e[ 'id' ] = $e[ 'event_id' ];
                $e[ 'type' ] = 'event';
                $e[ 'label' ] = date( "F d, Y", strtotime( $e[ 'event_date' ] ) );
                array_push( $list, $e );
              }
            }
          }
          return $list;

        break;

        default:
          return array( 'error' => "Invalid route." );
        break;
      }
    }

    private function seriesResults( $series ) {

      $q = new Query( "series_events" );
      $events =
        $q->addWhere( 'series_id', $series[ 'id' ] )
          ->joinOn( 'event', array( 'date' ) )
          ->select();
      foreach( $events as $index => $event ) {
        $events[ $index ][ 'event_date_ts' ] =
          strtotime( $event[ 'event_date' ] );
      }
      usort( $events, array( 'ResultsController', 'sortSeriesAsc' ) );
 
      $seriesResults = array();
      foreach( $events as $index => $event ) {
        $q = new Query( "results" );
        $eventResults = 
          $q->addWhere( 'event_id', $event[ 'event_id' ] )
            ->addWhere( 'comp_category_id', 0, '!=' )
            ->addWhere( 'noshow', 0 )
            ->joinOn( 'comp_category' )
            ->joinOn( "scca_class", array( "name", "pax" ) )
            ->joinOn( 'event', array( 'date' ) )
            ->select();

        foreach( $eventResults as $result ) {

          $key = preg_replace( "/ /", "-", strtolower( $result[ 'name' ].'-'.$result[ 'category' ] ) );
          if ( $result[ 'comp_category_divide_by_class' ] == 1 ) {
            $key .= '-'.strtolower( $result[ 'class' ] );
          }

          if ( array_key_exists( $key, $seriesResults ) &&
               ( $seriesResults[ $key ][ 'category' ] == $result[ 'category' ] ) ) {

            if ( !in_array( $result[ 'car' ], $seriesResults[ $key ][ 'cars' ] ) ) {
              array_push( $seriesResults[ $key ][ 'cars' ], $result[ 'car' ] );
            }
            if ( !in_array( $result[ 'class' ], $seriesResults[ $key ][ 'class' ] ) ) {
              array_push( $seriesResults[ $key ][ 'class' ], $result[ 'class' ] );
            }
            if ( !in_array( $result[ 'scca_class_name' ], $seriesResults[ $key ][ 'class_names' ] ) ) {
              array_push( $seriesResults[ $key ][ 'class_names' ], $result[ 'scca_class_name' ] );
            }
            $seriesResults[ $key ][ 'divide' ] = $result[ 'comp_category_divide_by_class' ];
            $seriesResults[ $key ][ 'events' ][ date( "M d", strtotime( $result[ 'event_date' ] ) ) ] =
              $result[ 'points' ];
            $seriesResults[ $key ][ 'points' ] += floatval( $result[ 'points' ] );

          } else {
            $seriesResults[ $key ] = array(
              'cars' => array( $result[ 'car' ] ),
              'category' => $result[ 'category' ],
              'class' => array( $result[ 'class' ] ),
              'class_names' => array( $result[ 'scca_class_name' ] ),
              'comp_category_id' => $result[ 'comp_category_id' ],
              'divide' => $result[ 'comp_category_divide_by_class' ],
              'events' => array( 
                date( "M j", strtotime( $result[ 'event_date' ] ) ) => $result[ 'points' ]
              ),
              'name' => $result[ 'name' ],
              'points' => floatval( $result[ 'points' ] )
            );
          }
        }
       
      }

      foreach( $seriesResults as $key => $result ) {

        // if all events, drop lowest
        if ( ( sizeof( $events ) > 2 ) && 
             ( sizeof( $result[ 'events' ] )  == sizeof( $events ) ) ) {
          $seriesResults[ $key ][ 'points' ] -=
            min( array_values( $result[ 'events' ] ) );
        }
        //$seriesResults[ $key ][ 'cars' ] =
        //  implode( $seriesResults[ $key ][ 'cars' ], ',' );
        $seriesResults[ $key ][ 'class' ] =
          implode( $seriesResults[ $key ][ 'class' ], '/' );
        $seriesResults[ $key ][ 'qualified' ] =
          ( sizeof( $result[ 'events' ] ) >= 3 );
      }
      
      $categories = array();
      foreach( $seriesResults as $r ) {

        if ( array_key_exists( $r[ 'category' ], $categories ) ) {

          if ( $r[ 'divide' ] == 1 ) {

            if ( !array_key_exists( $r[ 'class' ], $categories[ $r[ 'category' ] ] ) ) {
              $categories[ $r[ 'category' ] ][ $r[ 'class' ] ] = array(
                'qualified' => ( $r[ 'qualified' ] ? 1 : 0 ),
                'results' => array()
              );
            }

            array_push( $categories[ $r[ 'category' ] ][ $r[ 'class' ] ][ 'results' ], $r  );
            if ( $r[ 'qualified' ] ) {
              $categories[ $r[ 'category' ] ][ $r[ 'class' ] ][ 'qualified' ]++;
            }

          } else {

            array_push( $categories[ $r[ 'category' ] ][ 'results' ], $r  );
            if ( $r[ 'qualified' ] ) {
              $categories[ $r[ 'category' ] ][ 'qualified' ]++;
            }

          }

        } else {

          if ( $r[ 'divide' ] == 1 ) {

            $categories[ $r[ 'category' ] ] = array();
            $categories[ $r[ 'category' ] ][ 'divided' ] = true;
            $categories[ $r[ 'category' ] ][ $r[ 'class' ] ] = array(
              'qualified' => ( $r[ 'qualified' ] ? 1 : 0 ),
              'results' => array()
            );
            array_push( $categories[ $r[ 'category' ] ][ $r[ 'class' ] ][ 'results' ], $r  );

          } else {

            $categories[ $r[ 'category' ] ] = array(
              'qualified' => ( $r[ 'qualified' ] ? 1 : 0 ),
              'results' => array()
            );
            array_push( $categories[ $r[ 'category' ] ][ 'results' ], $r  );

          }
        }
      }
      unset( $seriesResults );

      foreach( array_keys( $categories ) as $key ) {
        if ( array_key_exists( 'results', $categories[ $key ] ) ) {
          usort( $categories[ $key ][ 'results' ], array( 'ResultsController', 'sortPoints' ) );
        } else {
          foreach( array_keys( $categories[ $key ] ) as $sccaClass ) {
            if ( is_array( $categories[ $key ][ $sccaClass ][ 'results' ] ) ) {
              usort( $categories[ $key ][ $sccaClass ][ 'results' ], array( 'ResultsController', 'sortPoints' ) );
            }
          }
          ksort( $categories[ $key ] );
        }
      }

      $data[ 'categories' ] = $categories;
      $data[ 'series_events' ] = $events;

      return $data;
    }

    public function getAction( $request ) {
    
      if ( empty( $request->url_elements ) ) {
        return array( 'error' => "Invalid route." );
      } else {
      
        switch ( array_shift( $request->url_elements ) ) {
        
          case "entrants":
            if ( empty( $request->url_elements ) ) {
              return array( 'error' => "Invalid route." );
            } else {
              $id = intval( array_shift( $request->url_elements ) );
              $q = new Query( "results" );
              $q->addWhere( 'event_id', $id )
                ->addOrder( "name_last" )
                ->addOrder( "name_first" );

              if ( array_key_exists( "name", $request->parameters ) ) {
                return
                  $q->addWhere( 'name', $request->parameters[ 'name' ] )
                    ->addOrder( 'run_group' )
                    ->select();
              } else {
                $items = $q->distinct( "name" );
                $names = array();
                foreach( $items as $item ) {
                  array_push( $names, $item[ 'name' ] );
                }
                return $names;
              }
            }
            break;                 

          case "event":
            if ( empty( $request->url_elements ) ) {
              return array( 'error' => "Invalid route." );
            } else {
              $id = intval( array_shift( $request->url_elements ) );
              if ( array_key_exists( 'order_by', $request->parameters ) &&
                   Database::hasColumn( 'results', $request->parameters[ 'order_by' ] ) ) {
                $order = $request->parameters[ 'order_by' ];
              } else {
                $order = "pax_time";
              }
              $q = new Query( "results" );
              $q->addWhere( 'event_id', $id )
                ->addWhere( 'noshow', 0 )
                ->addWhere( 'all_times', "[]", "!=" )
                ->joinOn( "scca_class", array( "name", "pax" ) )
                ->addOrder( $order, 'asc', 'decimal(6,3)' );
              return $q->select();
            }
            break;

          case "list":
            if ( !array_key_exists( 'organization_id', $request->parameters ) ) {
              return array( 'error' => "Invalid route." );
            } else {
              return $this->listResults(
                array_shift( $request->url_elements ),
                $request->parameters[ 'organization_id' ]
              );
            }
            break;

          case "series":
            if ( empty( $request->url_elements ) ) {
              return array( 'error' => "Invalid route." );
            } else {
              $id = intval( array_shift( $request->url_elements ) );
              $q = new Query( "series" );
              $series = $q->selectById( $id );
              return $this->seriesResults( $series );
            }
          break;
      
          default:
            return array( 'error' => "Invalid route." );
          break;
        }
      
      }
    }
  
    public function postAction( $request ) {

      if ( empty( $request->url_elements ) ) {
        return array( 'error' => "Invalid route." );
      } else {
        switch ( array_shift( $request->url_elements ) ) {

          case "import":
            if ( empty( $request->url_elements ) ||
                 !is_numeric( $request->url_elements[ 0 ] ) ) {
              return array( 'error' => "Invalid event ID specified." );
            } else {
              $id = intval( array_shift( $request->url_elements ) );
              $q = new Query( "events" );
              $event = $q->selectById( $id );

            $r = new Query( "organizations" );
              $org = $r->selectById( $event[ 'organization_id' ] );

              $privs = json_decode( $org[ 'privileges' ], true );
              $event[ 'runs_per_heat' ] = $privs[ 'default_runs' ];
              $q->updateById( $event );

              $q = new Query( "results" );
              $q->addWhere( "event_id", $event[ 'id' ] );

              if ( $q->count() == 0 ) {
              
                $q = new Query( "entry_forms" );
                $q->addWhere( "event_id", $event[ 'id' ] )
                  ->joinOn( "comp_category" )
                  ->joinOn( "scca_class" );
                $forms = $q->select();
                $return = array(
                  'entry_forms' => sizeof( $forms ),
                  'errors' => 0,
                  'result_rows' => 0,
                );
                  
                foreach( $forms as $f ) {

                  $q = new Query( "results" );
                  $scca_member = ( !empty( $f[ 'scca_number' ] ) && ( strtotime( $f[ 'scca_date' ] ) > 0 ) ) ? 1 : 0;
                  $result = array(
                    'event_id' => $f[ 'event_id' ],
                    'registration_id' => $f[ 'registration_id' ],
                    'name_first' => $f[ 'name_first' ],
                    'name_last' => $f[ 'name_last' ],
                    'name' => $f[ 'name_first' ].' '.$f[ 'name_last' ],
                    'car' => $f[ 'year' ].' '.$f[ 'make' ].'/'.$f[ 'model' ],
                    'scca_class_id' => $f[ 'scca_class_id' ],
                    'class' => $f[ 'scca_class_initials' ],
                    'comp_category_id' => $f[ 'comp_category_id' ],
                    'category' => $f[ 'comp_category_name' ],
                    'registered_online' => 1,
                    'scca_member' => $scca_member,
                    'noshow' => 0,
                    
                    'run_group' => $f[ 'run_group' ],
                    'position' => $f[ 'position' ],
                    
                    'all_times' => "[]",
                  );

                  if ( $q->insertNew( $result ) == 0 ) {
                    $return[ 'errors' ]++;
                  } else {
                    $return[ 'result_rows' ]++;
                  }
                }
                
                return $return;
              
              } else {
                return array( 'error' => "Registration data already imported." );
              }
              
            }
                 
          break;

          case "update":
            if ( empty( $request->url_elements ) ||
                 !is_numeric( $request->url_elements[ 0 ] ) ) {
              return array( 'error' => "Invalid event ID specified." );
            } else {
              $id = intval( array_shift( $request->url_elements ) );
              $q = new Query( "events" );
              $event = 
                $q->joinOn( "organization" )
                  ->selectById( $id );


              $privs = json_decode( $event[ 'organization_privileges' ], true );

              $updated = 0;
              $results = $request->parameters;

              $return = array();

              foreach( $results as $result ) {
                $id = $result[ 'id' ];
                $result[ 'car' ] = $result[ 'year' ].' '.$result[ 'car' ];
                unset( $result[ 'year' ] );

                $q = new Query( "comp_categories" );
                $comp_category =
                  $q->only( "name" )
                    ->selectByid( $result[ 'comp_category_id' ] );
                $result[ 'category' ] = $comp_category[ 'name' ];

                if ( $event[ 'event_type_id' ] == 2 ) { //rallycross
                  $q = new Query( "scca_rally_xclasses" );
                } else {
                  $q = new Query( "scca_classes" );
                }
                $scca_class = 
                  $q->selectById( $result[ 'scca_class_id' ] );
                $result[ 'class' ] = $scca_class[ 'initials' ];

                $result[ 'name' ] = $result[ 'name_first' ].' '.$result[ 'name_last' ];

                $times = array();
                foreach( $result[ 'all_times' ] as $time ) {
                  if ( is_numeric( $time[ 'raw' ] ) ) {
                    array_push( $times, floatval( $time[ 'raw' ] ) + intval( $time[ 'penalty' ] ) );
                  }
                }
                if ( empty( $times ) ) {
                  $result[ 'fast_time' ] = 999.999;
                } else {  
                  $result[ 'fast_time' ] = min( $times );
                }

                if ( $privs[ 'results_method' ] != "total_time" ) {
                  $result[ 'pax_time' ] = $result[ 'fast_time' ] * $scca_class[ 'pax' ];
                }

                $result[ 'all_times' ] = json_encode( $result[ 'all_times' ] );

                $q = new Query( "results" );
                if ( preg_match( "/^new/", $id ) ) {
                  $result[ 'event_id' ] = $event[ 'id' ];
                  $result[ 'inserted' ] = $q->insertNew( $result );
                  $result[ 'div' ] = $id;
                } else {
                  $result[ 'id' ] = $id;
                  $result[ 'updated' ] = $q->updateById( $result );
                }
                array_push( $return, $result );
              }

              // raw time rankings
              $q = new Query( "results" );
              $results =
                $q->addWhere( 'event_id', $event[ 'id' ] )
                  ->addWhere( 'fast_time', 0, "!=" )
                  ->addWhere( 'noshow', 0 )
                  ->addWhere( 'comp_category_id', 0, "!=" )
                  ->addOrder( 'fast_time', 'asc', 'decimal(6,3)' )
                  ->select();

              $rank = 1;
              foreach( $results as $result ) {
                $array = array(
                  'id' => $result[ 'id' ],
                  'time_rank' => $rank++
                );
                $q = new Query( "results" );
                $q->updateById( $array );
              }

              // pax time rankings
              $q = new Query( "results" );
              $results =
                $q->addWhere( 'event_id', $event[ 'id' ] )
                  ->addWhere( 'noshow', 0 )
                  ->addWhere( 'pax_time', 0, "!=" )
                  ->addWhere( 'comp_category_id', 0, "!=" )
                  ->addOrder( 'pax_time' )
                  ->select();

              $fast_pax = $results[ 0 ][ 'pax_time' ];
              $rank = 1;
              foreach( $results as $result ) {
                $array = array(
                  'id' => $result[ 'id' ],
                  'pax_rank' => $rank++
                );
                $q = new Query( "results" );
                $q->updateById( $array );
              }

              $q = new Query( "results" );
              $results =
                $q->addWhere( 'event_id', $event[ 'id' ] )
                  ->addOrder( 'pax_time' )
                  ->addWhere( 'noshow', 0 )
                  ->addWhere( 'pax_time', 0, "!=" )
                  ->select();

              $all_categories = array();
              $q = new Query( "comp_categories" );
              $items = $q->select();
              foreach( $items as $item ) {
                $all_categories[ $item[ 'id' ] ] = $item;
              } 

              $categories = array();
              $rank = 1;
              foreach( $results as $result ) {

                $category = $all_categories[ $result[ 'comp_category_id' ] ];
                if ( $category[ 'divide_by_class' ] == 1 ) {
                  $key = $category[ 'name' ]." ".$result[ 'class' ];
                } else {
                  $key = $category[ 'name' ];
                }

                if ( empty( $categories[ $key ] ) ) {
                  $categories[ $key ][ 'rank' ] = 1;
                  $categories[ $key ][ 'fastest' ] = $result[ 'pax_time' ]; 
                }

                $array = array(
                  'id' => $result[ 'id' ],
                  'class_rank' => $categories[ $key ][ 'rank' ]++,
                );

                if ( $result[ 'pax_time' ] > 0) {
                  $array[ 'points' ] = ( $fast_pax / $result[ 'pax_time' ] ) * 1000;
                } else {
                  $array[ 'points' ] = 0;
                }

                if ( $privs[ 'results_method' ] == "pax_in_category" ) {
                  if ($result[ 'pax_time' ] > 0) {
                    $array[ 'custom_result' ] = ( $categories[ $key ][ 'fastest' ] / floatval( $result[ 'pax_time' ] ) ) * 1000;
                  } else {
                    $array[ 'custom_result' ] = 0;
                  }
                }

                if ( $result[ 'comp_category_id' ] != 0 ) {
                  $array[ 'pax_rank' ] = $rank++;
                }
                $q = new Query( "results" );
                $q->updateById( $array );
              }

              return $return;
            }

          break;

          default:
            return array( 'error' => "Invalid route." );
          break;
        }
      }
    }
  
  }
?>
