<?php
/**
 * Tree registry functionality.
 *
 * @author  [donbidon](http://donbidon.rf.gd/)
 * @license [MIT](https://opensource.org/licenses/mit-license.php)
 */

declare(strict_types=1);

namespace donbidon\Core\Registry;

use donbidon\Core\Registry\Basic as Environment;
use RuntimeException;
use function array_pop;
use function explode;
use function is_array;
use function is_null;
use function is_string;
use function sizeof;
use function sprintf;
use function strpos;
use function trigger_error;

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
 * @author  [donbidon](http://donbidon.rf.gd/)
 * @license [MIT](https://opensource.org/licenses/mit-license.php)
 *
 * @todo Implement middleware calls.
 * @todo Rewrite documentation.
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
     */
    public function exists($key): bool
    {
        $this->validateKey($key);
        try {
            $this->setScope($key);
            $result = is_array($this->scope) ? parent::exists($key) : false;
        } catch (RuntimeException $e) {
            $result = false;
        }
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
        try {
            $this->setScope($key);
            $result = parent::isEmpty($key);
        } catch (RuntimeException $e) {
            $result = true;
        }
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
        $origKey = $key;
        $result = null;
        if (is_null($key)) {
            $result = parent::get($key, $default, $throw);
        } else {
            try {
                $this->setScope($key, false);
                $result = parent::get($key, $default, $throw);
                $this->key = null;
            } catch (RuntimeException $e) {
                $message = sprintf("Nonexistent key '%s'", $origKey);
                $this->key = null;
                if (is_string($throw)) {
                    throw new $throw($message);
                } else {
                    trigger_error($message, $throw);
                }
            }
        }
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
     * @param string|int $key
     * @param mixed[]    $options
     *
     * @return static
     */
    public function getBranch($key, ?array $options = null)
    {
        $this->validateKey($key);
        $scope = $this->get($key);
        /** @noinspection PhpUnnecessaryLocalVariableInspection */
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
     * @throws RuntimeException
     * @noinspection PhpDocSignatureInspection
     */
    protected function setScope(string &$key, bool $create = false): void
    {
        $this->scope = &$this->wholeScope;
        if (false === strpos($key, $this->delimiter)) {
            return;
        }
        $this->key = $key;
        $keys = explode($this->delimiter, $key);
        $lastKey = array_pop($keys);
        $lastIndex = sizeof($keys) - 1;
        foreach ($keys as $index => $key) {
            if (!isset($this->scope[$key]) || !is_array($this->scope[$key])) {
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
            if (!is_array($this->scope[$key])) {
                throw new RuntimeException("Complex scope must be an array");
            }
            $this->scope = &$this->scope[$key];
        }
        $key = $lastKey;
    }
}
