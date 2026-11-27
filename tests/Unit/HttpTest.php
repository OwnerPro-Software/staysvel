<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http as HttpClient;
use Staysvel\Http;

test('get sends a json request by default', function ()
{
    (new Http())->get('/booking/reservations-export', ['from' => '2026-01-01']);

    HttpClient::assertSent(function (Request $request)
    {
        return $request->method() === 'GET'
            && $request->hasHeader('Accept', 'application/json')
            && str_contains($request->url(), '/external/v1/booking/reservations-export?from=2026-01-01');
    });
});

test('get in xlsx mode sends the spreadsheet content type', function ()
{
    (new Http())->isXlsx()->get('/booking/reservations-export', ['from' => '2026-01-01']);

    HttpClient::assertSent(function (Request $request)
    {
        return $request->method() === 'GET'
            && $request->hasHeader('Accept', 'application/vnd.openxmlformats')
            && str_contains($request->url(), '/external/v1/booking/reservations-export?from=2026-01-01');
    });
});
