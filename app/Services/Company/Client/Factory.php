<?php

namespace App\Services\Company\Client;

use App\Models\Business;
use Throwable;
use App\Models\Company;
use App\Models\TaxInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Database\QueryException;
use App\Exceptions\DuplicateEntityException;

class Factory
{
    public function add(array $details): string
    {
        try {
            DB::beginTransaction();
            $details['user'] = auth()->id();
            $company = Company::create($details);
            DB::commit();
            return $company->id;
        } catch (Throwable $e) {
            DB::rollback();
            info($e);
            throw new (__('services.general_error'));
        }
    }

    public function update(Company $company, array $details)
    {
        try {
            DB::beginTransaction();
            $details['user'] = auth()->id();
            $company->update($details);
            DB::commit();
            return $company->id;
        } catch (Throwable $e) {
            DB::rollback();
            info($e);
            throw new (__('services.general_error'));
        }
    }

    public function updateCompany (TaxInformation $tax, Request $request)
    {
        DB::beginTransaction();
        try {

            $tax->update(
                Collection::make($request)
                ->except(
                    'source_of_fund'
                )
                ->toArray()
            );

            if ($request->has("source_of_fund")) {
                $this->sourceOfFund($tax, $request->get('source_of_fund'));
            }

            if ($request->has('source_of_wealth')) {
                $this->sourceOfWealth($tax, $request->get('source_of_wealth'));
            }

            DB::commit();
                return $tax->id;
        } catch (Throwable $e) {
            info($e);
            DB::rollBack();
            $this->throwException($e);
        }
    }

    public function sourceOfFund($tax, $request)
    {
        $fundSources = $tax->sourceOfFund()->first();
        $existingSourceOfFund = $fundSources->sourceOfFund;
        $fundPayload = $this->fundCollection($request);

        if ($existingSourceOfFund) {
            $tax->update($existingSourceOfFund);
        } else {
            $tax->sourceOfFund()->create($fundPayload);
        }

        if ($request->has('source_of_fund.countries')) {
            // creation of countries for source of funds
        }
    }

    private function fundCollection($request)
    {
        return [
            'source_fund_names' => $request->get('source_fund_names'),
        ];
    }

    public function sourceOfWealth($tax, $request)
    {
        $fundSources = $tax->sourceOfFund()->first();
        $existingSourceOfFund = $fundSources->sourceOfFund;
        $fundPayload = $this->wealthCollection($request);

        if ($existingSourceOfFund) {
            $tax->update($existingSourceOfFund);
        } else {
            $tax->sourceOfFund()->create($fundPayload);
        }

        if ($request->has('source_of_wealth.countries')) {
            // creation of countries for source of funds
        }
    }

    private function wealthCollection($request)
    {
        return [
            'source_wealth_names' => $request->get('source_wealth_names'),
        ];
    }

    private function throwException(Throwable $e)
    {
        if ($e instanceof QueryException && $e->errorInfo[1] == 1062) {
            throw new DuplicateEntityException($e->errorInfo[2], $e->getCode());
        }

        throw $e;
    }
}
