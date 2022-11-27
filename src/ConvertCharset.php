<?php

declare(strict_types=1);

namespace Phlib\Guzzle;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use function GuzzleHttp\Psr7\stream_for;

/**
 * Class ConvertCharset
 *
 * @package Phlib\Guzzle
 */
class ConvertCharset
{
    private string $headerRe = '/(charset=)([a-z0-9][a-z0-9-]+)(.*)/i';

    private string $metaRe = '/(<meta[^>]+charset=["\']?)([a-z][a-z0-9-]+)(["\']?[^>]*>)/i';

    public function __invoke(callable $handler): \Closure
    {
        return function (RequestInterface $request, $options) use ($handler) {
            $promise = $handler($request, $options);
            return $promise->then(
                function (ResponseInterface $response) use ($options): ResponseInterface {
                    $contentType = $response->getHeaderLine('Content-Type');
                    if (!preg_match('/^text\/html(?:[\t ]*;.*)?$/i', $contentType)) {
                        return $response;
                    }

                    $toCharset = $options['convert_charset'] ?? 'UTF-8';

                    return $this->rewriteCharset(
                        $this->convertEncoding(
                            $response,
                            $toCharset
                        ),
                        $toCharset
                    );
                }
            );
        };
    }

    private function convertEncoding(ResponseInterface $response, string $toCharset): ResponseInterface
    {
        $fromCharset = $this->getCharset($response);

        $content = mb_convert_encoding(
            (string)$response->getBody(),
            $toCharset,
            $fromCharset
        );

        return $response->withBody(stream_for($content));
    }

    private function rewriteCharset(ResponseInterface $response, string $toCharset): ResponseInterface
    {
        if ($response->hasHeader('Content-Type')) {
            $headers = $response->getHeader('Content-Type');
            $newHeaders = [];
            foreach ($headers as $header) {
                $newHeaders[] = preg_replace($this->headerRe, "\$1{$toCharset}\$3", $header);
            }
            $response = $response->withHeader('Content-Type', $newHeaders);
        }

        $content = (string)$response->getBody();
        $content = preg_replace($this->metaRe, "\$1{$toCharset}\$3", $content);

        return $response->withBody(stream_for($content));
    }

    private function getCharset(ResponseInterface $response): string
    {
        if ($response->hasHeader('Content-Type')) {
            $headers = $response->getHeader('Content-Type');
            foreach ($headers as $header) {
                if (preg_match($this->headerRe, $header, $matches)) {
                    return $matches[2];
                }
            }
        }

        $content = (string)$response->getBody();
        if (preg_match($this->metaRe, $content, $matches)) {
            return $matches[2];
        }

        return mb_detect_encoding((string)$response->getBody());
    }
}
