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

namespace eTraxis\Voter;

use Doctrine\ORM\EntityManagerInterface;
use eTraxis\Application\Dictionary\EventType;
use eTraxis\Application\Dictionary\StateResponsible;
use eTraxis\Application\Dictionary\StateType;
use eTraxis\Entity\Event;
use eTraxis\Entity\State;
use eTraxis\Entity\Template;
use eTraxis\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Voter for "State" entities.
 */
class StateVoter extends AbstractVoter implements VoterInterface
{
    public const CREATE_STATE           = 'state.create';
    public const UPDATE_STATE           = 'state.update';
    public const DELETE_STATE           = 'state.delete';
    public const SET_INITIAL            = 'state.set_initial';
    public const GET_TRANSITIONS        = 'state.transitions.get';
    public const SET_TRANSITIONS        = 'state.transitions.set';
    public const GET_RESPONSIBLE_GROUPS = 'state.responsible_groups.get';
    public const SET_RESPONSIBLE_GROUPS = 'state.responsible_groups.set';

    protected array $attributes = [
        self::CREATE_STATE           => Template::class,
        self::UPDATE_STATE           => State::class,
        self::DELETE_STATE           => State::class,
        self::SET_INITIAL            => State::class,
        self::GET_TRANSITIONS        => State::class,
        self::SET_TRANSITIONS        => State::class,
        self::GET_RESPONSIBLE_GROUPS => State::class,
        self::SET_RESPONSIBLE_GROUPS => State::class,
    ];

    private EntityManagerInterface $manager;

    /**
     * @codeCoverageIgnore Dependency Injection constructor.
     *
     * @param EntityManagerInterface $manager
     */
    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritDoc}
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        // User must be logged in.
        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {

            case self::CREATE_STATE:
                return $this->isCreateGranted($subject, $user);

            case self::UPDATE_STATE:
                return $this->isUpdateGranted($subject, $user);

            case self::DELETE_STATE:
                return $this->isDeleteGranted($subject, $user);

            case self::SET_INITIAL:
                return $this->isSetInitialGranted($subject, $user);

            case self::GET_TRANSITIONS:
                return $this->isGetTransitionsGranted($subject, $user);

            case self::SET_TRANSITIONS:
                return $this->isSetTransitionsGranted($subject, $user);

            case self::GET_RESPONSIBLE_GROUPS:
                return $this->isGetResponsibleGroupsGranted($subject, $user);

            case self::SET_RESPONSIBLE_GROUPS:
                return $this->isSetResponsibleGroupsGranted($subject, $user);

            default:
                return false;
        }
    }

    /**
     * Whether a new state can be created in the specified template.
     *
     * @param Template $subject Subject template.
     * @param User     $user    Current user.
     *
     * @return bool
     */
    private function isCreateGranted(Template $subject, User $user): bool
    {
        return $user->isAdmin && $subject->isLocked;
    }

    /**
     * Whether the specified state can be updated.
     *
     * @param State $subject Subject state.
     * @param User  $user    Current user.
     *
     * @return bool
     */
    private function isUpdateGranted(State $subject, User $user): bool
    {
        return $user->isAdmin && $subject->template->isLocked;
    }

    /**
     * Whether the specified state can be deleted.
     *
     * @param State $subject Subject state.
     * @param User  $user    Current user.
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return bool
     */
    private function isDeleteGranted(State $subject, User $user): bool
    {
        // User must be an admin and template must be locked.
        if (!$user->isAdmin || !$subject->template->isLocked) {
            return false;
        }

        // Can't delete a state if it was used in at least one issue.
        $query = $this->manager->createQueryBuilder();

        $query
            ->select('COUNT(event.id)')
            ->from(Event::class, 'event')
            ->where($query->expr()->in('event.type', ':types'))
            ->andWhere('event.parameter = :state')
            ->setParameter('state', $subject->id)
            ->setParameter('types', [
                EventType::ISSUE_CREATED,
                EventType::ISSUE_REOPENED,
                EventType::ISSUE_CLOSED,
                EventType::STATE_CHANGED,
            ]);

        $result = (int) $query->getQuery()->getSingleScalarResult();

        return $result === 0;
    }

    /**
     * Whether the specified state can be set as initial one.
     *
     * @param State $subject Subject state.
     * @param User  $user    Current user.
     *
     * @return bool
     */
    private function isSetInitialGranted(State $subject, User $user): bool
    {
        return $user->isAdmin && $subject->template->isLocked;
    }

    /**
     * Whether transitions of the specified state can be retrieved.
     *
     * @param State $subject Subject state.
     * @param User  $user    Current user.
     *
     * @return bool
     */
    private function isGetTransitionsGranted(State $subject, User $user): bool
    {
        return $user->isAdmin && $subject->type !== StateType::FINAL;
    }

    /**
     * Whether transitions of the specified state can be changed.
     *
     * @param State $subject Subject state.
     * @param User  $user    Current user.
     *
     * @return bool
     */
    private function isSetTransitionsGranted(State $subject, User $user): bool
    {
        return $user->isAdmin && $subject->type !== StateType::FINAL && $subject->template->isLocked;
    }

    /**
     * Whether responsible groups of the specified state can be retrieved.
     *
     * @param State $subject Subject state.
     * @param User  $user    Current user.
     *
     * @return bool
     */
    private function isGetResponsibleGroupsGranted(State $subject, User $user): bool
    {
        return $user->isAdmin && $subject->responsible === StateResponsible::ASSIGN;
    }

    /**
     * Whether responsible groups of the specified state can be changed.
     *
     * @param State $subject Subject state.
     * @param User  $user    Current user.
     *
     * @return bool
     */
    private function isSetResponsibleGroupsGranted(State $subject, User $user): bool
    {
        return $user->isAdmin && $subject->responsible === StateResponsible::ASSIGN && $subject->template->isLocked;
    }
}
