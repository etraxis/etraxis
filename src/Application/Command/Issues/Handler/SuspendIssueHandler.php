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

namespace eTraxis\Application\Command\Issues\Handler;

use eTraxis\Application\Command\Issues\SuspendIssueCommand;
use eTraxis\Application\Dictionary\EventType;
use eTraxis\Entity\Event;
use eTraxis\Repository\Contracts\EventRepositoryInterface;
use eTraxis\Repository\Contracts\IssueRepositoryInterface;
use eTraxis\Voter\IssueVoter;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Command handler.
 */
class SuspendIssueHandler
{
    private AuthorizationCheckerInterface $security;
    private TokenStorageInterface         $tokenStorage;
    private IssueRepositoryInterface      $issueRepository;
    private EventRepositoryInterface      $eventRepository;

    /**
     * @codeCoverageIgnore Dependency Injection constructor.
     *
     * @param AuthorizationCheckerInterface $security
     * @param TokenStorageInterface         $tokenStorage
     * @param IssueRepositoryInterface      $issueRepository
     * @param EventRepositoryInterface      $eventRepository
     */
    public function __construct(
        AuthorizationCheckerInterface $security,
        TokenStorageInterface         $tokenStorage,
        IssueRepositoryInterface      $issueRepository,
        EventRepositoryInterface      $eventRepository
    )
    {
        $this->security        = $security;
        $this->tokenStorage    = $tokenStorage;
        $this->issueRepository = $issueRepository;
        $this->eventRepository = $eventRepository;
    }

    /**
     * Command handler.
     *
     * @param SuspendIssueCommand $command
     *
     * @throws \InvalidArgumentException
     * @throws AccessDeniedHttpException
     * @throws NotFoundHttpException
     */
    public function __invoke(SuspendIssueCommand $command): void
    {
        /** @var \eTraxis\Entity\User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        /** @var null|\eTraxis\Entity\Issue $issue */
        $issue = $this->issueRepository->find($command->issue);

        if (!$issue) {
            throw new NotFoundHttpException('Unknown issue.');
        }

        if (!$this->security->isGranted(IssueVoter::SUSPEND_ISSUE, $issue)) {
            throw new AccessDeniedHttpException('You are not allowed to suspend this issue.');
        }

        $date = date_create_from_format('Y-m-d', $command->date, timezone_open($user->timezone) ?: null);
        $date->setTime(0, 0);

        if ($date->getTimestamp() < time()) {
            throw new \InvalidArgumentException('Date must be in future.');
        }

        $issue->suspend($date->getTimestamp());

        $event = new Event(EventType::ISSUE_SUSPENDED, $issue, $user);

        $this->issueRepository->persist($issue);
        $this->eventRepository->persist($event);
    }
}
