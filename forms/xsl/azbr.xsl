<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:param name="owner" select="'Registration'"/>
  <xsl:output method="html" encoding="iso-8859-1" indent="no"/>

  <xsl:template match="/">
  <html>
    <head>
      <title>Registration Forms</title>
      <link rel="stylesheet" type="text/css" href="/forms/css/azbr.css" media="screen, print" />
    </head>
    <body>
      <xsl:apply-templates/>
    </body>
  </html>
  </xsl:template>

  <xsl:template match="metadata">
  </xsl:template>

  <xsl:template match="form">

    <div class="form">

      <div class="leftbar bordered padded pull-left">

        <div class="bordered padded strong">
          <xsl:value-of select="entrant/name/lastname"/>, <xsl:value-of select="entrant/name/firstname"/>
          <span class="pull-right">
            Priority: <xsl:value-of select="@priority"/>
          </span>

        </div>

        <div class="bordered padded">
          <h3 class="centered strong underlined">
            Driver Information
          </h3>

          <strong>
            Name: 
            <xsl:value-of select="entrant/name/firstname"/>
            <xsl:text> </xsl:text>
            <xsl:value-of select="entrant/name/lastname"/>
          </strong>
          <br/>
        
          Address:
          <xsl:value-of select="entrant/address/street"/>
          <xsl:text> </xsl:text>
          <xsl:value-of select="entrant/address/city"/>
          <xsl:text> </xsl:text>
          <xsl:value-of select="entrant/address/state"/>
          <xsl:text> </xsl:text>
          <xsl:value-of select="entrant/address/zipcode"/>
          <br/>

          Phone:
          (H) <xsl:text> </xsl:text><xsl:value-of select="entrant/phonenumbers/home"/>
          (W) <xsl:text> </xsl:text><xsl:value-of select="entrant/phonenumbers/work"/>
          <br/>
        
          <strong>
            SCCA #:

            <xsl:choose>
              <xsl:when test="entrant/sccamembership/number != ''">
                <xsl:value-of select="entrant/sccamembership/number"/>,
                <xsl:value-of select="entrant/sccamembership/status"/>
              </xsl:when>
              <xsl:otherwise>
                
              </xsl:otherwise>
            </xsl:choose>

          </strong>
          <br/>
        
          Emergency Contact:
          <xsl:value-of select="entrant/emergencycontact/name"/> - <xsl:value-of select="entrant/emergencycontact/phonenumber"/>
          <br/>
        
          Codriver:
          <xsl:value-of select="registration/codriver"/>
          <br/>
<!--
          Email:
          <xsl:value-of select="entrant/email"/>
          <br/>
