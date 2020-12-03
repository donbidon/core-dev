<?php
/**
 * Registry interface.
 *
 * @author  [donbidon](http://donbidon.rf.gd/)
 * @license https://opensource.org/licenses/mit-license.php
 */

declare(strict_types=1);

namespace donbidon\Core\Registry;

use Countable;
use donbidon\Core\Registry\Middleware\Middleware;
use Iterator;
use RuntimeException;

/**
 * Registry interface.
 *
 * @author  [donbidon](http://donbidon.rf.gd/)
 * @license https://opensource.org/licenses/mit-license.php
 */
interface Registry extends Iterator, Countable
{
    public const MIDDLEWARE_PRE  = 1;
    public const MIDDLEWARE_POST = 2;

    /**
     * @param mixed[]  $scope
     * @param ?mixed[] $options
     */
    public function __construct(array $scope = [], ?array $options = null);

    /**
     * Returns used initial options.
     *
     * @return mixed[]
     */
    public function options(): array;

    /**
     * Adds middleware to enhance functionality.
     *
     * @param Middleware $middleware
     */
    public function addMiddleware(Middleware $middleware): void;

    /**
     * Sets scope value.
     *
     * @param string|int $key
     * @param mixed      $value
     */
    public function set($key, $value): void;

    /**
     * Returns true if scope exists, false otherwise.
     *
     * @param string|int $key
     */
    public function exists($key): bool;

    /**
     * Returns true if scope value is empty, false otherwise.
     *
     * @param string|int $key
     *
     * @link http://php.net/manual/en/function.empty.php
     */
    public function isEmpty($key): bool;

    /**
     * Returns scope value.
     *
     * @param ?string|int      $key      If not passed, whole scope will be returned
     * @param mixed            $default
     * @param ?string|int|null $throw    Throw exception or trigger error if no default value passed and
     *                                   key doesn't exist
     *
     * @return mixed
     */
    public function get($key = null, $default = null, $throw = RuntimeException::class);

    /**
     * Deletes scope key.
     *
     * @param string|int $key
     */
    public function delete($key): void;

    /**
     * Returns new registry from value of the key.
     *
     * @param string|int $key
     * @param mixed[] $options
     *
     * @return static
     */
    public function getBranch($key, ?array $options = null);

    /**
     * Overrides scope.
     *
     * @param mixed[] $scope
     */
    public function override(array $scope): void;
}
