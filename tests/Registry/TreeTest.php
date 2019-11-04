<?php
/**
 * Tree registry class unit tests.
 *
 * @copyright <a href="http://donbidon.rf.gd/" target="_blank">donbidon</a>
 * @license   https://opensource.org/licenses/mit-license.php
 */

declare(strict_types=1);

namespace donbidon\Core\Registry;

use donbidon\Core\Registry\Middleware\Stub;
use PHPUnit\Framework\Exception as PHPUnitException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Tree registry class unit tests.
 */
class TreeTest extends TestCase
{
    /**
     * Initial scope
     *
     * @var mixed[]
     */
    protected $initialScope = [
        'key_1'       => "value_1",
        'key_2'       => [
            'key_2_1'       => "value_2_1",
            'key_2_2'       => "value_2_2",
            'empty_key_2_1' => null,
        ],
        'empty_key_3' => "",
    ];

    /**
     * Registry instance
     *
     * @var Tree
     */
    protected $registry;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->registry = new Tree($this->initialScope);
    }

    /**
     * Tests default and custom keys delimiters.
     *
     * @covers \donbidon\Core\Registry\Tree::__construct
     * @covers \donbidon\Core\Registry\Tree::get
     */
    public function testDelimiters(): void
    {
        $options = [Tree::OPT_DELIMITER => "~"];
        $registry = new Tree($this->initialScope, $options);
        self::assertEquals("value_2_1", $registry->get("key_2~key_2_1"));
        $options = [];
        $registry = new Tree($this->initialScope, $options);
        self::assertEquals("value_2_1", $registry->get("key_2/key_2_1"));
    }

    /**
     * Tests exception when missing key and no default value passed.
     *
     * @covers \donbidon\Core\Registry\Tree::get
     * @covers \donbidon\Core\Registry\Tree::setScope
     */
    public function testExceptionOnNonexistentKey(): void
    {
        $key = 'nonexistent_key';
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(\sprintf("Missing key '%s'", $key));
        $this->registry->get($key);
    }

    /**
     * Tests triggered error when missing key and no default value passed.
     *
     * @covers \donbidon\Core\Registry\Tree::get
     * @covers \donbidon\Core\Registry\Tree::setScope
     */
    public function testErrorOnNonexistentKey(): void
    {
        $key = 'nonexistent_key';
        $this->expectException(PHPUnitException::class);
        $this->expectExceptionCode(\E_USER_WARNING);
        $this->expectExceptionMessage(\sprintf("Missing key '%s'", $key));
        $this->registry->get($key, null, \E_USER_WARNING);
    }

    /**
     * Tests exception when missing complex key and no default value passed.
     *
     * @covers \donbidon\Core\Registry\Tree::get
     * @covers \donbidon\Core\Registry\Tree::setScope
     */
    public function testExceptionOnNonexistentComplexKey(): void
    {
        $key = 'key_2/key_2_1/nonexistent_key';
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(\sprintf("Missing key '%s'", $key));
        $this->registry->get($key);
    }

    /**
     * Tests triggered error when missing key and no default value passed.
     *
     * @covers \donbidon\Core\Registry\Tree::get
     * @covers \donbidon\Core\Registry\Tree::setScope
     */
    public function testErrorOnNonexistentComplexKey(): void
    {
        $key = 'key_2/nonexistent_key/nonexistent_key';
        $this->expectException(PHPUnitException::class);
        $this->expectExceptionCode(\E_USER_WARNING);
        $this->expectExceptionMessage(\sprintf("Missing key '%s'", $key));
        $this->registry->get($key, null, \E_USER_WARNING);
    }

    /**
     * Tests common functionality.
     *
     * @covers \donbidon\Core\Registry\Tree::delete
     * @covers \donbidon\Core\Registry\Tree::exists
     * @covers \donbidon\Core\Registry\Tree::get
     * @covers \donbidon\Core\Registry\Tree::isEmpty
     * @covers \donbidon\Core\Registry\Tree::setScope
     */
    public function testCommonFunctionality(): void
    {
        $this->registry->set('key_1/key_1_1', "value_1_1");
        $this->registry->set('key_2/key_2_3', "value_2_3");

        self::assertEquals(
            ['key_1_1' => "value_1_1", ],
            $this->registry->get('key_1')
        );
        self::assertEquals(
            [
                'key_2_1'       => "value_2_1",
                'key_2_2'       => "value_2_2",
                'key_2_3'       => "value_2_3",
                'empty_key_2_1' => null,
            ],
            $this->registry->get('key_2')
        );
        self::assertEquals(
            100500,
            $this->registry->get('key_3', 100500)
        );

        self::assertTrue($this->registry->exists('key_1'));
        self::assertTrue($this->registry->exists('key_1/key_1_1'));
        self::assertTrue($this->registry->exists('key_2/empty_key_2_1'));
        self::assertFalse($this->registry->exists('key_1/key_1_2'));
        self::assertFalse($this->registry->exists('key_2/key_2_4'));
        self::assertFalse($this->registry->exists('key_3'));
        self::assertFalse($this->registry->exists('key_4/key_4_1'));
        self::assertFalse($this->registry->exists('key_5/key_5_1/key_5_1_1'));

        self::assertFalse($this->registry->isEmpty('key_1'));
        self::assertFalse($this->registry->isEmpty('key_1/key_1_1'));
        self::assertTrue($this->registry->isEmpty('key_1/key_1_2'));
        self::assertTrue($this->registry->isEmpty('key_2/empty_key_2_1'));
        self::assertTrue($this->registry->isEmpty('key_2/key_2_4'));
        self::assertTrue($this->registry->isEmpty('key_3'));
        self::assertTrue($this->registry->isEmpty('key_4/key_4_1'));
        self::assertTrue($this->registry->isEmpty('key_5/key_5_1/key_5_1_1'));

        $this->registry->delete('key_1/key_1_1');
        self::assertFalse($this->registry->exists('key_1/key_1_1'));
    }

    /**
     * Tests override.
     */
    public function testOverride(): void
    {
        $this->registry->override(['key_1' => "value_1*"]);
        self::assertEquals("value_1*", $this->registry->get('key_1'));
    }

    /**
     * Tests new registry creation from value of passed key.
     */
    public function testGetBranch(): void
    {
        $registry = $this->registry->getBranch('key_2');
        self::assertEquals(
            "value_2_2",
            $registry->get('key_2_2')
        );
        self::assertEquals(
            $this->initialScope['key_2'],
            $registry->get()
        );
    }

    /**
     * Tests new registry creation from value of passed key.
     */
    public function testMiddleware(): void
    {
        $this->registry->addMiddleware(
            new Stub($this->registry)
        );
        $this->registry->get('key_2/key_2_2');
        self::assertTrue($this->registry->exists('middleware'));
    }
}
