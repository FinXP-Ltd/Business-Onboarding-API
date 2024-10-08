<?php

namespace App\Services\Person\Client;

use Throwable;
use App\Models\Person\NaturalPerson;
use App\Exceptions\NaturalPersonException;
use App\Models\LookupType;
use App\Models\Lookup;
use App\Models\Auth\User;
use Illuminate\Support\Facades\Auth;
use DB;
use App\Traits\SaveUserToken;
class Factory
{
    use SaveUserToken;

    public function __construct(private NaturalPerson $person)
    {
        $this->person = $person;
    }

    public function add(array $person): string
    {
        try {
            DB::beginTransaction();
            $person['user_id']= $this->saveUsers();

            $natural = $this->person::create($person);
            $natural->addresses()->create($person['address']);
            $natural->identificationDocument()->create($person['identification_document']);
            $natural->additionalInfos()->create($person['additional_info']);
            DB::commit();

            return $natural->id;

        } catch (Throwable $e) {
            DB::rollback();
            info($e);
            throw new NaturalPersonException(__('services.general_error'));
        }
    }

    public function update(NaturalPerson $natural, array $person)
    {
        try {
            DB::beginTransaction();
            $natural->update($person);
            $natural->addresses->update($person['address']);
            $natural->identificationDocument->update($person['identification_document']);
            $natural->additionalInfos->update($person['additional_info']);
            DB::commit();

            return $natural->id;

        } catch (Throwable $e) {
            DB::rollback();
            info($e);
            throw new NaturalPersonException(__('services.general_error'));
        }
    }
}
