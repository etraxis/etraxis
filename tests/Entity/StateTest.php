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

use eTraxis\Application\Dictionary\FieldType;
use eTraxis\Application\Dictionary\StateResponsible;
use eTraxis\Application\Dictionary\StateType;
use eTraxis\ReflectionTrait;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \eTraxis\Entity\State
 */
class StateTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @covers ::__construct
     */
    public function testConstructor()
    {
        $template = new Template(new Project());
        $this->setProperty($template, 'id', 1);

        $state = new State($template, StateType::INITIAL);
        static::assertSame($template, $state->template);
        static::assertSame(StateType::INITIAL, $state->type);
    }

    /**
     * @covers ::__construct
     */
    public function testConstructorException()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Unknown state type: foo');

        $template = new Template(new Project());
        $this->setProperty($template, 'id', 1);

        new State($template, 'foo');
    }

    /**
     * @covers ::getters
     * @covers ::setters
     */
    public function testResponsible()
    {
        $state = new State(new Template(new Project()), StateType::INTERMEDIATE);

        $state->responsible = StateResponsible::ASSIGN;
        static::assertSame(StateResponsible::ASSIGN, $state->responsible);
    }

    /**
     * @covers ::getters
     * @covers ::setters
     */
    public function testResponsibleFinal()
    {
        $state = new State(new Template(new Project()), StateType::FINAL);

        $state->responsible = StateResponsible::ASSIGN;
        static::assertSame(StateResponsible::REMOVE, $state->responsible);
    }

    /**
     * @covers ::getters
     * @covers ::setters
     */
    public function testResponsibleException()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Unknown responsibility type: bar');

        $state = new State(new Template(new Project()), StateType::INTERMEDIATE);

        $state->responsible = 'bar';
    }

    /**
     * @covers ::getters
     * @covers ::setters
     */
    public function testNextState()
    {
        $template = new Template(new Project());
        $this->setProperty($template, 'id', 1);

        $nextState = new State($template, StateType::INTERMEDIATE);
        $this->setProperty($nextState, 'id', 2);

        $state = new State($template, StateType::INTERMEDIATE);
        static::assertNull($state->nextState);

        $state->nextState = $nextState;
        static::assertSame($nextState, $state->nextState);

        $state->nextState = null;
        static::assertNull($state->nextState);
    }

    /**
     * @covers ::getters
     * @covers ::setters
     */
    public function testNextStateFinal()
    {
        $template = new Template(new Project());
        $this->setProperty($template, 'id', 1);

        $nextState = new State($template, StateType::INTERMEDIATE);
        $this->setProperty($nextState, 'id', 2);

        $state = new State($template, StateType::FINAL);
        static::assertNull($state->nextState);

        $state->nextState = $nextState;
        static::assertNull($state->nextState);
    }

    /**
     * @covers ::getters
     * @covers ::setters
     */
    public function testNextStateException()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Unknown state: alien');

        $template1 = new Template(new Project());
        $this->setProperty($template1, 'id', 1);

        $template2 = new Template(new Project());
        $this->setProperty($template2, 'id', 2);

        $nextState = new State($template1, StateType::INTERMEDIATE);
        $this->setProperty($nextState, 'name', 'alien');

        $state = new State($template2, StateType::INTERMEDIATE);

        $state->nextState = $nextState;
    }

    /**
     * @covers ::getters
     */
    public function testIsFinal()
    {
        $template = new Template(new Project());
        $this->setProperty($template, 'id', 1);

        $initial      = new State($template, StateType::INITIAL);
        $intermediate = new State($template, StateType::INTERMEDIATE);
        $final        = new State($template, StateType::FINAL);

        static::assertFalse($initial->isFinal);
        static::assertFalse($intermediate->isFinal);
        static::assertTrue($final->isFinal);
    }

    /**
     * @covers ::getters
     */
    public function testFields()
    {
        $state = new State(new Template(new Project()), StateType::INTERMEDIATE);
        static::assertSame([], $state->roleTransitions);

        /** @var \Doctrine\Common\Collections\Collection $fields */
        $fields = $this->getProperty($state, 'fieldsCollection');

        $field1 = new Field($state, FieldType::CHECKBOX);
        $field2 = new Field($state, FieldType::CHECKBOX);

        $this->setProperty($field1, 'id', 1);
        $this->setProperty($field2, 'id', 2);

        $fields->add($field1);
        $fields->add($field2);

        static::assertSame([$field1, $field2], $state->fields);

        $field1->remove();

        static::assertSame([$field2], $state->fields);
    }

    /**
     * @covers ::getters
     */
    public function testRolePermissions()
    {
        $state = new State(new Template(new Project()), StateType::INTERMEDIATE);
        static::assertSame([], $state->roleTransitions);

        /** @var \Doctrine\Common\Collections\Collection $transitions */
        $transitions = $this->getProperty($state, 'roleTransitionsCollection');
        $transitions->add('Role transition A');
        $transitions->add('Role transition B');

        static::assertSame(['Role transition A', 'Role transition B'], $state->roleTransitions);
    }

    /**
     * @covers ::getters
     */
    public function testGroupPermissions()
    {
        $state = new State(new Template(new Project()), StateType::INTERMEDIATE);
        static::assertSame([], $state->groupTransitions);

        /** @var \Doctrine\Common\Collections\Collection $transitions */
        $transitions = $this->getProperty($state, 'groupTransitionsCollection');
        $transitions->add('Group transition A');
        $transitions->add('Group transition B');

        static::assertSame(['Group transition A', 'Group transition B'], $state->groupTransitions);
    }

    /**
     * @covers ::getters
     */
    public function testResponsibleGroups()
    {
        $state = new State(new Template(new Project()), StateType::INTERMEDIATE);
        static::assertSame([], $state->responsibleGroups);

        /** @var \Doctrine\Common\Collections\Collection $groups */
        $groups = $this->getProperty($state, 'responsibleGroupsCollection');
        $groups->add('Group A');
        $groups->add('Group B');

        static::assertSame(['Group A', 'Group B'], $state->responsibleGroups);
    }
}
