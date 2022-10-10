<?php
namespace App\Http\Controllers;

use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class BaseController extends Controller
{
    /* DEFINE HTTP RESPONSE CODE */
    const HTTP_OK                       = Response::HTTP_OK;
    const HTTP_INTERNAL_SERVER_ERROR    = Response::HTTP_INTERNAL_SERVER_ERROR;
    const HTTP_CREATED                  = Response::HTTP_CREATED;
    const HTTP_UNPROCESSABLE_ENTITY     = Response::HTTP_UNPROCESSABLE_ENTITY;
    const HTTP_NOT_FOUND                = Response::HTTP_NOT_FOUND;
    const HTTP_UNAUTHORIZED             = Response::HTTP_UNAUTHORIZED;
    const HTTP_BAD_REQUEST              = Response::HTTP_BAD_REQUEST;
    const HTTP_FORBIDDEN                = Response::HTTP_FORBIDDEN;
    const HTTP_NO_CONTENT               = Response::HTTP_NO_CONTENT;
    const HTTP_TOO_MANY_REQUESTS        = Response::HTTP_TOO_MANY_REQUESTS;
    const HTTP_SERVICE_UNAVAILABLE      = Response::HTTP_SERVICE_UNAVAILABLE;

    /* RETURN STANDARD QUERY PARAMETERS */
    public function getBaseQueryParams($request, $props)
    {
        $props['take']          = $request['take'] ?? 25;
        $props['take']          = is_numeric($props['take']) ? $props['take'] : 25;
        $props['skip']          = $request['page'] ?? 1;
        $props['skip']          = is_numeric($props['skip']) ? $props['skip'] : 1;
        $props['skip']          = $props['skip'] - 1;
        $props['search']        = $request['search'] ?? null;
        $props['filter']        = $request['filter'] ?? null;
        $props['date_field']    = $request['date_field'] ?? 'created_at';
        $props['start']         = $request['start'] ?? null;
        $props['start']         = strtotime($props['start']) ? date("Y/m/d", strtotime($props['start'])) : null;
        $props['end']           = $request['end'] ?? null;
        $props['end']           = strtotime($props['end']) ? date("Y/m/d", strtotime($props['end'])) : null;
        $props['sort_field']    = $request['sort_field'] ?? 'id';
        $props['sort_option']   = $request['sort_option'] ?? 'DESC';
        $props['sort_option']   = strtoupper($props['sort_option']) == 'ASC' ? 'ASC' : 'DESC';

        return $props;
    }

    /* RETURN EXCEPTION RESPONSE */
    public function returnExceptionResponse($status = "error", $statusCode = 400, $ex)
    {
        $exResponse = [];
        switch (true) {
            case config('app.debug') == true:
                $exResponse['message']  = $ex->getMessage();
                $exResponse['line']     = $ex->getLine();
                $exResponse['file']     = $ex->getFile();
                $exResponse['trace']    = $ex->getTrace();
                break;

            default:
                $exResponse['message'] = $ex->getMessage();
                break;
        }

        return response()->json(
            [
                'status'        => $status,
                'status_code'   => $statusCode,
                'message'       => $exResponse['message'],
                'data'          => $exResponse
            ]
        );
    }

    /* RETURN RESPONSE */
    public function returnResponse($status = 'success', $statusCode = 200, $message, $data = null)
    {
        return response()->json(
            [
                'status'        => $status,
                'status_code'   => $statusCode,
                'message'       => $message,
                'data'          => $data
            ]
        );
    }

    /* RETURN AUTH SUCCESS RESPONSE */
    public function authSuccessResponse($code = 200, $message, $data = null, $token = null){
        return response()->json(
            [
                'status'        => 'success',
                'status_code'   => $code,
                'access_token'  => $token,
                'message'       => $message,
                'data'          => $data
            ], self::HTTP_OK
        );
    }

    /* RETURN NO PERMISSION RESPONSE */
    public function returnNoPermissionResponse()
    {
        $response  = [
            'status'        => 'error',
            'status_code'   => self::HTTP_UNAUTHORIZED,
            'message'       => 'Anda tidak memiliki kewenangan melakukannya!',
            'data'          => null
        ];
        return $response;
    }

    /* RETURN REQUEST VALIDATION */
    public function returnValidator($props, $rules){
        return Validator::make($props, $rules);
    }
}
