<?php
define('CFR_TEST_LIBRARY_ONLY',true);
function current_user_can($cap){return $GLOBALS['allowed']??true;}
function selected($a,$b,$echo=true){$s=$a===$b?'selected':'';if($echo)echo $s;return $s;}
function set_theme_mod($key,$value){$GLOBALS['test_mods'][$key]=$value;}
function update_option($key,$value,$autoload=null){$GLOBALS['test_options'][$key]=$value;}
function delete_option($key){unset($GLOBALS['test_options'][$key]);}
function sanitize_text_field($value){return trim(strip_tags($value));}
function sanitize_textarea_field($value){return trim(strip_tags($value));}
function sanitize_email($value){return filter_var($value,FILTER_SANITIZE_EMAIL);}
function esc_url_raw($value){return preg_match('#^https?://#',$value)?$value:'';}
function wp_enqueue_media(){$GLOBALS['media_enqueued']=true;}
function wp_enqueue_script($handle,$src,$deps,$version,$footer){}
function wp_editor($content,$id,$args){$GLOBALS['editor_args']=$args;echo '<textarea>'.esc_html($content).'</textarea>';}
function submit_button($label,$type='primary',$name='submit'){echo '<button name="'.esc_attr($name).'">'.esc_html($label).'</button>';}
function check_admin_referer($action){$GLOBALS['checked_nonce']=$action;}
function wp_attachment_is_image($id){return 42===$id;}
require __DIR__.'/preview.php';
function check($condition,$label){if(!$condition)throw new Exception($label);echo "PASS: $label\n";}
$_GET['lang']='en';$_SERVER['REQUEST_METHOD']='GET';
foreach(array('header','footer','home') as $area){
 $_GET['content_page']=$area;$_GET['content_language']='en';ob_start();cfr_content_admin_screen();$html=ob_get_clean();
 check(strpos($html,'value="header"')!==false&&strpos($html,'value="footer"')!==false,'Header/Footer appear in the '.$area.' editor selector');
 if('footer'===$area)check(strpos($html,'name="disclaimer"')!==false&&strpos($html,'name="shared_email"')!==false,'Footer exposes disclaimer and contact details');
 if('home'===$area)check(strpos($html,'data-cfr-select-image="hero"')!==false&&$GLOBALS['editor_args']['media_buttons']===true,'Home exposes Media Library controls and Add Media');
}
$_GET['content_page']='header';$_SERVER['REQUEST_METHOD']='POST';$_POST=array('ui_brand_line'=>'Edited header line','ui_assess'=>'Discuss a case','shared_firm_name'=>'Example Firm');
ob_start();cfr_content_admin_screen();ob_end_clean();
check($GLOBALS['checked_nonce']==='cfr_branding_header_en','Header save verifies the language/area nonce');
check(cfr_t('brand_line')==='Edited header line'&&cfr_t('assess')==='Discuss a case','Saved header copy renders through the live translation helper');
check(cfr_firm_name()==='Example Firm','Firm name is editable in Theme Content');
$_GET['content_page']='footer';$_POST=array('disclaimer'=>'[FIRM NAME] provides case-support services.','description'=>'Edited footer description','shared_email'=>'office@example.test','shared_address'=>"Example address\nSecond line",'service_4'=>'Edited crypto link');
ob_start();cfr_content_admin_screen();ob_end_clean();
check($GLOBALS['checked_nonce']==='cfr_branding_footer_en','Footer save verifies the language/area nonce');
check(cfr_notice()==='Example Firm provides case-support services.','Disclaimer replaces the editable firm token');
check(strpos(cfr_contact_details(),'office@example.test')!==false&&cfr_footer_service_label(4)==='Edited crypto link','Saved contact and service labels appear publicly');
$_GET['lang']='fr';check(cfr_t('brand_line')!=='Edited header line','Header translations remain independent');
$GLOBALS['allowed']=false;ob_start();cfr_content_admin_screen();$denied=ob_get_clean();check($denied==='','Unauthorized visitors cannot render or save content controls');
