<?php

namespace App\Traits;

use Illuminate\Support\Facades\Crypt;

trait HasEncryptedFields
{
    public static function getHashedValue($value)
    {
        return hash_hmac('sha256', $value, config('hashing.bidx_key'));
    }

    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (in_array($key, $this->encryptable) && !is_null($value)) {
            $value = json_decode(Crypt::decryptString($value));
        }

        return $value;
    }

    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->encryptable) && !is_null($value)) {
            parent::setAttribute("{$key}_bidx", self::getHashedValue($value));
            $value = Crypt::encryptString(json_encode($value));
        }

        return parent::setAttribute($key, $value);
    }
}
