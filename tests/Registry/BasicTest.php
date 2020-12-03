<?php
/**
 * Basic registry class unit tests.
 *
 * @author  [donbidon](http://donbidon.rf.gd/)
 * @license https://opensource.org/licenses/mit-license.php
 */

declare(strict_types=1);

namespace donbidon\Core\Registry;

use InvalidArgumentException;
use PHPUnit\Framework\Exception as PHPUnitException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use function count;
use function sprintf;
use const E_USER_WARNING;

/**
 * Basic registry class unit tests.
 *
 * @author  [donbidon](http://donbidon.rf.gd/)
 * @license https://opensource.org/licenses/mit-license.php
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
     * Tests exception when invalid key passed.
     *
     * @covers \donbidon\Core\Registry\Basic::get
     * @covers \donbidon\Core\Registry\RegistryAbstract::validateKey
     */
    public function testExceptionOnInvalidKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid key passed");
        $this->registry->get($this);
    }

    /**
     * Tests exception when missing key and no default value passed.
     *
     * @covers \donbidon\Core\Registry\Basic::get
     */
    public function testExceptionOnNonexistentKey(): void
    {
        $key = 'nonexistent_key';
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf("Missing key '%s'", $key));
        $this->registry->get($key);
    }

    /**
     * Tests triggered error when missing key and no default value passed.
     *
     * @covers \donbidon\Core\Registry\Basic::get
     */
    public function testErrorOnNonexistentKey(): void
    {
        $key = 'nonexistent_key';
        $this->expectException(PHPUnitException::class);
        $this->expectExceptionCode(E_USER_WARNING);
        $this->expectExceptionMessage(sprintf("Missing key '%s'", $key));
        $this->registry->get($key, null, E_USER_WARNING);
    }

    /**
     * Tests common functionality.
     *
     * @covers \donbidon\Core\Registry\Basic::delete
     * @covers \donbidon\Core\Registry\Basic::exists
     * @covers \donbidon\Core\Registry\Basic::get
     * @covers \donbidon\Core\Registry\Basic::isEmpty
     * @covers \donbidon\Core\Registry\Basic::set
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
     * @covers \donbidon\Core\Registry\Basic::override
     */
    public function testOverride(): void
    {
        $expected = "value_1*";
        $this->registry->override(['key_1' => $expected]);
        self::assertEquals($expected, $this->registry->get('key_1'));
    }

    /**
     * Tests new registry creation from value of passed key.
     *
     * @covers \donbidon\Core\Registry\Basic::getBranch
     */
    public function testGetBranch(): void
    {
        $branch = $this->registry->getBranch('array');
        $expected = ['key_1_1' => "value_1_1"];
        self::assertEquals($expected, $branch->get());
    }

    /**
     * Tests Iterator interface implementation.
     *
     * @covers \donbidon\Core\Registry\RegistryAbstract::rewind
     * @covers \donbidon\Core\Registry\RegistryAbstract::current
     * @covers \donbidon\Core\Registry\RegistryAbstract::key
     * @covers \donbidon\Core\Registry\RegistryAbstract::next
     * @covers \donbidon\Core\Registry\RegistryAbstract::valid
     */
    public function testIteratorInterface(): void
    {
        $result = [];
        foreach ($this->registry as $value) {
            $result[$this->registry->key()] = $value;
        }
        $expected = $this->initialScope;
        self::assertEquals($expected, $result);
    }

    /**
     * Tests Countable interface implementation.
     *
     * @covers \donbidon\Core\Registry\RegistryAbstract::count
     */
    public function testCountableInterface(): void
    {
        $expected = count($this->initialScope);
        self::assertEquals($expected, count($this->registry));
    }

    /**
     * Tests Countable interface implementation.
     *
     * @covers \donbidon\Core\Registry\RegistryAbstract::options
     */
    public function testOptions(): void
    {
        $expected = [];
        self::assertEquals($expected, $this->registry->options());
    }
}
