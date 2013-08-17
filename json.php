<?php
/* Shanghai Public Transportation Card Balance Inquiry API
 *
 * Author: LYONNA
 * Date: Aug 2013
 */

  define('URL', 'http://jtk.sptcc.com:8080/servlet?addr=1.1.1.1&hiddentype=index&Card_id=');
  define('PORT', 8080);
  define('UA', 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.71 Safari/537.36');

  if (isset($_GET['id']) && strlen($_GET['id'])==11 && ctype_digit($_GET['id'])) {
    $header = getbalance($_GET['id']);
    if ($header) {
      echo outputbalance($header);
    }
  }
  else {
    exit('{"error":"invalidcardid"}');
  }

  function getbalance($id) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, URL.$id);
    curl_setopt($ch, CURLOPT_PORT, PORT);
    curl_setopt($ch, CURLOPT_USERAGENT, UA);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $header = curl_exec($ch);
    curl_close($ch);

    return $header;
  }

  function outputbalance($header) {
    if (preg_match('/type=notvalidcardid/', $header)) {
      exit('{"error":"invalidcardid"}');
    }
    else {
      preg_match('/card_num=([\w]*=)&/', $header, $id);
      preg_match('/time=([\w]*=)&/', $header, $time);
      preg_match('/card_balance=([\w]*=)/', $header, $balance);

      $balanceinfo = array();
      $balanceinfo['card_id'] = base64_decode($id[1]);
      $balanceinfo['time'] = strtotime(base64_decode($time[1]));
      $balanceinfo['balance'] = base64_decode($balance[1]) * 0.01;

      return json_encode($balanceinfo);
    }
  }
?>
