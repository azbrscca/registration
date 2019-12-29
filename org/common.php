<?php
  function orgNavbar( $org ) {

    $tokens = explode( "/", $_SERVER[ 'SCRIPT_FILENAME' ] );
    $current = $tokens[ sizeof( $tokens ) - 1 ];
    $privs = json_decode( $org[ 'privileges' ], true );

    $links = array(
      'index.html' => "Basic Information",
      'regulations.html' => "Supplemental Regulations",
      'payment.html' => "Payment Information",
      'entry_fees.html' => "Entry Fees",
    );

    if ( array_key_exists( 'apiaccess', $privs ) &&
          ( $privs[ 'apiaccess' ] == "true" ) ) {
      $links[ 'api.html' ] = "API Access";
    }
?>
        <div class="navbar">
          <div class="navbar-inner">
            <p class="navbar-text pull-left">
              <?php echo $org[ 'name' ]; ?> Information
            </p>
            <ul class="nav">
              <li class="divider-vertical"></li>
              <?php foreach( $links as $link => $text ) { ?>
              <li<?php if ( $current == $link ) { echo ' class="active"'; } ?>><a href="<?php echo $link; ?>"><?php echo $text; ?></a></li>
              <li class="divider-vertical"></li>
              <?php } ?>
            </ul>

          </div>
        </div>
<?php
  }
?>
