<?php

function error($message, $code)
{
    return response()->json([
        'success' => false,
        'message' => [$message]
    ], $code);
}

function success($message)
{
    return response()->json([
        'success' => true,
        'message' => [$message]
    ], SUCCESS_200);
}

function successWithData($message, $data)
{
    return response()->json([
        'success' => true,
        'message' => [$message],
        'data' => $data
    ], SUCCESS_200);
}
