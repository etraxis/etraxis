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

namespace eTraxis\Entity\FieldTypes;

use eTraxis\Application\Dictionary\FieldType;
use eTraxis\Application\Dictionary\StateType;
use eTraxis\Application\Seconds;
use eTraxis\Entity\Field;
use eTraxis\Entity\Project;
use eTraxis\Entity\State;
use eTraxis\Entity\Template;
use eTraxis\ReflectionTrait;
use eTraxis\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @coversDefaultClass \eTraxis\Entity\FieldTypes\DateTrait
 */
class DateTraitTest extends WebTestCase
{
    use ReflectionTrait;

    private TranslatorInterface $translator;
    private ValidatorInterface  $validator;
    private Field               $object;
    private DateInterface       $facade;

    /**
     * @noinspection PhpFieldAssignmentTypeMismatchInspection
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->translator = $this->client->getContainer()->get('translator');
        $this->validator  = $this->client->getContainer()->get('validator');

        $state = new State(new Template(new Project()), StateType::INTERMEDIATE);

        $this->object = new Field($state, FieldType::DATE);
        $this->setProperty($this->object, 'id', 1);
        $this->object->isRequired = false;

        $this->facade = $this->callMethod($this->object, 'getFacade', [$this->doctrine->getManager()]);
    }

    /**
     * @covers ::asDate
     */
    public function testJsonSerialize()
    {
        $expected = [
            'minimum' => DateInterface::MIN_VALUE,
            'maximum' => DateInterface::MAX_VALUE,
            'default' => null,
        ];

        static::assertSame($expected, $this->facade->jsonSerialize());
    }

    /**
     * @covers ::asDate
     */
    public function testValidationConstraints()
    {
        $this->object->name = 'Custom field';
        $this->facade
            ->setMinimumValue(0)
            ->setMaximumValue(7);

        $now = time();

        $errors = $this->validator->validate(date('Y-m-d', $now), $this->facade->getValidationConstraints($this->translator));
        static::assertCount(0, $errors);

        $errors = $this->validator->validate(date('Y-m-d', $now + Seconds::ONE_DAY * 7), $this->facade->getValidationConstraints($this->translator));
        static::assertCount(0, $errors);

        $errors = $this->validator->validate(date('Y-m-d', $now - Seconds::ONE_DAY), $this->facade->getValidationConstraints($this->translator));
        static::assertNotCount(0, $errors);
        static::assertSame(sprintf('\'Custom field\' should be in range from %s to %s.', date('n/j/y', $now), date('n/j/y', $now + Seconds::ONE_DAY * 7)), $errors->get(0)->getMessage());

        $errors = $this->validator->validate(date('Y-m-d', $now + Seconds::ONE_DAY * 8), $this->facade->getValidationConstraints($this->translator));
        static::assertNotCount(0, $errors);
        static::assertSame(sprintf('\'Custom field\' should be in range from %s to %s.', date('n/j/y', $now), date('n/j/y', $now + Seconds::ONE_DAY * 7)), $errors->get(0)->getMessage());

        $errors = $this->validator->validate('2015-22-11', $this->facade->getValidationConstraints($this->translator));
        static::assertNotCount(0, $errors);
        static::assertSame('This value is not valid.', $errors->get(0)->getMessage());

        $this->object->isRequired = true;

        $errors = $this->validator->validate(null, $this->facade->getValidationConstraints($this->translator));
        static::assertNotCount(0, $errors);
        static::assertSame('This value should not be blank.', $errors->get(0)->getMessage());

        $this->object->isRequired = false;

        $errors = $this->validator->validate(null, $this->facade->getValidationConstraints($this->translator));
        static::assertCount(0, $errors);
    }

    /**
     * @covers ::asDate
     */
    public function testMinimumValue()
    {
        $parameters = $this->getProperty($this->object, 'parameters');

        $value = random_int(DateInterface::MIN_VALUE, DateInterface::MAX_VALUE);
        $min   = DateInterface::MIN_VALUE - 1;
        $max   = DateInterface::MAX_VALUE + 1;

        $this->facade->setMinimumValue($value);
        static::assertSame($value, $this->facade->getMinimumValue());
        static::assertSame($value, $this->getProperty($parameters, 'parameter1'));

        $this->facade->setMinimumValue($min);
        static::assertSame(DateInterface::MIN_VALUE, $this->facade->getMinimumValue());

        $this->facade->setMinimumValue($max);
        static::assertSame(DateInterface::MAX_VALUE, $this->facade->getMinimumValue());
    }

    /**
     * @covers ::asDate
     */
    public function testMaximumValue()
    {
        $parameters = $this->getProperty($this->object, 'parameters');

        $value = random_int(DateInterface::MIN_VALUE, DateInterface::MAX_VALUE);
        $min   = DateInterface::MIN_VALUE - 1;
        $max   = DateInterface::MAX_VALUE + 1;

        $this->facade->setMaximumValue($value);
        static::assertSame($value, $this->facade->getMaximumValue());
        static::assertSame($value, $this->getProperty($parameters, 'parameter2'));

        $this->facade->setMaximumValue($min);
        static::assertSame(DateInterface::MIN_VALUE, $this->facade->getMaximumValue());

        $this->facade->setMaximumValue($max);
        static::assertSame(DateInterface::MAX_VALUE, $this->facade->getMaximumValue());
    }

    /**
     * @covers ::asDate
     */
    public function testDefaultValue()
    {
        $parameters = $this->getProperty($this->object, 'parameters');

        $value = random_int(DateInterface::MIN_VALUE, DateInterface::MAX_VALUE);
        $min   = DateInterface::MIN_VALUE - 1;
        $max   = DateInterface::MAX_VALUE + 1;

        $this->facade->setDefaultValue($value);
        static::assertSame($value, $this->facade->getDefaultValue());
        static::assertSame($value, $this->getProperty($parameters, 'defaultValue'));

        $this->facade->setDefaultValue($min);
        static::assertSame(DateInterface::MIN_VALUE, $this->facade->getDefaultValue());

        $this->facade->setDefaultValue($max);
        static::assertSame(DateInterface::MAX_VALUE, $this->facade->getDefaultValue());

        $this->facade->setDefaultValue(null);
        static::assertNull($this->facade->getDefaultValue());
        static::assertNull($this->getProperty($parameters, 'defaultValue'));
    }
}
