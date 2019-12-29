<?php
  class Display {
  
    public static function closeContainer( $logged_in = true ) {
?>

      <footer class="navbar navbar-fixed-bottom">
        <div class="container-fluid">
          <div class="row-fluid">
            <div class="span1">
              <img src="<?php echo baseHref; ?>img/pointer-cone-40x20.png" />
            </div>
          </div>
        </div>
      </footer>

    </div><!-- /.fluid-container-->
    
    <script src="<?php echo baseHref; ?>js/jquery-1.7.2.min.js"></script>
    <script src="<?php echo baseHref; ?>js/bootstrap.js"></script>
    <script src="<?php echo baseHref; ?>js/mindthecones.js"></script>
<?php
    if ( !$logged_in ) {
?>
    <script type="text/javascript" src="<?php echo baseHref; ?>js/sha1.js"></script>
    <script type="text/javascript">

      $( "#remember-btn" ).click( function() {
      
        if ( $( this ).hasClass( "btn-success" ) ) {
          $( this ).removeClass( 'btn-success' );
          $( "#remember_me" ).val( 0 );
        } else {
          $( this ).addClass( 'btn-success' );
          $( "#remember_me" ).val( 1 );
        }
      
      });
    
      $( "#login-form" ).submit( function() {

        $( "#login-error" ).slideUp();
        if ( ( $( "#username" ).val().length == 0 ) ||
             ( $( "#password" ).val().length == 0 ) ) {
          $( "#login-error" ).html( "Please enter your username and password." ).slideDown();
          return false;
        } else {
          $( "#sha1-password" ).val( $.sha1( $( "#password" ).val() ) );
          return true;
        }
        return false;
      });
    </script>
<?php
      } // if ( $logged_in ) 
    }

    public static function closeBody() {
?>
  </body>
</html>
<?php
    }

    public static function navBar( $user = array(), $org = array(), $showLogin = true ) {
?>
    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container-fluid">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand" href="<?php echo baseHref; ?>index.html">Registration Home
            <img alt="Small Cone" src="<?php echo baseHref; ?>img/pointer-cone-40x20.png" />
          </a>
<?php if ( empty( $user ) && $showLogin ) { ?>
          <a class="btn btn-primary pull-right hidden-desktop" href="<?php echo baseHref; ?>login.html"><i class="icon-signin"></i></a>
<?php } ?>

          <div class="nav-collapse collapse">
<?php if ( !empty( $user ) ) { ?>
            <ul class="nav">
              <li><a href="<?php echo baseHref; ?>garage/index.html">Garage</a></li>
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown"> Account <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li><a href="<?php echo baseHref; ?>account/info.html">Basic Information</a></li>              
                  <li><a href="<?php echo baseHref; ?>account/password.html">Change Password</a></li>
                  <li><a href="<?php echo baseHref; ?>account/reminders.html">Email Reminders</a></li>
                </ul>
              </li>
<?php
        if ( !empty( $org ) ) {
          $privs = json_decode( $org[ 'privileges' ], true );
?>
              <li class="divider-vertical"></li>
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" id="org-nav-li"> <?php echo $org[ 'name' ]; ?> <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li><a href="<?php echo baseHref; ?>org/events/index.html">Events</a></li>
<?php     if ( $privs[ 'results' ] == "true" ) { ?>
                  <li><a href="<?php echo baseHref; ?>org/series.html">Event Series</a></li>
<?php      } ?>
                  <li class="divider"></li>
                  <li><a href="<?php echo baseHref; ?>org/categories.html">Competition Categories</a></li>
                  <li><a href="<?php echo baseHref; ?>org/groups.html">Competition Category Groups</a></li>
                  <li><a href="<?php echo baseHref; ?>org/sites.html">Sites</a></li>
<?php      if ( $privs[ 'configuration' ] == "true" ) { ?>
                  <li><a href="<?php echo baseHref; ?>org/positions.html">Work Positions</a></li>
<?php      } ?>
                  <li class="divider"></li>
                  <li><a href="<?php echo baseHref; ?>org/index.html">Organization Information</a></li>
                </ul>
              </li>
<?php   }
        if ( $user[ 'user_type_id' ] == 9 ) { ?>
              <li class="divider-vertical"></li>
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown"> System <b class="caret"></b></a>
                <ul class="dropdown-menu">
		  <li><a href="<?php echo baseHref; ?>system/event_types.html">Event Types</a></li>
                  <li><a href="<?php echo baseHref; ?>system/orgs.html">Organizations</a></li>              
                  <li class="divider"></li>
                  <li><a href="<?php echo baseHref; ?>system/session.html">Change Organization</a></li>
                </ul>
              </li>
<?php   } ?>
            </ul>


            <form class="navbar-form pull-right" id="logout-form" name="logout-form">
              <a class="btn" href="<?php echo baseHref; ?>logout.html">Log Out</a>
            </form>
            <p class="navbar-text pull-right">
              <?php echo $user[ 'name_first' ]." ".$user[ 'name_last' ]; ?> &nbsp; 
            </p>

<?php
      } else if ( empty( $user ) && $showLogin ) {
        $username = "";
        if ( !empty( $_COOKIE[ 'registration-username' ] ) ) {
          $username = $_COOKIE[ 'registration-username' ];
        }  
?>
            <form action="<?php echo baseHref; ?>login.html" class="navbar-form pull-right" id="login-form" method="post" name="login-form">
              <input class="input-medium" id="username" name="username" placeholder="Username" type="text" value="<?php echo $username; ?>" />
              <input class="input-medium" id="password" placeholder="Password" type="password" />
              <input id="sha1-password" name="password" type="hidden" />
              
              <!-- <button class="btn hide" id="remember-btn" type="button">Remember Me</button> -->
              <input name="remember_me" id="remember_me" type="hidden" value="0" />

              <button id="login-btn" class="btn btn-primary" type="submit">Login</button>
              <a href="<?php echo baseHref; ?>account/register.html" class="btn" type="button">Create Account</a>
              <p class="navbar-text text-error hide" id="login-error"></p>
            </form>
<?php } ?>
            <ul class="nav">
              <li><a href="<?php echo baseHref; ?>help/index.html">Help</a></li>
            </ul>
          </div><!-- nav-collapse -->
        </div>
      </div>

    </div>
<?php
    }

    public static function openBody() {
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Registration</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <link href="<?php echo baseHref; ?>css/bootstrap.no-icons.min.css" rel="stylesheet">
    <link href="<?php echo baseHref; ?>css/font-awesome.min.css" rel="stylesheet">
    <!--[if IE 7]>
    <link href="<?php echo baseHref; ?>css/font-awesome-ie7.min.css" rel="stylesheet">
    <![endif]-->

    
    <style type="text/css">
      html {
        overflow-y: scroll;
      }
      body {
        background-attachment: fixed;
        background-image: url('<?php echo baseHref; ?>img/background.png');
        background-position: right;
        background-repeat: no-repeat;
        padding-top: 60px;
        padding-bottom: 40px;
      }
    </style>
    <link href="<?php echo baseHref; ?>css/bootstrap-responsive.css" rel="stylesheet">
    <link href="<?php echo baseHref; ?>css/datepicker.css" rel="stylesheet">
    <link href="<?php echo baseHref; ?>css/timepicker.css" rel="stylesheet">

    <!--[if lt IE 9]>
      <script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <link rel="shortcut icon" href="<?php echo baseHref; ?>img/favicon.png">
  </head>

  <body>
<?php
    }

    public static function openContainer() {
?>
    <div class="container-fluid">
<?php
    }
  }
?>
