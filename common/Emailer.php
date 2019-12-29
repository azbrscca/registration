<?php
  require_once osDir."/phpmailer/class.phpmailer.php";

  class Emailer {

    private $from = array();
    private $message;
		private $subject;
		private $to = array();
    private $cc = array();
    private $bcc = array();

    public function __construct() {
      $this->from[ 'address' ] = "noreply@azbrscca.org";
      $this->from[ 'name' ] = "Registration";
    }

    public function addBcc( $name, $address ) {
      array_push( $this->bcc, array( 'address' => $address, 'name' => $name ) );
      return $this;
    }

    public function addCc( $name, $address ) {
      array_push( $this->cc, array( 'address' => $address, 'name' => $name ) );
      return $this;
    }
	   
    public function addRecipient( $name, $address ) {
      array_push( $this->to, array( 'address' => $address, 'name' => $name ) );
      return $this;
    }
    
    public function setMessage( $message ) {
      $this->message = $message;
      return $this;
    }
    
    public function setSender( $name, $address ) {
      $this->from[ 'address' ] = $address;
      $this->from[ 'name' ] = $name;
      return $this;
    }
    
    public function setSubject( $subject ) {
      $this->subject = $subject;
      return $this;
    }
    
    public function send() {

      $mail = new PHPMailer();
      $mail->From = $this->from[ 'address' ];
      $mail->FromName = $this->from[ 'name' ];
      $mail->Subject = $this->subject;

      // blind carbon copies
      foreach( $this->bcc as $item ) {
        $mail->AddBCC( $item[ 'address' ], $item[ 'name' ] );
      }

      // carbon copies
      foreach( $this->cc as $item ) {
        $mail->AddCC( $item[ 'address' ], $item[ 'name' ] );
      }

      foreach( $this->to as $item ) {
        $mail->AddAddress( $item[ 'address' ], $item[ 'name' ] );
      }
      $mail->MsgHTML( $this->message );
      $mail->AltBody = "To view this message please use an HTML compatible email viewer";
      return $mail->Send();
    }

  } // class Emailer
?>
