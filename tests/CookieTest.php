<?php

namespace Ixbox\Moonlight;

use PHPUnit\Framework\TestCase;

class CookieTest extends TestCase
{
    /**
     * @test
     */
    public function 文字列表現()
    {
        $cookie = new Cookie('Name', 'Value', [
            'expires' => 0,
            'path' => '/path',
            'domain' => 'example.com',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);

        $cookie = $cookie->withExpires(1);

        $this->assertEquals(
            'Name=Value; Expires=Thu, 01 Jan 1970 00:00:00 GMT; Max-Age=0; Path=/path; Domain=example.com; Secure; HttpOnly; SameSite=Strict',
            (string) $cookie,
        );
    }
}
