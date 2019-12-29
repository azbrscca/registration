<?php
  class SeriesController extends BaseController {

    public function postAction( $request ) {

      if ( empty( $request->url_elements ) ) {
        return array( 'error' => "Invalid route." );
      } else {

        if ( ( $request->orgId != apiMasterId ) &&
              empty( $request->parameters[ 'organization_id' ] ) ) {
          $request->parameters[ 'organization_id' ] = $request->orgId;
        }

        $series_id = array_shift( $request->url_elements );


        $q = new Query("series_events");
        $last_event = $q->joinOn("event", array("date"))
                        ->addWhere("series_id", $series_id)
                        ->addOrderOnJoin("events", "date", "desc")
                        ->selectOne();

        if ( !empty($last_event)) {
          $q = new Query("series");
          $series = $q->selectById($series_id);
          $series['date'] = $last_event['event_date'];
          $q->updateById($series);
        }
      }
    }  
  }
  
?>
