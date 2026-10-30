const fs=require('fs'),path=require('path'),assert=require('assert/strict');
const {execFileSync}=require('child_process');
const php=process.env.PHP_BINARY||'C:\\xampp\\php\\php.exe';
const root=path.join(__dirname,'../confidential-fund-recovery');
const walk=d=>fs.readdirSync(d,{withFileTypes:true}).flatMap(e=>e.isDirectory()?walk(path.join(d,e.name)):[path.join(d,e.name)]);
for(const f of walk(root).filter(f=>f.endsWith('.php')))execFileSync(php,['-l',f]);
execFileSync(process.execPath,['--check',path.join(root,'assets/js/main.js')]);
execFileSync(process.execPath,['--check',path.join(root,'assets/js/content-admin.js')]);
const source=fs.readFileSync(path.join(__dirname,'../content-source.md'),'utf8').trim().replace(/\r/g,'');
const enBlocks=source.split(/\n\s*\n/).map(b=>b.trim().split('\n').map(s=>s.trim()).filter(Boolean));
const slugs=['home','about','faq','contact','privacy-policy','terms-of-use'];
for(const lang of ['en','fr','nl','de']){
 const data=JSON.parse(fs.readFileSync(path.join(root,'inc/translations/'+lang+'.json')));
 assert.equal(data.blocks.length,20);assert.equal(data.headings.length,20);assert.equal(data.pages.length,6);
 assert.deepEqual(data.pages.map(p=>p.slug),slugs);
 assert.deepEqual(Object.keys(data.ui),Object.keys(JSON.parse(fs.readFileSync(path.join(root,'inc/translations/en.json'))).ui));
 const home=data.pages[0].content;
 assert.equal((home.match(/data-home-section=/g)||[]).length,19);
 let previous=-1;
 data.blocks.forEach((block,i)=>{
   assert.equal(block.length,enBlocks[i].length,lang+' block line count');
   const first=block[0].replaceAll('&','&amp;');const position=home.indexOf(first);assert(position>previous,lang+' order '+i);previous=position;
   for(const line of block){const text=line.replace(/^- /,'').replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;');assert(home.includes(text),lang+' omitted '+line);}
 });
 if(lang==='en')assert.deepEqual(data.blocks,enBlocks,'English source must be exact');
 for(const page of data.pages){
  for(const m of page.content.matchAll(/\{\{([^}]+)\}\}(?:#([a-z0-9-]+))?/g)){assert(slugs.includes(m[1]));if(m[2]&&m[2]!=='case-form')assert(data.pages.find(p=>p.slug===m[1]).content.includes('id="'+m[2]+'"'));}
  const html=execFileSync(php,[path.join(__dirname,'preview.php'),lang,page.slug],{encoding:'utf8'});
  assert(!/Fatal error|Warning:|Notice:/.test(html),lang+' '+page.slug+' PHP runtime output');
  assert(html.includes('lang="'+({en:'en-GB',fr:'fr-FR',nl:'nl-NL',de:'de-DE'}[lang])+'"'));
  assert(html.includes('>'+data.ui.about+'</a>'));
  assert(html.includes('lang='+lang),lang+' internal language persistence');
  for(const code of ['en','fr','nl','de'])assert(html.includes('page='+page.slug+'&amp;lang='+code));
  assert(!/aria-disabled|translations pending|\{\{|\[cfr_/.test(html));
  if(page.slug==='contact')assert(html.includes(data.ui.form_title)&&html.includes('name="case_language" value="'+lang+'"'));
  if(page.slug==='home'){assert.equal((html.match(/class="editorial-photo"/g)||[]).length,9,'nine relevant image placements');assert.equal((html.match(/class="section-symbol"/g)||[]).length,0,'eyebrow icons removed from all sections');}
  if(page.slug==='about')assert(html.includes('Boardroom%20ledger%20review-5.webp'));
  assert(!html.includes('[FIRM NAME]')&&!html.includes('[ ]'));
  if(lang==='en')assert(html.includes('FUNDS RETURN provides financial investigation, forensic analysis, asset tracing, and case-support services.'));
  assert(html.includes('DISCLAIMER'));
  assert(!html.includes('English · French · Dutch')&&!html.includes('Anglais · Français · Néerlandais'));
  if(page.slug==='home')assert(html.includes(data.ui.site_title.replaceAll('&','&amp;')));
  if(page.slug==='contact')assert(html.includes('Confidential%20Financial%20Case%20Review-6.webp'),'Contact image is visible');
  if(['privacy-policy','terms-of-use'].includes(page.slug))assert(html.includes('class="legal-draft"')&&html.includes('class="legal-sections"'));
  const visible=html.replace(/<div id="important-notice"[\s\S]*?<\/div>/,'').replace(/<[^>]*>/g,' ');
  assert(!/\btrading\b|\binvestments?\b|investissement|belegging|anlagebetrug|anlageplattform|anlageangebote|\bforex\b|\bbrokers?\b|courtiers?|courtage|handelskont/i.test(visible),'Trading/investment terminology remains in '+lang+'/'+page.slug);
 }
 console.log('PASS: '+lang+' all 20 ordered blocks, full copy, 6 rendered pages, translated navigation/form and equivalent-page switcher');
}
console.log(execFileSync(php,[path.join(__dirname,'setup-v2.php')],{encoding:'utf8'}).trim());
console.log(execFileSync(php,[path.join(__dirname,'branding.php')],{encoding:'utf8'}).trim());
console.log(execFileSync(php,[path.join(__dirname,'language-default.php')],{encoding:'utf8'}).trim());
for(const [scenario,status]of Object.entries({valid:'sent',nonce:'expired',email:'invalid',short:'invalid',long:'invalid',array:'invalid',consent:'invalid',honeypot:'invalid',method:'expired',rate:'limited','mail-fail':'failed',redirect:'sent'})){
 const r=JSON.parse(execFileSync(php,[path.join(__dirname,'form.php'),scenario],{encoding:'utf8'}));
 assert(r.url.includes('assessment='+status));assert(r.url.includes('lang=de'));assert.equal(r.code,303);assert(!r.url.includes('test@'));assert.equal(Boolean(r.mail),['valid','mail-fail','redirect'].includes(scenario));
 console.log('PASS: form '+scenario+' → '+status+'; selected language preserved');
}
console.log('All automated checks passed. API doubles do not replace live WordPress/email testing.');
