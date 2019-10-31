<?php
/**
 * Tree registry functionality.
 *
 * @copyright <a href="http://donbidon.rf.gd/" target="_blank">donbidon</a>
 * @license   https://opensource.org/licenses/mit-license.php
 */

declare(strict_types=1);

namespace donbidon\Core\Registry;

use donbidon\Core\Registry\Basic as Environment;
use RuntimeException;

/**
 * Tree registry functionality.
 *
 * ```php
 * $registry = new \donbidon\Core\Registry\Tree([
 *     'key_1' => "value_1",
 *     'key_2' => [
 *         'key_2_1' => "value_2_1",
 *         'key_2_2' => "value_2_2",
 *     ],
 * ]);
 * var_dump($registry->exists('key_1'));
 * var_dump($registry->get('key_2/key_2_2'));
 * var_dump($registry->exists('key_2/key_2_3'));
 * ```
 * outputs
 * ```
 * bool(true)
 *
 * bool(false)
 * ```
 *
 * @todo Rewrite docs.
 */
class Tree extends Basic
{
    public const OPT_DELIMITER = "delimiter";

    /**
     * Default options
     *
     * @var mixed[]
     */
    protected $defaults = [self::OPT_DELIMITER => "/"];

    /**
     * Keys delimiter
     *
     * @var string
     */
    protected $delimiter = "/";

    /**
     * Full scope, temporary scope according to complex key
     * will be stored in self::$scope
     *
     * @var mixed[]
     *
     * @internal
     */
    protected $wholeScope;

    /**
     * Internal key
     *
     * @var string|int
     */
    protected $key;

    /**
     * Constructor.
     *
     * @param mixed[] $scope
     * @param mixed[] $options
     */
    public function __construct(array $scope = [], ?array $options = null)
    {
        $this->wholeScope = $scope;
        parent::__construct([], $options);
        $this->options['tree-like'] = true;
        if (isset($this->options[self::OPT_DELIMITER])) {
            $this->delimiter = $this->options[self::OPT_DELIMITER];
        } else {
            $this->options[self::OPT_DELIMITER] = $this->delimiter;
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param string|int $key
     * @param mixed      $value
     */
    public function set($key, $value): void
    {
        $this->validateKey($key);
        $this->setScope($key, true);
        parent::set($key, $value);
        $this->key = null;
    }

    /**
     * {@inheritdoc}
     *
     * @param string|int $key
     *
     * @return bool
     */
    public function exists($key): bool
    {
        $this->validateKey($key);
        $this->setScope($key);
        $result = \is_array($this->scope) ? parent::exists($key) : false;
        $this->key = null;

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @param string|int $key
     */
    public function isEmpty($key): bool
    {
        $this->validateKey($key);
        $this->setScope($key);
        $result = parent::isEmpty($key);
        $this->key = null;

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @param ?string|int      $key      If not passed, whole scope will be returned
     * @param mixed            $default
     * @param ?string|int|null $throw    Throw exception or trigger error if no default value passed and
     *                                   key doesn't exist
     *
     * @return mixed
     */
    public function get($key = null, $default = null, $throw = RuntimeException::class)
    {
        $this->validateKey($key, true);
        if (!\is_null($key)) {
            $this->setScope($key);
        }
        $result = parent::get($key, $default, $throw);
        $this->key = null;

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @param string|int $key
     */
    public function delete($key): void
    {
        $this->validateKey($key);
        $this->setScope($key);
        parent::delete($key);
        $this->key = null;
    }

    /**
     * {@inheritdoc}
     *
     * Replaces all references by its values.
     *
     * @param string|int $key
     * @param mixed[]    $options
     *
     * @return static
     */
    public function getBranch($key, ?array $options = null)
    {
        $this->validateKey($key);
        $scope = $this->get($key);
        $result = new static($scope, $options);

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed[] $scope
     */
    public function override(array $scope): void
    {
        $this->wholeScope = $scope;
        parent::override($scope);
    }

    /**
     * Shifts scope according to complex key.
     *
     * @param &string $key
     * @param bool    $create
     */
    protected function setScope(string &$key, bool $create = false): void
    {
        $this->scope = &$this->wholeScope;
        if (false === \strpos($key, $this->delimiter)) {
            return;
        }
        $this->key = $key;
        $keys = \explode($this->delimiter, $key);
        $lastKey = \array_pop($keys);
        $lastIndex = \sizeof($keys) - 1;
        foreach ($keys as $index => $key) {
            if (!isset($this->scope[$key]) || !\is_array($this->scope[$key])) {
                if ($create) {
                    $this->scope[$key] = [];
                } elseif (!isset($this->scope[$key]) && $index == $lastIndex) {
                    return;
                }
            }
            $env = new Environment([
                'key'   => $key,
                'scope' => $this->scope,
            ]);
            $this->callMiddleware(__METHOD__, $env);
            // if ($env->exists(':key:')) {
            //     $key = $env->get(':key:');
            //     $this->setScope($key, $create);
            // }
            $this->scope = &$this->scope[$key];
        }
        $key = $lastKey;
    }
}
