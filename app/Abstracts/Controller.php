<?php

namespace App\Abstracts;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Http\Response;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    const SORT_ASC = 'asc';
    const SORT_DESC = 'desc';

    /**
     * Wrap the response
     *
     * @param array $response   the return response
     * @param int   $statusCode the return status code
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function response($response, $statusCode = Response::HTTP_OK): JsonResponse
    {
        return response()->json(
            $response,
            $statusCode
        );
    }

    /**
     * Collect the objects and do a pagination if the request has the necessary
     * queries
     *
     */
    protected function collect($model)
    {
        $request = request();

        if ($this->isPaginated()) {
            $limit = $request->input('limit', 10);
            $page = $request->input('page', 1);

            return $model->paginate(
                $limit,
                ['*'],
                'page',
                $page
            );
        }

        return $model->get();
    }

    protected function isPaginated(): bool
    {
        $request = request();

        return ($request->has('limit') && is_numeric($request->input('limit')))
            || ($request->has('page') && is_numeric($request->input('page')));
    }
}
