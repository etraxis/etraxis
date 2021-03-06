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

namespace eTraxis\Controller\UsersController;

use eTraxis\Entity\User;
use eTraxis\TransactionalTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers \eTraxis\Controller\API\UsersController::updateUser
 */
class UpdateUserTest extends TransactionalTestCase
{
    public function testSuccess()
    {
        $this->loginAs('admin@example.com');

        /** @var User $user */
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['email' => 'nhills@example.com']);

        $data = [
            'email'    => 'chaim.willms@example.com',
            'fullname' => $user->fullname,
            'admin'    => $user->isAdmin,
            'disabled' => !$user->isEnabled(),
            'locale'   => $user->locale,
            'theme'    => $user->theme,
            'timezone' => $user->timezone,
        ];

        $uri = sprintf('/api/users/%s', $user->id);

        $this->client->xmlHttpRequest(Request::METHOD_PUT, $uri, $data);

        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->doctrine->getManager()->refresh($user);

        static::assertSame('chaim.willms@example.com', $user->email);
    }

    public function test400()
    {
        $this->loginAs('admin@example.com');

        /** @var User $user */
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['email' => 'nhills@example.com']);

        $uri = sprintf('/api/users/%s', $user->id);

        $this->client->xmlHttpRequest(Request::METHOD_PUT, $uri);

        static::assertSame(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function test401()
    {
        /** @var User $user */
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['email' => 'nhills@example.com']);

        $data = [
            'email'    => 'chaim.willms@example.com',
            'fullname' => $user->fullname,
            'admin'    => $user->isAdmin,
            'disabled' => !$user->isEnabled(),
            'locale'   => $user->locale,
            'theme'    => $user->theme,
            'timezone' => $user->timezone,
        ];

        $uri = sprintf('/api/users/%s', $user->id);

        $this->client->xmlHttpRequest(Request::METHOD_PUT, $uri, $data);

        static::assertSame(Response::HTTP_UNAUTHORIZED, $this->client->getResponse()->getStatusCode());
    }

    public function test403()
    {
        $this->loginAs('artem@example.com');

        /** @var User $user */
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['email' => 'nhills@example.com']);

        $data = [
            'email'    => 'chaim.willms@example.com',
            'fullname' => $user->fullname,
            'admin'    => $user->isAdmin,
            'disabled' => !$user->isEnabled(),
            'locale'   => $user->locale,
            'theme'    => $user->theme,
            'timezone' => $user->timezone,
        ];

        $uri = sprintf('/api/users/%s', $user->id);

        $this->client->xmlHttpRequest(Request::METHOD_PUT, $uri, $data);

        static::assertSame(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function test404()
    {
        $this->loginAs('admin@example.com');

        /** @var User $user */
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['email' => 'nhills@example.com']);

        $data = [
            'email'    => 'chaim.willms@example.com',
            'fullname' => $user->fullname,
            'admin'    => $user->isAdmin,
            'disabled' => !$user->isEnabled(),
            'locale'   => $user->locale,
            'theme'    => $user->theme,
            'timezone' => $user->timezone,
        ];

        $uri = sprintf('/api/users/%s', self::UNKNOWN_ENTITY_ID);

        $this->client->xmlHttpRequest(Request::METHOD_PUT, $uri, $data);

        static::assertSame(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    public function test409()
    {
        $this->loginAs('admin@example.com');

        /** @var User $user */
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['email' => 'nhills@example.com']);

        $data = [
            'email'    => 'vparker@example.com',
            'fullname' => $user->fullname,
            'admin'    => $user->isAdmin,
            'disabled' => !$user->isEnabled(),
            'locale'   => $user->locale,
            'theme'    => $user->theme,
            'timezone' => $user->timezone,
        ];

        $uri = sprintf('/api/users/%s', $user->id);

        $this->client->xmlHttpRequest(Request::METHOD_PUT, $uri, $data);

        static::assertSame(Response::HTTP_CONFLICT, $this->client->getResponse()->getStatusCode());
    }
}
