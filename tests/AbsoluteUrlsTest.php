<?php

declare(strict_types=1);

namespace Phlib\Guzzle;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @package Phlib\Guzzle-Middleware
 */
class AbsoluteUrlsTest extends TestCase
{
    /**
     * @dataProvider transformFullPageProvider
     */
    public function testTransformFullPage(string $baseUrl, string $preFile, string $postFile): void
    {
        $response = new Response(200, [
            'Content-Type' => 'text/html',
        ], file_get_contents($preFile));

        $handler = new HandlerStack(new MockHandler([$response]));
        $handler->push(new AbsoluteUrls());
        $client = new HttpClient([
            'handler' => $handler,
        ]);
        $response = $client->get($baseUrl);

        Assert::assertSame(
            file_get_contents($postFile),
            (string)$response->getBody()
        );
    }

    public function transformFullPageProvider(): array
    {
        return [
            [
                'https://example.com/foo/bar/world',
                __DIR__ . '/_files/AbsoluteUrls/pre.html',
                __DIR__ . '/_files/AbsoluteUrls/post.html',
            ],
        ];
    }

    /**
     * @dataProvider transformProvider
     */
    public function testTransform(string $baseUrl, string $source, string $expected): void
    {
        $response = new Response(200, [
            'Content-Type' => 'text/html',
        ], $source);

        $handler = new HandlerStack(new MockHandler([$response]));
        $handler->push(new AbsoluteUrls());
        $client = new HttpClient([
            'handler' => $handler,
        ]);
        $response = $client->get($baseUrl);

        $this->assertSame(
            $expected,
            (string)$response->getBody()
        );
    }

