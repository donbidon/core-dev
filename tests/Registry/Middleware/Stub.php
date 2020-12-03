<?php
/**
 * Registry middleware class unit tests.
 *
 * @author  [donbidon](http://donbidon.rf.gd/)
 * @license https://opensource.org/licenses/mit-license.php
 */

declare(strict_types=1);

namespace donbidon\Core\Registry\Middleware;

use donbidon\Core\Registry\Registry;

/**
 * Registry middleware class unit tests.
 *
 * @author  [donbidon](http://donbidon.rf.gd/)
 * @license https://opensource.org/licenses/mit-license.php
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

    // phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    /**
     * @param Registry $env  Environment
     * @noinspection PhpUnusedParameterInspection
     */
    protected function setScope(Registry $env): void
    {
        $this->registry->set('middleware', true);
    }
    // phpcs:enable
}
