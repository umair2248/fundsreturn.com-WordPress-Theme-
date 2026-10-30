import assert from 'node:assert/strict';
const target=(await(await fetch('http://127.0.0.1:9231/json/list')).json()).find(t=>t.type==='page');
const socket=new WebSocket(target.webSocketDebuggerUrl);await new Promise(r=>socket.onopen=r);
let id=0;const pending=new Map();
socket.onmessage=e=>{const m=JSON.parse(e.data);if(m.id){pending.get(m.id)(m.result);pending.delete(m.id)}};
const send=(method,params={})=>new Promise(r=>{pending.set(++id,r);socket.send(JSON.stringify({id,method,params}))});
const ev=async expression=>{const r=await send('Runtime.evaluate',{expression,returnByValue:true,awaitPromise:true});if(r.exceptionDetails)throw Error(JSON.stringify(r.exceptionDetails));return r.result.value;};
const wait=ms=>new Promise(r=>setTimeout(r,ms));
await send('Page.enable');await send('Runtime.enable');
await send('Network.enable');await send('Network.setCacheDisabled',{cacheDisabled:true});
await send('Emulation.setFocusEmulationEnabled',{enabled:true});
await send('Page.addScriptToEvaluateOnNewDocument',{source:'window.entryCalls=[];const originalAnimate=Element.prototype.animate;Element.prototype.animate=function(frames,options){window.entryCalls.push({tag:this.tagName,cls:this.className,duration:options.duration,delay:options.delay,transform:frames[0].transform});return originalAnimate.call(this,frames,options)};'});
const visit=async page=>{await send('Page.navigate',{url:'http://127.0.0.1:8765/tests/preview.php?page='+page+'&lang=fr'});await wait(1100);};
for(const width of [1440,390]){
 await send('Emulation.setDeviceMetricsOverride',{width,height:1000,deviceScaleFactor:1,mobile:width<769});
 for(const page of ['home','about','faq','contact','privacy-policy','terms-of-use']){
  await visit(page);
  assert(await ev('window.entryCalls.length>2'),page+' entrance animation coverage');
  assert(await ev('window.entryCalls.every(a=>a.duration>=800&&a.duration<=1100&&a.transform.includes("translateY"))'));
  const before=await ev('window.entryCalls.length');
  await ev('document.querySelector(".footer-grid").scrollIntoView({behavior:"instant"})');await wait(80);
  assert(await ev('document.getAnimations().some(a=>a.playState==="running")'),'visible movement during footer entry');
  assert(await ev('window.entryCalls.length')>before,'scroll triggers new entrances');
  await wait(1300);
  assert(await ev('document.documentElement.scrollWidth<=innerWidth'),'no horizontal overflow');
  assert(await ev('[...document.querySelectorAll(".footer-grid>div")].every(e=>getComputedStyle(e).opacity==="1")'),'footer fully visible after animation');
 }
 console.log('PASS: all six pages animate at '+width+'px; scroll movement and final visibility verified.');
}
await visit('home');
await ev('document.querySelector("#smart-contract .check-list").scrollIntoView({behavior:"instant"})');await wait(100);
assert(await ev('window.entryCalls.some(a=>a.delay===90)&&window.entryCalls.some(a=>a.delay===180)'),'cards stagger');
await send('Emulation.setEmulatedMedia',{features:[{name:'prefers-reduced-motion',value:'reduce'}]});
await wait(300);assert(await ev('matchMedia("(prefers-reduced-motion: reduce)").matches'));
assert.equal(await ev('document.getAnimations().filter(a=>a.playState==="running").length'),0);
await visit('home');assert.equal(await ev('window.entryCalls.length'),0,'reduced motion disables entrances');
await send('Emulation.setEmulatedMedia',{features:[]});
await visit('contact');
await ev('document.querySelector(".case-form").scrollIntoView({behavior:"instant"})');await wait(50);
await ev('document.getElementById("cfr-name").focus()');
assert(await ev('document.activeElement.id==="cfr-name"'),'name field receives keyboard focus');
await wait(100);
assert(await ev('!document.querySelector(".case-form").getAnimations().some(a=>a.playState==="running")'),'keyboard focus cancels form movement');
await send('Emulation.setScriptExecutionDisabled',{value:true});await visit('home');
assert.equal(await ev('document.querySelectorAll(".home-section").length'),19);
assert(await ev('[...document.querySelectorAll(".home-section")].every(e=>getComputedStyle(e).opacity==="1")'),'no JS visibility');
await send('Emulation.setScriptExecutionDisabled',{value:false});
console.log('PASS: staggered cards, reduced-motion preference, keyboard focus and no-JavaScript visibility.');
socket.close();