-->
        </div><!-- / entrant -->   
        
        <div class="bordered padded">
          <h3 class="centered strong underlined">
            Car Information
          </h3>

          Car:
          <xsl:value-of select="competition/car/year"/>
          <xsl:text> </xsl:text>
          <xsl:value-of select="competition/car/make"/>
          <xsl:text> </xsl:text>
          <xsl:value-of select="competition/car/model"/>,
          <xsl:value-of select="competition/car/color"/>
          <br/>
        
          Engine:
          <xsl:value-of select="competition/car/engine"/>
          <br/>
        
          Tire Brand/Size:
          <xsl:value-of select="competition/car/tire_brand"/>
          /
          <xsl:value-of select="competition/car/tire_size"/>
          <br/>        

          Tire Type:
          <xsl:value-of select="competition/car/tire_type"/>
          <br/><br/>

          TO Car:
            <xsl:choose>

              <xsl:when test="time-only">

                <xsl:choose>
                  <xsl:when test="time-only/car/@competition-car">
                    Same as Competition
                  </xsl:when>
                  <xsl:otherwise>
                    <xsl:value-of select="time-only/car/year"/>
                    <xsl:text> </xsl:text>
                    <xsl:value-of select="time-only/car/make"/>
                    <xsl:text> </xsl:text>
                    <xsl:value-of select="time-only/car/model"/>
                    [<xsl:value-of select="time-only/car/class"/>/TO]
                    (<xsl:value-of select="time-only/car/color"/>)
                  </xsl:otherwise>
                </xsl:choose>

              </xsl:when>

              <xsl:otherwise>
                None
              </xsl:otherwise>

            </xsl:choose>

          <br/>

        </div>
      
        <div class="bordered centered padded" style="font-size: 18px;">
          <em>This is a Work/Run event.  For each group you run (including Time Onlys), you must work.
          Failure to report for work assignment will result in a DSQ for the event.</em>
        </div>
        
        <div class="bordered">
          <h3 class="centered strong underlined">
            Tech Information
          </h3>

          <div class="padded pull-left" style="width: 30%">
            <div class="bordered pull-left" style="background-color: #efefef; width: 100%;">
              <div class="padded">
                Battery:
                <div class="bordered pull-right" style="background-color: #ffffff; width: 25px;">
                   <xsl:text>&#160;</xsl:text> 
                </div>
              </div>
              <div class="padded">
                Tire Pressure:
                <div class="bordered pull-right" style="background-color: #ffffff; width: 25px;">
                   <xsl:text>&#160;</xsl:text> 
                </div>
              </div>
              <div class="padded">
                No Loose Items:
                <div class="bordered pull-right" style="background-color: #ffffff; width: 25px;">
                   <xsl:text>&#160;</xsl:text> 
                </div>
              </div>
            </div>
          </div>

          <div class="padded pull-left" style="width: 30%">
            <div class="bordered pull-left" style="background-color: #efefef; width: 100%;">
            
              <div class="padded">
                Throttle:
                <div class="bordered pull-right" style="background-color: #ffffff; width: 25px;">
                   <xsl:text>&#160;</xsl:text> 
                </div>
              </div>
              <div class="padded">
                Brakes:
                <div class="bordered pull-right" style="background-color: #ffffff; width: 25px;">
                   <xsl:text>&#160;</xsl:text> 
                </div>
              </div>
              <div class="padded">
                Steering:
                <div class="bordered pull-right" style="background-color: #ffffff; width: 25px;">
                   <xsl:text>&#160;</xsl:text> 
                </div>
              </div>
            </div>
          </div>

          <div class="padded pull-left" style="width: 30%">
            <div class="bordered pull-left" style="background-color: #efefef; width: 100%;">
            
              <div class="padded">
                Helmet:
                <div class="bordered pull-right" style="background-color: #ffffff; width: 25px;">
                   <xsl:text>&#160;</xsl:text> 
                </div>
              </div>
              <div class="padded">
                Seat Belt:
                <div class="bordered pull-right" style="background-color: #ffffff; width: 25px;">
                   <xsl:text>&#160;</xsl:text> 
                </div>
              </div>
              <div class="padded">
                Wheel Bearings:
                <div class="bordered pull-right" style="background-color: #ffffff; width: 25px;">
                   <xsl:text>&#160;</xsl:text> 
                </div>
              </div>
            </div>
          </div>

          <div class="font-small padded" style="clear: both;">
            Inspected By:
          </div>

        </div>

      </div> <!-- / leftbar -->
      
      <div class="rightbar bordered padded pull-left">

        <div class="bordered centered underlined">
          <h3>Car Number</h3>
        </div>
        
        <div class="bordered centered">
          <h1>
            <xsl:value-of select="competition/car-number"/>
            <xsl:if test="time-only">, <xsl:value-of select="time-only/car-number"/></xsl:if>
          </h1>
        </div>

        <div class="bordered centered underlined">
          <h3>Car Class / Category</h3>
        </div>

        <div class="bordered centered">
          <h2>
            <xsl:value-of select="competition/car/class"/>/<xsl:value-of select="competition/car/category"/><br/>

              <xsl:if test="time-only">
                <xsl:choose>
                  <xsl:when test="time-only/car/@competition-car">
                    <xsl:value-of select="competition/car/class"/>/TO
                  </xsl:when>
                  <xsl:otherwise>
                    <xsl:value-of select="time-only/car/class"/>/TO
                  </xsl:otherwise>
                </xsl:choose>
              </xsl:if>
          </h2>
        </div>

        <div class="bordered centered underlined">
          <h3>Work Assignment</h3>
        </div>

        <div class="bordered centered">
          <h3>
            <xsl:value-of select="competition/work-assign"/><br/>

            <xsl:if test="time-only">
              <xsl:value-of select="time-only/work-assign"/><br/>
            </xsl:if>
          </h3>
        </div>

        <div class="bordered centered padded" style="font-size: 18px;">
          <strong>Event Date:</strong>
          <xsl:text>&#160;</xsl:text>
          <xsl:value-of select="../../@formatteddate"/>
        </div>
        
        <div class="bordered padded" style="font-size: 18px;">
          <div class="centered">
            <h3 class="strong">Entry Fees</h3>
            <em>Make checks payable to <strong>Arizona Border Region SCCA</strong></em>
            <br/><br/>
          </div>
          Pre-Reg SCCA:
          <span class="pull-right">
            $<xsl:value-of select="../../metadata/entryFees/scca_comp_online"/>
          </span><br/>
          Pre-Reg Non SCCA:
          <span class="pull-right">
            $<xsl:value-of select="../../metadata/entryFees/wknd_comp_online"/>
          </span><br/>
          Time Only:
          <span class="pull-right">
            $<xsl:value-of select="../../metadata/entryFees/scca_to_online"/>
          </span><br/>
        </div>

        <div class="centered">
          <xsl:choose>
            <xsl:when test="registration/payment">
              <strong><xsl:value-of select="registration/payment"/></strong>
            </xsl:when>
            <xsl:otherwise>
              <xsl:text>&#160;</xsl:text> 
            </xsl:otherwise>          
          </xsl:choose>
        </div>
      
 
      </div> <!-- / rightbar -->
    </div><!-- / form -->

    <div style="clear: both; page-break-after: always;" />

  </xsl:template>
  
</xsl:stylesheet>
