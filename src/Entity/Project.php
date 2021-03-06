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

namespace eTraxis\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as Assert;
use Webinarium\PropertyTrait;

/**
 * Project.
 *
 * @ORM\Table(name="projects")
 * @ORM\Entity(repositoryClass="eTraxis\Repository\ProjectRepository")
 * @Assert\UniqueEntity(fields={"name"}, message="project.conflict.name")
 *
 * @property-read int         $id          Unique ID.
 * @property      string      $name        Name of the project.
 * @property      null|string $description Optional description of the project.
 * @property-read int         $createdAt   Unix Epoch timestamp when the project has been registered.
 * @property      bool        $isSuspended Whether the project is suspended.
 *                                         When project is suspended, its issues are read-only, and new issues cannot be created.
 * @property-read Group[]     $groups      List of project groups.
 * @property-read Template[]  $templates   List of project templates.
 */
class Project
{
    use PropertyTrait;

    // Constraints.
    public const MAX_NAME        = 25;
    public const MAX_DESCRIPTION = 100;

    // JSON properties.
    public const JSON_ID          = 'id';
    public const JSON_NAME        = 'name';
    public const JSON_DESCRIPTION = 'description';
    public const JSON_CREATED     = 'created';
    public const JSON_SUSPENDED   = 'suspended';

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected int $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=25, unique=true)
     */
    protected string $name;

    /**
     * @var null|string
     *
     * @ORM\Column(name="description", type="string", length=100, nullable=true)
     */
    protected ?string $description = null;

    /**
     * @var int
     *
     * @ORM\Column(name="created_at", type="integer")
     */
    protected int $createdAt;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_suspended", type="boolean")
     */
    protected bool $isSuspended;

    /**
     * @var Collection|Group[]
     *
     * @ORM\OneToMany(targetEntity="eTraxis\Entity\Group", mappedBy="project")
     * @ORM\OrderBy({"name": "ASC"})
     */
    protected Collection $groupsCollection;

    /**
     * @var Collection|Template[]
     *
     * @ORM\OneToMany(targetEntity="Template", mappedBy="project")
     * @ORM\OrderBy({"name": "ASC"})
     */
    protected Collection $templatesCollection;

    /**
     * Creates new project.
     */
    public function __construct()
    {
        $this->createdAt   = time();
        $this->isSuspended = false;

        $this->groupsCollection    = new ArrayCollection();
        $this->templatesCollection = new ArrayCollection();
    }

    /**
     * {@inheritDoc}
     */
    protected function getters(): array
    {
        return [
            'groups'    => fn (): array => $this->groupsCollection->getValues(),
            'templates' => fn (): array => $this->templatesCollection->getValues(),
        ];
    }
}
