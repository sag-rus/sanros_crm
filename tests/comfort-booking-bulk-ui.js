const fs=require('fs'),assert=require('assert'),{JSDOM}=require('jsdom');
const dom=new JSDOM('<div id="body"></div>',{url:'https://crm.example/',runScripts:'outside-only'}),w=dom.window;
w.eval(fs.readFileSync(__dirname+'/../js/jquery-1.9.1.js','utf8'));const $=w.jQuery;
w.select_menu=()=>{};w.confirm=()=>true;
let calls=[],pending=[],role=true;
const row=(id,state,search)=>({id,name:'Object '+id,state,analogSearch:search});
let rows=[row(1,'pending',{matches:[]}),row(2,'pending',{matches:[{id:20,name:'Analog'}]}),row(3,'disabled',{matches:[]}),row(4,'pending',null),row(5,'approved',{matches:[]})];
$.ajax=o=>{calls.push(o.data);const d=$.Deferred();if(o.data.action==='list')d.resolve({data:{rows,page:1,pages:2,total:10,pendingCount:2},csrf:'token',canModerate:role});else pending.push(d);return d.promise({abort:()=>{}});};
const source=fs.readFileSync(__dirname+'/../modules/module-object.js','utf8');w.eval(source.slice(source.indexOf('function comfort_booking_panel(){'),source.indexOf('// Counts are read from the website')));w.comfort_booking_count=()=>{};
const all=()=>$('thead input');const check=i=>$('.cb-row-select').eq(i).prop('checked',true).trigger('change');const bulk=()=>calls.filter(c=>c.action.startsWith('bulk-'));
rows[0].nameCleanup={outcome:'changed',original_name:'Отель <Ривьера>',cleaned_name:'<Ривьера>'};
rows[1].nameCleanup={outcome:'unchanged'};rows[2].nameCleanup={outcome:'needs_review'};
w.comfort_booking_panel();
assert($('.cb-name-cleanup').eq(0).text().includes('Отель <Ривьера>'));
assert.strictEqual($('.cb-name-cleanup').eq(0).children().length,0);
assert($('.cb-name-cleanup').eq(0).prev().hasClass('cb-analogs'));
assert($('.cb-name-cleanup').eq(1).text().includes('изменений не требуется'));
assert($('.cb-name-cleanup').eq(2).text().includes('ручная проверка'));
assert($('.cb-name-cleanup').eq(3).text().includes('ещё не выполнена'));

assert($('.cb-row-select').eq(1).prop('disabled'));assert($('.cb-row-select').eq(2).prop('disabled'));
all().prop('checked',true).trigger('change');assert.strictEqual($('.cb-row-select:checked').length,3);
assert($('.cb-bulk-actions .btn-success').prop('disabled'));assert(!$('.cb-bulk-actions .btn-danger').prop('disabled'));
// Select-all excludes analogs and disabled entries, submits sequentially and stops on uncertainty.
$('.cb-bulk-actions .btn-danger').click();assert.strictEqual(bulk().length,1);assert.strictEqual(bulk()[0].id,1);
assert(all().prop('disabled'));assert($('form input').prop('disabled'));
pending.shift().resolve({success:true});assert.strictEqual(bulk()[1].id,4);
pending.shift().reject({status:0});assert.strictEqual(bulk().length,2);assert($('.alert-info').text().includes('остановлена'));assert.strictEqual($('.cb-row-select:checked').length,0);
// Approval sends only chosen pending entries with a completed empty search.
calls=[];check(0);assert(all().prop('indeterminate'));$('.cb-bulk-actions .btn-success').click();assert.strictEqual(bulk()[0].action,'bulk-approve');assert.strictEqual(bulk()[0].csrf,'token');
pending.shift().resolve({success:true});assert($('.alert-info').text().includes('одобрено: 1 из 1'));
check(0);$('form button').filter((i,e)=>$(e).text()==='Обновить').click();assert.strictEqual($('.cb-row-select:checked').length,0);
role=false;w.comfort_booking_panel();assert.strictEqual($('.cb-row-select:not(:disabled)').length,0);assert.strictEqual($('.cb-bulk-actions').css('display'),'none');
dom.window.close();console.log('Bulk selection and progress checks passed');
