<?php

declare(strict_types=1);

namespace Phlib\Guzzle;

use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Sabre\Uri;

/**
 * @package Phlib\Guzzle-Middleware
 */
class AbsoluteUrls
{
    public function __invoke(callable $handler): \Closure
    {
        return function (RequestInterface $request, $options) use ($handler) {
            $promise = $handler($request, $options);
            return $promise->then(
                function (ResponseInterface $response) use ($request): ResponseInterface {
                    $contentType = $response->getHeaderLine('Content-Type');
                    if (!preg_match('/^text\/html(?:[\t ]*;.*)?$/i', $contentType)) {
                        return $response;
                    }

                    return $response->withBody(
                        Utils::streamFor(
                            $this->transform(
                                (string)$response->getBody(),
                                (string)$request->getUri()
                            )
                        )
                    );
                }
            );
        };
    }

    private function transform(string $content, string $baseUrl): string
    {
        $baseUrl = Uri\normalize($baseUrl);

        $search = [
            '/(href=")([^"]+)(")/i',
            '/(src=")([^"]+)(")/i',
            '/(background=")([^"]+)(")/i',
            '/(url\(["\']?)(.+?)(["\']?\))/i',
        ];

        return preg_replace_callback(
            $search,
            function (array $matches) use ($baseUrl): string {
                $path = Uri\resolve($baseUrl, $matches[2]);
                return $matches[1] . $path . $matches[3];
            },
            $content
        );
    }
}
