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

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use eTraxis\Entity\DecimalValue;

class DecimalValueRepository extends ServiceEntityRepository implements Contracts\DecimalValueRepositoryInterface
{
    use CachedRepositoryTrait;

    /**
     * {@inheritDoc}
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DecimalValue::class);
    }

    /**
     * @codeCoverageIgnore Proxy method.
     *
     * {@inheritDoc}
     */
    public function persist(DecimalValue $entity): void
    {
        $this->getEntityManager()->persist($entity);
        $this->deleteFromCache($entity->id);
    }

    /**
     * @codeCoverageIgnore Proxy method.
     *
     * {@inheritDoc}
     */
    public function remove(DecimalValue $entity): void
    {
        $this->getEntityManager()->remove($entity);
        $this->deleteFromCache($entity->id);
    }

    /**
     * @codeCoverageIgnore Proxy method.
     *
     * {@inheritDoc}
     */
    public function refresh(DecimalValue $entity): void
    {
        $this->getEntityManager()->refresh($entity);
        $this->deleteFromCache($entity->id);
    }

    /**
     * {@inheritDoc}
     */
    public function find($id, $lockMode = null, $lockVersion = null)
    {
        return $this->findInCache($id, fn ($id) => parent::find($id));
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $value): DecimalValue
    {
        /** @var null|DecimalValue $entity */
        $entity = $this->findOneBy([
            'value' => $value,
        ]);

        // If value doesn't exist yet, create it.
        if ($entity === null) {

            $entity = new DecimalValue($value);

            $this->getEntityManager()->persist($entity);
            $this->getEntityManager()->flush();
        }

        return $entity;
    }
}
