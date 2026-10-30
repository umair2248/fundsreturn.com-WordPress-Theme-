<?php
define('ABSPATH', __DIR__); define('CFR_VERSION','2.0.0');
$posts=$options=$mods=$menus=$items=array(); $next=1;
function get_template_directory(){return dirname(__DIR__).'/confidential-fund-recovery';}
function get_option($k,$default=false){global $options;return $options[$k]??$default;}
function update_option($k,$v){global $options;$options[$k]=$v;}
function get_page_by_path($slug){global $posts;foreach($posts as $p)if($p['post_name']===$slug)return (object)$p;return null;}
function wp_insert_post($data,$error=false){global $posts,$next;$id=$next++;$posts[$id]=array_map(function($v){return is_string($v)?stripslashes($v):$v;},$data)+array('ID'=>$id);return $id;}
function wp_update_post($data){throw new Exception('Existing post must never be updated');}
function wp_trash_post($id){throw new Exception('Existing post must never be trashed');}
function wp_slash($data){return array_map(function($v){return is_string($v)?addslashes($v):$v;},$data);}
function is_wp_error($v){return false;}
function update_post_meta($id,$k,$v){}
function get_theme_mod($k,$default=null){global $mods;return $mods[$k]??$default;}
function set_theme_mod($k,$v){global $mods;$mods[$k]=$v;}
function wp_get_nav_menu_object($name){global $menus;return isset($menus[$name])?(object)array('term_id'=>$menus[$name]):false;}
function wp_create_nav_menu($name){global $menus;return $menus[$name]=count($menus)+1;}
function wp_get_nav_menu_items($id){global $items;return $items[$id]??array();}
function wp_update_nav_menu_item($menu,$id,$data){global $items;$items[$menu][]=(object)array('object_id'=>$data['menu-item-object-id'],'title'=>$data['menu-item-title']);return count($items[$menu]);}
function add_action($hook,$callback){}
function check($v,$s){if(!$v)throw new Exception($s);echo "PASS: $s\n";}
require get_template_directory().'/inc/setup.php';
cfr_create_starter_content();
check(count($posts)===6,'exactly six pages on fresh setup');
check(array_column($posts,'post_name')===array('home','about','faq','contact','privacy-policy','terms-of-use'),'only the six requested English pages');
check($options['page_on_front']===1,'sets homepage only when absent');
check(count($items[1])===3&&count($items[2])===2,'three navigation and two legal links');
$snapshot=serialize(array($posts,$options,$mods,$menus,$items));cfr_create_starter_content();
check($snapshot===serialize(array($posts,$options,$mods,$menus,$items)),'repeat setup does nothing');
unset($options['cfr_setup_v2']);$original=$posts;$options['page_on_front']=99;cfr_create_starter_content();
check($posts===$original,'existing post title and content never changed');
check($options['page_on_front']===99,'existing homepage preserved');
check(count($items[1])===3&&count($items[2])===2,'retries do not duplicate menu items');
