<?php
  require_once "../db/Query.php";
  require_once "../common/DatabaseHelper.php";
  
  class PaypalButtonHelper {

    public static function displayPaypalButton( $event, $registration ) {
      $event_id = $event[ 'id' ];
      $entrant_id = $registration[ 'entrant_id' ];
      
      //2021 two days of awesome check
      $registeredForBoth = false;
      // if( ($event_id == 460) || ($event_id == 461) ) {
        // $registeredForBoth = DatabaseHelper::isEntrantRegistered($entrant_id, 460) && DatabaseHelper::isEntrantRegistered($entrant_id, 461);
      // }
      
      if( $registeredForBoth ) {
        
      } else {
        if ( $registration[ 'entrant_scca_status' ] == 1  ) {
          if( $registration[ 'entry_fee' ] == 30 ) { ?>
            <form target="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post">
            <input type="hidden" name="cmd" value="_s-xclick">
            <input type="hidden" name="hosted_button_id" value="9JCHFM4ZXT6AG">
            <table>
            <tr><td><input type="hidden" name="on0" value="Driver name">Driver name</td></tr><tr><td><input type="text" name="os0" maxlength="200" value="<?php echo $registration[ 'entrant_name_first' ], " ", $registration[ 'entrant_name_last' ]?>"></td></tr>
            </table>
            <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_cart_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
            <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
            </form>
          <?php } else if ( $registration[ 'entry_fee' ] == 40 ) { ?>
            <form target="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post">
            <input type="hidden" name="cmd" value="_s-xclick">
            <input type="hidden" name="hosted_button_id" value="EASWPKGAUB6HY">
            <table>
            <tr><td><input type="hidden" name="on0" value="Driver name">Driver name</td></tr><tr><td><input type="text" name="os0" maxlength="200" value="<?php echo $registration[ 'entrant_name_first' ], " ", $registration[ 'entrant_name_last' ]?>"></td></tr>
            </table>
            <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_cart_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
            <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
            </form>
          <?php } ?>
        <?php } else {
          if( $registration[ 'entry_fee' ] == 40 ) { ?>
            <form target="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post">
            <input type="hidden" name="cmd" value="_s-xclick">
            <input type="hidden" name="hosted_button_id" value="MCT4HMFYRQYNE">
            <table>
            <tr><td><input type="hidden" name="on0" value="Driver name">Driver name</td></tr><tr><td><input type="text" name="os0" maxlength="200" value="<?php echo $registration[ 'entrant_name_first' ], " ", $registration[ 'entrant_name_last' ]?>"></td></tr>
            </table>
            <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_cart_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
            <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
            </form>
          <?php } else if ( $registration[ 'entry_fee' ] == 50 ) { ?>
            <form target="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post">
            <input type="hidden" name="cmd" value="_s-xclick">
            <input type="hidden" name="hosted_button_id" value="VYQZ5UML7B3ZE">
            <table>
            <tr><td><input type="hidden" name="on0" value="Driver name">Driver name</td></tr><tr><td><input type="text" name="os0" maxlength="200" value="<?php echo $registration[ 'entrant_name_first' ], " ", $registration[ 'entrant_name_last' ]?>"></td></tr>
            </table>
            <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_cart_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
            <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
            </form>
          <?php } ?>
        <?php }
      }
    }

  } // class PaypalButtonHelper
?>
