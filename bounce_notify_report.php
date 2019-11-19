<?php
/*
 * Name: bounce_notify_report.php   V1.0  11/16/19
 */

require_once "bounce_notify_class_bounce.php";

use Drupal\bounce_notify\BounceNotify;

/** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * bounce_notify_report
 * 
 *
 * @return $output - display.
 */
function bounce_notify_report() {
  $bounceObj = new BounceNotify();
  // get the reported non-delivery emails.
  $bounceList = $bounceObj->getBounces();
  $output = "<p>Table of undeliverable email addresses.</p>";
  if(empty($bounceList)) {return "<p>No undeliveralbe emails addresses have been reported.</p>";}
  $out = '<table style="white-space: nowrap; width:600px;">';
  $out .= '<thead><tr>
    <th style="text-align: left; width:150px;">County</th>
    <th style="width:100px;">Sender</th>
    <th style="width:100px;">NL</th>
    <th style="width:50px;">Bounced email</th>
    <th style="width:50px;">Date</th>
    <th style="width:50px;">Code</th>
    <th style="width:100px;">Description</th>
    </tr></thead><tbody>';
  $cnt = 0;
  foreach ($bounceList as $reportId => $bouncer) {
    // Report this eamil as having delivery problems.
    $cnt++;
    $cd_nl = $bouncer['recipientFirstName'].' '.$bouncer['recipientLastName'];
    $cd_coodinator = $bouncer['senderFirstName'].' '.$bouncer['senderLastName'];
    $out .= '<tr>
      <td style="text-align: left;">'.$bouncer['county'].'</td>'.
      '<td>'.$cd_coodinator.'</td>'.
      '<td>'.$cd_nl.'</td>'.
      '<td>'.$bouncer['recipientEmail'].'</td>'.
      '<td>'.$bouncer['date'].'</td>'.
      '<td>'.$bouncer['code'].'</td>'.
      '<td>'.$bouncer['description'].'</td>'.
      '</tr>';
  }
  $out .= '</tbody></table>';
  $output .= $out;
  return $output;
} 
