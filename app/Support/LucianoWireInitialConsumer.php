<?php
namespace App\Support;

/** Unintegrated CRM initial-only transaction. Launch gates and installed entry point are still required. */
final class LucianoWireInitialConsumer
{
    private const TABLES=['catalog'=>'external_price_catalog','observations'=>'external_price_observation',
        'daily'=>'external_daily_price','restrictions'=>'external_stay_restriction','offers'=>'external_stay_offer'];

    public static function apply(\PDO $pdo, $hotel, $json, $expectedSha, bool $dryRun=false): array
    {
        $scopes=['kazan'=>'1096','sochi'=>'1658'];
        if (!is_string($hotel) || !isset($scopes[$hotel]) || !is_string($json)
            || strlen($json)<1 || strlen($json)>LucianoWireEnvelope::MAX_WIRE_BYTES
            || !is_string($expectedSha) || !preg_match('/^[a-f0-9]{64}$/D',$expectedSha)
            || !hash_equals($expectedSha,hash('sha256',$json))) throw new \RuntimeException('Invalid initial wire input');
        if ($pdo->getAttribute(\PDO::ATTR_DRIVER_NAME)!=='mysql' || $pdo->inTransaction()
            || !function_exists('tPayload')) throw new \RuntimeException('Initial consumer needs the deployed CRM read-back contract and an idle MySQL connection');
        $wire=json_decode($json,true);
        if (json_last_error()!==JSON_ERROR_NONE || !is_array($wire) || LucianoWireEnvelope::encode($wire)!==$json
            || ($wire['crm_object_id']??null)!==$scopes[$hotel]) throw new \RuntimeException('Noncanonical or foreign initial wire');
        $fields=[];
        foreach (self::TABLES as $section=>$table) {
            $fields[$section]=array_column(self::rows($pdo,'SHOW COLUMNS FROM '.$table),'Field');
            $fields[$section]=array_values(array_diff($fields[$section],['id','snapshot_id','catalog_id','observation_id','crm_room_id','crm_rate_id']));
            $fields[$section][]='catalog_key';
            if (in_array($section,['daily','restrictions','offers'],true)) $fields[$section][]='observation_key';
        }
        // Entire packet validation precedes every INSERT; bind only the subsequently allocated actual snapshot ID.
        $cursor=new LucianoWireRowCursor($hotel,$wire,$fields);
        $payload=$cursor->originalPayload();$header=$payload['snapshot'];
        foreach (['generated_at','checked_from','checked_to'] as $field) self::utc($header[$field]??null);
        if ($header['checked_to']<$header['checked_from']) throw new \RuntimeException('Invalid original quote check interval');
        $object=$scopes[$hotel];$lock='luciano_crm_initial_'.$object;$held=false;
        try {
            $heldRows=self::rows($pdo,'SELECT GET_LOCK(?,0) AS held',[$lock]);
            if (count($heldRows)!==1 || (string)$heldRows[0]['held']!=='1') throw new \RuntimeException('Own initial consumer is busy');
            $held=true;
            if ($pdo->exec('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ')===false || !$pdo->beginTransaction()) throw new \RuntimeException('Could not begin isolated initial transaction');
            $tables=array_merge(['external_price_state','external_price_snapshot','external_sync_inbox'],array_values(self::TABLES));
            $engines=self::rows($pdo,'SELECT TABLE_NAME,ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('.implode(',',array_fill(0,count($tables),'?')).')',$tables);
            $engineMap=[];foreach ($engines as $e) {$engineMap[$e['TABLE_NAME']]=$e['ENGINE'];}
            foreach ($tables as $table) if (($engineMap[$table]??null)!=='InnoDB') throw new \RuntimeException('Initial import needs transactional storage');
            $states=self::rows($pdo,"SELECT * FROM external_price_state WHERE source='price_tonia_ru' AND crm_object_id=? FOR UPDATE",[$object]);
            $objects=self::rows($pdo,'SELECT id,name FROM object WHERE id=?',[$object]);
            $count=(int)self::rows($pdo,"SELECT COUNT(*) AS n FROM external_price_snapshot WHERE source='price_tonia_ru' AND crm_object_id=?",[$object])[0]['n'];
            $stateFields=array_column(self::rows($pdo,'SHOW COLUMNS FROM external_price_state'),'Field');
            $dormant=LucianoStateOnboarding::plan($hotel,$objects,$states,$count,$stateFields,'InnoDB');
            if ($dormant['action']!=='already_dormant') throw new \RuntimeException('Initial consumer cannot create or enable a destination state');
            $now=gmdate('Y-m-d H:i:s');
            $manifest=['transport_header'=>$header,'transport_payload_sha256'=>$wire['payload_sha256'],
                'transport_sender'=>'price','transport_mappings_sha256'=>$wire['mappings_sha256']];
            $snapshot=['source'=>'price_tonia_ru','crm_object_id'=>$object,'snapshot_key'=>$header['snapshot_key'],
                'upstream_snapshot_key'=>$header['snapshot_key'],'schema_version'=>'2','status'=>'staging',
                'coverage_from'=>$header['coverage_from'],'coverage_to_exclusive'=>$header['coverage_to_exclusive'],
                'generated_at'=>$header['generated_at'],'checked_from'=>$header['checked_from'],'checked_to'=>$header['checked_to'],
                'manifest_json'=>LucianoWireEnvelope::encode($manifest),'created_at'=>$now];
            $inbox=['snapshot_id'=>'18446744073709551615','sender'=>'price','chunk_key'=>'luciano-wire-v1',
                'payload_sha256'=>$wire['payload_sha256'],'row_count'=>$header['row_count'],'status'=>'applied',
                'payload_json'=>$json,'received_at'=>$now,'applied_at'=>$now];
            $packet=(int)self::rows($pdo,'SELECT @@max_allowed_packet AS packet')[0]['packet'];
            if ($packet<=16384) throw new \RuntimeException('Insufficient SQL packet budget');
            self::budget($pdo,'external_price_snapshot',$snapshot,$packet);
            self::budget($pdo,'external_sync_inbox',$inbox,$packet);
            // Budget every source row with maximum-width future IDs before the first INSERT.
            foreach (self::TABLES as $section=>$table) foreach ($payload['data'][$section] as $row) {
                $row['snapshot_id']='18446744073709551615';
                if ($section==='catalog') unset($row['catalog_key']);
                else {
                    unset($row['catalog_key']);$row['catalog_id']='18446744073709551615';
                    if ($section!=='observations') {
                        $row['observation_id']=$row['observation_key']===null?null:'18446744073709551615';unset($row['observation_key']);
                    }
                }
                self::budget($pdo,$table,$row,$packet);
            }
            $sid=self::insert($pdo,'external_price_snapshot',$snapshot);$cursor->bindSnapshotId($sid);
            while (($descriptor=$cursor->next())!==null) {
                $stmt=$pdo->prepare($descriptor['sql']);
                if (!$stmt || !$stmt->execute($descriptor['parameters']) || $stmt->rowCount()!==1) throw new \RuntimeException('Expected exactly one section row INSERT');
                $cursor->acceptId($pdo->lastInsertId());
            }
            $completed=$cursor->complete();$applied=gmdate('Y-m-d H:i:s');
            $stmt=$pdo->prepare("UPDATE external_price_snapshot SET status='ready',row_count=?,checksum=?,imported_at=? WHERE id=? AND source='price_tonia_ru' AND crm_object_id=? AND snapshot_key=? AND status='staging'");
            if (!$stmt || !$stmt->execute([$header['row_count'],$wire['payload_sha256'],$applied,$sid,$object,$header['snapshot_key']]) || $stmt->rowCount()!==1) throw new \RuntimeException('Scoped snapshot completion failed');
            $snapshots=self::rows($pdo,"SELECT * FROM external_price_snapshot WHERE source='price_tonia_ru' AND crm_object_id=? AND id=?",[$object,$sid]);
            $expectedSnapshot=$snapshot;$expectedSnapshot['status']='ready';
            $expectedSnapshot+=['id'=>$sid,'row_count'=>$header['row_count'],'checksum'=>$wire['payload_sha256'],
                'imported_at'=>$applied,'published_at'=>null,'error_summary'=>null];
            if (count($snapshots)!==1 || !self::matches($snapshots[0],$expectedSnapshot)) throw new \RuntimeException('Ready snapshot read-back failed');
            $reconstructed=\tPayload($pdo,$snapshots[0]);
            if (LucianoWireEnvelope::encode($reconstructed)!==LucianoWireEnvelope::encode($payload)) throw new \RuntimeException('Full imported payload differs from original');
            $inbox['snapshot_id']=$sid;$inbox['applied_at']=$applied;self::insert($pdo,'external_sync_inbox',$inbox);
            $stored=self::rows($pdo,"SELECT * FROM external_sync_inbox WHERE snapshot_id=? AND sender='price' AND chunk_key='luciano-wire-v1'",[$sid]);
            if (count($stored)!==1 || !self::matches($stored[0],$inbox+['error_summary'=>null])
                || !hash_equals($expectedSha,hash('sha256',$stored[0]['payload_json']))) throw new \RuntimeException('Compressed inbox read-back differs');
            $after=self::rows($pdo,"SELECT * FROM external_price_state WHERE source='price_tonia_ru' AND crm_object_id=?",[$object]);
            $countAfter=(int)self::rows($pdo,"SELECT COUNT(*) AS n FROM external_price_snapshot WHERE source='price_tonia_ru' AND crm_object_id=?",[$object])[0]['n'];
            if ($countAfter!==1 || LucianoWireEnvelope::encode($after)!==LucianoWireEnvelope::encode($states)) throw new \RuntimeException('Destination state or initial count changed');
            if ($dryRun) {
                if (!$pdo->rollBack()) throw new \RuntimeException('Initial rehearsal rollback failed');
            } elseif (!$pdo->commit()) throw new \RuntimeException('Initial transaction commit failed');
            return ['snapshot_id'=>$sid,'snapshot_key'=>$header['snapshot_key'],'status'=>'ready',
                'section_counts'=>$completed['section_counts'],'payload_sha256'=>$wire['payload_sha256'],'wire_sha256'=>$expectedSha,
                'database_written'=>!$dryRun,'dry_run'=>$dryRun,'activated'=>false,'published'=>false,'outbox_created'=>false,'ack_sent'=>false,
                'catalogue_mapping_verified'=>false,'consumer_integrated'=>false,'delivery_ready'=>false];
        } catch (\Throwable $error) {
            if ($pdo->inTransaction()) $pdo->rollBack();throw $error;
        } finally {
            if ($held) self::rows($pdo,'SELECT RELEASE_LOCK(?) AS released',[$lock]);
        }
    }

