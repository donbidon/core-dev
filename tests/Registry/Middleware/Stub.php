<?php
/**
 * Registry middleware class unit tests.
 *
 * @copyright <a href="http://donbidon.rf.gd/" target="_blank">donbidon</a>
 * @license   https://opensource.org/licenses/mit-license.php
 */

declare(strict_types=1);

namespace donbidon\Core\Registry\Middleware;

use donbidon\Core\Registry\Registry;

/**
 * Registry middleware class unit tests.
 */
class Stub extends MiddlewareAbstract
{
    /**
     * Registry
     *
     * @var Registry
     */
    protected $registry;

    /**
     * {@inheritdoc}
     */
    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
        $this->handle(false);
        $this->handle(true);
    }

    /**
     * @param Registry $env  Environment
     */
    protected function setScope(Registry $env): void
    {
        $this->registry->set('middleware', true);
    }
}
