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

namespace eTraxis\Repository;

use eTraxis\Entity\File;
use eTraxis\WebTestCase;

/**
 * @coversDefaultClass \eTraxis\Repository\FileRepository
 */
class FileRepositoryTest extends WebTestCase
{
    private Contracts\FileRepositoryInterface $repository;

    /**
     * @noinspection PhpFieldAssignmentTypeMismatchInspection
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->doctrine->getRepository(File::class);
    }

    /**
     * @covers ::__construct
     */
    public function testRepository()
    {
        static::assertInstanceOf(FileRepository::class, $this->repository);
    }

    /**
     * @covers ::find
     */
    public function testFind()
    {
        [$expected] = $this->repository->findBy(['name' => 'Inventore.pdf']);
        static::assertNotNull($expected);

        $value = $this->repository->find($expected->id);
        static::assertSame($expected, $value);
    }

    /**
     * @covers ::getFullPath
     */
    public function testFullPath()
    {
        /** @var File $file */
        [$file] = $this->repository->findAll();

        $expected = getcwd() . \DIRECTORY_SEPARATOR . 'var' . \DIRECTORY_SEPARATOR . $file->uuid;

        static::assertSame($expected, $this->repository->getFullPath($file));
    }
}
