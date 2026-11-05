// NODE_PATH=/path/to/test-only/node_modules node tests/comfort-booking-reservations-ui.js
const fs=require('fs'),assert=require('assert'),{JSDOM}=require('jsdom');
const root=require('path').resolve(__dirname,'..');
const dom=new JSDOM('<div id="info_turist"></div>',{url:'https://crm.example/CRM/',runScripts:'outside-only'});
const w=dom.window;w.eval(fs.readFileSync(root+'/js/jquery-1.9.1.js','utf8'));const $=w.jQuery;
let calls=[],failCommit=true,confirmed=false,canWaive=false,checks=0;
const person={firstName:'Test',lastName:'Guest',phone:'+70000000000'};
const offer={roomId:10,rateId:20,room:'Room',rate:'Rate',total:'2000.00',rules:['First night penalty']};
const summary={hotel:'Fixture',arrival:'2026-11-10',departure:'2026-11-12',room:'Room',rate:'Rate',adults:1,children:0,total:'2000.00',prepayment:'0.00',payment:'Cash',customer:person,guests:[person]};
const booking={number:'fixture',hotelId:'101',status:'new',arrivalDate:summary.arrival,departureDate:summary.departure,paymentInfo:{prepayment:{value:'0.00'}},paymentMethodComment:'Cash',customer:person,roomStays:[{adults:1,children:0,total:{amountAfterTaxes:'2000.00'},guests:[person]}]};
function response(data){return {success:true,data,csrf:'x'.repeat(64),canEdit:true,canWaive,defaults:{hotelId:101,arrivalDate:summary.arrival,departureDate:summary.departure,customer:person}};}
$.ajax=function(options){calls.push(options.data);const d=$.Deferred();setTimeout(()=>{
 const a=options.data.action;
 if(a==='detail')d.resolve(response(confirmed?{booking,selection:{room:'Room',rate:'Rate',adults:1},hotel:{name:'<img src=x onerror=alert(1)>'},delivery:'confirmed',canChange:true,revision:'v',rules:[],history:[]}:{booking:null,revision:'empty'}));
 if(a==='offers')d.resolve(response({offers:[offer]}));
 if(a==='prepare')d.resolve(response({token:'same-token',summary:{after:summary,rules:[]}}));
 if(a==='commit'){if(failCommit){failCommit=false;d.reject({});}else d.resolve(response({booking,selection:{room:'Room',rate:'Rate'},hotel:{name:'Fixture'},delivery:'waiting',canChange:false,rules:[],history:[]}));}
},0);return d.promise();};
w.confirm=()=>true;w.eval(fs.readFileSync(root+'/modules/module-comfort-booking-reservations.js','utf8'));
const wait=()=>new Promise(r=>setTimeout(r,20));
const click=t=>{$('button').filter(function(){return $(this).text()===t;}).trigger('click');};
function ok(v,m){assert(v,m);checks++;}
(async()=>{
 w.cbBookingMount(100);click('Открыть / обновить');await wait();click('Подобрать номер и создать бронь');
 ok($('input[type=date]').length===2,'trip date controls');click('Проверить цены и наличие');await wait();
 $('select').last().val('0').trigger('change');click('Проверить перед отправкой');await wait();
 ok(calls.find(x=>x.action==='prepare').requestId===100,'request bound to panel');
 click('Подтвердить и отправить');await wait();ok($('button').filter(function(){return $(this).text()==='Повторить тот же запрос';}).length===1,'uncertain request retry');
 click('Повторить тот же запрос');await wait();ok(calls.filter(x=>x.action==='commit').length===2&&calls.filter(x=>x.action==='commit').every(x=>x.token==='same-token'),'retry same idempotent token');
 ok($('#info_turist').text().includes('Ожидается подтверждение TravelLine'),'no fake confirmation');
 confirmed=true;click('Открыть / обновить');await wait();ok(!$('button').filter(function(){return $(this).text()==='Отменить без штрафа';}).length,'manager cannot see waiver');
 ok($('img').length===0&&$('#info_turist').text().includes('<img'),'catalog text escaped');
 canWaive=true;click('Открыть / обновить');await wait();ok($('button').filter(function(){return $(this).text()==='Отменить без штрафа';}).length===1,'admin waiver button');
 dom.window.close();console.log(checks+' UI checks passed');
})().catch(e=>{dom.window.close();console.error(e);process.exitCode=1;});
