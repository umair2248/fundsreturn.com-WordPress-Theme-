<?php
define('CFR_TEST_LIBRARY_ONLY',true);
require __DIR__.'/preview.php';
function check($condition,$label){if(!$condition)throw new Exception($label);echo "PASS: $label\n";}
unset($_GET['lang'],$_COOKIE['cfr_language']);
check(cfr_current_language()==='fr','new visitor defaults to French');
check(cfr_html_language('')==='lang="fr-FR"','default HTML language is French');
check(cfr_t('about')==='À propos','default visible UI is French');
check(strpos(cfr_render_page_content('home'),cfr_translation_data('fr')['headings'][0])!==false,'default Home body is French');
$_GET['lang']='invalid';check(cfr_current_language()==='fr','invalid language falls back to French');
$_GET['lang']=array('en');check(cfr_current_language()==='fr','array language falls back safely');
unset($_GET['lang']);$_COOKIE['cfr_language']='nl';check(cfr_current_language()==='fr','bare URL defaults to French even with a previous language cookie');
$_GET['lang']='en';check(cfr_current_language()==='en','explicit English URL overrides the cookie');
$old='<a href="/?page_id=5#investment">Investment Fraud</a><p>Fraudulent Brokers &amp; Forex</p>';
$clean=cfr_clean_public_copy($old,'en');
check(strpos($clean,'href="/?page_id=5#investment"')!==false,'terminology cleanup preserves links and anchors');
check(strpos($clean,'Brokers')===false&&strpos($clean,'Forex')===false,'HTML-encoded old headings are neutralized too');
check(strpos(cfr_clean_public_copy('Trading Payments Recovery','en'),'Trading')===false,'old saved temporary brand is neutralized');
check(strpos(cfr_clean_public_copy('Investment Fraud','en'),'Financial Scheme Investigation')!==false,'old saved section heading is neutralized');
check(cfr_clean_public_copy('Payments Recovery','en')==='FUNDS RETURN','old platform name is replaced');
check(strpos(cfr_clean_public_copy('<p>English · French · Dutch</p>','en'),'English')===false,'highlighted static language line is removed from saved content');
check(strpos(cfr_effective_page_content('privacy-policy','en','[LEGAL CONTENT PLACEHOLDER]'),'class="legal-draft"')!==false,'old legal placeholders upgrade to the editable draft');
