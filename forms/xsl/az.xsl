<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:param name="owner" select="'Registration'"/>
  <xsl:output method="html" encoding="iso-8859-1" indent="no"/>

  <xsl:template match="/">
  <html>
    <head>
      <title>Registration Forms</title>
      <link rel="stylesheet" type="text/css" href="/forms/css/az.css" media="screen, print" />
    </head>
    <body>
      <xsl:apply-templates/>
    </body>
  </html>
  </xsl:template>

  <xsl:template match="form">

    <div class="form bordered padded">

      <div>
        <div class="pull-left" style="margin-left: 10px;">
          <h3><em>Arizona Region SCCA Solo 2 Entry Form</em></h3>
        </div>

        <div class="bordered padded pull-right" style="font-size: 18px; margin-right: 25px;">
          Event Date:
          <strong>
            <xsl:text>&#160;</xsl:text>
            <xsl:value-of select="../../@formatteddate"/>
          </strong>
        </div>

      </div>

      <div class="clearboth leftbar bordered padded pull-left">

        <div class="bordered padded">
          <h3 class="strong underlined">
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

          City/State/MD:
          <xsl:value-of select="entrant/address/city"/>
          <xsl:text> </xsl:text>
          <xsl:value-of select="entrant/address/state"/>
          <xsl:text> </xsl:text>
          <xsl:value-of select="entrant/address/zipcode"/>
          <br/>

          Phone:
          <xsl:value-of select="entrant/phonenumbers/home"/>
          <br/>

          Dr. Lic #:
          <span style="float: right;">
            Minor?
            <xsl:value-of select="entrant/minor"/>
          </span>
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

        <div class="bordered padded">
          <strong>Tech Inspection:</strong> Bring your car to tech ready to run with all
          loose items removed, tire pressure adjusted, and wheel lugs checked and tightened.
          If you have a helmet, bring it with you to tech.
          <br/><br/>
          <xsl:text>&#160;</xsl:text>____________________________________
          <br/>
          <div class="padded" style="font-size: 12px;">
            Signature of Tech Inspector<br/><br/>
            I certify that I have inspected this vehicle with the Arizona Region SCCA Solo 2 Tech Inspection Checklist
          </div>
        </div>

        <div class="bordered padded" style="font-size: 16px; text-indent: 10px;">
          <p>
            By pre-registering and checking the 'I have read and understood all the regulations...'
          check box on the online registration form, you have sgreed to abide by all SCCA
          and AZ Solo regulations. No additional signature is required.
          </p>
          <p>
            Make checks payable to “AZ Region SCCA Solo 2”
          </p>
        </div>
      </div> <!-- / leftbar -->

      <div class="rightbar bordered padded pull-left">

        <div class="bordered padded">
          <h3 class="strong underlined">
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
          <br/>

          Engine:
          <xsl:value-of select="competition/car/engine"/>
          <br/>

          Tire Brand:
          <xsl:value-of select="competition/car/tire_brand"/>
          <xsl:text> </xsl:text>
          <xsl:value-of select="competition/car/tire_size"/>
          <br/>

          Tire Type:
          <xsl:value-of select="competition/car/tire_type"/>
          <br/>

          Codriver:
          <xsl:value-of select="registration/codriver"/>
          <br/>

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
          <h3>Car Number</h3>
        </div>

        <div class="bordered centered">
          <h1>
            <xsl:value-of select="competition/car-number"/><br/>
            <xsl:if test="time-only">
              <xsl:value-of select="time-only/car-number"/>
            </xsl:if>
          </h1>
        </div>


        <div class="bordered centered underlined">
          <h3>Work Assignment</h3>
        </div>

        <div class="bordered centered">
          <h2>
            <xsl:value-of select="competition/work-assign"/><br/>

            <xsl:if test="time-only">
              <xsl:value-of select="time-only/work-assign"/><br/>
            </xsl:if>
          </h2>
        </div>

      </div> <!-- / rightbar -->

    </div><!-- / form -->

    <div style="clear: both; page-break-after: always;" />

  </xsl:template>

</xsl:stylesheet>
