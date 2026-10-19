<?php

namespace App\Support;

/** Lossless wire draft only. Existing gateways do not accept this format yet. */
final class LucianoWireEnvelope
{
    const FORMAT = 'luciano-wire-envelope-v1';
    const MAX_WIRE_BYTES = 33554432;
    const MAX_DECODED_BYTES = 268435456;

    public static function pack(array $envelope): array
    {
        self::checkEnvelope($envelope);
        $json = self::encode($envelope);
        $bytes = strlen($json);
        if ($bytes > self::MAX_DECODED_BYTES) throw new \RuntimeException('Decoded envelope exceeds wire draft bound');
        $gzip = gzencode($json, 9);
        if ($gzip === false) throw new \RuntimeException('Wire draft compression failed');
        $p = $envelope['payload'];
        $wire = [
            'format'=>self::FORMAT, 'encoding'=>'gzip-base64',
            'source'=>$p['source'], 'sender'=>$envelope['sender'],
            'crm_object_id'=>$p['crm_object_id'], 'external_property_key'=>$p['external_property_key'],
            'snapshot_key'=>$p['snapshot']['snapshot_key'],
            'payload_sha256'=>$envelope['payload_sha256'], 'mappings_sha256'=>$envelope['mappings_sha256'],
            'decoded_bytes'=>$bytes, 'decoded_sha256'=>hash('sha256',$json),
            'compressed_sha256'=>hash('sha256',$gzip), 'encoded_envelope'=>base64_encode($gzip),
        ];
        if (strlen(self::encode($wire)) > self::MAX_WIRE_BYTES) throw new \RuntimeException('Encoded wire draft exceeds bound');
        return $wire;
    }

    public static function unpack(array $wire, string $receiver): array
    {
        $wanted = ['format','encoding','source','sender','crm_object_id','external_property_key','snapshot_key',
            'payload_sha256','mappings_sha256','decoded_bytes','decoded_sha256','compressed_sha256','encoded_envelope'];
        $actual = array_keys($wire); sort($actual); sort($wanted);
        if ($actual !== $wanted || $wire['format'] !== self::FORMAT || $wire['encoding'] !== 'gzip-base64'
            || $wire['source'] !== 'price_tonia_ru' || !in_array($receiver,['crm','site'],true)
            || $wire['sender'] !== ($receiver === 'crm' ? 'price' : 'crm')
            || !is_int($wire['decoded_bytes']) || $wire['decoded_bytes'] < 1
            || $wire['decoded_bytes'] > self::MAX_DECODED_BYTES) {
            throw new \RuntimeException('Unsupported scoped wire draft');
        }
        self::checkScope($wire['crm_object_id'],$wire['external_property_key'],$wire['snapshot_key']);
        foreach (['payload_sha256','mappings_sha256','decoded_sha256','compressed_sha256'] as $key) {
            if (!is_string($wire[$key]) || !preg_match('/^[a-f0-9]{64}$/D',$wire[$key])) throw new \RuntimeException('Invalid wire digest');
        }
        if (!is_string($wire['encoded_envelope']) || strlen($wire['encoded_envelope']) > self::MAX_WIRE_BYTES
            || strlen(self::encode($wire)) > self::MAX_WIRE_BYTES) throw new \RuntimeException('Encoded wire draft exceeds bound');
        $gzip = base64_decode($wire['encoded_envelope'],true);
        if ($gzip === false || base64_encode($gzip) !== $wire['encoded_envelope'] || strlen($gzip)<18
            || substr($gzip,0,4)!=="\x1f\x8b\x08\x00" || !hash_equals($wire['compressed_sha256'],hash('sha256',$gzip))) {
            throw new \RuntimeException('Invalid compressed wire draft');
        }
        // Limit allocation before JSON decoding; a size claim never permits more.
        $json = @gzdecode($gzip,$wire['decoded_bytes']+1);
        if ($json === false || strlen($json)!==$wire['decoded_bytes']
            || !hash_equals($wire['decoded_sha256'],hash('sha256',$json))) throw new \RuntimeException('Decoded wire draft integrity failed');
        $tail=unpack('Vcrc/Vsize',substr($gzip,-8));
        if ($tail['size']!==strlen($json) || sprintf('%08x',$tail['crc'])!==hash('crc32b',$json)) {
            throw new \RuntimeException('Unexpected gzip trailer');
        }
        $envelope=json_decode($json,true,512);
        if (json_last_error()!==JSON_ERROR_NONE || !is_array($envelope) || self::encode($envelope)!==$json) {
            throw new \RuntimeException('Wire draft is not a canonical envelope');
        }
        self::checkEnvelope($envelope);
        $p=$envelope['payload'];
        foreach (['source','crm_object_id','external_property_key'] as $key) {
            if ($wire[$key]!==$p[$key]) throw new \RuntimeException('Wire identity does not match envelope');
        }
        if ($wire['snapshot_key']!==$p['snapshot']['snapshot_key'] || $wire['sender']!==$envelope['sender']
            || $wire['payload_sha256']!==$envelope['payload_sha256'] || $wire['mappings_sha256']!==$envelope['mappings_sha256']) {
            throw new \RuntimeException('Wire header does not match envelope');
        }
        return $envelope;
    }

