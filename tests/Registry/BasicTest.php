<?php
/**
 * Basic registry class unit tests.
 *
 * @copyright <a href="http://donbidon.rf.gd/" target="_blank">donbidon</a>
 * @license   https://opensource.org/licenses/mit-license.php
 */

declare(strict_types=1);

namespace donbidon\Core\Registry;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Exception as PHPUnitException;
use RuntimeException;

/**
 * Basic registry class unit tests.
 */
class BasicTest extends TestCase
{
    /**
     * Initial scope
     *
     * @var mixed[]
     */
    protected $initialScope = [
        'key_1'       => "value_1",
        'empty_key_1' => "",
        'empty_key_2' => "0",
        'empty_key_3' => 0,
        'empty_key_4' => null,
        'key_2'       => "value_2",
        'array'       => [
            'key_1_1' => "value_1_1",
        ],
    ];

    /**
     * Registry instance
     *
     * @var Basic
     */
    protected $registry;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->registry = new Basic($this->initialScope);
    }

    /**
     * Tests exception when missing key and no default value passed.
     *
     * @return void
     *
     * @covers \donbidon\Core\Registry\Basic::__construct
     * @covers \donbidon\Core\Registry\Basic::get
     */
    public function testExceptionOnNonexistentKey(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Missing key 'nonexistent_key'");

        $this->registry->get('nonexistent_key');
    }

    /**
     * Tests triggered error when missing key and no default value passed.
     *
     * @return void
     *
     * @covers \donbidon\Core\Registry\Basic::get
     */
    public function testErrorOnNonexistentKey(): void
    {
        $this->expectException(PHPUnitException::class);
        $this->expectExceptionCode(\E_USER_WARNING);
        $this->expectExceptionMessage("Missing key 'nonexistent_key'");

        $this->registry->get('nonexistent_key', null, \E_USER_WARNING);
    }

    /**
     * Tests common functionality.
     *
     * @return void

     * @covers \donbidon\Core\Registry\Basic::get
     * @covers \donbidon\Core\Registry\Basic::set
     * @covers \donbidon\Core\Registry\Basic::delete
     * @covers \donbidon\Core\Registry\Basic::exists
     * @covers \donbidon\Core\Registry\Basic::isEmpty
     */
    public function testCommonFunctionality(): void
    {
        self::assertEquals("value_1", $this->registry->get('key_1'));
        self::assertEquals(100500, $this->registry->get('key_3', 100500));
        self::assertEquals("", $this->registry->get('empty_key_1'));
        self::assertEquals("0", $this->registry->get('empty_key_2'));
        self::assertEquals(0, $this->registry->get('empty_key_3'));
        self::assertEquals(null, $this->registry->get('empty_key_4'));
        self::assertEquals($this->initialScope, $this->registry->get());

        $this->registry->set('key_1', "value_1_1");
        self::assertEquals("value_1_1", $this->registry->get('key_1'));

        $this->registry->delete('key_1');
        self::assertFalse($this->registry->exists('key_1'));
        self::assertTrue($this->registry->isEmpty('key_1'));

        self::assertTrue($this->registry->isEmpty('key_3'));
        self::assertTrue($this->registry->isEmpty('empty_key_1'));
        self::assertTrue($this->registry->isEmpty('empty_key_2'));
        self::assertTrue($this->registry->isEmpty('empty_key_3'));
        self::assertTrue($this->registry->isEmpty('empty_key_4'));
    }

    /**
     * Tests override.
     *
     * @return void
     *
     * @covers \donbidon\Core\Registry\Basic::override
     */
    public function testOverride(): void
    {
        $this->registry->override(['key_1' => "value_1*"]);
        self::assertEquals("value_1*", $this->registry->get('key_1'));
    }

    /**
     * Tests new registry creation from value of passed key.
     *
     * @return void
     *
     * @throws \ReflectionException  Risen from Basic::getBranch().
     *
     * @covers \donbidon\Core\Registry\Basic::getBranch
     */
    public function testGetBranch(): void
    {
        $registry = $this->registry->getBranch('array');
        self::assertEquals(
            [
                'key_1_1' => "value_1_1",
            ],
            $registry->get()
        );
    }

    /**
     * Tests Iterator interface implementation.
     *
     * @return void
     *
     * @covers \donbidon\Core\Registry\Basic::__construct
     * @covers \donbidon\Core\Registry\Basic::rewind
     * @covers \donbidon\Core\Registry\Basic::current
     * @covers \donbidon\Core\Registry\Basic::key
     * @covers \donbidon\Core\Registry\Basic::next
     * @covers \donbidon\Core\Registry\Basic::valid
     */
    public function testIteratorInterface(): void
    {
        $result = [];
        foreach ($this->registry as $value) {
            $result[$this->registry->key()] = $value;
        }
        self::assertEquals(
            $this->initialScope,
            $result
        );
    }

    /**
     * Tests Countable interface implementation.
     *
     * @return void
     *
     * @covers \donbidon\Core\Registry\Basic::__construct
     * @covers \donbidon\Core\Registry\Basic::count
     */
    public function testCountableInterface(): void
    {
        self::assertEquals(
            \count($this->initialScope),
            \count($this->registry)
        );
    }


    /**
     * Tests Countable interface implementation.
     *
     * @return void
     *
     * @covers \donbidon\Core\Registry\Basic::__construct
     * @covers \donbidon\Core\Registry\Basic::options
     */
    public function testOptions(): void
    {
        self::assertEquals(
            [],
            $this->registry->options()
        );
    }
}
