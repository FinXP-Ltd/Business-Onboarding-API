<?php

namespace App\Services;

use Throwable;
use DB;
use Illuminate\Http\Response;
use App\Models\Company;
use Auth;

class CompanyService
{
    public function __construct(private Company $company)
    {
        $this->company = $company;
    }

    public function add(array $details): string
    {
        try {
            DB::beginTransaction();
            $details['user'] = auth()->id();
            $company = $this->company::create($details);
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
}
