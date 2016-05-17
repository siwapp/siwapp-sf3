<?php

namespace Siwapp\CoreBundle\Tests;

use Siwapp\CoreBundle\HtmlPageMerger;

class HtmlPageMergerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerTestMerge
     */
    public function testMerge(array $pages, $expected)
    {
        $merger = new HtmlPageMerger;
        // Remove new lines before check.
        $this->assertEquals($expected, str_replace(PHP_EOL, '', $merger->merge($pages)));
    }

    public function providerTestMerge()
    {
        return [
            // Simple case.
            [
                [
                    '<html><head></head><body>Foo</body></html>',
                    '<html><head></head><body>Bar</body></html>',
                ],
                '<html><head></head><body>FooBar</body></html>',
            ],
            // Test that body attributes dont make a difference.
            [
                [
                    '<html><head></head><body>Foo</body></html>',
                    '<html><head></head><body attr="sth">Bar</body></html>',
                ],
                '<html><head></head><body>FooBar</body></html>',
            ],
            [
                [
                    '<html><head></head><body attr="sth">Foo</body></html>',
                    '<html><head></head><body>Bar</body></html>',
                ],
                '<html><head></head><body>FooBar</body></html>',
            ],
            // Check that CAPS tags also are OK.
            [
                [
                    '<html><head></head><BODY>Foo</BODY></html>',
                    '<html><head></head><BODY>Bar</BODY></html>',
                ],
                '<html><head></head><body>FooBar</body></html>',
            ],
        ];
    }
}
