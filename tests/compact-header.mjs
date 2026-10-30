import assert from 'node:assert/strict';
import fs from 'node:fs';
const target=(await(await fetch('http://127.0.0.1:9231/json/list')).json()).find(t=>t.type==='page');
const socket=new WebSocket(target.webSocketDebuggerUrl);await new Promise(r=>socket.onopen=r);
let id=0,onLoad;const pending=new Map();
socket.onmessage=e=>{const m=JSON.parse(e.data);if(m.id){pending.get(m.id)(m.result);pending.delete(m.id)}else if(m.method==='Page.loadEventFired'&&onLoad){onLoad();onLoad=null}};
const send=(method,params={})=>new Promise(r=>{pending.set(++id,r);socket.send(JSON.stringify({id,method,params}))});
const ev=async expression=>{const r=await send('Runtime.evaluate',{expression,returnByValue:true,awaitPromise:true});if(r.exceptionDetails)throw Error(JSON.stringify(r.exceptionDetails));return r.result.value;};
const wait=ms=>new Promise(r=>setTimeout(r,ms));
await send('Page.enable');
await send('Emulation.setEmulatedMedia',{features:[{name:'prefers-reduced-motion',value:'reduce'}]});
for(const width of [320,390,768,1024,1440]){
 await send('Emulation.setDeviceMetricsOverride',{width,height:900,deviceScaleFactor:1,mobile:width<769});
 for(const lang of ['en','fr','nl','de']){
  const loaded=new Promise(r=>onLoad=r);
  await send('Page.navigate',{url:'http://127.0.0.1:8765/tests/preview.php?page=home&lang='+lang});await loaded;await ev('document.fonts.ready.then(()=>true)');
  const original=await ev('document.querySelector(".site-header-stack").getBoundingClientRect().height');
  await ev('window.scrollTo({top:1000,behavior:"instant"})');await wait(300);
  const metric=await ev('(()=>{const h=document.querySelector(".site-header-stack"),b=h.getBoundingClientRect(),u=h.querySelector(".utility-bar").getBoundingClientRect();return {height:b.height,top:b.top,utility:u.top,compact:h.classList.contains("is-compact"),overflow:document.documentElement.scrollWidth>innerWidth}})()');
  assert(metric.compact&&!metric.overflow,JSON.stringify({width,lang,metric}));assert.equal(metric.top,0);assert.equal(metric.utility,0);
  assert(metric.height<original,JSON.stringify({width,lang,metric,original}));
  assert(metric.height<=(width<=768?86:96),JSON.stringify({width,lang,metric}));
  if(lang==='fr'){
   await ev('document.body.classList.add("admin-bar");window.dispatchEvent(new Event("resize"))');
   await wait(100);
   assert.equal(await ev('document.querySelector(".site-header-stack").getBoundingClientRect().top'),width>782?32:width>600?46:0);
   await ev('document.body.classList.remove("admin-bar");window.dispatchEvent(new Event("resize"))');
  }
  if(width<=1024){
   await ev('document.querySelector(".menu-toggle").click()');await wait(100);
   assert(await ev('document.querySelector(".menu-toggle").getAttribute("aria-expanded")==="true"'));
   assert(await ev('document.querySelector(".site-navigation").getBoundingClientRect().bottom<=innerHeight'));
   await ev('document.querySelector(".menu-toggle").click()');await wait(100);
  }
  await ev('document.getElementById("case-review").scrollIntoView({behavior:"instant"})');await wait(100);
  assert(await ev('document.getElementById("case-review").getBoundingClientRect().top>=document.querySelector(".site-header-stack").getBoundingClientRect().bottom'));
  if(lang==='fr'&&[390,1440].includes(width)){const shot=await send('Page.captureScreenshot',{format:'png'});fs.writeFileSync('.qa/compact-header-'+width+'.png',Buffer.from(shot.data,'base64'));}
  await ev('window.scrollTo({top:0,behavior:"instant"})');await wait(300);
  assert(await ev('!document.querySelector(".site-header-stack").classList.contains("is-compact")'));
 }
 console.log('PASS: '+width+'px; complete header sticks and shrinks; menu, anchors and restoration work in four languages.');
}
await send('Emulation.setEmulatedMedia',{features:[]});
socket.close();
