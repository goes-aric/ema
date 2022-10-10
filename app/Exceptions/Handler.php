<?php

namespace App\Exceptions;

use Throwable;
use ErrorException;
use Illuminate\Http\Response;
use Illuminate\Database\QueryException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

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
        //
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
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($request->wantsJson()) {
            if ($exception instanceof ModelNotFoundException){
                $message = "Resource not found. Error Message : ";
                $response = $this->returnExceptionResponse(Response::HTTP_NOT_FOUND, $message, $exception);

                return $response;
            }

            if ($exception instanceof PostTooLargeException) {
                $message = "Size of attached file should be less " . ini_get("upload_max_filesize") . "B. Error Message : ";
                $response = $this->returnExceptionResponse(Response::HTTP_REQUEST_ENTITY_TOO_LARGE, $message, $exception);

                return $response;
            }

            if ($exception instanceof ThrottleRequestsException) {
                $message = "Too Many Requests, Please Slow Down. Error Message : ";
                $response = $this->returnExceptionResponse(Response::HTTP_TOO_MANY_REQUESTS, $message, $exception);

                return $response;
            }

            if ($exception instanceof QueryException || $exception instanceof \InvalidArgumentException) {
                $message = "There was Issue with the Query. Error Message : ";
                $response = $this->returnExceptionResponse(Response::HTTP_INTERNAL_SERVER_ERROR, $message, $exception);

                return $response;
            }

            if ($exception instanceof ErrorException) {
                $message = "There was some internal error. Error Message : ";
                $response = $this->returnExceptionResponse(Response::HTTP_INTERNAL_SERVER_ERROR, $message, $exception);

                return $response;
            }

            if ($exception instanceof \Error) {
                $message = "There was some internal error. Error Message : ";
                $response = $this->returnExceptionResponse(Response::HTTP_INTERNAL_SERVER_ERROR, $message, $exception);

                return $response;
            }
        }

        return parent::render($request, $exception);
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return response()->json([
            'status'        => 'error',
            'status_code'   => Response::HTTP_UNAUTHORIZED,
            'message'       => 'Unauthenticated or Token Expired, Please Login'
        ], Response::HTTP_UNAUTHORIZED);
    }

    protected function returnExceptionResponse($httpResponse, $message, $exception)
    {
        switch (true) {
            case config('app.debug') == true:
                $exResponse['message']  = $message . " " . $exception->getMessage();
                $exResponse['line']     = $exception->getLine();
                $exResponse['file']     = $exception->getFile();
                $exResponse['trace']    = $exception->getTrace();
                break;

            default:
                $exResponse['message'] = $exception->getMessage();
                break;
        }

        return response()->json(
            [
                'status'        => 'error',
                'status_code'   => $httpResponse,
                'message'       => $exResponse['message'],
                'return'        => $exResponse
            ], $httpResponse
        );
    }
}
