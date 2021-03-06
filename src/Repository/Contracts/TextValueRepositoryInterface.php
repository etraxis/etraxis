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

namespace eTraxis\Repository\Contracts;

use Doctrine\Common\Collections\Selectable;
use Doctrine\Persistence\ObjectRepository;
use eTraxis\Entity\TextValue;

/**
 * Interface to the 'TextValue' entities repository.
 */
interface TextValueRepositoryInterface extends CachedRepositoryInterface, ObjectRepository, Selectable
{
    /**
     * @see \Doctrine\Persistence\ObjectManager::persist()
     *
     * @param TextValue $entity
     */
    public function persist(TextValue $entity): void;

    /**
     * @see \Doctrine\Persistence\ObjectManager::remove()
     *
     * @param TextValue $entity
     */
    public function remove(TextValue $entity): void;

    /**
     * @see \Doctrine\Persistence\ObjectManager::refresh()
     *
     * @param TextValue $entity
     */
    public function refresh(TextValue $entity): void;

    /**
     * Finds specified text value entity.
     * If the value doesn't exist yet, creates it.
     *
     * @param string $value Text value.
     *
     * @return TextValue
     */
    public function get(string $value): TextValue;
}
