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
    /**
     * @var string
     */
    private $charset;

    /**
     * @var string
     */
    private $headerRe = '/(charset=)([a-z0-9][a-z0-9-]+)(.*)/i';

    /**
     * @var string
     */
    private $metaRe = '/(<meta[^>]+charset=["\']?)([a-z][a-z0-9-]+)(["\']?[^>]*>)/i';

    /**
     * ConvertCharset constructor.
     *
     * @param string $charset
     */
    public function __construct(string $charset = 'utf8')
    {
        $this->charset = $charset;
    }

    /**
     * @param callable $handler
     * @return \Closure
     */
    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, $options) use ($handler) {
            $promise = $handler($request, $options);
            return $promise->then(
                function (ResponseInterface $response) {

                    $contentType = $response->getHeader('Content-Type')[0] ?? '';
                    if (!preg_match('/^text\/html(?:[\t ]*;.*)?$/i', $contentType)) {
                        return $response;
                    }

                    return $this->rewriteCharset(
                        $this->convertEncoding(
                            $response
                        )
                    );
                }
            );
        };
    }

    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    private function convertEncoding(ResponseInterface $response): ResponseInterface
    {
        $fromCharset = $this->getCharset($response);

        $content  = mb_convert_encoding(
            (string)$response->getBody(),
            $this->charset,
            $fromCharset
        );

        return $response->withBody(stream_for($content));
    }

    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    private function rewriteCharset(ResponseInterface $response): ResponseInterface
    {
        if ($response->hasHeader('Content-Type')) {
            $headers = $response->getHeader('Content-Type');
            $newHeaders = [];
            foreach ($headers as $header) {
                $newHeaders[] = preg_replace($this->headerRe, "\$1{$this->charset}\$3", $header);
            }
            $response = $response->withHeader('Content-Type', $newHeaders);
        }

        $content = (string)$response->getBody();
        $content = preg_replace($this->metaRe, "\$1{$this->charset}\$3", $content);

        return $response->withBody(stream_for($content));
    }

    /**
     * @param ResponseInterface $response
     * @return string
     * @throws \Exception
     */
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
