<?php 

require_once('../../../interface/globals.php');
require_once('../../../library/appointments.inc.php');

$events = array();

$fetchedEvents = fetchAllEvents('0000-00-00', '2040-01-01');

foreach($fetchedEvents as $row) {
  
  // skip cancelled appointments
  if ($GLOBALS['display_canceled_appointments'] != 1) {
     if ($row['pc_apptstatus'] == "x") { continue; }
  }
  
  $e = array();
  $e = $row;
  $e['id'] = $row['pc_eid'];
  $e['resourceId'] = $row['pc_aid'];
  $e['title'] = $row['pc_title'];
  $e['start'] = $row['pc_eventDate'] . " " . $row['pc_startTime'];
  $e['end'] = $row['pc_eventDate'] . " " . $row['pc_endTime'];
  $e['allDay'] = ($e['pc_alldayevent'] == 1) ? true : false;
  $e['color'] = $row['pc_catcolor'];
  
  if($row["pc_pid"] > 0) {
    $e['description'] = $row['pc_apptstatus'] . " " . $row['lname'] . ", " . $row['fname'] . " (" . $row['pc_title'];
    if(!empty($row["pc_hometext"])) {
      $e['description'] = $e['description'] . ": " . $row["pc_hometext"];
    }
    $e['description'] = $e['description'] . ")";
    switch($GLOBALS['calendar_appt_style']) {
      case 1:
        $e['title'] = $row['pc_apptstatus'] . " " . $row['lname'];
        break;
      case 2:
        $e['title'] = $row['pc_apptstatus'] . " " . $row['lname'] . ", " . $row['fname'];
        break;
      case 3:
        $e['title'] = $row['pc_apptstatus'] . " " . $row['lname'] . ", " . $row['fname'] . " (" . $row['pc_title'] . ")";
        break;
      case 4:
        $e['title'] = $e['description'];  // Case 4 is exactly the same as the event tooltip
        break;
      default:
        $e['title'] = $row['pc_apptstatus'] . " " . $row['lname'] . ", " . $row['fname'];
    }
  } else {
    $e['description'] = $row['pc_title'];
  }
  // Merge the event array into the return array
  array_push($events, $e);
}

// Set a background event to indicate provider shifts
foreach($events as $eStart) {
  if($eStart['pc_catid'] == 2) {
    foreach($events as $eEnd) {
      if($eStart['pc_aid'] == $eEnd['pc_aid'] && $eEnd['pc_catid'] == 3 && $eStart['pc_eventDate'] == $eEnd['pc_eventDate']) {
        $e = array();
        $e['start'] = $eStart['start'];
        $e['end'] = $eEnd['start'];
        $e['resourceId'] = $eStart['resourceId'];
        // $e['color'] = 'gray';
        // $e['className'] = 'fc-business';
        $e['rendering'] = 'background';
        array_push($events, $e);
      }
    }
  }
}

// Output json for our calendar
echo json_encode($events);
exit();

  
?>
