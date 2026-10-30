<?php
// One request per CLI invocation; no actual email, database or network operations.
define('ABSPATH',__DIR__);
define('MINUTE_IN_SECONDS',60);
$scenario=$argv[1]??'valid';
$_SERVER['REQUEST_METHOD']='POST';
$_SERVER['REMOTE_ADDR']='192.0.2.1';
$_POST=array('return_page'=>'10','cfr_nonce'=>'valid','case_name'=>'Test Person','case_email'=>'test@example.test','case_phone'=>'','case_summary'=>'A fraudulent platform blocked withdrawal after my transfer.','acknowledge'=>'1','website'=>'','case_language'=>'de');
if($scenario==='nonce') $_POST['cfr_nonce']='bad';
if($scenario==='email') $_POST['case_email']="test@example.test\r\nBcc: attacker@example.test";
if($scenario==='short') $_POST['case_summary']='short';
if($scenario==='long') $_POST['case_summary']=str_repeat('a',4001);
if($scenario==='array') $_POST['case_name']=array('bad');
if($scenario==='consent') unset($_POST['acknowledge']);
if($scenario==='honeypot') $_POST['website']='spam';
if($scenario==='method') $_SERVER['REQUEST_METHOD']='GET';
if($scenario==='redirect') $_POST['return_page']='999';
function add_shortcode($a,$b) {}
function cfr_languages() { return array('en'=>'English','fr'=>'Français','nl'=>'Nederlands','de'=>'Deutsch'); }
function add_action($a,$b) {}
function sanitize_text_field($v) { return trim(preg_replace('/[\r\n\t]+/',' ',strip_tags($v))); }
function sanitize_textarea_field($v) { return trim(strip_tags($v)); }
function wp_unslash($v) { return stripslashes($v); }
function absint($v) { return abs((int)$v); }
function get_post_type($id) { return $id===10?'page':'post'; }
function get_post_status($id) { return 'publish'; }
function get_permalink($id) { return 'https://example.test/?page_id='.$id; }
function cfr_page_url($slug) { return 'https://example.test/'.$slug.'/'; }
function wp_verify_nonce($value,$action) { return $value==='valid'; }
function wp_salt($key) { return 'test-salt'; }
function get_transient($key) { global $scenario; return $scenario==='rate'; }
function set_transient($key,$value,$ttl) { $GLOBALS['rate_set']=$ttl; }
function sanitize_email($email) { return filter_var($email,FILTER_SANITIZE_EMAIL); }
function is_email($email) { return filter_var($email,FILTER_VALIDATE_EMAIL); }
function get_theme_mod($key,$default='') { return $default; }
function get_option($key) { return 'admin@example.test'; }
function __($s,$d) { return $s; }
function wp_mail($to,$subject,$body,$headers) { global $scenario; $GLOBALS['mail']=array('to'=>$to,'headers'=>$headers); return $scenario!=='mail-fail'; }
function add_query_arg($key,$value,$url) { return $url.(strpos($url,'?')===false?'?':'&').$key.'='.$value; }
function wp_safe_redirect($url,$code) { echo json_encode(array('url'=>$url,'code'=>$code,'mail'=>$GLOBALS['mail']??null,'rate'=>$GLOBALS['rate_set']??null)); }
require dirname(__DIR__).'/confidential-fund-recovery/inc/forms.php';
cfr_handle_assessment();
