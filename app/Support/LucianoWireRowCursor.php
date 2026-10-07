<?php
namespace App\Support;

/** Pure insertion cursor: validates the full CRM packet, then replaces only transport foreign keys. */
final class LucianoWireRowCursor
{
    private $data;
    private $snapshotId;
    private $position=0;
    private $index=0;
    private $pending=null;
    private $catalogIds=[];
    private $observationIds=[];
    private $usedIds=[];
    private $counts=[];
    private const TABLES=['catalog'=>'external_price_catalog','observations'=>'external_price_observation',
        'daily'=>'external_daily_price','restrictions'=>'external_stay_restriction','offers'=>'external_stay_offer'];

    public function __construct($hotel, array $wire, array $sectionFields, $snapshotId)
    {
        $scopes=['kazan'=>['1096','14'],'sochi'=>['1658','15']];
        if (!is_string($hotel) || !isset($scopes[$hotel])
            || [$wire['crm_object_id']??null,$wire['external_property_key']??null]!==$scopes[$hotel]) {
            throw new \RuntimeException('Foreign CRM insertion cursor scope');
        }
        self::id($snapshotId);
        $validated=LucianoWireImportPlan::validate($wire,'crm',$sectionFields);
        $this->data=$validated['envelope']['payload']['data'];$this->snapshotId=$snapshotId;
        foreach ($this->data as $section=>$records) {
            foreach ($records as $row) foreach (['id','snapshot_id','catalog_id','observation_id','crm_room_id','crm_rate_id'] as $reserved) {
                if (array_key_exists($reserved,$row)) throw new \RuntimeException('Database identity supplied by source');
            }
            $this->counts[$section]=0;$this->usedIds[$section]=[];
        }
    }

    /** Same descriptor is returned until its actual generated row ID has been accepted. No SQL is executed here. */
    public function next()
    {
        if ($this->pending!==null) return $this->pending;
        $sections=array_keys(self::TABLES);
        while ($this->position<count($sections) && $this->index>=count($this->data[$sections[$this->position]])) {
            $this->position++;$this->index=0;
        }
        if ($this->position===count($sections)) return null;
        $section=$sections[$this->position];$source=$this->data[$section][$this->index];$row=$source;
        if ($section==='catalog') unset($row['catalog_key']);
        else {
            $key=$row['catalog_key'];
            if (!isset($this->catalogIds[$key])) throw new \RuntimeException('Catalogue ID has not been recorded');
            unset($row['catalog_key']);$row['catalog_id']=$this->catalogIds[$key];
            if ($section!=='observations') {
                $key=$row['observation_key'];unset($row['observation_key']);
                if ($key!==null && !isset($this->observationIds[$key])) throw new \RuntimeException('Observation ID has not been recorded');
                if ($key===null && $section!=='restrictions') throw new \RuntimeException('Mandatory observation ID is absent');
                $row['observation_id']=$key===null?null:$this->observationIds[$key];
            }
        }
        $row['snapshot_id']=$this->snapshotId;
        foreach ($row as $field=>$value) {
            if (!is_string($field) || !preg_match('/^[a-z_]+$/D',$field) || ($value!==null && !is_string($value))) {
                throw new \RuntimeException('Invalid insertion field or scalar');
            }
        }
        $table=self::TABLES[$section];
        $this->pending=['section'=>$section,'row_index'=>$this->index,'table'=>$table,'values'=>$row,
            'sql'=>'INSERT INTO `'.$table.'` (`'.implode('`,`',array_keys($row)).'`) VALUES ('.implode(',',array_fill(0,count($row),'?')).')',
            'parameters'=>array_values($row)];
        return $this->pending;
    }

    /** Call only with the generated ID returned by the future consumer's successful INSERT. */
    public function acceptId($id): void
    {
        self::id($id);
        if ($this->pending===null) throw new \RuntimeException('No pending insertion');
        $section=$this->pending['section'];
        if (isset($this->usedIds[$section][$id])) throw new \RuntimeException('Generated row ID reused within one table');
        $source=$this->data[$section][$this->index];
        if ($section==='catalog') $this->catalogIds[$source['catalog_key']]=$id;
        elseif ($section==='observations') $this->observationIds[$source['observation_key']]=$id;
        $this->usedIds[$section][$id]=true;$this->counts[$section]++;$this->index++;$this->pending=null;
    }

    public function complete(): array
    {
        if ($this->next()!==null) throw new \RuntimeException('Insertion cursor is incomplete');
        foreach ($this->data as $section=>$rows) if ($this->counts[$section]!==count($rows)) {
            throw new \RuntimeException('Insertion cursor count differs');
        }
        return ['section_counts'=>$this->counts,'all_descriptors_consumed'=>true,
            'database_written'=>false,'native_sql_executed'=>false,'consumer_integrated'=>false,'delivery_ready'=>false];
    }

    private static function id($id): void
    {
        if (!is_string($id) || !preg_match('/^[1-9][0-9]{0,19}$/D',$id)
            || (strlen($id)===20 && strcmp($id,'18446744073709551615')>0)) {
            throw new \RuntimeException('Noncanonical generated database ID');
        }
    }
}
