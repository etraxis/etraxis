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

namespace eTraxis\Controller\GroupsController;

use eTraxis\Entity\Group;
use eTraxis\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers \eTraxis\Controller\API\GroupsController::listGroups
 */
class ListGroupsTest extends WebTestCase
{
    public function testSuccess()
    {
        $this->loginAs('admin@example.com');

        $expected = array_map(fn (Group $group) => [
            $group->name,
            $group->description,
        ], $this->doctrine->getRepository(Group::class)->findAll());

        $uri = '/api/groups';

        $this->client->xmlHttpRequest(Request::METHOD_GET, $uri);

        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $actual  = array_map(fn (array $row) => [
            $row['name'],
            $row['description'],
        ], $content['data']);

        static::assertSame(0, $content['from']);
        static::assertSame(17, $content['to']);
        static::assertSame(18, $content['total']);

        sort($expected);
        sort($actual);

        static::assertSame($expected, $actual);
    }

    public function test401()
    {
        $uri = '/api/groups';

        $this->client->xmlHttpRequest(Request::METHOD_GET, $uri);

        static::assertSame(Response::HTTP_UNAUTHORIZED, $this->client->getResponse()->getStatusCode());
    }

    public function test403()
    {
        $this->loginAs('artem@example.com');

        $uri = '/api/groups';

        $this->client->xmlHttpRequest(Request::METHOD_GET, $uri);

        static::assertSame(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }
}
