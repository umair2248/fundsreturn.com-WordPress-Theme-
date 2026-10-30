import fs from 'node:fs';
import assert from 'node:assert/strict';
const targets=await(await fetch('http://127.0.0.1:9231/json/list')).json();
const target=targets.find(t=>t.type==='page');
const socket=new WebSocket(target.webSocketDebuggerUrl);
await new Promise((resolve,reject)=>{socket.onopen=resolve;socket.onerror=reject;});
let next=0;const pending=new Map();const errors=[];
socket.onmessage=event=>{const msg=JSON.parse(event.data);if(msg.id){const slot=pending.get(msg.id);if(slot){pending.delete(msg.id);if(msg.error)slot.reject(Error(JSON.stringify(msg.error)));else slot.resolve(msg.result);}}else if(msg.method==='Runtime.exceptionThrown')errors.push(msg.params.exceptionDetails);};
const send=(method,params={})=>new Promise((resolve,reject)=>{const id=++next;pending.set(id,{resolve,reject});socket.send(JSON.stringify({id,method,params}));});
await send('Page.enable');await send('Runtime.enable');await send('Network.enable');
const evaluate=async expression=>{const r=await send('Runtime.evaluate',{expression,returnByValue:true,awaitPromise:true});if(r.exceptionDetails)throw Error(JSON.stringify(r.exceptionDetails));return r.result.value;};
const navigate=async url=>{await send('Page.navigate',{url});await evaluate('new Promise(r=>{if(document.readyState==="complete")r(true);else window.addEventListener("load",()=>r(true),{once:true});})');};
fs.mkdirSync('.qa',{recursive:true});
for(const width of [1440,768,390,320]){
 await send('Emulation.setDeviceMetricsOverride',{width,height:1000,deviceScaleFactor:1,mobile:width<769});
 for(const lang of ['en','fr','nl','de']){
  for(const page of ['home','contact']){
   await navigate(`http://127.0.0.1:8765/tests/preview.php?page=${page}&lang=${lang}`);
   const m=await evaluate(`({width:innerWidth,scroll:document.documentElement.scrollWidth,sections:document.querySelectorAll('[data-home-section]').length,lang:document.documentElement.lang,h1:document.querySelector('h1').textContent,active:[...document.querySelectorAll('.language-switcher a[aria-current]')].map(x=>x.textContent),form:!!document.querySelector('.case-form'),script:document.documentElement.classList.contains('js')})`);
   assert(m.scroll<=m.width,`${page}/${lang} overflow at ${width}: ${JSON.stringify(m)}`);
   assert(m.lang.startsWith(lang));assert(m.active.every(c=>c.toLowerCase()===lang));assert(m.script);
   if(page==='home') {
    assert(await evaluate('document.querySelector(".site-image-hero img").decode().then(()=>true).catch(()=>false)'), 'Hero image must actually load');
    assert(await evaluate('getComputedStyle(document.querySelector(".home-hero")).backgroundColor==="rgb(36, 35, 34)"'), 'Graphite hero color');
   }
   if(page==='home')assert.equal(m.sections,19);else assert(m.form);
   if(lang==='en'&&page==='home'&&[1440,390].includes(width)){
    const shot=await send('Page.captureScreenshot',{format:'png',captureBeyondViewport:false});
    fs.writeFileSync(`.qa/home-${width}.png`,Buffer.from(shot.data,'base64'));
   }
   if(width===390&&page==='contact'&&lang==='en'){
    await evaluate('document.querySelector(".menu-toggle").click()');
    assert(await evaluate('document.querySelector(".menu-toggle").getAttribute("aria-expanded")==="true"'));
    await send('Input.dispatchKeyEvent',{type:'keyDown',key:'Escape',code:'Escape',windowsVirtualKeyCode:27});
    assert(await evaluate('document.querySelector(".menu-toggle").getAttribute("aria-expanded")==="false"'));
   }
  }
 }
 console.log(`PASS: browser Home/Contact in 4 languages at ${width}px; no horizontal overflow`);
}
await navigate('http://127.0.0.1:8765/tests/preview.php?page=contact&lang=fr');
await evaluate('document.querySelector(".language-switcher a[hreflang=de]").click()');
await evaluate('new Promise(r=>{if(document.readyState==="complete")r(true);else window.addEventListener("load",()=>r(true),{once:true});})');
assert(await evaluate('location.search.includes("page=contact")&&location.search.includes("lang=de")'));
await navigate('http://127.0.0.1:8765/tests/preview.php?page=about');
assert(await evaluate('document.documentElement.lang==="de-DE"'));
assert.equal(errors.length,0,JSON.stringify(errors));
console.log('PASS: actual language link changes content on the equivalent page; language cookie persists to About; keyboard Escape works; no browser exceptions.');
await send('Emulation.setDeviceMetricsOverride',{width:1440,height:1000,deviceScaleFactor:1,mobile:false});
await navigate('http://127.0.0.1:8765/tests/preview.php?page=home&lang=en');
assert(await evaluate('document.querySelector(".important-notice").textContent.includes("provides financial investigation, forensic analysis, asset tracing, and case-support services.")'));
assert(await evaluate('getComputedStyle(document.querySelector(".site-footer")).backgroundColor==="rgb(36, 35, 34)"'));
await evaluate('window.scrollTo(0,document.body.scrollHeight)');
const footerShot=await send('Page.captureScreenshot',{format:'png',captureBeyondViewport:false});
fs.writeFileSync('.qa/footer-1440.png',Buffer.from(footerShot.data,'base64'));
console.log('PASS: requested footer disclaimer and graphite footer render correctly.');
await navigate('http://127.0.0.1:8765/tests/preview.php?page=contact&lang=fr');
assert(await evaluate('document.querySelector(".contact-photo img").decode().then(()=>true).catch(()=>false)'));
assert(await evaluate('!document.querySelector("main").textContent.includes("Anglais · Français · Néerlandais")'));
const contactShot=await send('Page.captureScreenshot',{format:'png',captureBeyondViewport:false});
fs.writeFileSync('.qa/contact-1440.png',Buffer.from(contactShot.data,'base64'));
await navigate('http://127.0.0.1:8765/tests/preview.php?page=privacy-policy&lang=fr');
assert(await evaluate('document.querySelectorAll(".legal-sections>section").length===9'));
await navigate('http://127.0.0.1:8765/tests/preview.php?page=home&lang=fr');
assert(await evaluate('document.title.startsWith("FUNDS RETURN |")'));
console.log('PASS: Contact photography, highlighted-line removal, structured legal drafts and FUNDS RETURN title.');
socket.close();
