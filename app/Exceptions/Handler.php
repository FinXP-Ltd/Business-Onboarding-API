<?php

namespace App\Exceptions;

use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Throwable;
use Illuminate\Http\Response;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use KeycloakGuard\Exceptions\TokenException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Finxp\Reactor\Reactor;
use Spatie\Permission\Exceptions\UnauthorizedException;
class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
       UnauthorizedException::class,
       AuthenticationException::class
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request   Request Instance
     * @param \Exception               $exception Exception Instance
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function render($request, Throwable $exception)
    {
        Reactor::report($exception, 'error');

        $defaultException = $exception->getCode();
        $defaultMessage = $exception->getMessage();

        switch (true) {
            case $exception instanceof UnauthorizedException:
                $code =  Response::HTTP_FORBIDDEN;
                $message = $exception->getMessage();
                break;
            case $exception instanceof AuthenticationException:
                $code = Response::HTTP_UNAUTHORIZED;
                $message = __('response.error.unauthenticated');
                break;
            case $exception instanceof ModelNotFoundException:
            case $exception instanceof NotFoundHttpException:
                $code = Response::HTTP_NOT_FOUND;
                $message = __('response.error.general_not_found');
                break;
            case $exception instanceof ValidationException:
                $code = Response::HTTP_UNPROCESSABLE_ENTITY;
                $message = $exception->errors();
                break;
            case $exception instanceof DuplicateEntityException:
                $code = Response::HTTP_CONFLICT;
                $message = $defaultMessage;
                break;
            case $exception instanceof TokenException:
                $code = Response::HTTP_UNAUTHORIZED;
                $message = str_replace('[Keycloak Guard] ', '', $defaultMessage);
                break;
            case $exception instanceof DocumentException:
            case $exception instanceOf CompanyRepresentativeDocumentException:
            case $exception instanceof Auth0Exception:
            case $exception instanceof HttpExceptionInterface:
                $code = $defaultException;
                $message = $defaultMessage;
                break;
            case $exception instanceof QueryException:
                $code = Response::HTTP_TOO_MANY_REQUESTS;
                $message = 'Too many connection';
                break;
            case $exception instanceof SharedApplicationException:
                $code = Response::HTTP_INTERNAL_SERVER_ERROR;
                $message = 'Unable to share application!';
                break;
            default:
                $code = Response::HTTP_INTERNAL_SERVER_ERROR;
                $message = $defaultMessage;
                break;
        }

        if ($defaultMessage === 'Too Many Attempts.') {
            $code = Response::HTTP_TOO_MANY_REQUESTS;
        }

        return response()->json(
            [
                'code' => $code,
                'status' => 'failed',
                'message' => $message
            ],
            $code
        );
    }
}
