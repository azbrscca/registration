<?php
  require_once osDir."fpdf-1.7/fpdf.php";

  class ResultsPDF extends FPDF {

    private static $event;

    function __construct( $event ) {
      parent::__construct();
      self::$event = $event;
    }

    function clean_name( $dirty_name ) {
      $clean_name = trim( $dirty_name );
      $clean_name = ucfirst( $clean_name );
      $clean_name = preg_replace( "/&#039;/", "'", $clean_name );
      $clean_name = html_entity_decode( $clean_name );
      return $clean_name;
    }

    function Footer() {
      $this->SetY(-20);
      //$this->Image( rootDir."/img/pointer-cone-40x20.png" );
      $this->SetFont( 'Arial', 'I', 8);
      $this->Cell( 0, 10, "Page ".$this->PageNo()." of {nb}",0,0,'R');
    }

    function Header() {

      $title = 
        "SCCA Participation Report - ".
        self::$event[ 'organization_name' ]." - ".
        date( "D m-d-Y", self::$event[ 'date_ts' ] );

      $this->SetFont( 'Arial', 'B', 10 );
      $this->Cell( 80 );
      $this->Cell( 30, 10, $title, 0, 0, 'C' );
      $this->Ln();
    }

    function resultsTable( $cols, $widths, $data ) {

      $this->AddPage();

      $this->SetFillColor(255,0,0);
      $this->SetTextColor(255);
      $this->SetDrawColor(128,0,0);
      $this->SetLineWidth(.1);
      $this->SetFont( 'Arial', 'B', 8 );

      $this->Cell( 10 );
      foreach( $cols as $key => $value ) {
        $this->Cell( $widths[ $key ], 7, $value, 1, 0, 'C', true );
      }
      $this->Ln();

      $fill = false;
      $this->SetFillColor(224,235,255);
      $this->SetTextColor(0);
      $this->SetFont( 'Arial', '', 8 );

      foreach( $data as $row ) {
          $this->Cell( 10 );
        foreach( $cols as $key => $value ) {
          $this->Cell( $widths[ $key ], 7, self::clean_name( $row[ $key ] ), 1, 0, 'LR', $fill );
        }
        $fill = !$fill;
        $this->Ln();
      }
    }
  }
?>
