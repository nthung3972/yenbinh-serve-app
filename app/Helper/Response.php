<?php

namespace App\Helper;

/**
 * Response Class helper
 */
class Response
{
    /**
     * @param array $data
     * @param string $message
     * @param int $code
     * @param bool $success
     * @return \Illuminate\Http\JsonResponse
     */
    public static function data($data = [], $message = 'Successfully', $code = 200, $success = true)
    {
        $dataFormat = [
            'success' => $success,
            'data' => $data,
            'message' => $message,
            'code' => $code
        ];
        return response()->json($dataFormat, $code);
    }

    /**
     * @param array $data
     * @param int $code
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    public static function dataError($code = 422, $data = [], $message = 'Error')
    {
        // Đảm bảo $code là HTTP status hợp lệ
        if (!is_numeric($code) || $code < 100 || $code > 599) {
            $code = 422; // fallback an toàn
        } else {
            $code = (int) $code;
        }

        $dataFormat = [
            'success' => false,
            'data' => $data,
            'message' => $message,
            'code' => $code
        ];

        return response()->json($dataFormat, $code);
    }
}
