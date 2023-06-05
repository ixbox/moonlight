<?php

namespace Ixbox\Moonlight;

use PHPUnit\Framework\TestCase;

class PatisserieTest extends TestCase
{
    /**
     * @test
     */
    public function Cookieの作成()
    {
        $patisserie = new Patisserie([
            'expires' => 0,
            'path' => '/path',
            'domain' => 'example.com',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        $patisserie = $patisserie->withDomain('example.net');

        $cookie = $patisserie->bake('Name', 'Value', [
            'samesite' => 'None',
        ]);

        $this->assertEquals(
            'Name=Value; Expires=Thu, 01 Jan 1970 00:00:00 GMT; Max-Age=0; Path=/path; Domain=example.net; Secure; HttpOnly; SameSite=None',
            (string) $cookie,
        );
    }
}
