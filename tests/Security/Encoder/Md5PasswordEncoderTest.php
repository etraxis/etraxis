<?php

//----------------------------------------------------------------------
//
//  Copyright (C) 2018 Artem Rodygin
//
//  This file is part of eTraxis.
//
//  You should have received a copy of the GNU General Public License
//  along with eTraxis. If not, see <https://www.gnu.org/licenses/>.
//
//----------------------------------------------------------------------

namespace eTraxis\Security\Encoder;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

/**
 * @coversDefaultClass \eTraxis\Security\Encoder\Md5PasswordEncoder
 */
class Md5PasswordEncoderTest extends TestCase
{
    private Md5PasswordEncoder $encoder;

    protected function setUp()
    {
        parent::setUp();

        $this->encoder = new Md5PasswordEncoder();
    }

    /**
     * @covers ::encodePassword
     */
    public function testEncodePassword()
    {
        static::assertSame('8dbdda48fb8748d6746f1965824e966a', $this->encoder->encodePassword('simple'));
    }

    /**
     * @covers ::encodePassword
     */
    public function testEncodePasswordMaxLength()
    {
        $raw = str_repeat('*', Md5PasswordEncoder::MAX_PASSWORD_LENGTH);

        try {
            $this->encoder->encodePassword($raw);
        }
        catch (\Exception $exception) {
            static::fail();
        }

        static::assertTrue(true);
    }

    /**
     * @covers ::encodePassword
     */
    public function testEncodePasswordTooLong()
    {
        $this->expectException(BadCredentialsException::class);

        $raw = str_repeat('*', Md5PasswordEncoder::MAX_PASSWORD_LENGTH + 1);

        $this->encoder->encodePassword($raw);
    }

    /**
     * @covers ::isPasswordValid
     */
    public function testIsPasswordValid()
    {
        $encoded = '8dbdda48fb8748d6746f1965824e966a';
        $valid   = 'simple';
        $invalid = 'invalid';

        static::assertTrue($this->encoder->isPasswordValid($encoded, $valid));
        static::assertFalse($this->encoder->isPasswordValid($encoded, $invalid));
    }

    /**
     * @covers ::needsRehash
     */
    public function testNeedsRehash()
    {
        $encoded = '8dbdda48fb8748d6746f1965824e966a';

        static::assertTrue($this->encoder->needsRehash($encoded));
    }
}
