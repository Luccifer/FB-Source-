<?php
/*
* @author Mark Slee
*
* @package ubersearch
*/

ini_set('memory_limit', '100M'); // to be safe we are increasing the memory limit for search

include_once $_SERVER['PHP_ROOT'].'/html/init.php'; // final lib include
include_once $_SERVER['PHP_ROOT'].'/lib/s.php';
include_once $_SERVER['PHP_ROOT'].'/lib/browse.php';
include_once $_SERVER['PHP_ROOT'].'/lib/events.php';
include_once $_SERVER['PHP_ROOT'].'/lib/websearch_classifier/websearch_classifier.php';

flag_allow_guest();
$user = search_require_login();

if ($_POST) {
  $arr = us_flatten_checkboxes($_POST, array('ii'));
  $qs = '?';
  foreach($arr as $key = > $val) {
    $qs. = $key.'='.urlencode($val).'&';
  }
  $qs = substr($qs, 0, (strlen($qs) - 1));
  redirect($_SERVER['PHP_SELF'].$qs);
}

// If they performed a classmates search, these values are
// needed to pre-populate dropdowns
param_get_slashed(array('hy' = > $PARAM_STRING, 'hs' = > $PARAM_INT, 'adv' = > $PARAM_EXISTS, 'events' = > $PARAM_EXISTS, 'groups' = > $PARAM_EXISTS, 'classmate' = > $PARAM_EXISTS, 'coworker' = > $PARAM_EXISTS));
$pos = strpos($get_hy, ':');
if ($pos !== false) {
  $hsid = intval(substr($get_hy, 0, $pos));
  $hsyear = intval(substr($get_hy, $pos + 1));
} else {
  $hsid = intval($get_hs);
  $hsyear = null;
}

tpl_set('hs_id', $hsid);
tpl_set('hs_name', get_high_school($hsid));
tpl_set('hs_year', $hsyear);
tpl_set('is_advanced_search', $get_adv);
tpl_set('user', $user);
tpl_set('count_total', 0); // pre-set count_total for the sake of ads page length

// Events search calendar data
param_get(array('k' = > $PARAM_HEX, 'n' = > $PARAM_SINT));

if (($get_k == search_module::get_key(SEARCH_MOD_EVENT, SEARCH_TYPE_AS))) {

  $EVENTS_CAL_DAYS_AHEAD = 60;
  $events_begin = strftime("%Y%m01"); // first of the month
  $events_end = strftime("%Y%m%d", strtotime(strftime("%m/01/%Y")) + (86400 * $EVENTS_CAL_DAYS_AHEAD));
  $events_params = array('dy1' = > $events_begin, 'dy2' = > $events_end);

  param_get(array('c1' = > $PARAM_INT, 'c2' = > $PARAM_INT), 'evt_');
  if (isset($evt_c1)) {
    $events_params['c1'] = $evt_c1;
  }
  if (isset($evt_c2)) {
    $events_params['c2'] = $evt_c2;
  }
  $results = events_get_calendar($user, $get_n, $events_params);
  tpl_set('events_date', $results['events_date']);
}




// Holy shit, is this the cleanest fucking frontend file you've ever seen?!
ubersearch($_GET, $embedded = false, $template = true);

// Render it
render_template($_SERVER['PHP_ROOT'].'/html/s.phpt');

/**
 * login function for s.php
 *
 * @author Philip Fung
 */

function search_require_login() {

  //check if user is logged in
  $user = require_login(true);

  if($user 0 && !is_unregistered($user)) { return $user; }

  // this is an unregistered user
  param_get(
    array('k' = > $GLOBALS['PARAM_HEX'], // search key (used by rest of ubersearch code)
  ));

  global $get_k;
  $search_key = $get_k;

  //Let user see event or group search if criteria are obeyed
  if ($search_key && (search_module::get_key_type($search_key) == SEARCH_MOD_EVENT || search_module::get_key_type($search_key) == SEARCH_MOD_GROUP) //event or group search
  ) {
    return $user;
  } else {
    go_home();
  }
}
