<?php

namespace Staysvel;

use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http AS HttpClient;
use Throwable;

class Http
{
    /**
     * Process-wide hook that decides what is thrown when Stays cannot be
     * reached. Null keeps the ConnectionException from the Laravel client.
     *
     * @var (Closure(ConnectionException, string, string): Throwable)|null
     */
    private static ?Closure $whenUnreachable = null;

    private bool $isXlsx = false;
    private int $timeout = 30;
    private int $connectTimeout = 10;

    /**
     * @param (Closure(ConnectionException, string, string): Throwable)|null $callback
     */
    public static function whenUnreachable(?Closure $callback): void
    {
        self::$whenUnreachable = $callback;
    }

    public function timeout(int $timeout): static
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function connectTimeout(int $connectTimeout): static
    {
        $this->connectTimeout = $connectTimeout;

        return $this;
    }

    public function isXlsx(): static
    {
        $this->isXlsx = true;

        return $this;
    }

    /**
     * @throws ConnectionException|Throwable
     */
    public function get(string $uri, array $parameters = []): Response
    {
        return $this->send('GET', $uri, fn (PendingRequest $client) => $client->get($uri, $parameters));
    }

    /**
     * @throws ConnectionException|Throwable
     */
    public function post(string $uri, array $parameters = []): Response
    {
        return $this->send('POST', $uri, fn (PendingRequest $client) => $client->post($uri, $parameters));
    }

    /**
     * @throws ConnectionException|Throwable
     */
    public function patch(string $uri, array $parameters = []): Response
    {
        return $this->send('PATCH', $uri, fn (PendingRequest $client) => $client->patch($uri, $parameters));
    }

    /**
     * @throws ConnectionException|Throwable
     */
    public function delete(string $uri): Response
    {
        return $this->send('DELETE', $uri, fn (PendingRequest $client) => $client->delete($uri));
    }

    /**
     * Single exit point for every request: a ConnectionException (timeout,
     * refused connection, DNS failure) is handed to the whenUnreachable hook,
     * when one is registered, and whatever the hook returns is thrown instead.
     *
     * @param Closure(PendingRequest): Response $request
     *
     * @throws ConnectionException|Throwable
     */
    private function send(string $method, string $uri, Closure $request): Response
    {
        try {
            return $request($this->client());
        }
        catch (ConnectionException $connectionException)
        {
            if (self::$whenUnreachable === null)
            {
                throw $connectionException;
            }

            throw (self::$whenUnreachable)($connectionException, $method, $uri);
        }
    }

    private function client(): PendingRequest
    {
        $client = $this->isXlsx ? HttpClient::staysXlsx() : HttpClient::stays();

        return $client
            ->timeout($this->timeout)
            ->connectTimeout($this->connectTimeout);
    }
}