    public static function encode($value): string
    {
        $result=json_encode(self::canonical($value),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        if ($result===false) throw new \RuntimeException('Wire JSON encoding failed');
        return $result;
    }

    private static function canonical($value)
    {
        if (!is_array($value)) return $value;
        if (array_keys($value)!==range(0,count($value)-1)) ksort($value,SORT_STRING);
        foreach ($value as &$child) $child=self::canonical($child);
        unset($child); return $value;
    }

    private static function checkScope($object,$property,$key): void
    {
        if (!is_string($object) || !is_string($property) || !is_string($key)
            || !in_array([$object,$property],[['1096','14'],['1658','15']],true)
            || !preg_match('/^reload-v1-luciano-initial-'.$object.'-[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/D',$key)) {
            throw new \RuntimeException('Foreign wire draft identity');
        }
    }

    private static function checkEnvelope(array $e): void
    {
        if (!isset($e['payload'],$e['sender'],$e['payload_sha256'],$e['mappings'],$e['mappings_sha256'])
            || !is_array($e['payload']) || !is_array($e['mappings']) || !in_array($e['sender'],['price','crm'],true)
            || !is_string($e['payload_sha256']) || !is_string($e['mappings_sha256'])
            || !hash_equals(hash('sha256',self::encode($e['payload'])),$e['payload_sha256'])
            || !hash_equals(hash('sha256',self::encode($e['mappings'])),$e['mappings_sha256'])) {
            throw new \RuntimeException('Envelope integrity failed');
        }
        $p=$e['payload'];
        if (($p['source']??null)!=='price_tonia_ru' || ($p['format']??null)!=='price-tonia-snapshot-v1'
            || !isset($p['snapshot'],$p['data']) || !is_array($p['snapshot']) || !is_array($p['data'])
            || ($p['snapshot']['schema_version']??null)!=='2') throw new \RuntimeException('Unsupported envelope');
        self::checkScope($p['crm_object_id']??null,$p['external_property_key']??null,$p['snapshot']['snapshot_key']??null);
        $sections=array_keys($p['data']);sort($sections);
        if ($sections!==['catalog','daily','observations','offers','restrictions'] || !is_array($p['data']['daily'])
            || !count($p['data']['daily']) || (string)count($p['data']['daily'])!==($p['snapshot']['row_count']??null)) {
            throw new \RuntimeException('Incomplete envelope sections');
        }
        foreach ($p['data'] as $records) {
            if (!is_array($records)) throw new \RuntimeException('Invalid envelope records');
            foreach ($records as $row) {
                if (!is_array($row)) throw new \RuntimeException('Invalid envelope row');
                foreach ($row as $value) if ($value!==null && !is_string($value)) throw new \RuntimeException('Invalid envelope scalar');
            }
        }
    }
}
