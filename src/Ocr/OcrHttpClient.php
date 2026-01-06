<?php

declare(strict_types=1);

namespace PhpVision\YandexVision\Ocr;

use PhpVision\YandexVision\Ocr\Request\OcrRequestInterface;
use PhpVision\YandexVision\Exception\ApiException;
use PhpVision\YandexVision\Exception\HttpException;
use PhpVision\YandexVision\Exception\ValidationException;
use PhpVision\YandexVision\Transports\TransportInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

final readonly class OcrHttpClient
{
    public function __construct(
        private TransportInterface $transport,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory
    ) {
    }

    /**
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    public function send(OcrRequestInterface $request): array
    {
        return $this->sendJsonRequest(
            $request->getMethod(),
            $request->getUrl(),
            $request->getHeaders(),
            $request->getBody()
        );
    }

    /**
     * @param array<string, string> $headers
     * @param array<string, mixed>|null $body
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    private function sendJsonRequest(string $method, string $url, array $headers, ?array $body): array
    {
        $request = $this->requestFactory->createRequest($method, $url);
        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        if ($body !== null) {
            try {
                $json = json_encode($body, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            } catch (\JsonException $exception) {
                throw new ValidationException('Unable to encode request payload to JSON: ' . $exception->getMessage());
            }
            $request = $request->withBody($this->streamFactory->createStream($json));
        }

        try {
            $response = $this->transport->send($request);
        } catch (ClientExceptionInterface $exception) {
            throw new HttpException('HTTP transport error: ' . $exception->getMessage(), 0, $exception);
        }

        return $this->parseJsonResponse($response);
    }

    /**
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    private function parseJsonResponse(ResponseInterface $response): array
    {
        $statusCode = $response->getStatusCode();
        $requestId = $response->getHeaderLine('x-request-id');
        $meta = [
            'status_code' => $statusCode,
            'request_id' => $requestId !== '' ? $requestId : null,
        ];

        $body = (string) $response->getBody();
        if ($statusCode < 200 || $statusCode >= 300) {
            $snippet = $this->truncateBody($body);
            $message = 'API request failed with status ' . $statusCode;
            if ($snippet !== '') {
                $message .= ': ' . $snippet;
            }
            throw new ApiException($message, $statusCode);
        }

        if ($body === '') {
            return [[], $meta];
        }

        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new ValidationException('Unable to decode JSON response: ' . $exception->getMessage());
        }

        if (!is_array($data)) {
            throw new ValidationException('Unexpected JSON response payload.');
        }

        return [$data, $meta];
    }

    private function truncateBody(string $body, int $limit = 1000): string
    {
        $body = trim($body);
        if ($body === '') {
            return '';
        }

        if (strlen($body) > $limit) {
            return substr($body, 0, $limit) . '...';
        }

        return $body;
    }
}
