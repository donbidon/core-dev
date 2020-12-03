<?php
/**
 * Registry middleware abstract class.
 *
 * @copyright <a href="http://donbidon.rf.gd/" target="_blank">donbidon</a>
 * @license   https://opensource.org/licenses/mit-license.php
 */

declare(strict_types=1);

namespace donbidon\Core\Registry\Middleware;

use donbidon\Core\Registry\Registry;
use function call_user_func;
use function is_callable;
use function preg_replace;

/**
 * Registry middleware abstract class.
 */
abstract class MiddlewareAbstract implements Middleware
{
    /**
     * Flag specifying to run handlers
     *
     * @var bool
     */
    private $handle = true;

    /**
     * {@inheritdoc}
     */
    public function process(string $method, Registry $env): void
    {
        $method = preg_replace("/^.+::/", "", $method);
        if ($this->handle && is_callable([$this, $method])) {
            call_user_func([$this, $method], $env);
        }
    }

    /**
     * Sets flag specifying to run handlers.
     *
     * @param bool $handle
     */
    protected function handle(bool $handle): void
    {
        $this->handle = $handle;
    }
}
