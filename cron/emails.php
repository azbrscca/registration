<?php
  $path = realpath(dirname(__FILE__));

  require_once $path."/../common/Common.php";
  require_once $path."/../common/Emailer.php";
  require_once $path."/../db/Query.php";

  function eventCloseReminders($delta) {

    $now = time();
    $target = $now + $delta*60*60;
    $nowDt = date( "Y-m-d H:i:s", $now);
    $targetDt = date( "Y-m-d H:i:s", $target);

    $q = new Query("events");
    $q->addWhere("public",1)
      ->addWhere("registration_close", $targetDt, "<")
      ->addWhere("registration_open", $nowDt, "<")
      ->addWhere("registration_close", $nowDt, ">")
      ->addOrder("date","desc")
      ->timestamps(true)
      ->joinOn("event_type",array("name"))
      ->joinOn("organization",array("name","shortname"))
      ->joinOn("site",array("name"));

    $events = $q->select();

    foreach( $events as $event ) {

      $event_reminder = getOrCreateEventReminder($event['id']);
      $send_reminder = ($event_reminder['event_close'] == "0");

      if ($send_reminder) {
        $q = new Query("reminders");
        $q->addWhere("organization_id",$event['organization_id'])
          ->addWhere("event_open",1)
          ->joinOn("entrant");
        $reminders = $q->select();

        $event_reminder['event_close'] = 1;
        updateEventRedminer($event_reminder);

        $subject = 
          "Online registration closing soon for "
          .$event['organization_shortname']." "
          .$event['event_type_name']." on "
          .date( "l F, j", $event['date_ts']);

        $message = getEmailMessage($event,"is closing soon");

        $e = new Emailer();
        $e->addRecipient( "noreply@azbrscca.org", "noreply@azbrscca.org" )
          ->setSubject($subject)
          ->setMessage($message);

        foreach($reminders as $reminder) {
          $e->addBcc( $reminder[ 'entrant_name_first' ].' '.$reminder[ 'entrant_name_last' ],
                      $reminder[ 'entrant_email' ] );
        }

        $e->send();
      }
    }
  }

  function eventOpenReminders($delta) {

    $now = time();
    $target = $now - $delta*60*60;
    $nowDt = date( "Y-m-d H:i:s", $now);
    $targetDt = date( "Y-m-d H:i:s", $target);

    $q = new Query("events");
    $q->addWhere("public","1")
      ->addWhere("registration_open", $targetDt, ">")
      ->addWhere("registration_open", $nowDt, "<")
      ->addWhere("registration_close", $nowDt, ">")
      ->addOrder("date","desc")
      ->timestamps(true)
      ->joinOn("event_type",array("name"))
      ->joinOn("organization",array("name","shortname"))
      ->joinOn("site",array("name"));

    $events = $q->select();

    foreach( $events as $event ) {

      $event_reminder = getOrCreateEventReminder($event['id']);
      $send_reminder = ($event_reminder['event_open'] == "0");

      if ($send_reminder) {
        $q = new Query("reminders");
        $q->addWhere("organization_id",$event['organization_id'])
          ->addWhere("event_open",1)
          ->joinOn("entrant");
        $reminders = $q->select();

        $event_reminder['event_open'] = 1;
        updateEventRedminer($event_reminder);

        $subject = 
          "Online registration now open for "
          .$event['organization_shortname']." "
          .$event['event_type_name']." on "
          .date( "l F, j", $event['date_ts']);

        $message = getEmailMessage($event,"is now open");

        $e = new Emailer();
        $e->addRecipient( "noreply@azbrscca.org", "noreply@azbrscca.org" )
          ->setSubject($subject)
          ->setMessage($message);

        foreach($reminders as $reminder) {
          $e->addBcc( $reminder[ 'entrant_name_first' ].' '.$reminder[ 'entrant_name_last' ],
                      $reminder[ 'entrant_email' ] );
        }

        $e->send();
      }
    }
  }

  function getEmailMessage($event,$status) {

    $eventDate = date( "l, F j, Y", $event[ 'date_ts' ] );
    $regCloseDate = date( "l, F j, Y", $event[ 'registration_close_ts' ] )
                    ." at "
                    .date( "g:ia", $event[ 'registration_close_ts' ] );
    $regLink = baseHref."register/".$event['id'];
    $prefsLink = baseHref."account/reminders.html";

    $message = <<<MTC
      <p>
        Online registration for the {$event[ 'organization_name' ]} {$event[ 'name' ]} {$event['event_type_name']}
        event taking place on {$eventDate} at {$event[ 'site_name' ]} {$status}!
      </p>
      <p>
        To register, click <a href="{$regLink}">here</a> or copy the link below into your web browser.
      </p>
      <p>
        {$regLink}
      </p>
      <p>
        Online registration for this event closes on {$regCloseDate}.
      </p>
      <hr/>
      <small>
        This is an automated message.
        This mailbox is not monitored and replies to this message will not be viewed or processed.
        If you no longer wish to recieve event email reminders please visit
        <a href="{$prefsLink}">the registration site</a> and update your email reminder preferences.
      </small>
MTC;

    return $message;

  }

  function getOrCreateEventReminder($event_id) {
    $q = new Query("event_reminders");
    $q->addWhere("event_id", $event_id);

    if ($q->count() == 0) {
      $row = array('event_id' => $event_id, 'event_open' => 0, 'event_close' => 0);
      $row['id'] = $q->insertNew($row);
      return $row;
    } else {
      return $q->selectOne();
    } 
  }

  function updateEventRedminer($reminder) {
    $q = new Query("event_reminders");
    return $q->updateById($reminder);
  }

  /* if not run at command line, don't process reminders
  define('CLI', PHP_SAPI === 'cli');
  if (PHP_SAPI === 'cli') {
  */

  $delta = 12; // hours
  eventOpenReminders($delta);
  eventCloseReminders($delta);

  /*
  } else {
    header( "Location: ".baseHref ) ;
  }
  */
?>
