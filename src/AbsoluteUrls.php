<?php

declare(strict_types=1);

namespace Phlib\Guzzle;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Sabre\Uri;

use function GuzzleHttp\Psr7\stream_for;

/**
 * Class AbsoluteUrls
 *
 * @package Phlib\Guzzle
 */
class AbsoluteUrls
{
    /**
     * @return \Closure
     */
    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, $options) use ($handler) {
            $promise = $handler($request, $options);
            return $promise->then(
                function (ResponseInterface $response) use ($request) {
                    $contentType = $response->getHeaderLine('Content-Type');
                    if (!preg_match('/^text\/html(?:[\t ]*;.*)?$/i', $contentType)) {
                        return $response;
                    }

                    return $response->withBody(
                        stream_for(
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
            function (array $matches) use ($baseUrl) {
                $path = Uri\resolve($baseUrl, $matches[2]);
                return $matches[1] . $path . $matches[3];
            },
            $content
        );
    }
}
