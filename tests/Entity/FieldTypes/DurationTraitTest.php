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
use eTraxis\Entity\Field;
use eTraxis\Entity\Project;
use eTraxis\Entity\State;
use eTraxis\Entity\Template;
use eTraxis\ReflectionTrait;
use eTraxis\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @coversDefaultClass \eTraxis\Entity\FieldTypes\DurationTrait
 */
class DurationTraitTest extends WebTestCase
{
    use ReflectionTrait;

    private TranslatorInterface $translator;
    private ValidatorInterface  $validator;
    private Field               $object;
    private DurationInterface   $facade;

    /**
     * @noinspection PhpFieldAssignmentTypeMismatchInspection
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->translator = $this->client->getContainer()->get('translator');
        $this->validator  = $this->client->getContainer()->get('validator');

        $state = new State(new Template(new Project()), StateType::INTERMEDIATE);

        $this->object = new Field($state, FieldType::DURATION);
        $this->setProperty($this->object, 'id', 1);
        $this->object->isRequired = false;

        $this->facade = $this->callMethod($this->object, 'getFacade', [$this->doctrine->getManager()]);
    }

    /**
     * @covers ::asDuration
     */
    public function testJsonSerialize()
    {
        $expected = [
            'minimum' => '0:00',
            'maximum' => '999999:59',
            'default' => null,
        ];

        static::assertSame($expected, $this->facade->jsonSerialize());
    }

    /**
     * @covers ::asDuration
     */
    public function testValidationConstraints()
    {
        $this->object->name = 'Custom field';
        $this->facade
            ->setMinimumValue('0:00')
            ->setMaximumValue('24:00');

        $errors = $this->validator->validate('0:00', $this->facade->getValidationConstraints($this->translator));
        static::assertCount(0, $errors);

        $errors = $this->validator->validate('24:00', $this->facade->getValidationConstraints($this->translator));
        static::assertCount(0, $errors);

        $errors = $this->validator->validate('24:01', $this->facade->getValidationConstraints($this->translator));
        static::assertNotCount(0, $errors);
        static::assertSame('\'Custom field\' should be in range from 0:00 to 24:00.', $errors->get(0)->getMessage());

        $errors = $this->validator->validate('0:60', $this->facade->getValidationConstraints($this->translator));
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
     * @covers ::asDuration
     */
    public function testMinimumValue()
    {
        $parameters = $this->getProperty($this->object, 'parameters');

        $duration = 866;
        $value    = '14:26';
        $min      = '0:00';
        $max      = '999999:59';

        $this->facade->setMinimumValue($value);
        static::assertSame($value, $this->facade->getMinimumValue());
        static::assertSame($duration, $this->getProperty($parameters, 'parameter1'));

        $this->facade->setMinimumValue($min);
        static::assertSame($min, $this->facade->getMinimumValue());

        $this->facade->setMinimumValue($max);
        static::assertSame($max, $this->facade->getMinimumValue());
    }

    /**
     * @covers ::asDuration
     */
    public function testMaximumValue()
    {
        $parameters = $this->getProperty($this->object, 'parameters');

        $duration = 866;
        $value    = '14:26';
        $min      = '0:00';
        $max      = '999999:59';

        $this->facade->setMaximumValue($value);
        static::assertSame($value, $this->facade->getMaximumValue());
        static::assertSame($duration, $this->getProperty($parameters, 'parameter2'));

        $this->facade->setMaximumValue($min);
        static::assertSame($min, $this->facade->getMaximumValue());

        $this->facade->setMaximumValue($max);
        static::assertSame($max, $this->facade->getMaximumValue());
    }

    /**
     * @covers ::asDuration
     */
    public function testDefaultValue()
    {
        $parameters = $this->getProperty($this->object, 'parameters');

        $duration = 866;
        $value    = '14:26';
        $min      = '0:00';
        $max      = '999999:59';

        $this->facade->setDefaultValue($value);
        static::assertSame($value, $this->facade->getDefaultValue());
        static::assertSame($duration, $this->getProperty($parameters, 'defaultValue'));

        $this->facade->setDefaultValue($min);
        static::assertSame($min, $this->facade->getDefaultValue());

        $this->facade->setDefaultValue($max);
        static::assertSame($max, $this->facade->getDefaultValue());

        $this->facade->setDefaultValue(null);
        static::assertNull($this->facade->getDefaultValue());
        static::assertNull($this->getProperty($parameters, 'defaultValue'));
    }

    /**
     * @covers ::asDuration
     */
    public function testToNumber()
    {
        static::assertNull($this->facade->toNumber(null));
        static::assertNull($this->facade->toNumber('0:99'));
        static::assertSame(866, $this->facade->toNumber('14:26'));
    }

    /**
     * @covers ::asDuration
     */
    public function testToString()
    {
        static::assertNull($this->facade->toString(null));
        static::assertSame('0:00', $this->facade->toString(DurationInterface::MIN_VALUE - 1));
        static::assertSame('999999:59', $this->facade->toString(DurationInterface::MAX_VALUE + 1));
        static::assertSame('14:26', $this->facade->toString(866));
    }
}
