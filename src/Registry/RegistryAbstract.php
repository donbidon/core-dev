<?php
/**
 * Registry abstract class.
 *
 * @author  [donbidon](http://donbidon.rf.gd/)
 * @license https://opensource.org/licenses/mit-license.php
 */

declare(strict_types=1);

namespace donbidon\Core\Registry;

use donbidon\Core\Registry\Basic as Environment;
use donbidon\Core\Registry\Middleware\Middleware;
use InvalidArgumentException;
use function array_keys;
use function is_int;
use function is_null;
use function is_string;
use function sizeof;

/**
 * Registry abstract class.
 *
 * @author  [donbidon](http://donbidon.rf.gd/)
 * @license https://opensource.org/licenses/mit-license.php
 */
abstract class RegistryAbstract implements Registry
{
    /**
     * Default options
     *
     * @var mixed[]
     */
    protected $defaults = [];

    /**
     * Scope
     *
     * @var mixed[]
     */
    protected $scope;

    /**
     * Initial options
     *
     * @var mixed[]
     */
    protected $options;

    /**
     * Position for Iterator interface implementation
     *
     * @var int
     */
    protected $position;

    /**
     * Scope
     *
     * @var Middleware[]
     */
    private $middlewares = [];

    /**
     * Constructor.
     *
     * @param mixed[] $scope
     * @param mixed[] $options
     */
    public function __construct(array $scope = [], ?array $options = null)
    {
        $this->scope    = $scope;
        $this->options  = is_null($options) ? $this->defaults : $options;
        $this->rewind();
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed[]
     */
    public function options(): array
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function addMiddleware(Middleware $middleware): void
    {
        $this->middlewares[] = $middleware;
    }

    /**
     * Iterator interface implementation.
     *
     * @see https://www.php.net/manual/en/iterator.rewind.php
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * Iterator interface implementation.
     *
     * @return mixed
     *
     * @see https://www.php.net/manual/en/iterator.current.php
     */
    public function current()
    {
        return $this->scope[array_keys($this->scope)[$this->position]];
    }

    /**
     * Iterator interface implementation.
     *
     * @return mixed
     *
     * @see https://www.php.net/manual/en/iterator.key.php
     */
    public function key()
    {
        return array_keys($this->scope)[$this->position];
    }

    /**
     * Iterator interface implementation.
     *
     * @see https://www.php.net/manual/en/iterator.next.php
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * Iterator interface implementation.
     *
     * @see https://www.php.net/manual/en/iterator.valid.php
     */
    public function valid(): bool
    {
        return isset(array_keys($this->scope)[$this->position]);
    }

    /**
     * Countable interface implementation.
     *
     * @see https://www.php.net/manual/en/countable.count.php
     */
    public function count(): int
    {
        return sizeof($this->scope);
    }

    /**
     * Calls middleware handlers.
     *
     * @noinspection PhpDocSignatureInspection
     */
    protected function callMiddleware(string $method, Environment $env): void
    {
        foreach ($this->middlewares as $middleware) {
            $middleware->process($method, $env);
        }
    }

    /**
     * Validates key.
     *
     * @param ?string|?int $key
     * @param bool $nullAllowed
     *
     * @throws InvalidArgumentException  If invalid key passed.
     */
    protected function validateKey($key = null, bool $nullAllowed = false): void
    {
        if (!(
            is_string($key) || is_int($key) || ($nullAllowed ? is_null($key) : false)
        )) {
            throw new InvalidArgumentException("Invalid key passed");
        }
    }
}
