<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:param name="owner" select="'Registration'"/>
  <xsl:output method="html" encoding="iso-8859-1" indent="no"/>

  <xsl:template match="/">
  <html>
    <head>
      <title>Registration Forms</title>
      <link rel="stylesheet" type="text/css" href="/forms/css/sscc.css" media="screen, print" />
    </head>
    <body>
      <xsl:apply-templates/>
    </body>
  </html>
  </xsl:template>

  <xsl:template match="form">

    <div class="form">

      <div class="leftbar bordered padded pull-left">

        <div class="bordered centered">
          <h3><xsl:value-of select="../../@organization"/></h3>
        </div>

        <div class="bordered centered padded">
          Checks payable to<br/>
          <em><strong><xsl:value-of select="../../@organization"/></strong></em><br/>
          Website: http://www.sierrasportscars.net/

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
          <xsl:value-of select="entrant/address/street"/><br/>

          City:
          <xsl:value-of select="entrant/address/city"/>

          State:
          <xsl:value-of select="entrant/address/state"/>

          Zip:
          <xsl:value-of select="entrant/address/zipcode"/>
          <br/>

          Phone:
          <xsl:value-of select="entrant/phonenumbers/home"/>
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
        </div>

      </div> <!-- / leftbar -->

      <div class="rightbar bordered padded pull-left">

        <div class="bordered centered">
          <h3>Event Date:
          <xsl:text>&#160;</xsl:text>
          <xsl:value-of select="../../@formatteddate"/>
          </h3>
        </div>

        <div class="bordered padded">
          <span class="strong pull-left underlined">Entry Fees:</span><br/>

          <span class="pull-left" style="width: 45%;">
            SSCC <xsl:text>&#38;</xsl:text> SCCA:
            <span class="pull-right">
              $<xsl:value-of select="../../metadata/entryFees/scca_comp_online"/>
            </span><br/>
            SCCA Weekend:
            <span class="pull-right">
              $<xsl:value-of select="../../metadata/entryFees/wknd_comp_online"/>
            </span>
          </span>
          <span class="pull-left" style="width: 5%;">
            <xsl:text>&#160;</xsl:text>
          </span>
          <span class="pull-left" style="width: 45%;">
            Time Only
            <span class="pull-right">
              $<xsl:value-of select="../../metadata/entryFees/scca_to_online"/>
            </span>
          </span>
          <div class="clearboth"></div>
        </div>

        <div class="bordered padded">
          <h3 class="centered strong underlined">
            Car Information
          </h3>

          Car:
          <xsl:value-of select="competition/car/year"/>
          <xsl:text> </xsl:text>
          <xsl:value-of select="competition/car/make"/>
          <xsl:text> </xsl:text>
          <xsl:value-of select="competition/car/model"/>
          <br/>

          Color:
          <xsl:value-of select="competition/car/color"/>

          <span class="pull-right" style="width: 50%;">
            Engine:
            <xsl:value-of select="competition/car/engine"/>
          </span>
          <br/>

          Tire Brand:
          <xsl:value-of select="competition/car/tire_brand"/>

          <span class="pull-right" style="width: 50%;">
            Tire Type:
            <xsl:value-of select="competition/car/tire_type"/>
          </span>
          <br/>

          Tire Size:
          <xsl:value-of select="competition/car/tire_size"/>
          <br/>

          Class:
          <xsl:value-of select="competition/car/class"/>/<xsl:value-of select="competition/car/category"/>

          <xsl:if test="time-only">
            <xsl:choose>
              <xsl:when test="time-only/car/@competition-car">
              , <xsl:value-of select="competition/car/class"/>/TO
              </xsl:when>
              <xsl:otherwise>
              , <xsl:value-of select="time-only/car/class"/>/TO
              </xsl:otherwise>
            </xsl:choose>

          </xsl:if>

          <br/>

          Codriver:
          <xsl:value-of select="registration/codriver"/>
          <br/>

        </div>

      </div> <!-- / rightbar -->

      <div class="clearboth">
        <div class="centered">
          <h4 style="margin: 0; padding: 0;"><em>This is a Work/Run event.  For each group you run (including Time Onlys), you must work.
        Failure to report for work assignment will result in a DSQ for the event.</em>
          </h4>
        </div>
      </div>

      <div class="padded pull-left" style="width: 240px;">
        <div class="bordered padded pull-left" style="width: 100%;">
          <div class="bordered centered underlined" style="width: auto;">
            <h3>Car Class</h3>
          </div>

          <div class="bordered padded centered" style="height: 127px;">
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
        </div>
      </div>

      <div class="padded pull-left" style="width: 240px;">
        <div class="bordered padded pull-left" style="width: 100%;">
          <div class="bordered centered underlined">
            <h3>Car Number</h3>
          </div>

          <div class="bordered padded centered" style="height: 127px;">
          </div>
        </div>
      </div>

      <div class="padded pull-left" style="width: 240px;">
        <div class="bordered padded pull-left" style="width: 100%;">
          <div class="bordered centered underlined">
            <h3>Work Position</h3>
          </div>

          <div class="bordered padded centered" style="height: 127px;">
          </div>
        </div>
      </div>

      <div class="padded pull-left" style="width: 240px;">
        <div class="bordered padded pull-left" style="width: 100%;">
          <div class="bordered centered underlined">
            <h3>Run Order:<br/>A, B, C, D</h3>
          </div>

          <div class="bordered centered padded"  style="height: 100px; width: auto;">
            <strong>A</strong> works for <strong>C</strong><br/>
            <strong>C</strong> works for <strong>A</strong><br/>
            <strong>B</strong> works for <strong>D</strong><br/>
            <strong>D</strong> works for <strong>B</strong><br/>
          </div>
        </div>
      </div>

     <div class="clearboth padded">
        <div class="bordered">
          <h3 class="centered strong underlined">
            Tech Information
          </h3>

          <div class="padded pull-left" style="width: 32%;">
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

          <div class="padded pull-left" style="width: 32%">
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

          <div class="padded pull-left" style="width: 32%">
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
      </div>

      <div class="clearboth"></div>

     <div class="clearboth padded">
        <div class="bordered padded">
          <h3 class="centered strong">
            Timing Information for
            <xsl:value-of select="entrant/name/firstname"/>
            <xsl:text> </xsl:text>
            <xsl:value-of select="entrant/name/lastname"/>
            <br/>

            <xsl:value-of select="competition/car/year"/>
            <xsl:text> </xsl:text>
            <xsl:value-of select="competition/car/make"/>
            <xsl:text> </xsl:text>
            <xsl:value-of select="competition/car/model"/>

            [<xsl:value-of select="competition/car/class"/>/<xsl:value-of select="competition/car/category"/>]

            (<xsl:value-of select="competition/car/color"/>)
          </h3>
          <div class="bordered padded">
            <table border="1" cellpadding="5" cellspacing="0">
              <tr>
                <td><strong>Competition</strong></td>
                <td width="125">Run 1</td>
                <td width="125">Run 2</td>
                <td width="125">Run 3</td>
                <td width="125">Run 4</td>
                <td width="125">Run 5</td>
                <td width="145">Best</td>
              </tr>
              <tr>
                <td height="40">Raw Time</td>
                <td><xsl:text>&#160;</xsl:text></td>
                <td><xsl:text>&#160;</xsl:text></td>
                <td><xsl:text>&#160;</xsl:text></td>
                <td><xsl:text>&#160;</xsl:text></td>
                <td><xsl:text>&#160;</xsl:text></td>
                <td rowspan="3"><xsl:text>&#160;</xsl:text></td>
              </tr>
              <tr>
                <td height="40">Penalty (Sec)</td>
                <td><xsl:text>&#160;</xsl:text></td>
                <td><xsl:text>&#160;</xsl:text></td>
                <td><xsl:text>&#160;</xsl:text></td>
                <td><xsl:text>&#160;</xsl:text></td>
                <td><xsl:text>&#160;</xsl:text></td>
              </tr>
              <tr>
                <td height="40">Total Time</td>
                <td><xsl:text>&#160;</xsl:text></td>
                <td><xsl:text>&#160;</xsl:text></td>
                <td><xsl:text>&#160;</xsl:text></td>
                <td><xsl:text>&#160;</xsl:text></td>
                <td><xsl:text>&#160;</xsl:text></td>
              </tr>
            </table>
          </div>

          <h3 class="centered strong">

            <xsl:choose>

              <xsl:when test="time-only">

                <xsl:choose>
                  <xsl:when test="time-only/car/@competition-car">

                    <xsl:value-of select="competition/car/year"/>
                    <xsl:text> </xsl:text>
                    <xsl:value-of select="competition/car/make"/>
                    <xsl:text> </xsl:text>
                    <xsl:value-of select="competition/car/model"/>
                    [<xsl:value-of select="competition/car/class"/>/TO]
                    (<xsl:value-of select="competition/car/color"/>)

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

          </h3>

          <div class="bordered padded">
            <table border="1" cellpadding="5" cellspacing="0">
              <tr>
                <td><strong>Time Only</strong></td>
                <td width="125">Run 1</td>
                <td width="125">Run 2</td>
                <td width="125">Run 3</td>
                <td width="125">Run 4</td>
                <td width="125">Run 5</td>
                <td width="145">Best</td>
              </tr>
              <tr>
                <td height="40">Raw Time</td>
                <td><xsl:text>&#160;</xsl:text></td>
                <td><xsl:text>&#160;</xsl:text></td>
                <td><xsl:text>&#160;</xsl:text></td>
                <td><xsl:text>&#160;</xsl:text></td>
                <td><xsl:text>&#160;</xsl:text></td>
                <td rowspan="3"><xsl:text>&#160;</xsl:text></td>
              </tr>
              <tr>
                <td height="40">Penalty Seconds</td>
                <td><xsl:text>&#160;</xsl:text></td>
                <td><xsl:text>&#160;</xsl:text></td>
                <td><xsl:text>&#160;</xsl:text></td>
                <td><xsl:text>&#160;</xsl:text></td>
                <td><xsl:text>&#160;</xsl:text></td>
              </tr>
              <tr>
                <td height="40">Total Time</td>
                <td><xsl:text>&#160;</xsl:text></td>
                <td><xsl:text>&#160;</xsl:text></td>
                <td><xsl:text>&#160;</xsl:text></td>
                <td><xsl:text>&#160;</xsl:text></td>
                <td><xsl:text>&#160;</xsl:text></td>
              </tr>
            </table>
          </div>

        </div>
     </div>

    </div><!-- / form -->
    <div style="clear: both; page-break-after: always;" />

  </xsl:template>

</xsl:stylesheet>
