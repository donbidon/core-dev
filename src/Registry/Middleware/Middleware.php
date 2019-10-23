<?php
/**
 * Middleware interface used to enhance registry functionality.
 *
 * @copyright <a href="http://donbidon.rf.gd/" target="_blank">donbidon</a>
 * @license   https://opensource.org/licenses/mit-license.php
 */

declare(strict_types=1);

namespace donbidon\Core\Registry\Middleware;

use donbidon\Core\Registry\Registry;

/**
 * Middleware interface used to enhance registry functionality.
 */
interface Middleware
{
    /**
     * Constructor.
     *
     * @param Registry $registry
     */
    public function __construct(Registry $registry);

    /**
     * Calls handlers.
     *
     * @param string   $method
     * @param Registry $env  Environment
     *
     * @return void
     */
    public function process(string $method, Registry $env): void;
}