    public function transformProvider(): array
    {
        return [
            [
                'http://example.com',
                '<a href="#">',
                '<a href="http://example.com/">',
            ],
            [
                'http://example.com/test1/test2/tmp.html',
                '<a href="http://example.com/test1/page2.html">',
                '<a href="http://example.com/test1/page2.html">',
            ],
            [
                'http://example.com/test1/test2/tmp.html',
                '<a href="../page2.html">',
                '<a href="http://example.com/test1/page2.html">',
            ],
            [
                'http://example.com/test1/test2/tmp.html',
                '<a href="mailto:test@example.com">',
                '<a href="mailto:test@example.com">',
            ],

            // absolutely relative, shallow URL path
            [
                'http://example.com/tmp.html',
                '<body background="/images/bg.jpg">',
                '<body background="http://example.com/images/bg.jpg">',
            ],
            [
                'http://example.com/tmp.html',
                "div { background: url('/images/bg.gif'); }",
                "div { background: url('http://example.com/images/bg.gif'); }",
            ],
            [
                'http://example.com/tmp.html',
                '<img src="/images/default.png">',
                '<img src="http://example.com/images/default.png">',
            ],
            [
                'http://example.com/tmp.html',
                '<a href="/page2.html">',
                '<a href="http://example.com/page2.html">',
            ],

            // relative, shallow URL path
            [
                'http://example.com/tmp.html',
                '<body background="images/bg.jpg">',
                '<body background="http://example.com/images/bg.jpg">',
            ],
            [
                'http://example.com/tmp.html',
                "div { background: url('images/bg.gif'); }",
                "div { background: url('http://example.com/images/bg.gif'); }",
            ],
            [
                'http://example.com/tmp.html',
                '<img src="images/default.png">',
                '<img src="http://example.com/images/default.png">',
            ],
            [
                'http://example.com/tmp.html',
                '<a href="page2.html">',
                '<a href="http://example.com/page2.html">',
            ],

            // absolute, shallow URL path
            [
                'http://example.com/tmp.html',
                '<body background="http://example.com/images/bg.jpg">',
                '<body background="http://example.com/images/bg.jpg">',
            ],
            [
                'http://example.com/tmp.html',
                "div { background: url('http://example.com/images/bg.gif'); }",
                "div { background: url('http://example.com/images/bg.gif'); }",
            ],
            [
                'http://example.com/tmp.html',
                '<img src="http://example.com/images/default.png">',
                '<img src="http://example.com/images/default.png">',
            ],
            [
                'http://example.com/tmp.html',
                '<a href="http://example.com/page2.html">',
                '<a href="http://example.com/page2.html">',
            ],

            // absolutely relative, one deep URL path
            [
                'http://example.com/path/tmp.html',
                '<body background="/images/bg.jpg">',
                '<body background="http://example.com/images/bg.jpg">',
            ],
            [
                'http://example.com/path/tmp.html',
                "div { background: url('/images/bg.gif'); }",
                "div { background: url('http://example.com/images/bg.gif'); }",
            ],
            [
                'http://example.com/path/tmp.html',
                '<img src="/images/default.png">', '<img src="http://example.com/images/default.png">',
            ],
            [
                'http://example.com/path/tmp.html',
                '<a href="/page2.html">',
                '<a href="http://example.com/page2.html">',
            ],

            // relative, one deep URL path
            [
                'http://example.com/path/tmp.html',
                '<body background="images/bg.jpg">',
                '<body background="http://example.com/path/images/bg.jpg">',
            ],
            [
                'http://example.com/path/tmp.html',
                "div { background: url('images/bg.gif'); }",
                "div { background: url('http://example.com/path/images/bg.gif'); }",
            ],
            [
                'http://example.com/path/tmp.html',
                '<img src="images/default.png">',
                '<img src="http://example.com/path/images/default.png">',
            ],
            [
                'http://example.com/path/tmp.html',
                '<a href="page2.html">',
                '<a href="http://example.com/path/page2.html">',
            ],

            // absolute, one deep URL path
            [
                'http://example.com/path/tmp.html',
                '<body background="http://example.com/images/bg.jpg">',
                '<body background="http://example.com/images/bg.jpg">',
            ],
            [
                'http://example.com/path/tmp.html',
                "div { background: url('http://example.com/images/bg.gif'); }",
                "div { background: url('http://example.com/images/bg.gif'); }",
            ],
            [
                'http://example.com/path/tmp.html',
                '<img src="http://example.com/images/default.png">',
                '<img src="http://example.com/images/default.png">',
            ],
            [
                'http://example.com/path/tmp.html',
                '<a href="http://example.com/page2.html">',
                '<a href="http://example.com/page2.html">',
            ],

            // absolutely relative, two deep URL path
            [
                'http://example.com/path/to/tmp.html',
                '<body background="/images/bg.jpg">',
                '<body background="http://example.com/images/bg.jpg">',
            ],
            [
                'http://example.com/path/to/tmp.html',
                "div { background: url('/images/bg.gif'); }",
                "div { background: url('http://example.com/images/bg.gif'); }",
            ],
            [
                'http://example.com/path/to/tmp.html',
                '<img src="/images/default.png">',
                '<img src="http://example.com/images/default.png">',
            ],
            [
                'http://example.com/path/to/tmp.html',
                '<a href="/page2.html">',
                '<a href="http://example.com/page2.html">',
            ],

            // relative, two deep URL path
            [
                'http://example.com/path/to/tmp.html',
                '<body background="images/bg.jpg">',
                '<body background="http://example.com/path/to/images/bg.jpg">',
            ],
            [
                'http://example.com/path/to/tmp.html',
                "div { background: url('images/bg.gif'); }",
                "div { background: url('http://example.com/path/to/images/bg.gif'); }",
            ],
            [
                'http://example.com/path/to/tmp.html',
                '<img src="images/default.png">',
                '<img src="http://example.com/path/to/images/default.png">',
            ],
            [
                'http://example.com/path/to/tmp.html',
                '<a href="page2.html">',
                '<a href="http://example.com/path/to/page2.html">',
            ],
        ];
    }

    public function onlyTextHtmlProvider(): array
    {
        return [
            [
                'text/html',
                'assertNotSame',
            ],
            [
                'text/html; charset=utf8',
                'assertNotSame',
            ],
            [
                'text/html+x',
                'assertSame',
            ],
            [
                'application/json',
                'assertSame',
            ],
        ];
    }

    /**
     * @dataProvider onlyTextHtmlProvider
     */
    public function testOnlyTextHtml(string $contentType, string $assertion): void
    {
        $baseUrl = 'http://example.com/path/to/tmp.html';
        $source = '<a href="page2.html">';

        $response = new Response(200, [
            'Content-Type' => $contentType,
        ], $source);

        $handler = new HandlerStack(new MockHandler([$response]));
        $handler->push(new AbsoluteUrls());
        $client = new HttpClient([
            'handler' => $handler,
        ]);
        $response = $client->get($baseUrl);

        $actual = (string)$response->getBody();

        $this->{$assertion}($source, $actual);
    }
}
