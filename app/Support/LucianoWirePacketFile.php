<?php
namespace App\Support;

/** Stage only authenticated-by-digest Luciano PRICE wire DATA. No database or transport actions. */
final class LucianoWirePacketFile
{
    public static function validate($hotel, $expectedSha, $json): array
    {
        $scopes=['kazan'=>['1096','14'],'sochi'=>['1658','15']];
        if (!is_string($hotel) || !isset($scopes[$hotel]) || !is_string($expectedSha)
            || !preg_match('/^[a-f0-9]{64}$/D',$expectedSha) || !is_string($json)
            || strlen($json)<1 || strlen($json)>LucianoWireEnvelope::MAX_WIRE_BYTES
            || !hash_equals($expectedSha,hash('sha256',$json))) throw new \RuntimeException('Invalid scoped packet digest or size');
        $wire=json_decode($json,true);
        if (json_last_error()!==JSON_ERROR_NONE || !is_array($wire) || LucianoWireEnvelope::encode($wire)!==$json
            || ($wire['crm_object_id']??null)!==$scopes[$hotel][0]
            || ($wire['external_property_key']??null)!==$scopes[$hotel][1]) throw new \RuntimeException('Packet framing or scope differs');
        $envelope=LucianoWireEnvelope::unpack($wire,'crm');
        $counts=[];foreach ($envelope['payload']['data'] as $section=>$rows) $counts[$section]=count($rows);
        return ['packet_path'=>'/var/tmp/price-tonia-luciano-wire-v1-'.$wire['crm_object_id'].'-'.$wire['snapshot_key'].'-crm-packet.json',
            'hotel'=>$hotel,'object_id'=>$wire['crm_object_id'],'property_id'=>$wire['external_property_key'],
            'snapshot_key'=>$wire['snapshot_key'],'wire_sha256'=>$expectedSha,'wire_bytes'=>strlen($json),
            'original_payload_sha256'=>$wire['payload_sha256'],'original_envelope_sha256'=>$wire['decoded_sha256'],
            'original_envelope_bytes'=>$wire['decoded_bytes'],'section_counts'=>$counts,
            'full_codec_verified'=>true,'schema_and_quotes_validated'=>false,
            'database_written'=>false,'snapshot_created'=>false,'activated'=>false,'outbox_created'=>false,
            'gateway_called'=>false,'ack_sent'=>false,'consumer_integrated'=>false,'delivery_ready'=>false];
    }

    public static function stage($hotel, $expectedSha, $json): array
    {
        // Derive the path from a freshly validated packet; no caller-supplied destination or plan.
        $report=self::validate($hotel,$expectedSha,$json);$path=$report['packet_path'];
        clearstatcache(true,$path);$before=@lstat($path);
        if ($before!==false) {
            self::regular($before);
            if (is_link($path)) throw new \RuntimeException('Existing packet is a symlink');
            $f=@fopen($path,'rb');if (!$f) throw new \RuntimeException('Existing packet cannot be opened');
            try {
                if (!flock($f,LOCK_SH|LOCK_NB)) throw new \RuntimeException('Existing packet is busy');
                $opened=fstat($f);self::identity($before,$opened);
                $read=stream_get_contents($f,LucianoWireEnvelope::MAX_WIRE_BYTES+1);
                $after=fstat($f);self::identity($opened,$after);
                if ($opened['size']!==$after['size'] || $opened['mtime']!==$after['mtime'] || $opened['ctime']!==$after['ctime']
                    || $read!==$json) throw new \RuntimeException('Existing packet differs; preserve it without overwrite');
                clearstatcache(true,$path);self::identity($after,lstat($path));
            } finally { fclose($f); }
            return $report+['file_written'=>false,'existing_identical_packet'=>true];
        }
        $mask=umask(0077);$f=@fopen($path,'x+b');umask($mask);
        if (!$f) throw new \RuntimeException('Exclusive packet creation failed');
        try {
            if (!flock($f,LOCK_EX|LOCK_NB)) throw new \RuntimeException('Packet lock failed');
            $opened=fstat($f);self::regular($opened);
            $offset=0;while ($offset<strlen($json)) {
                $n=fwrite($f,substr($json,$offset,1048576));if (!$n) throw new \RuntimeException('Packet write failed');$offset+=$n;
            }
            if (!fflush($f)) throw new \RuntimeException('Packet flush failed');
            rewind($f);$read=stream_get_contents($f,LucianoWireEnvelope::MAX_WIRE_BYTES+1);
            $after=fstat($f);self::identity($opened,$after);
            clearstatcache(true,$path);self::identity($after,lstat($path));
            if (is_link($path) || $after['size']!==strlen($json) || $read!==$json
                || !hash_equals($expectedSha,hash('sha256',$read))) throw new \RuntimeException('Packet read-back differs');
        } finally { fclose($f); }
        // Failed/partial DATA is deliberately retained; never unlink, truncate or replace existing artifacts.
        return $report+['file_written'=>true,'existing_identical_packet'=>false];
    }

    private static function regular($s): void
    {
        if (!is_array($s) || ($s['mode']&0170000)!==0100000 || ($s['mode']&0777)!==0600) {
            throw new \RuntimeException('Packet must be a regular 0600 DATA file');
        }
    }
    private static function identity($a,$b): void
    {
        self::regular($a);self::regular($b);
        if ($a['dev']!==$b['dev'] || $a['ino']!==$b['ino']) throw new \RuntimeException('Packet file identity changed');
    }
}
