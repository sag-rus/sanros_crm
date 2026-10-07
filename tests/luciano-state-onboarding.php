<?php
require_once __DIR__.'/../app/Support/LucianoWireEnvelope.php';
require_once __DIR__.'/../app/Support/LucianoWireDestination.php';
require_once __DIR__.'/../app/Support/LucianoStateOnboarding.php';
use App\Support\LucianoStateOnboarding as Onboarding;
use App\Support\LucianoWireEnvelope as Wire;
function onboardingReject($fn) { try {$fn();} catch(RuntimeException $e){return;}throw new RuntimeException('Unsafe onboarding was accepted'); }
function onboardingEvidence($hotel='kazan') {
    return [$hotel,[['id'=>$hotel==='kazan'?'1096':'1658','name'=>$hotel==='kazan'?'Luciano':'Luciano Sochi сан кур']],[],0,Onboarding::FIELDS,'InnoDB'];
}
$checks=0;
foreach (['kazan','sochi'] as $hotel) {
    $a=onboardingEvidence($hotel);$plan=Onboarding::plan(...$a);$row=Onboarding::insertValues($plan,'2026-10-07 06:00:00');
    if ($plan['action']!=='insert_dormant_state' || $row['active_snapshot_id']!==null || $row['import_enabled']!=='0'
        || $row['publish_enabled']!=='0' || $row['stale_after_seconds']!==null || $row['local_property_id']!==null
        || $row['billing_basis']!=='night' || $row['last_attempt_at']!==null || count($row)!==16
        || $row['upstream_property_key']!==($hotel==='kazan'?'3026':'434') || $plan['activation_allowed'] || $plan['database_written']) throw new RuntimeException('Invalid dormant plan');$checks++;
    $a[2]=[$row];$ready=Onboarding::plan(...$a);
    if ($ready['action']!=='already_dormant' || $ready['evidence']['states'][0]!==$row) throw new RuntimeException('Existing state not preserved');$checks++;
    onboardingReject(function()use($ready){Onboarding::insertValues($ready,'2026-10-07 06:00:01');});$checks++;
    $changed=$plan;$changed['insert_values_without_timestamps']['publish_enabled']='1';
    onboardingReject(function()use($changed){Onboarding::insertValues($changed,'2026-10-07 06:00:01');});$checks++;
    $a[2][0]['crm_object_id']=(int)$row['crm_object_id'];$a[2][0]['external_property_key']=(int)$row['external_property_key'];
    $a[2][0]['import_enabled']=0;$a[2][0]['publish_enabled']=0;
    if (Onboarding::plan(...$a)['action']!=='already_dormant') throw new RuntimeException('Native integer state rejected');$checks++;
}
$base=onboardingEvidence();
foreach (['foreign hotel','missing hotel','wrong name','wrong object','duplicate object','missing object','snapshot exists',
    'string count','float count','negative count','MyISAM','missing schema field','extra schema field','duplicate schema field','ambiguous states'] as $case) {
    $a=$base;
    switch ($case) {
        case 'foreign hotel':$a[0]='foreign';break;
        case 'missing hotel':$a[0]=null;break;
        case 'wrong name':$a[1][0]['name']='Another hotel';break;
        case 'wrong object':$a[1][0]['id']='1658';break;
        case 'duplicate object':$a[1][]=$a[1][0];break;
        case 'missing object':$a[1]=[];break;
        case 'snapshot exists':$a[3]=1;break;
        case 'string count':$a[3]='0';break;
        case 'float count':$a[3]=0.0;break;
        case 'negative count':$a[3]=-1;break;
        case 'MyISAM':$a[5]='MyISAM';break;
        case 'missing schema field':array_pop($a[4]);break;
        case 'extra schema field':$a[4][]='unreviewed';break;
        case 'duplicate schema field':$a[4][]='source';break;
        case 'ambiguous states':$a[2]=[[],[]];break;
    }
    onboardingReject(function()use($a){Onboarding::plan(...$a);});$checks++;
}
$row=Onboarding::insertValues(Onboarding::plan(...$base),'2026-10-07 06:00:00');
foreach ([['source'=>'other'],['crm_object_id'=>'1658'],['external_property_key'=>'15'],['upstream_property_key'=>'434'],
    ['local_property_id'=>'14'],['active_snapshot_id'=>'1'],['import_enabled'=>1],['publish_enabled'=>1],
    ['stale_after_seconds'=>900],['last_attempt_at'=>'2026-10-07 06:00:00'],['last_success_at'=>'2026-10-07 06:00:00'],
    ['last_error'=>'failure'],['billing_basis'=>'day_inclusive'],['timezone'=>'UTC'],['external_property_key'=>'014'],
    ['crm_object_id'=>1096.0],['import_enabled'=>false],['created_at'=>null],['updated_at'=>'2026-02-30 00:00:00'],
    ['updated_at'=>'2026-10-07 05:59:59']] as $change) {
    $a=$base;$a[2]=[array_replace($row,$change)];onboardingReject(function()use($a){Onboarding::plan(...$a);});$checks++;
}
foreach (array_keys($row) as $field) {
    $a=$base;$r=$row;unset($r[$field]);$a[2]=[$r];onboardingReject(function()use($a){Onboarding::plan(...$a);});$checks++;
}
$plan=Onboarding::plan(...$base);
foreach ([null,'2026-02-30 00:00:00','2026-10-07T06:00:00Z'] as $utc) {
    onboardingReject(function()use($plan,$utc){Onboarding::insertValues($plan,$utc);});$checks++;
}
if ($plan['evidence_sha256']!==hash('sha256',Wire::encode($plan['evidence']))) throw new RuntimeException('Evidence fingerprint differs');$checks++;
echo "PASS $checks Luciano dormant state planning/no-overwrite fixtures; no actual migration or database\n";
