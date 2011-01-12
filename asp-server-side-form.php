<?php
/** 
* Free for all usage. No waranty whatever.
*/

/** If you want you can set your default address here */
//$site = 'http://somehost/someform.aspx';
$site = '';



// verify we have an address to which we want to submit data
if (is_array($_GET) && array_key_exists('site', $_GET))
  $site = urldecode($_GET['site']);
if (!$site)
  throw new Exception('You must define site parameter to the url where to submit the data');



// create a curl handler
$cr = curl_init($site);
// some default timeouts
curl_setopt($cr, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($cr, CURLOPT_TIMEOUT, 15);
// we usually do GET and then POST
curl_setopt($cr, CURLOPT_HTTPGET, true);
// we need the result
curl_setopt($cr, CURLOPT_RETURNTRANSFER, true);
// in case of self-signed certificates
curl_setopt($cr, CURLOPT_SSL_VERIFYPEER, false);
// follow the redirects if any
curl_setopt($cr, CURLOPT_FOLLOWLOCATION, true);
// copy the useragent of the caller
curl_setopt($cr, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);



// load the page to retreive the __VIEWSTATE and other asp stuff
$response = curl_exec($cr);
if ($response === false)
  throw new Exception("Problem reading data from $site: " . curl_error($cr));
// retreive the final url
$info = curl_getinfo($cr);
$url = $info['url'];



// parse the page to retreive all params !!! only one form expected
if (!preg_match_all('/<input[^>]+name="([^">]+)"[^>]+value="([^">]*)"[^>]*>/', $response, $groups))
  throw new Exception("No form found on $site");
// convert the array
$params = array();
foreach ($groups[1] as $k=>$v)
  $params[$v] = $groups[2][$k];
// override with the get params
$params = array_merge($params, $_GET);



// now output a form with javascript autosubmit
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>ASP server-side form auto submit</title>
  <meta name="description" content="Submits ASP server-side form using GET params">
  <meta name="author" content="Geno Roupsky">
</head>
<body>
  <form name="form" method="post" action="<?php echo $url; ?>">
<?php foreach ($params as $k=>$v): ?>
  	<input type="hidden" name="<?php echo $k;?>" value="<?php echo $v;?>" />
<?php endforeach; ?>
    <input type="submit" />
  </form>
  <script type="text/javascript">document.forms['form'].submit();</script>
</body>
</html>
