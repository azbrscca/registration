<?php
  class RemindersController extends BaseController {

    public function postAction( $request ) {

      $data = $request->parameters;

      $q = new Query("reminders");
      $q->addWhere("entrant_id",$data['entrant_id']);
      $q->addWhere("organization_id",$data['organization_id']);

      if ( $q->count() == 0 ) {
        $data['id'] = $q->insertNew($data);
        $data['updated'] = ($data['id'] != 0);
      } else if ( $q->count() == 1 ) {
        $reminder = $q->selectOne();
        $data['id'] = $reminder['id'];
        $data['updated'] = ($q->updateById($data) == 1);
      } else {
        // multiple records, which we should never have
        // delete all then insert to be safe?
      }
      return $data;
    }
  }
?>