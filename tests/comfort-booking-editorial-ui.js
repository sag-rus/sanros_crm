const fs=require('fs'),assert=require('assert'),{JSDOM}=require('jsdom'),path=require('path');
const root=path.resolve(__dirname,'..'),dom=new JSDOM('<body></body>',{url:'https://crm.example/',runScripts:'outside-only'}),w=dom.window;
w.eval(fs.readFileSync(root+'/js/jquery-1.9.1.js','utf8'));const $=w.jQuery;
$.fn.modal=function(action){if(action==='hide')this.trigger('hidden.bs.modal');return this;};
let calls=[],failure=false,n=0;
const fields={h1:'Hotel',title:'Title',description:'Description',keywords:'Keywords',summary:'First\nSecond'};
$.ajax=function(o){calls.push(o.data);const d=$.Deferred();setTimeout(()=>{if(failure)d.reject({responseJSON:{error:'Конфликт правок'}});else d.resolve({success:true,csrf:'csrf',data:{fields:o.data.fields?JSON.parse(o.data.fields):fields,revision:'new'}});},0);return d.promise();};
w.eval(fs.readFileSync(root+'/modules/module-comfort-booking-editorial.js','utf8'));
const wait=()=>new Promise(r=>setTimeout(r,20));function ok(x,m){assert(x,m);n++;}
(async()=>{
 w.cbEditorialOpen({id:123,name:'<img src=x>'},'token');await wait();
 ok($('img').length===0,'name escaped');ok(calls[0].action==='editorial-read'&&calls[0].id===123,'reads selected property through API');
 ok($('input,textarea').length===5&&!$('#cb-editorial-body').length,'only five permitted fields');
 ok($('#cb-editorial-summary').val()==='First\nSecond','multiline value preserved');
 $('#cb-editorial-h1').val('New H1');$('form').trigger('submit');await wait();
 ok(calls[1].action==='editorial-save'&&calls[1].revision==='new'&&JSON.parse(calls[1].fields).h1==='New H1','save submits latest revision and values');
 ok($('.alert-success').length===1,'success shown only after API receipt');
 failure=true;$('#cb-editorial-h1').val('Unsaved');$('form').trigger('submit');await wait();
 ok($('.alert-danger').text()==='Конфликт правок'&&$('#cb-editorial-h1').val()==='Unsaved','failed save keeps entered text');
 $('button').filter(function(){return $(this).text()==='Закрыть';}).click();ok(!$('#cb-editorial-modal').length,'closing removes fields from DOM');
 dom.window.close();console.log(n+' editorial UI checks passed');
})().catch(e=>{dom.window.close();console.error(e);process.exitCode=1;});
