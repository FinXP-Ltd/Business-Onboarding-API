<?php

namespace App\Services\NonPerson\Client;

use Throwable;
use App\Models\NonNaturalPerson\NonNaturalPerson;
use App\Exceptions\NonNaturalPersonException;
use Illuminate\Support\Facades\Auth;
use App\Models\Auth\User;
use DB;
use App\Traits\SaveUserToken;

class Factory
{
    use SaveUserToken;

    public function __construct(private NonNaturalPerson $nonPerson)
    {
        $this->nonPerson = $nonPerson;
    }

    public function add(array $nonPerson)
    {
        try {
            DB::beginTransaction();
            $nonPerson['user_id']= $this->saveUsers();
            $non = $this->nonPerson::create($nonPerson);
            $non->addresses()->create($nonPerson['address']);
            DB::commit();
            return $non->id;
        } catch (Throwable $e) {
            DB::rollback();
            info($e);
            throw new NonNaturalPersonException(__('services.general_error'));
        }
    }

    public function update(NonNaturalPerson $nonNatural, array $nonPerson)
    {
        try {
            DB::beginTransaction();
            $nonNatural->update($nonPerson);
            $nonNatural->addresses()->update($nonPerson['address']);
            DB::commit();
            return $nonNatural->id;
        } catch (Throwable $e) {
            DB::rollback();
            info($e);
            throw new NonNaturalPersonException(__('services.general_error'));
        }
    }
}
