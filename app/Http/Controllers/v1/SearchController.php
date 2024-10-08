<?php

namespace App\Http\Controllers\v1;

use App\Abstracts\Controller as BaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests\SearchPersonRequest;
use App\Models\Auth\User;
use App\Models\Person\NaturalPerson;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Models\NonNaturalPerson\NonNaturalPerson;
use Illuminate\Support\Facades\Crypt;

class SearchController extends BaseController
{
    public function searchPerson(SearchPersonRequest $request)
    {
        $user = User::where('auth0', auth()->id())->firstOrFail();
        $hashedName = NaturalPerson::getHashedValue($request->name);

        $naturalPersons = NaturalPerson::where('user_id', $user?->id)
            ->where('name_bidx', 'like', "%{$hashedName}%")
            ->orWhere('surname', 'like', "%{$request->name}%")
            ->select('id', 'name', 'surname', 'date_of_birth', 'email_address')
            ->get()->map(function (NaturalPerson $natural) {
                return [
                    'id' => $natural->id,
                    'name' => $natural->name.' '.$natural->surname,
                    'date_of_birth' => $natural->date_of_birth,
                    'email_address' => $natural->email_address,
                    'type' => 'P'
                ];
            })->toArray();

        $nonPersons = NonNaturalPerson::where('user_id', $user?->id)
            ->where('name', 'like', "%{$request->name}%")
            ->select('id', 'name', 'registration_number')
            ->get()->map(function (NonNaturalPerson $non) {
                return [
                    'id' => $non->id,
                    'name' => $non->name,
                    'registration_number' => $non->registration_number,
                    'type' => 'N',
                ];
            })->toArray();

        $person =array_merge($naturalPersons, $nonPersons);

        return $this->response([
            'status' => Response::HTTP_OK,
            'message' => __('response.search.success', [
                'search_found' => count($person)
            ]),
            'data' => $person
        ], Response::HTTP_OK);
    }
}
