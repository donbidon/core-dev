<?php
/**
 * Registry interface.
 *
 * @copyright <a href="http://donbidon.rf.gd/" target="_blank">donbidon</a>
 * @license   https://opensource.org/licenses/mit-license.php
 */

declare(strict_types=1);

namespace donbidon\Core\Registry;

use Countable;
use donbidon\Core\Registry\Middleware\Middleware;
use Iterator;
use RuntimeException;

/**
 * Registry interface.
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
     *
     * @return void
     */
    public function addMiddleware(Middleware $middleware): void;

    /**
     * Sets scope value.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function set(string $key, $value): void;

    /**
     * Returns true if scope exists, false otherwise.
     *
     * @param string $key
     *
     * @return bool
     */
    public function exists(string $key): bool;

    /**
     * Returns true if scope value is empty, false otherwise.
     *
     * @param string $key
     *
     * @return bool
     *
     * @link   http://php.net/manual/en/function.empty.php
     */
    public function isEmpty(string $key): bool;

    /**
     * Returns scope value.
     *
     * @param string          $key      If not passed, whole scope will be returned
     * @param mixed           $default
     * @param string|int|null $throw    Throw exception or trigger error if no default value passed and
     *                                  key doesn't exist
     *
     * @return mixed
     */
    public function get(?string $key = null, $default = null, $throw = RuntimeException::class);

    /**
     * Deletes scope key.
     *
     * @param string $key
     *
     * @return void
     */
    public function delete(string $key): void;

    /**
     * Returns new registry from value of the key.
     *
     * @param string  $key
     * @param mixed[] $options
     *
     * @return static
     */
    public function getBranch(string $key, ?array $options = null);

    /**
     * Overrides scope.
     *
     * @param mixed[] $scope
     *
     * @return void
     */
    public function override(array $scope): void;
}
