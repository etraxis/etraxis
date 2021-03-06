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
use eTraxis\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers \eTraxis\Controller\API\UsersController::listUsers
 */
class ListUsersTest extends WebTestCase
{
    public function testSuccess()
    {
        $this->loginAs('admin@example.com');

        $expected = array_map(fn (User $user) => $user->fullname, $this->doctrine->getRepository(User::class)->findAll());

        $uri = '/api/users';

        $this->client->xmlHttpRequest(Request::METHOD_GET, $uri);

        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $actual  = array_map(fn (array $row) => $row['fullname'], $content['data']);

        static::assertSame(0, $content['from']);
        static::assertSame(34, $content['to']);
        static::assertSame(35, $content['total']);

        sort($expected);
        sort($actual);

        static::assertSame($expected, $actual);
    }

    public function test401()
    {
        $uri = '/api/users';

        $this->client->xmlHttpRequest(Request::METHOD_GET, $uri);

        static::assertSame(Response::HTTP_UNAUTHORIZED, $this->client->getResponse()->getStatusCode());
    }

    public function test403()
    {
        $this->loginAs('artem@example.com');

        $uri = '/api/users';

        $this->client->xmlHttpRequest(Request::METHOD_GET, $uri);

        static::assertSame(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }
}
