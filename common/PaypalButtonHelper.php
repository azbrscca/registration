<?php
  require_once "../db/Query.php";
  require_once "../common/DatabaseHelper.php";
  
  class PaypalButtonHelper {

    public static function displayPaypalButton( $event, $registration ) {
      $event_id = $event[ 'id' ];
      $entrant_id = $registration[ 'entrant_id' ];
      $totalRemainingEntryFee = $registration[ 'entry_fee' ];
      $event_id_2da1 = 484;
      $event_id_2da2 = 485;
      $buttonDelivered = false;
      
      //2021 two days of awesome check
      // TODO: fix this in the DB so that it just checks if there's a linked event
      $registeredForBoth = false;
      if( ($event_id == $event_id_2da1) || ($event_id == $event_id_2da2) ) {
        $registeredForBoth = DatabaseHelper::isEntrantRegistered($entrant_id, $event_id_2da1) && DatabaseHelper::isEntrantRegistered($entrant_id, $event_id_2da2);
      }
      
      if( $registeredForBoth ) {
        //We need to calculate the Two Days of Awesome entry fee. The discount is $10. However, weekend members get a break on the $10 weekend member fee, so their discount is $20
        if ( $registration[ 'entrant_scca_status' ] == 1  ) {
          $totalRemainingEntryFee = -10;
        } else {
          $totalRemainingEntryFee = -20;
        }
        $totalRemainingEntryFee = $totalRemainingEntryFee + DatabaseHelper::getEntrantEntryFee($entrant_id, $event_id_2da1) + DatabaseHelper::getEntrantEntryFee($entrant_id, $event_id_2da2);
      }
      ?>
      <strong>
        Your Entry Fee: <i class="icon-usd"></i> <?php echo number_format( $totalRemainingEntryFee, 2 ); ?><p/>
      </strong>
      <?php
      if( $registeredForBoth ) {
        // Two days of awesome reg with a discount - SCCA members
        if ( $registration[ 'entrant_scca_status' ] == 1  ) {
          if( $totalRemainingEntryFee == 50 ) { 
            $buttonDelivered = true;?>
            <form target="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post">
            <input type="hidden" name="cmd" value="_s-xclick">
            <input type="hidden" name="hosted_button_id" value="W6EZ2EBECYGTU">
            <table>
            <tr><td><input type="hidden" name="on0" value="Driver name">Driver name</td></tr><tr><td><input type="text" name="os0" maxlength="200" value="<?php echo $registration[ 'entrant_name_first' ], " ", $registration[ 'entrant_name_last' ]?>"></td></tr>
            </table>
            <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_cart_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
            <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
            </form>
          <?php } else if ( $totalRemainingEntryFee == 60 ) { 
            $buttonDelivered = true;?>
            <form target="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post">
            <input type="hidden" name="cmd" value="_s-xclick">
            <input type="hidden" name="hosted_button_id" value="8B8D7HJYLWCHL">
            <table>
            <tr><td><input type="hidden" name="on0" value="Driver name">Driver name</td></tr><tr><td><input type="text" name="os0" maxlength="200" value="<?php echo $registration[ 'entrant_name_first' ], " ", $registration[ 'entrant_name_last' ]?>"></td></tr>
            </table>
            <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_cart_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
            <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
            </form>
          <?php } else if ( $totalRemainingEntryFee == 70 ) { 
            $buttonDelivered = true;?>
            <form target="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post">
            <input type="hidden" name="cmd" value="_s-xclick">
            <input type="hidden" name="hosted_button_id" value="XPXBJSCVJG7J2">
            <table>
            <tr><td><input type="hidden" name="on0" value="Driver name">Driver name</td></tr><tr><td><input type="text" name="os0" maxlength="200" value="<?php echo $registration[ 'entrant_name_first' ], " ", $registration[ 'entrant_name_last' ]?>"></td></tr>
            </table>
            <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_cart_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
            <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
            </form>
          <?php } ?>
        <?php } else {
          // Two days of awesome reg with a discount - weekend members
          if( $totalRemainingEntryFee == 60 ) { 
            $buttonDelivered = true;?>
            <form target="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post">
            <input type="hidden" name="cmd" value="_s-xclick">
            <input type="hidden" name="hosted_button_id" value="AXRBR3TQJ623S">
            <table>
            <tr><td><input type="hidden" name="on0" value="Driver name">Driver name</td></tr><tr><td><input type="text" name="os0" maxlength="200" value="<?php echo $registration[ 'entrant_name_first' ], " ", $registration[ 'entrant_name_last' ]?>"></td></tr>
            </table>
            <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_cart_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
            <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
            </form>
          <?php } else if ( $totalRemainingEntryFee == 70 ) { 
            $buttonDelivered = true;?>
            <form target="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post">
            <input type="hidden" name="cmd" value="_s-xclick">
            <input type="hidden" name="hosted_button_id" value="3BH6QQLRN5XVC">
            <table>
            <tr><td><input type="hidden" name="on0" value="Driver name">Driver name</td></tr><tr><td><input type="text" name="os0" maxlength="200" value="<?php echo $registration[ 'entrant_name_first' ], " ", $registration[ 'entrant_name_last' ]?>"></td></tr>
            </table>
            <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_cart_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
            <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
            </form>
          <?php } else if ( $totalRemainingEntryFee == 80 ) { 
            $buttonDelivered = true;?>
            <form target="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post">
            <input type="hidden" name="cmd" value="_s-xclick">
            <input type="hidden" name="hosted_button_id" value="JS4HT2TZP8MT6">
            <table>
            <tr><td><input type="hidden" name="on0" value="Driver name">Driver name</td></tr><tr><td><input type="text" name="os0" maxlength="200" value="<?php echo $registration[ 'entrant_name_first' ], " ", $registration[ 'entrant_name_last' ]?>"></td></tr>
            </table>
            <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_cart_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
            <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
            </form>
          <?php } ?>
        <?php }
      } else {
        // Single event reg only - SCCA members
        if ( $registration[ 'entrant_scca_status' ] == 1  ) {
          if( $totalRemainingEntryFee == 30 ) { 
            $buttonDelivered = true;?>
            <form target="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post">
            <input type="hidden" name="cmd" value="_s-xclick">
            <input type="hidden" name="hosted_button_id" value="9JCHFM4ZXT6AG">
            <table>
            <tr><td><input type="hidden" name="on0" value="Driver name">Driver name</td></tr><tr><td><input type="text" name="os0" maxlength="200" value="<?php echo $registration[ 'entrant_name_first' ], " ", $registration[ 'entrant_name_last' ]?>"></td></tr>
            </table>
            <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_cart_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
            <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
            </form>
          <?php } else if ( $totalRemainingEntryFee == 40 ) { 
            $buttonDelivered = true;?>
            <form target="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post">
            <input type="hidden" name="cmd" value="_s-xclick">
            <input type="hidden" name="hosted_button_id" value="EASWPKGAUB6HY">
            <table>
            <tr><td><input type="hidden" name="on0" value="Driver name">Driver name</td></tr><tr><td><input type="text" name="os0" maxlength="200" value="<?php echo $registration[ 'entrant_name_first' ], " ", $registration[ 'entrant_name_last' ]?>"></td></tr>
            </table>
            <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_cart_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
            <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
            </form>
          <?php } else if ( $totalRemainingEntryFee == 42 ) { 
            $buttonDelivered = true;?>
            <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
            <input type="hidden" name="cmd" value="_s-xclick" />
            <input type="hidden" name="hosted_button_id" value="L9UEHWPF83EL2" />
            <table>
            <tr><td><input type="hidden" name="on0" value="Driver name">Driver name</td></tr><tr><td><input type="text" name="os0" maxlength="200" value="<?php echo $registration[ 'entrant_name_first' ], " ", $registration[ 'entrant_name_last' ]?>"></td></tr>
            </table>
            <input type="hidden" name="currency_code" value="USD" />
            <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_cart_LG.gif" border="0" name="submit" title="PayPal - The safer, easier way to pay online!" alt="Add to Cart" />
            </form>
          <?php } else if ( $totalRemainingEntryFee == 52 ) { 
            $buttonDelivered = true;?>
            <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
            <input type="hidden" name="cmd" value="_s-xclick" />
            <input type="hidden" name="hosted_button_id" value="UUPMCBF3EUF6N" />
            <table>
            <tr><td><input type="hidden" name="on0" value="Driver name">Driver name</td></tr><tr><td><input type="text" name="os0" maxlength="200" value="<?php echo $registration[ 'entrant_name_first' ], " ", $registration[ 'entrant_name_last' ]?>"></td></tr>
            </table>
            <input type="hidden" name="currency_code" value="USD" />
            <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_cart_LG.gif" border="0" name="submit" title="PayPal - The safer, easier way to pay online!" alt="Add to Cart" />
            </form>
          <?php } ?>
        <?php } else {
          // Single event reg only - weekend members
          if( $totalRemainingEntryFee == 45 ) { 
            $buttonDelivered = true;?>
            <form target="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post">
            <input type="hidden" name="cmd" value="_s-xclick">
            <input type="hidden" name="hosted_button_id" value="MCT4HMFYRQYNE">
            <table>
            <tr><td><input type="hidden" name="on0" value="Driver name">Driver name</td></tr><tr><td><input type="text" name="os0" maxlength="200" value="<?php echo $registration[ 'entrant_name_first' ], " ", $registration[ 'entrant_name_last' ]?>"></td></tr>
            </table>
            <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_cart_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
            <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
            </form>
          <?php } else if ( $totalRemainingEntryFee == 55 ) { 
            $buttonDelivered = true;?>
            <form target="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post">
            <input type="hidden" name="cmd" value="_s-xclick">
            <input type="hidden" name="hosted_button_id" value="VYQZ5UML7B3ZE">
            <table>
            <tr><td><input type="hidden" name="on0" value="Driver name">Driver name</td></tr><tr><td><input type="text" name="os0" maxlength="200" value="<?php echo $registration[ 'entrant_name_first' ], " ", $registration[ 'entrant_name_last' ]?>"></td></tr>
            </table>
            <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_cart_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
            <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
            </form>
          <?php } else if ( $totalRemainingEntryFee == 57 ) { 
            $buttonDelivered = true;?>
            <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
            <input type="hidden" name="cmd" value="_s-xclick" />
            <input type="hidden" name="hosted_button_id" value="KKZY7CYF6YGHA" />
            <table>
            <tr><td><input type="hidden" name="on0" value="Driver name">Driver name</td></tr><tr><td><input type="text" name="os0" maxlength="200" value="<?php echo $registration[ 'entrant_name_first' ], " ", $registration[ 'entrant_name_last' ]?>"></td></tr>
            </table>
            <input type="hidden" name="currency_code" value="USD" />
            <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_cart_LG.gif" border="0" name="submit" title="PayPal - The safer, easier way to pay online!" alt="Add to Cart" />
            </form>
          <?php } else if ( $totalRemainingEntryFee == 67 ) { 
            $buttonDelivered = true;?>
            <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
            <input type="hidden" name="cmd" value="_s-xclick" />
            <input type="hidden" name="hosted_button_id" value="HZ88SZ9TL2E54" />
            <table>
            <tr><td><input type="hidden" name="on0" value="Driver name">Driver name</td></tr><tr><td><input type="text" name="os0" maxlength="200" value="<?php echo $registration[ 'entrant_name_first' ], " ", $registration[ 'entrant_name_last' ]?>"></td></tr>
            </table>
            <input type="hidden" name="currency_code" value="USD" />
            <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_cart_LG.gif" border="0" name="submit" title="PayPal - The safer, easier way to pay online!" alt="Add to Cart" />
            </form>
          <?php } ?>
        <?php }
      }
      
      //User might get here if they pay for comp only, then update their registration
      if( !$buttonDelivered ) {
        if( $totalRemainingEntryFee == 20 ) { 
          $buttonDelivered = true;?>
          
        <?php } else if ( $totalRemainingEntryFee == 10 ) { 
          $buttonDelivered = true;?>
          
        <?php } else { ?>
          Please send a PayPal payment to payments@azbrscca.org to complete payment
        <?php }
      }
    }

  } // class PaypalButtonHelper
?>
