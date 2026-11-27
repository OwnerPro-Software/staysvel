<?php

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http as HttpClient;
use Staysvel\Http;
use Staysvel\Stays;

class PmsUnreachableForTest extends RuntimeException
{
}

beforeEach(function ()
{
    // TestCase::setUp() registers a catch-all 200 stub and stubs are matched
    // in registration order, so start from a clean factory and make every
    // request fail the way an unreachable Stays does (Guzzle ConnectException,
    // which the Laravel client converts into a ConnectionException).
    HttpClient::swap(new Factory());
    HttpClient::fake(fn ($request) => Factory::failedConnection()($request));
});

afterEach(function ()
{
    // The hook is process-wide static state — do not leak it into other tests.
    Stays::whenUnreachable(null);
});

dataset('http calls', [
    'get' => ['GET', '/content/listings/abc', fn (Http $http) => $http->get('/content/listings/abc')],
    'post' => ['POST', '/booking/reservations', fn (Http $http) => $http->post('/booking/reservations', ['a' => 1])],
    'patch' => ['PATCH', '/booking/reservations/abc', fn (Http $http) => $http->patch('/booking/reservations/abc', ['a' => 1])],
    'delete' => ['DELETE', '/booking/reservations/abc', fn (Http $http) => $http->delete('/booking/reservations/abc')],
    'get xlsx' => ['GET', '/booking/reservations-export', fn (Http $http) => $http->isXlsx()->get('/booking/reservations-export')],
]);

test('with a hook the call throws the exception the hook returned', function (string $method, string $uri, Closure $call)
{
    $thrown = new PmsUnreachableForTest('stays is down');

    Stays::whenUnreachable(fn () => $thrown);

    try
    {
        $call(new Http());
    }
    catch (Throwable $caught)
    {
        expect($caught)->toBe($thrown);

        return;
    }

    $this->fail('Expected the hook exception to be thrown.');
})->with('http calls');

test('the hook receives the connection exception, the http verb and the uri', function (string $method, string $uri, Closure $call)
{
    $received = [];

    Stays::whenUnreachable(function (ConnectionException $connectionException, string $method, string $uri) use (&$received): Throwable
    {
        $received = [$connectionException, $method, $uri];

        return new PmsUnreachableForTest('stays is down', 0, $connectionException);
    });

    expect(fn () => $call(new Http()))->toThrow(PmsUnreachableForTest::class);

    expect($received[0])->toBeInstanceOf(ConnectionException::class)
        ->and($received[1])->toBe($method)
        ->and($received[2])->toBe($uri);
})->with('http calls');

test('without a hook the connection exception escapes as before', function (string $method, string $uri, Closure $call)
{
    expect(fn () => $call(new Http()))->toThrow(ConnectionException::class);
})->with('http calls');

test('whenUnreachable(null) removes the hook', function (string $method, string $uri, Closure $call)
{
    Stays::whenUnreachable(fn () => new PmsUnreachableForTest('stays is down'));
    Stays::whenUnreachable(null);

    expect(fn () => $call(new Http()))->toThrow(ConnectionException::class);
})->with('http calls');

test('the hook applies to calls made through the Stays chain', function ()
{
    $received = [];

    Stays::whenUnreachable(function (ConnectionException $connectionException, string $method, string $uri) use (&$received): Throwable
    {
        $received = [$method, $uri];

        return new PmsUnreachableForTest('stays is down');
    });

    expect(fn () => Stays::timeout(30)->content()->listings()->get('abc'))
        ->toThrow(PmsUnreachableForTest::class);

    expect($received)->toBe(['GET', '/content/listings/abc']);
});

test('the hook applies to the xlsx export made through the Stays chain', function ()
{
    $received = [];

    Stays::whenUnreachable(function (ConnectionException $connectionException, string $method, string $uri) use (&$received): Throwable
    {
        $received = [$method, $uri];

        return new PmsUnreachableForTest('stays is down');
    });

    expect(fn () => Stays::booking()->reservations()->export()->xlsx())
        ->toThrow(PmsUnreachableForTest::class);

    expect($received)->toBe(['GET', '/booking/reservations-export']);
});
