<?php
  class SccaController extends BaseController {

    private static $scca_token = sccaToken;
    private static $scca_url = "https://netforum.scca.com/xweb/secure/sccawebservices.asmx";
    private static $scca_function = "VerifyMembership";

    public function getAction( $request ) {

      if ( empty( $request->parameters[ 'name_first' ] ) ) {
        return array( 'error' => "Please enter your first name as it appears on your SCCA membership" );
      } else if ( empty( $request->parameters[ 'name_last'] ) ) {
        return array( 'error' => "Please enter your last name as it appears on your SCCA membership" );
      } else if ( empty( $request->parameters[ 'scca_number' ] ) ) {
        return array( 'error' => "Please enter your SCCA member number." );
      } else {

        $data = array(
          'Token' => self::$scca_token,
          'MemberId' => $request->parameters[ 'scca_number' ],
          'First' => $request->parameters[ 'name_first' ],
          'Last' => $request->parameters[ 'name_last' ],
        );
        $queryString = http_build_query( $data );
        $url = self::$scca_url . '/' . self::$scca_function . '?' . $queryString;

        $cs = curl_init();
        curl_setopt($cs, CURLOPT_URL, $url);
        curl_setopt($cs, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($cs, CURLOPT_TIMEOUT, 60);
        $response = curl_exec($cs);

        libxml_use_internal_errors(true);
        try {
          $xml = new SimpleXMLElement($response);
          if ( empty( $xml ) ) {
            return array(
              'error' => "Membership not found. Please verify your first name, last name, and SCCA member number."
            );
          } else {

            $date_ts = strtotime( trim( $xml->MembershipDetails->Expiration ) );
            $status = ( trim( $xml->MembershipDetails->Status ) == "Active" ) ? 1 : 0;

            $data =  array(
              'scca_date' => date( "Y-m-d", $date_ts ),
              'scca_date_full' => date( 'F j, Y', $date_ts ),
              'scca_date_ts' => $date_ts,
              'scca_status' => $status,
            );

            if ( !$status ) {
              $data[ 'warning' ] = "Your membership is not active.";
            } else if ( $date_ts < time() ) {
              $data[ 'warning' ] = "Your membership expired on ".date( 'F j, Y', $date_ts ).".";
            }
            return $data;
          }
        } catch ( Exception $e ) {
          return array( 'error' => "Sorry, an error prevented us from verifying your membership." );
        }

      }

    }

  }
?>
