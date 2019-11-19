<?php
/*
 * Name: bounce_notify_class_bounce.php   V1.0  11/15/19
 */
/**
 * @file
 * Contains Drupal\bounce_notify\BounceNotify.
 */

namespace Drupal\bounce_notify;

class BounceNotify {
  const BOUNCEBLOCKTBL = 'bounce_blocked';
  const BOUNCENONDELIVERYTBL = 'bounce_non_delivery_report';
  const BOUNCECODESCORETBL = 'bounce_code_score';
  const BOUNCEREPORTNOTIFYTBL = 'bounce_notified';

  public $bounceNotifyList = array(
    'reportId' => 'report_id',
    'blockedId' => 'blocked_id',
    'notified' => 'Notified',
    'date' => 'Date',
    'county' => 'County',
    'recipientFirstName' => 'NLFirstName',
    'recipientLastName' => 'NLLastName',
    'recipientEmail' => 'NLEmail',
    'senderFirstName' => 'SFirstName',
    'senderLastName' => 'SLastName',
    'senderEmail' => 'SEmail',
    'code' => 'Code',
    'description' => 'Description',
  );
  public $bounceBlockList = array(
    'blockedId' => 'blocked_id',
    'mail' => 'mail',
    'created' => 'createed',
  );
  public $nonDeliveryList = array(
    'reportId' => 'report_id',
    'mail' => 'mail',
    'code' => 'code',
    'analyst' => 'analyst',
    'report' => 'report',
    'status' => 'status',
    'created' => 'created',
  );
  public $bounceCodeScoreList = array (
      'code' => 'code',
      'type' => 'type',
      'score' => 'score',
      'description' => 'description',
  );

  public function deleteNotification($mail) {
    try {
      db_delete(self::BOUNCEREPORTNOTIFYTBL)
        ->condition('NLEmail', $mail)
        ->execute();
    }
    catch (Exception $e) {
      $error = $e->getMessage();
      watchdog('bounce_notify','delete failed: '.$error,WATCHDOG_DEBUG);
      //nlp_debug_msg('e', $e->getMessage() );
      return;
    }
  }
  
  public function bouncedStatus($reportId) {
   try {
      $query = db_select(self::BOUNCENONDELIVERYTBL, 'n');
      $query->addField('n','report_id');
      $query->condition('report_id',$reportId);
      $result = $query->execute();
    }
    catch (Exception $e) {
      $error = $e->getMessage();
      watchdog('bounce_notify_class_bounce','select failed: '.$error,WATCHDOG_DEBUG);
      //nlp_debug_msg('e', $e->getMessage() );
      return '';
    }
    $dbbounced = $result->fetchAssoc();
    if(!empty($dbbounced)) {return TRUE;}
    return FALSE;
  }
  
  public function bouncesByMail($mail) {
   try {
      $query = db_select(self::BOUNCENONDELIVERYTBL, 'n');
      $query->fields('n');
      $query->condition('mail',$mail);
      $result = $query->execute();
    }
    catch (Exception $e) {
      $error = $e->getMessage();
      watchdog('bounce_notify','select by mail failed: '.$error,WATCHDOG_DEBUG);
      //nlp_debug_msg('e', $e->getMessage() );
      return array();
    }
    $flipList = array_flip($this->nonDeliveryList);
    $bounceList = array();
    do {
      $bouncer = $result->fetchAssoc();
      if (!$bouncer) {break;}
      $bounceInfo = array();
      foreach ($bouncer as $dbKey => $field) {
        $bounceInfo[$flipList[$dbKey]] = $field;
      }
      $bounceList[$bounceInfo['reportId']] = $bounceInfo;
    } while (TRUE);
    return $bounceList;
  }
  
  public function blockedStatus($email) {
   try {
      $query = db_select(self::BOUNCEBLOCKTBL, 'n');
      $query->addField('n','blocked_id');
      $query->condition('mail',$email);
      $result = $query->execute();
    }
    catch (Exception $e) {
      $error = $e->getMessage();
      watchdog('bounce_notify_class_bounce','select failed: '.$error,WATCHDOG_DEBUG);
      //nlp_debug_msg('e', $e->getMessage() );
      return '';
    }
    $blocked = $result->fetchAssoc();
    $bstat = ($blocked)? 'Blocked':''; 
    return $bstat;
  }
  
  public function getBounces() {
    try {
      $query = db_select(self::BOUNCEREPORTNOTIFYTBL, 'n');
      $query->orderBy('county');
      $query->orderBy('NLemail');
      $query->fields('n');
      $result = $query->execute();
    }
    catch (Exception $e) {
      $error = $e->getMessage();
      watchdog('bounce_notify_class_bounce','select failed: '.$error,WATCHDOG_DEBUG);
      //nlp_debug_msg('e', $e->getMessage() );
      return array();
    }
    $flipList = array_flip($this->bounceNotifyList);
    $bounceList = array();
    do {
      $bouncer = $result->fetchAssoc();
      if (!$bouncer) {break;}
      $bounceInfo = array();
      foreach ($bouncer as $dbKey => $field) {
        $bounceInfo[$flipList[$dbKey]] = $field;
      }
      $bounceList[$bounceInfo['reportId']] = $bounceInfo;
    } while (TRUE);
    return $bounceList;
  }
  
    public function bounceProcessedStatus($mail) {
    try {
      $query = db_select(self::BOUNCEREPORTNOTIFYTBL, 'n');
      $query->addField('n', 'Notified');
      $query->condition('NLEmail',$mail);
      $result = $query->execute();
     }
    catch (Exception $e) {
      watchdog('bounce_notify_class_bounce', 'Opps, search for report status failed');
      return 'N';
    }
    $notified_result = $result->fetchAssoc();
    $bounced_notify = (!empty($notified_result) ? $notified_result['Notified']:'N');
    return $bounced_notify;
  }
  
  public function getDescription ($code) {
    try {
      $query = db_select(self::BOUNCECODESCORETBL, 'c');
      $query->addField('c', 'description');
      $query->condition('code',$code);
      $result = $query->execute();
    }
    catch (Exception $e) {
      watchdog('bounce_notify_class_bounce', 'Opps, email failed search for bounce code.');
      return NULL;
    }
    $desc_result = $result->fetchAssoc();
    $description = (!empty($desc_result))?$desc_result['description']:'Unknown';
    return $description;
  }
  
  public function setBounceProcessedStatus ($values) {
    foreach ($this->bounceNotifyList as $nlpKey => $dbKey) {
      if(isset($values[$nlpKey])) {
        $fields[$dbKey] = $values[$nlpKey];
      }
    }
    $fields['blocked_id'] = NULL;
    $fields['Notified'] = 'Y';
    $report_id = $fields['report_id'];
    unset($fields['report_id']);
    try {
      db_merge(self::BOUNCEREPORTNOTIFYTBL)
        ->key(array('report_id' => $report_id))
        ->fields($fields)
        ->execute();
    }
    catch (Exception $e) {
      $error = $e->getMessage();
      watchdog('bounce_notify_class_bounce','notification insert failed: '.$error,WATCHDOG_DEBUG);
      return;
    }
    return;
  }
  
}
