<?php

  function configurePills() {
  
    $tokens = explode( "/", $_SERVER[ 'SCRIPT_FILENAME' ] );
    $pill = $tokens[ sizeof( $tokens ) - 1 ];
  
	$pills = array(
	  'one.html' => "Run Group Structure",
	  'two.html' => "Run / Work Order",
	  'three.html' => "Competition Category Run Groups",
	  'four.html' => "Run Group Assignment",
	  'five.html' => "Work Position Assignment",
	  'six.html' => "Download Data",
	);

?>
          <div class="span3">
            <div class="span3" data-spy="affix" id="affix-div">
              <ul class="nav nav-pills nav-stacked" id="configure-ul">
<?php foreach( $pills as $link => $text ) { ?>
                <li<?php if ( $pill == $link ) { echo ' class="active"'; } ?>><a href="<?php echo $link; ?>"><?php echo $text; ?></a></li>
<?php } ?>
              </ul>
            </div>
          </div>
<?php
  }

  function configureNav( $event, $org ) {
  
    $q = new Query( "registrations" );
    $compCount = $q->addWhere( 'event_id', $event[ 'id' ] )->count();
    $toCount = $q->addWhere( 'time_only_reg', 1 )->count();

    $q = new Query( "entry_forms" );
    $formCount = $q->addWhere( 'event_id', $event[ 'id' ] )->count();
  
?>
        <div class="navbar">
          <div class="navbar-inner">
            <p class="navbar-text pull-left">
              <?php echo $org[ 'name' ]; ?> Event Configuration
            </p>
            <ul class="nav">
              <li class="divider-vertical"></li>
            </ul>
            <p class="navbar-text pull-left">
<?php
  if ( !empty( $event[ 'name' ] ) ) { echo $event[ 'name' ].' '; }
  echo $event[ 'date' ].' at '.$event[ 'site_name' ]
?>            
            </p>
            <p class="navbar-text pull-right">
	      <?php if ( $event[ 'time_only_reg' ] == 1 ) { ?>
                <?php echo $compCount; ?> competition entires, <?php echo $toCount; ?> time only entires, <?php echo $compCount + $toCount; ?> total entries.
	      <?php } else { ?>
	        <?php echo $compCount; ?> entries.
	      <?php } ?>
	        <?php echo $formCount; ?> entry forms.
            </p>
          </div>
        </div>
<?php
  }
?>
