import fs from 'node:fs';
import assert from 'node:assert/strict';
const target=(await(await fetch('http://127.0.0.1:9231/json/list')).json()).find(t=>t.type==='page');
const socket=new WebSocket(target.webSocketDebuggerUrl);await new Promise(r=>socket.onopen=r);
let id=0;const pending=new Map();
socket.onmessage=e=>{const m=JSON.parse(e.data);if(m.id){pending.get(m.id)(m.result);pending.delete(m.id)}};
const send=(method,params={})=>new Promise(r=>{pending.set(++id,r);socket.send(JSON.stringify({id,method,params}))});
const evaluate=async expression=>{const r=await send('Runtime.evaluate',{expression,awaitPromise:true,returnByValue:true});if(r.exceptionDetails)throw Error(JSON.stringify(r.exceptionDetails));return r.result.value;};
const pause=ms=>new Promise(r=>setTimeout(r,ms));
await send('Page.enable');
for(const width of [1440,390]){
 await send('Emulation.setDeviceMetricsOverride',{width,height:1000,deviceScaleFactor:1,mobile:width<769});
 await send('Page.navigate',{url:'http://127.0.0.1:8765/tests/preview.php?page=home&lang=fr'});
 await pause(700);
 const result=await evaluate('Promise.all([...document.querySelectorAll("main .editorial-photo")].map(async img=>{img.loading="eager";return img.decode().then(()=>true).catch(()=>false)}))');
 assert.equal(result.length,9);assert(result.every(Boolean));
 assert.equal(await evaluate('new Set([...document.querySelectorAll("main .editorial-photo")].map(i=>i.src)).size'),8);
 assert.equal(await evaluate('document.querySelectorAll(".home-section .section-symbol svg").length'),0);
 assert.equal(await evaluate('document.querySelectorAll("#financial-investigation img").length'),0);
 assert(await evaluate('(()=>{const styles=[...document.querySelectorAll(".home-section p")].map(e=>getComputedStyle(e));return styles.every(s=>s.fontSize==="18px"&&s.fontFamily===styles[0].fontFamily&&s.lineHeight===styles[0].lineHeight)})()'),'uniform description typography');
 for(const section of ['financial-investigation','professional-standards','expertise','investment','transaction-intelligence','payment','asset-tracing','contact']){
  assert(await evaluate('(()=>{const s=document.getElementById("'+section+'"),shell=s.querySelector(".section-shell").getBoundingClientRect(),h=s.querySelector("h2").getBoundingClientRect(),i=s.querySelector(".section-index").getBoundingClientRect();return Math.abs((h.left+h.right-shell.left-shell.right)/2)<2&&Math.abs((i.left+i.right-shell.left-shell.right)/2)<2})()'),section+' heading and eyebrow centered');
 }
 if(width===1440){
  for(const section of ['financial-investigation','professional-standards']){
   assert(await evaluate('(()=>{const cards=[...document.querySelectorAll("#'+section+' .analysis-list article")].map(e=>e.getBoundingClientRect());return cards.length===4&&Math.abs(cards[0].top-cards[1].top)<2&&Math.abs(cards[2].top-cards[3].top)<2&&cards[2].top>cards[0].bottom})()'),section+' 2 by 2 cards');
  }
 }
 for(const section of await evaluate('[...document.querySelectorAll(".home-section")].map(s=>s.id)')){
  await evaluate('document.getElementById("'+section+'").scrollIntoView({behavior:"instant"})');
  await pause(120);
  const metrics=await evaluate('(()=>{const s=document.getElementById("'+section+'"),i=s.querySelector(".section-index").getBoundingClientRect(),h=s.querySelector("h2").getBoundingClientRect();return {above:i.bottom<=h.top,overflow:document.documentElement.scrollWidth>innerWidth}})()');
  assert(metrics.above,section+' number must sit above its heading');assert(!metrics.overflow,section+' overflow');
 }
 const reveals=await evaluate('document.querySelectorAll(".reveal-ready").length');
 for(let index=0;index<reveals;index++){
  await evaluate('document.querySelectorAll(".reveal-ready")['+index+'].scrollIntoView({block:"center",behavior:"instant"})');await pause(120);
 }
 assert(await evaluate('[...document.querySelectorAll(".reveal-ready")].every(e=>e.classList.contains("is-visible"))'),'scroll reveals complete');
 for(const section of ['financial-investigation','professional-standards','brokers','payment','recovery-process','consultation','confidentiality','asset-tracing','contact']){
  await evaluate('document.getElementById("'+section+'").scrollIntoView({behavior:"instant"})');await pause(700);
  const shot=await send('Page.captureScreenshot',{format:'png'});
  fs.writeFileSync('.qa/section-'+section+'-'+width+'.png',Buffer.from(shot.data,'base64'));
 }
 console.log('PASS: '+width+'px; all 8 used image assets decode, icons removed and numbers above headings, scroll reveals and section layouts.');
}
await send('Emulation.setEmulatedMedia',{features:[{name:'prefers-reduced-motion',value:'reduce'}]});
await send('Page.reload');await pause(700);
assert.equal(await evaluate('document.querySelectorAll(".reveal-ready").length'),0);
console.log('PASS: reduced-motion users receive fully visible content without reveal animation.');
await send('Emulation.setEmulatedMedia',{features:[]});
await send('Emulation.setScriptExecutionDisabled',{value:true});
await send('Page.reload');await pause(700);
assert.equal(await evaluate('document.querySelectorAll(".home-section").length'),19);
assert(await evaluate('[...document.querySelectorAll(".home-section")].every(e=>getComputedStyle(e).opacity==="1")'));
console.log('PASS: all 19 sections remain visible without JavaScript.');
await send('Emulation.setScriptExecutionDisabled',{value:false});
socket.close();
