<?php
  class MetricsController extends BaseController {
  
    public function discountAmount( &$item, $index ) {
      switch( $item[ 'type' ] ) {
        case 2:
          $item[ 'amount' ] = $item[ 'entryFee' ] - $item[ 'amount' ];
        break;
      }
          
    }

    private static function sortDiscounts( $one, $two ) {
      if ( $one[ 'entrant_name_last' ] == $two[ 'entrant_name_last' ] ) {

        if ( $one[ 'entrant_name_first' ] == $two[ 'entrant_name_first' ] ) {
          return strcmp( strtolower( $one[ 'entry' ] ), strtolower( $two[ 'entry' ] ) );
        } else {
          return strcmp(
            strtolower( $one[ 'entrant_name_first' ] ),
            strtolower( $two[ 'entrant_name_first' ] ) );
        }
      } else {
        return strcmp(
          strtolower( $one[ 'entrant_name_last' ] ),
          strtolower( $two[ 'entrant_name_last' ] ) );
      }
    }

    public function getAction( $request ) {

      if ( empty( $request->url_elements ) ) {
        return array( 'error' => "Invalid route." );
      } else {

        switch ( array_shift( $request->url_elements ) ) {
          case "event":

            if ( empty( $request->url_elements ) ) {
              return array( 'error' => "Invalid route." );
            } else {          
              $id = intval( array_shift( $request->url_elements ) );
              return $this->metrics( $id );
            }

          break;

          default:
            return array( 'error' => "Invalid route." );
          break;
        }
      }
    }

    public function metrics( $id ) {

			$q = new Query( 'events' );
			$event = $q->timestamps( true )->selectById( $id );
			if ( empty( $event ) ) {
			  return array();
			}

			// grab discounts
			$q = new Query( 'discounts' );
			$d =
				$q->addWhere( 'organization_id', $event[ 'organization_id' ] )
				  ->joinOn( 'entrant', array( "name_first", "name_last" ) )
				  ->timestamps( true )
				  ->select();

			$discounts = array();
			foreach( $d as $e ) {
			  if ( ( $e[ 'begins_on_ts' ] < $event[ 'date_ts' ] ) &&
			       ( ( $e[ 'ends_on' ] == "0000-00-00" ) || ( $e[ 'ends_on_ts' ] > $event[ 'date_ts' ] ) ) ) {
			    $key = $e[ 'entrant_name_first' ].' '.$e[ 'entrant_name_last' ];
			    $discounts[ $key ] = $e;
			  }
			}

			$totals = array( 'Competition' => 0, 'Time Only' => 0 );
			
			$entryFees =
				array(
					'SCCA Member' => array(
					  'Registered Online' => array(
							 'Competition' => 25,
							 'Time Only' => 10,
						),
					  'Registered At Event' => array(
							 'Competition' => 30, 
							 'Time Only' => 10,
						)
					),
					'Non SCCA Member' => array(
					  'Registered Online' => array(
							 'Competition' => 35,
							 'Time Only' => 10,
						),
					  'Registered At Event' => array(
							 'Competition' => 40,
							 'Time Only' => 10,
						)
					),
					'Paypal' => array( 'Competition' => 2 )
				);			

      $eventDiscounts = array();
				
			$paymentTypes = array(
				'Cash or Check' => array(),
				'Paypal' => array(
				  'Competition' => 2
				)
			);
			
			$data =
				array(
					'SCCA Member' => array(
					  'Registered Online' => array(
							 'Competition' => 
								 array( 'noshow' => 0, 'Cash or Check' => 0, 'Paypal' => 0 ),
							 'Time Only' => 
								 array( 'noshow' => 0, 'Cash or Check' => 0, 'Paypal' => 0 ),
						),
					  'Registered At Event' => array(
							 'Competition' => 
								 array( 'noshow' => 0, 'Cash or Check' => 0, 'Paypal' => 0 ),
							 'Time Only' => 
								 array( 'noshow' => 0, 'Cash or Check' => 0, 'Paypal' => 0 ),
						)
					),
					'Non SCCA Member' => array(
					  'Registered Online' => array(
							 'Competition' => 
								 array( 'noshow' => 0, 'Cash or Check' => 0, 'Paypal' => 0 ),
							 'Time Only' => 
								 array( 'noshow' => 0, 'Cash or Check' => 0, 'Paypal' => 0 ),
						),
					  'Registered At Event' => array(
							 'Competition' => 
								 array( 'noshow' => 0, 'Cash or Check' => 0, 'Paypal' => 0 ),
							 'Time Only' => 
								 array( 'noshow' => 0, 'Cash or Check' => 0, 'Paypal' => 0 ),
						)
					)
				);			

			foreach( $discounts as $discount ) {
			  if ( !array_key_exists( $discount[ 'comment' ], $data ) ) {
			    $data[ $discount[ 'comment' ] ] = array(
					  'Registered Online' => array(
							 'Competition' => 
								 array( 'Cash or Check' => 0, 'Paypal' => 0 ),
							 'Time Only' => 
								 array( 'Cash or Check' => 0, 'Paypal' => 0 ),
						),
					  'Registered At Event' => array(
							 'Competition' => 
								 array( 'Cash or Check' => 0, 'Paypal' => 0 ),
							 'Time Only' => 
								 array( 'Cash or Check' => 0, 'Paypal' => 0 ),
						)
			    );
			    
			    $entryFees[ $discount[ 'comment' ] ] = array(
					  'Registered Online' => array(
							 'Competition' => 20,
							 'Time Only' => 0,
						),
					  'Registered At Event' => array(
							 'Competition' => 20,
							 'Time Only' => 0,
						)
					);
			  }
			}
			
			// event totals
			$q = new Query( 'results' );
			$results = 
				$q->addWhere( 'event_id', $event[ 'id' ] )
					->select();

			foreach( $results as $result ) {
			  $keyA = ( $result[ 'scca_member' ] == 1 ) ? 'SCCA Member' : 'Non SCCA Member';
			  $keyB = ( $result[ 'registration_id' ] > 0 ) ? 'Registered Online' : 'Registered At Event';
			  $keyC = ( $result[ 'comp_category_id' ] > 0 ) ? 'Competition' : 'Time Only';

        if ( $result[ 'noshow' ] == 1 ) {
  			  $data[ $keyA ][ $keyB ][ $keyC ][ 'noshow' ]++;

        } else {

  			  $totals[ $keyC ]++;
//  			  $data[ $keyA ][ $keyB ][ $keyC ][ 'all' ]++;

  			  $hasDiscount = array_key_exists( $result[ 'name' ], $discounts );
  			  $payment = "Cash or Check";
  			  $registration = array();

  			  if ( $result[ 'registration_id' ] > 0 ) {
  			    $q = new Query( "registrations" );
  			    $registration = $q->selectById( $result[ 'registration_id' ] );
  			    
  			    if ( $registration[ 'payment_id' ] > 0 ) {
  			      $payment = "Paypal";
    			  }
    			  
    			  if ( $hasDiscount ) {
    			    $discount = $discounts[ $result[ 'name' ] ];
    			    $discount[ 'entry' ] = $keyC;
     			  	array_push( $eventDiscounts, $discount );
    			    $keyA = $discounts[ $result[ 'name' ] ][ 'comment' ];
    			  }
  			    $data[ $keyA ][ $keyB ][ $keyC ][ $payment ]++;

  			  } else if ( $hasDiscount ) {
						$discount = $discounts[ $result[ 'name' ] ];
						$discount[ 'entry' ] = $keyC;
						array_push( $eventDiscounts, $discount );
  			  	$data[ $discount[ 'comment' ] ][ $keyB ][ $keyC ][ 'Cash or Check' ]++;
  			  } else {
  			    $data[ $keyA ][ $keyB ][ $keyC ][ 'Cash or Check' ]++;
  			  }
  			  
        }
			  			  
			}
	
			usort( $eventDiscounts, array( $this, "sortDiscounts" ) );
			$q = new Query( "payments" );
			$eventPayments = 
			  $q->addWhere( 'event_id', $event[ 'id' ] )
			    ->joinOn( 'entrant', array( "name_first", "name_last" ) )
			    ->select();
			usort( $eventPayments, array( "Functions", "sortByEntrantName" ) );
			
      return array(
        'totals' => $totals,
        'data' => $data,
        'discounts' => $eventDiscounts,
        'entryFees' => $entryFees,
        'payments' => $eventPayments,
        'paymentTypes' => $paymentTypes,
      );
    }
  }
?>
