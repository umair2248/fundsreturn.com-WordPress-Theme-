<?php
/** Local adapter with isolated WordPress doubles. Not included in the theme ZIP. */
define('ABSPATH',__DIR__);define('DAY_IN_SECONDS',86400);define('MINUTE_IN_SECONDS',60);
$shortcodes=array();$loops=false;
$slugs=array('home','about','faq','contact','privacy-policy','terms-of-use');
if(PHP_SAPI==='cli'){$_GET['lang']=$argv[1]??'en';$_GET['page']=$argv[2]??'home';}
$page=isset($_GET['page'])&&in_array($_GET['page'],$slugs,true)?$_GET['page']:'home';
if(isset($_GET['cookie_test'])){unset($_GET['lang']);$_COOKIE['cfr_language']='nl';}
function add_action($hook,$callback,$priority=10,$args=1){}
function add_filter($hook,$callback,$priority=10,$args=1){$GLOBALS['test_filters'][$hook][]=$callback;}
function add_shortcode($name,$callback){$GLOBALS['shortcodes'][$name]=$callback;}
function do_shortcode($html){return preg_replace_callback('/\[([a-z_]+)([^\]]*)\]/',function($m){$callback=$GLOBALS['shortcodes'][$m[1]]??null;if(!$callback)return $m[0];$attrs=array();preg_match_all('/([a-z_]+)="([^"]*)"/',$m[2],$pairs,PREG_SET_ORDER);foreach($pairs as $p)$attrs[$p[1]]=$p[2];return call_user_func($callback,$attrs);},$html);}
function shortcode_atts($defaults,$attrs){return array_merge($defaults,$attrs);}
function get_template_directory(){return dirname(__DIR__).'/confidential-fund-recovery';}
function get_template_directory_uri(){return '/confidential-fund-recovery';}
function get_theme_mod($key,$default=''){return $GLOBALS['test_mods'][$key]??$default;}
function get_option($key,$default=false){return $GLOBALS['test_options'][$key]??$default;}
function __($s,$d=null){return $s;}
function esc_html__($s,$d=null){return esc_html($s);}
function esc_attr__($s,$d=null){return esc_attr($s);}
function esc_html_e($s,$d=null){echo esc_html($s);}
function esc_attr_e($s,$d=null){echo esc_attr($s);}
function esc_html($s){return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');}
function esc_attr($s){return esc_html($s);}
function esc_url($s,$protocols=null){return esc_html($s);}
function sanitize_key($s){return preg_replace('/[^a-z0-9_-]/','',strtolower($s));}
function absint($value){return abs((int)$value);}
function wp_unslash($s){return stripslashes($s);}
function add_query_arg($key,$value,$url){$parts=explode('#',$url,2);$base=$parts[0];$parts2=explode('?',$base,2);parse_str($parts2[1]??'',$query);$query[$key]=$value;return $parts2[0].'?'.http_build_query($query).(isset($parts[1])?'#'.$parts[1]:'');}
function home_url($path='/'){return 'http://127.0.0.1:8765'.($path==='/'?'/tests/preview.php?page=home':$path);}
function get_page_by_path($slug){$i=array_search($slug,$GLOBALS['slugs'],true);return $i===false?null:(object)array('ID'=>$i+1);}
function get_permalink($id){$id=is_object($id)?$id->ID:$id;return 'http://127.0.0.1:8765/tests/preview.php?page='.($GLOBALS['slugs'][$id-1]??'home');}
function get_queried_object_id(){return array_search($GLOBALS['page'],$GLOBALS['slugs'],true)+1;}
function get_the_ID(){return get_queried_object_id();}
function get_post_field($field,$id){return $field==='post_name'?($GLOBALS['slugs'][$id-1]??'home'):'';}
function is_singular($type=null){return true;}
function is_front_page(){return $GLOBALS['page']==='home';}
function is_home(){return false;}
function is_admin(){return false;}
function is_ssl(){return false;}
function wp_parse_url($s,$part=-1){return parse_url($s,$part);}
function have_posts(){return !$GLOBALS['loops'];}
function the_post(){$GLOBALS['loops']=true;}
function the_ID(){echo get_the_ID();}
function get_the_title(){return ucfirst($GLOBALS['page']);}
function post_class($extra=''){echo 'class="'.esc_attr($extra).'"';}
function body_class($extra=''){echo 'class="'.esc_attr($extra).'"';}
function bloginfo($key){echo $key==='charset'?'UTF-8':'Trading Payments Recovery';}
function get_bloginfo($key){return 'Trading Payments Recovery';}
function has_custom_logo(){return false;}
function has_post_thumbnail(){return false;}
function language_attributes(){echo cfr_html_language('');}
function wp_body_open(){}
function wp_head(){$title=cfr_page_definition($GLOBALS['page'])['title'];foreach($GLOBALS['test_filters']['pre_get_document_title']??array() as $callback)$title=$callback($title);echo '<title>'.esc_html($title).'</title><link rel="stylesheet" href="/confidential-fund-recovery/assets/css/main.css"><link rel="stylesheet" href="/confidential-fund-recovery/assets/css/sections.css"><link rel="stylesheet" href="/confidential-fund-recovery/assets/css/responsive.css">';cfr_language_head();}
function wp_footer(){echo '<script src="/confidential-fund-recovery/assets/js/main.js"></script>';}
function get_header(){include get_template_directory().'/header.php';}
function get_footer(){include get_template_directory().'/footer.php';}
function wp_nav_menu($args){$slugs=$args['theme_location']==='primary'?array('about','faq','contact'):array('privacy-policy','terms-of-use');$items=array();foreach($slugs as $slug){$id=get_page_by_path($slug)->ID;$items[]=(object)array('object_id'=>$id,'title'=>$slug,'url'=>get_permalink($id));}$items=cfr_language_menu_items($items,(object)$args);echo '<ul class="'.esc_attr($args['menu_class']).'">';foreach($items as $item)echo '<li><a href="'.esc_url($item->url).'">'.esc_html($item->title).'</a></li>';echo '</ul>';}
function admin_url($path=''){return '/'.$path;}
function wp_nonce_field($action,$name='_wpnonce'){echo '<input type="hidden" name="'.esc_attr($name).'" value="preview-nonce">';}
function wp_date($f){return '2026';}
function the_content(){echo cfr_render_page_content($GLOBALS['page']);}
function wp_link_pages(){}
require get_template_directory().'/functions.php';
if(!defined('CFR_TEST_LIBRARY_ONLY')) {
    cfr_persist_language();
    include get_template_directory().($page==='home'?'/front-page.php':'/page.php');
}
