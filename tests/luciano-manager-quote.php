<?php
// Synthetic unit fixtures only: never connected to a CRM DB or live queue.
require __DIR__.'/../core/luciano_manager_quote.php';
$count=0;
function verify($ok) { global $count; if(!$ok)throw new RuntimeException('Assertion failed');$count++; }
function rejects($fn) { global $count; try{$fn();}catch(RuntimeException $e){$count++;return;}throw new RuntimeException('Expected rejection'); }
$b=['id'=>1,'id_obj'=>1096,'date_z'=>'2026-10-12','date_v'=>'2026-10-14','number_turist'=>2,'children_rest'=>0];
$p=['id_room'=>1,'ratePlan'=>2,'date_z'=>'2026-10-12','days'=>2,'number'=>1,'type'=>3,'add_one_day'=>1,'sum'=>'37650.00'];
$build=function($booking,$positions){return LucianoManagerQuote::build($booking,$positions,'2026-10-06');};
$q=$build($b,[$p]);verify($q['quoted_total']==='37650.00' && $q['price_basis']==='stay_total' && $q['nights']===2);
verify($build(array_replace($b,['number_turist'=>3]),[array_replace($p,['sum'=>'47650.00'])])['quoted_total']==='47650.00');
verify($build(array_replace($b,['id_obj'=>1658]),[array_replace($p,['sum'=>'61200.00'])])['provider_id']===434);
verify($build($b,[array_replace($p,['type'=>2,'sum'=>'14450'])])['quoted_total']==='28900.00');
verify($build($b,[array_replace($p,['type'=>2,'days'=>1,'sum'=>'17650.00']),array_replace($p,['type'=>2,'days'=>1,'date_z'=>'2026-10-13','sum'=>'20000.00'])])['quoted_total']==='37650.00');
verify($build(array_replace($b,['date_v'=>'2026-10-13']),[array_replace($p,['type'=>2,'days'=>1,'sum'=>'17650.00'])])['quoted_total']==='17650.00');
rejects(function()use($build,$b,$p){$build(array_replace($b,['id_obj'=>848]),[$p]);});
rejects(function()use($build,$b,$p){$build(array_replace($b,['number_turist'=>4]),[$p]);});
rejects(function()use($build,$b,$p){$build(array_replace($b,['children_rest'=>1]),[$p]);});
rejects(function()use($build,$b,$p){$build($b,[array_replace($p,['number'=>2])]);});
rejects(function()use($build,$b,$p){$build($b,[array_replace($p,['type'=>1])]);});
rejects(function()use($build,$b,$p){$build($b,[array_replace($p,['add_one_day'=>0])]);});
rejects(function()use($build,$b,$p){$build($b,[array_replace($p,['days'=>1])]);});
rejects(function()use($build,$b,$p){$build(array_replace($b,['date_v'=>'2026-10-13']),[array_replace($p,['days'=>1])]);});
rejects(function()use($build,$b,$p){$build($b,[$p,array_replace($p,['type'=>2])]);});
rejects(function()use($build,$b,$p){$build($b,[array_replace($p,['type'=>2,'days'=>1]),array_replace($p,['type'=>2,'days'=>1,'date_z'=>'2026-10-13','ratePlan'=>3])]);});
rejects(function()use($build,$b,$p){$build($b,[array_replace($p,['type'=>2,'days'=>1]),array_replace($p,['type'=>2,'days'=>1,'date_z'=>'2026-10-14'])]);});
rejects(function()use($build,$b,$p){$build($b,[array_replace($p,['sum'=>'37650.001'])]);});
rejects(function()use($build,$b,$p){$build($b,[array_replace($p,['sum'=>'0.00'])]);});
rejects(function()use($build,$b,$p){$build(array_replace($b,['date_v'=>'2026-10-20']),[array_replace($p,['days'=>8])]);});
rejects(function()use($b,$p){LucianoManagerQuote::build($b,[$p],'2026-10-13');});
verify($build(array_replace($b,['date_z'=>'2027-04-30','date_v'=>'2027-05-07']),[array_replace($p,['date_z'=>'2027-04-30','days'=>7])])['quoted_total']==='37650.00');
echo "PASS $count Luciano CRM manager quote fixtures\n";
