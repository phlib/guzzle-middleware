<?php
declare(strict_types=1);

namespace Phlib\Guzzle;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class ConvertCharsetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider metaProvider
     * @param string $toCharset
     * @param string $preFile
     * @param string $postFile
     */
    public function testMeta(string $toCharset, string $preFile, string $postFile)
    {
        $response = new Response(200, [ 'Content-Type' => 'text/html' ], file_get_contents($preFile));

        $handler = new HandlerStack(new MockHandler([$response]));
        $handler->push(new ConvertCharset());
        $client = new HttpClient([
            'convert_charset' => $toCharset,
            'handler'         => $handler
        ]);
        $response = $client->get('http://www.example.com/');

        $this->assertSame(
            file_get_contents($postFile),
            (string)$response->getBody()
        );
    }

    public function metaProvider()
    {
        return [
            [
                'UTF-8',
                __DIR__ . '/_files/ConvertCharset/chinese-big5.meta.html',
                __DIR__ . '/_files/ConvertCharset/chinese-big5.meta-utf8.html',
            ],
            [
                'UTF-8',
                __DIR__ . '/_files/ConvertCharset/chinese.meta.html',
                __DIR__ . '/_files/ConvertCharset/chinese.meta-utf8.html',
            ],
            [
                'UTF-8',
                __DIR__ . '/_files/ConvertCharset/euc-kr.meta.html',
                __DIR__ . '/_files/ConvertCharset/euc-kr.meta-utf8.html',
            ],
            [
                'UTF-8',
                __DIR__ . '/_files/ConvertCharset/french.meta.html',
                __DIR__ . '/_files/ConvertCharset/french.meta-utf8.html',
            ],
            [
                'UTF-8',
                __DIR__ . '/_files/ConvertCharset/german.meta.html',
                __DIR__ . '/_files/ConvertCharset/german.meta-utf8.html',
            ],
            [
                'UTF-8',
                __DIR__ . '/_files/ConvertCharset/greek.meta.html',
                __DIR__ . '/_files/ConvertCharset/greek.meta-utf8.html',
            ],
            [
                'UTF-8',
                __DIR__ . '/_files/ConvertCharset/hebrew-visual.meta.html',
                __DIR__ . '/_files/ConvertCharset/hebrew-visual.meta-utf8.html',
            ],
            [
                'UTF-8',
                __DIR__ . '/_files/ConvertCharset/iso-8859-1.meta.html',
                __DIR__ . '/_files/ConvertCharset/iso-8859-1.meta-utf8.html',
            ],
            [
                'UTF-8',
                __DIR__ . '/_files/ConvertCharset/japanese.meta.html',
                __DIR__ . '/_files/ConvertCharset/japanese.meta-utf8.html',
            ],
            [
                'UTF-8',
                __DIR__ . '/_files/ConvertCharset/russian.meta.html',
                __DIR__ . '/_files/ConvertCharset/russian.meta-utf8.html',
            ],
            [
                'UTF-8',
                __DIR__ . '/_files/ConvertCharset/windows-1252.meta.html',
                __DIR__ . '/_files/ConvertCharset/windows-1252.meta-utf8.html',
            ],

            // Reverse
            [
                'big5',
                __DIR__ . '/_files/ConvertCharset/chinese-big5.meta-utf8.html',
                __DIR__ . '/_files/ConvertCharset/chinese-big5.meta.html',
            ],
            [
                'GB2312',
                __DIR__ . '/_files/ConvertCharset/chinese.meta-utf8.html',
                __DIR__ . '/_files/ConvertCharset/chinese.meta.html',
            ],
            [
                'euc-kr',
                __DIR__ . '/_files/ConvertCharset/euc-kr.meta-utf8.html',
                __DIR__ . '/_files/ConvertCharset/euc-kr.meta.html',
            ],
            [
                'iso-8859-1',
                __DIR__ . '/_files/ConvertCharset/french.meta-utf8.html',
                __DIR__ . '/_files/ConvertCharset/french.meta.html',
            ],
            [
                'iso-8859-1',
                __DIR__ . '/_files/ConvertCharset/german.meta-utf8.html',
                __DIR__ . '/_files/ConvertCharset/german.meta.html',
            ],
            [
                'iso-8859-7',
                __DIR__ . '/_files/ConvertCharset/greek.meta-utf8.html',
                __DIR__ . '/_files/ConvertCharset/greek.meta.html',
            ],
            [
                'iso-8859-8',
                __DIR__ . '/_files/ConvertCharset/hebrew-visual.meta-utf8.html',
                __DIR__ . '/_files/ConvertCharset/hebrew-visual.meta.html',
            ],
            [
                'ISO-8859-1',
                __DIR__ . '/_files/ConvertCharset/iso-8859-1.meta-utf8.html',
                __DIR__ . '/_files/ConvertCharset/iso-8859-1.meta.html',
            ],
            [
                'iso-2022-jp',
                __DIR__ . '/_files/ConvertCharset/japanese.meta-utf8.html',
                __DIR__ . '/_files/ConvertCharset/japanese.meta.html',
            ],
            [
                'iso-8859-5',
                __DIR__ . '/_files/ConvertCharset/russian.meta-utf8.html',
                __DIR__ . '/_files/ConvertCharset/russian.meta.html',
            ],
            [
                'windows-1252',
                __DIR__ . '/_files/ConvertCharset/windows-1252.meta-utf8.html',
                __DIR__ . '/_files/ConvertCharset/windows-1252.meta.html',
            ],
        ];
    }

    /**
     * @dataProvider headerProvider
     * @param string $header
     * @param string $preFile
     * @param string $postFile
     */
    public function testHeader(string $header, string $preFile, string $postFile)
    {
        $response = new Response(200, [
            'Content-Type' => $header
        ], file_get_contents($preFile));

        $handler = new HandlerStack(new MockHandler([$response]));
        $handler->push(new ConvertCharset());
        $client = new HttpClient(['handler' => $handler]);
        $response = $client->get('http://www.example.com/');

        $this->assertSame(
            file_get_contents($postFile),
            (string)$response->getBody(),
            $this->getDataSetAsString()
        );
    }

    public function headerProvider()
    {
        return [
            [
                'text/html; charset=ISO-8859-1',
                __DIR__ . '/_files/ConvertCharset/iso-8859-1.header.html',
                __DIR__ . '/_files/ConvertCharset/iso-8859-1.header-utf8.html',
            ],
            [
                'text/html; charset=iso-8859-7',
                __DIR__ . '/_files/ConvertCharset/greek.header.html',
                __DIR__ . '/_files/ConvertCharset/greek.header-utf8.html',
            ],

            // test no conversion happens when we're not text/html
            [
                'text/html+x; charset=iso-8859-7',
                __DIR__ . '/_files/ConvertCharset/greek.header.html',
                __DIR__ . '/_files/ConvertCharset/greek.header.html',
            ],
            [
                'application/json',
                __DIR__ . '/_files/ConvertCharset/greek.header.html',
                __DIR__ . '/_files/ConvertCharset/greek.header.html',
            ],
        ];
    }
}
