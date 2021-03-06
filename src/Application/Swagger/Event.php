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

namespace eTraxis\Application\Swagger;

use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as API;

/**
 * Descriptive class for API annotations.
 */
class Event
{
    /**
     * @API\Property(type="string", enum={
     *     "comment.private",
     *     "comment.public",
     *     "dependency.added",
     *     "dependency.removed",
     *     "file.attached",
     *     "file.deleted",
     *     "issue.assigned",
     *     "state.changed",
     *     "issue.closed",
     *     "issue.created",
     *     "issue.edited",
     *     "issue.reopened",
     *     "issue.resumed",
     *     "issue.suspended"
     * }, example="issue.created", description="Event type.")
     */
    public string $type;

    /**
     * @API\Property(type="object", ref=@Model(type=eTraxis\Application\Swagger\UserInfo::class), description="Initiator of the event.")
     */
    public UserInfo $user;

    /**
     * @API\Property(type="integer", example=1089280800, description="Unix Epoch timestamp when the event has took place.")
     */
    public int $timestamp;

    /**
     * @API\Property(type="object", ref=@Model(type=eTraxis\Application\Swagger\StateInfo::class), description="State, if applicable.")
     */
    public StateInfo $state;

    /**
     * @API\Property(type="object", ref=@Model(type=eTraxis\Application\Swagger\UserInfo::class), description="Assignee, if applicable.")
     */
    public UserInfo $assignee;

    /**
     * @API\Property(type="object", ref=@Model(type=eTraxis\Application\Swagger\File::class), description="File, if applicable.")
     */
    public File $file;

    /**
     * @API\Property(type="object", ref=@Model(type=eTraxis\Application\Swagger\Issue::class), description="Issue, if applicable.")
     */
    public Issue $issue;
}
