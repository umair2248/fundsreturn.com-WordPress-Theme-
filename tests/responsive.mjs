import assert from 'node:assert/strict';
import fs from 'node:fs';
const target=(await(await fetch('http://127.0.0.1:9231/json/list')).json()).find(t=>t.type==='page');
const socket=new WebSocket(target.webSocketDebuggerUrl);await new Promise(r=>socket.onopen=r);
let id=0,onLoad;const pending=new Map();
socket.onmessage=e=>{const m=JSON.parse(e.data);if(m.id){pending.get(m.id)(m.result);pending.delete(m.id)}else if(m.method==='Page.loadEventFired'&&onLoad){onLoad();onLoad=null}};
const send=(method,params={})=>new Promise(r=>{pending.set(++id,r);socket.send(JSON.stringify({id,method,params}))});
const ev=async expression=>{const r=await send('Runtime.evaluate',{expression,returnByValue:true,awaitPromise:true});if(r.exceptionDetails)throw Error(JSON.stringify(r.exceptionDetails));return r.result.value;};
await send('Page.enable');await send('Runtime.enable');
await send('Emulation.setEmulatedMedia',{features:[{name:'prefers-reduced-motion',value:'reduce'}]});
const visit=async(page,lang)=>{
 const loaded=new Promise(r=>onLoad=r);
 await send('Page.navigate',{url:'http://127.0.0.1:8765/tests/preview.php?page='+page+'&lang='+lang});
 await loaded;await ev('document.fonts.ready.then(()=>true)');
};
for(const width of [320,360,390,430,768,1024,1440]){
 await send('Emulation.setDeviceMetricsOverride',{width,height:900,deviceScaleFactor:1,mobile:width<769});
 for(const lang of ['en','fr','nl','de'])for(const page of ['home','about','faq','contact','privacy-policy','terms-of-use']){
  await visit(page,lang);
  const metrics=await ev('(()=>{const visible=e=>{const r=e.getBoundingClientRect();return r.width&&r.height&&!e.closest(".form-trap")};const outside=[...document.querySelectorAll("main h1,main h2,main p,main .button,main .section-copy,main .service-copy,main .check-list,main input,main textarea,.site-branding,.menu-toggle")].filter(visible).filter(e=>{const r=e.getBoundingClientRect();return r.left< -1||r.right>innerWidth+1}).map(e=>({tag:e.tagName,cls:e.className,id:e.id}));return {overflow:document.documentElement.scrollWidth>innerWidth,outside,h1:parseFloat(getComputedStyle(document.querySelector("h1")).fontSize),description:parseFloat(getComputedStyle(document.querySelector(".hero-copy>p:not(.eyebrow)")||document.querySelector("main p")).fontSize),font:document.querySelector(".case-form input[type=text]")?getComputedStyle(document.querySelector(".case-form input[type=text]")).fontSize:null}})()');
  assert(!metrics.overflow,JSON.stringify({width,lang,page,metrics}));
  assert.deepEqual(metrics.outside,[],JSON.stringify({width,lang,page,metrics}));
  if(width<=768){assert(metrics.h1<=40);if(page==='home')assert(metrics.description<=17);}
  if(width<=768&&page==='contact')assert.equal(metrics.font,'16px','form text remains readable without mobile input zoom');
  if(width<=540){
   await ev('document.querySelector(".menu-toggle").click()');
   assert(await ev('document.querySelector(".site-navigation").getBoundingClientRect().right<=innerWidth'));
   await ev('document.querySelector(".menu-toggle").click()');
  }
  if(lang==='nl'&&[320,390,768].includes(width)&&['home','contact'].includes(page)){
   const shot=await send('Page.captureScreenshot',{format:'png'});
   fs.writeFileSync('.qa/responsive-'+page+'-'+width+'.png',Buffer.from(shot.data,'base64'));
  }
 }
 console.log('PASS: '+width+'px; all six pages in all four languages fit, type scales and navigation works.');
}
await send('Emulation.setEmulatedMedia',{features:[]});
console.log('PASS: 168 page/language/viewport combinations, including long Dutch/German copy.');
socket.close();
