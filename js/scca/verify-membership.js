        function verify_scca( prefix ) {

          $( "#" + prefix + "scca_help_block" ).empty();

          if ( $( "#" + prefix + "name_first" ).val() == "" ) {
            $( "#" + prefix + "scca_help_block" )
              .append( $( "<div/>", { 'class' : "text-warning" } )
                .append( $( "<i/>", { 'class' : "icon-warning-sign" } ) )
                .append( "Please enter your first name as it appears on your SCCA Membership." )
              );
              $( "#" + prefix + "scca_status" ).val( 0 );
          } else if ( $( "#" + prefix + "name_last" ).val() == "" ) {
            $( "#" + prefix + "scca_help_block" )
              .append( $( "<div/>", { 'class' : "text-warning" } )
                .append( $( "<i/>", { 'class' : "icon-warning-sign" } ) )
                .append( "Please enter your last name as it appears on your SCCA Membership." )
              );
              $( "#" + prefix + "scca_status" ).val( 0 );
          } else if ( $( "#" + prefix + "scca_number" ).val() == "" ) {

              // scca member is empty do nothing
              $( "#" + prefix + "scca_status" ).val( 0 );

          } else {

            $( "#" + prefix + "scca_help_block" )
              .append( $( "<i/>", { 'class' : "icon-spinner icon-spin icon-large" } ) )
              .append( " Verifying SCCA membership..." )
 
            $.getJSON( "/reg-api/scca",
                        { 'name_first' : $( "#" + prefix + "name_first" ).val(),
                          'name_last' : $( "#" + prefix + "name_last" ).val(),
                          'scca_number' : $( "#" + prefix + "scca_number" ).val() },
                        function( json ) {
              $( "#" + prefix + "scca_help_block" ).empty();
              var error_msg = "";
              if ( json.error ) {
                $( "#" + prefix + "scca_help_block" )
                  .append( $( "<div/>", { 'class' : "text-error" } )
                    .append( $( "<i/>", { 'class' : "icon-remove-sign" } ) )
                    .append( " " )
                    .append( json.error )
                  );
              } else if ( json.warning ) {
                $( "#" + prefix + "scca_help_block" )
                  .append( $( "<div/>", { 'class' : "text-warning" } )
                    .append( $( "<i/>", { 'class' : "icon-warning-sign" } ) )
                    .append( " " )
                    .append( json.warning )
                  );
              } else {
                $( "#" + prefix + "scca_help_block" )
                  .append( $( "<div/>", { 'class' : "text-success" } )
                    .append( $( "<i/>", { 'class' : "icon-ok" } ) )
                    .append( " SCCA membership active. Expiry date: " + json.scca_date_full + "." )
                  );
              }
              
              if ( json.scca_date ) {
                $( "#" + prefix + "scca_date" ).val( json.scca_date );
              }

              if ( json.scca_status ) {
                $( "#" + prefix + "scca_status" ).val( json.scca_status );
              } else {
                $( "#" + prefix + "scca_status" ).val( 0 );
              }

            }).fail( function() {
              $( "#" + prefix + "scca_help_block" ).empty();
              $( "#" + prefix + "scca_help_block" )
                .append( $( "<div/>", { 'class' : "text-error" } )
                  .append( $( "<i/>", { 'class' : "icon-remove-sign" } ) )
                  .append( " " )
                  .append( "Unable to reach the online database for membership verification." )
                );
              $( "#" + prefix + "scca_status" ).val( 0 );
            });
          }
        }