    private static function rows(\PDO $pdo, $sql, array $values=[]): array
    {
        $stmt=$pdo->prepare($sql);
        if (!$stmt || !$stmt->execute($values)) throw new \RuntimeException('Initial consumer scoped read failed');
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    private static function insert(\PDO $pdo, $table, array $row): string
    {
        $stmt=$pdo->prepare('INSERT INTO `'.$table.'` (`'.implode('`,`',array_keys($row)).'`) VALUES ('.implode(',',array_fill(0,count($row),'?')).')');
        if (!$stmt || !$stmt->execute(array_values($row)) || $stmt->rowCount()!==1) throw new \RuntimeException('Expected exactly one metadata INSERT');
        $id=$pdo->lastInsertId();
        if (!is_string($id) || !preg_match('/^[1-9][0-9]{0,19}$/D',$id)
            || (strlen($id)===20 && strcmp($id,'18446744073709551615')>0)) throw new \RuntimeException('Invalid generated metadata ID');
        return $id;
    }
    private static function budget(\PDO $pdo, $table, array $row, $packet): void
    {
        $literals=[];
        foreach ($row as $value) {
            if ($value===null) $literals[]='NULL';
            else {$q=$pdo->quote((string)$value);if ($q===false) throw new \RuntimeException('SQL quoting failed');$literals[]=$q;}
        }
        $sql='INSERT INTO `'.$table.'` (`'.implode('`,`',array_keys($row)).'`) VALUES ('.implode(',',$literals).')';
        if (strlen($sql)+16384>=$packet) throw new \RuntimeException('Initial SQL frame exceeds reviewed packet budget');
    }
    private static function utc($value): void
    {
        if (!is_string($value)) throw new \RuntimeException('Missing original UTC timestamp');
        $date=\DateTimeImmutable::createFromFormat('!Y-m-d H:i:s',$value,new \DateTimeZone('UTC'));
        if (!$date || $date->format('Y-m-d H:i:s')!==$value) throw new \RuntimeException('Invalid original UTC timestamp');
    }
    private static function matches(array $row,array $expected): bool
    {
        foreach ($expected as $field=>$value) {
            if (!array_key_exists($field,$row)) return false;
            if ($value===null) {if ($row[$field]!==null) return false;}
            elseif ((!is_string($row[$field]) && !is_int($row[$field])) || (string)$row[$field]!==$value) return false;
        }
        return true;
    }
}
