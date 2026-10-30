const fs=require('fs'),assert=require('assert/strict'),crypto=require('crypto');
const baseline=JSON.parse(fs.readFileSync('.qa/before-recovery-content.json','utf8'));
const hash=p=>crypto.createHash('sha256').update(fs.readFileSync(p)).digest('hex');
const tags=s=>s.match(/<[^>]*>|\[cfr_[^\]]*\]|\{\{[^}]*\}\}/g);
const changes=[];
for(const [file,previous]of Object.entries(baseline.files)){
 if(hash(file)!==previous){
  assert(/\/inc\/(translations\/(en|fr|nl|de)\.(json|txt)|neutral-copy\.json|source-content\.txt)$/.test(file),'Non-content theme file changed: '+file);
  changes.push(file);
 }
}
for(const lang of ['en','fr','nl','de']){
 const data=JSON.parse(fs.readFileSync('confidential-fund-recovery/inc/translations/'+lang+'.json'));
 const previous=baseline.translations[lang];
 assert.deepEqual(data.pages.map(p=>p.slug),previous.pages.map(p=>p.slug));
 data.pages.forEach((page,i)=>{
  assert.deepEqual(tags(page.content),tags(previous.pages[i].content),'Markup/attributes/shortcodes changed: '+lang+'/'+page.slug);
  if(['privacy-policy','terms-of-use'].includes(page.slug))assert.equal(page.content,previous.pages[i].content);
 });
 assert(!/\bAI\b|\bKI\b|\bIA\b/.test(data.headings[0]),'AI remains a headline service');
 assert(!/\bAI\b|\bKI\b|\bIA\b/.test(data.ui.description),'AI remains in main brand subtitle');
}
const english=JSON.parse(fs.readFileSync('confidential-fund-recovery/inc/translations/en.json'));
assert.equal(english.headings[0],'Recover Your Stolen Funds with Legal and Forensic Authority.');
assert(english.blocks[0][0].includes('AI supports the analysis'));
assert.equal(hash('confidential-fund-recovery-theme.zip'),baseline.zipHash,'Original ZIP changed');
console.log('PASS: exact headline, supporting AI role, all four languages, unchanged markup and legal pages.');
console.log('PASS: '+changes.length+' content-only theme files changed; templates/styles/scripts/images/logos unchanged. Original ZIP SHA256 unchanged.');
