<?php
/**
 * Registry functionality.
 *
 * @copyright <a href="http://donbidon.rf.gd/" target="_blank">donbidon</a>
 * @license   https://opensource.org/licenses/mit-license.php
 */

declare(strict_types=1);

namespace donbidon\Core\Registry;

use RuntimeException;
use function array_key_exists;
use function is_null;
use function is_string;
use function sprintf;
use function trigger_error;

/**
 * Static and non-static registry functionality.
 *
 * Registry functionality.
 * ```php
 * use \donbidon\Core\Registry\Basic;
 *
 * $registry = new Basic[
 *     'key_1'     => "value_1",
 *     'key_2'     => "value_2",
 *     'ref_1'     => "~~> ref_2",
 *     'ref_2'     => "~~> ref_value",
 *     'ref_value' => "final reference value",
 * ], Basic::ACTION_ALL & ~Basic::ACTION_MODIFY);
 * var_dump($registry->exists('key_1'));
 * var_dump($registry->exists('key_3'));
 * var_dump($registry->get('key_3', "default value"));
 * var_dump($registry->get('ref_1'));
 * var_dump($registry->get('ref_2'));
 * $registry->set('key_3', "value_3");
 * $registry->set('key_1', "value_11");
 * ```
 * outputs
 * ```
 * bool(true)
 * bool(false)
 * string(13) "default value"
 * string(21) "final reference value"
 * string(21) "final reference value"
 *
 * Fatal error: Uncaught RuntimeException: ACTION_MODIFY: no permissions for key 'key_1'
 * ```
 *
 * @todo Implement middleware calls.
 * @todo Rewrite documentation.
 */
class Basic extends RegistryAbstract
{
    /**
     * {@inheritdoc}
     *
     * @param string|int $key
     * @param mixed      $value
     */
    public function set($key, $value): void
    {
        $this->validateKey($key);
        $this->scope[$key] = $value;
    }

    /**
     * {@inheritdoc}
     *
     * @param string|int $key
     */
    public function exists($key): bool
    {
        $this->validateKey($key);
        $result = array_key_exists($key, $this->scope);
        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @param string|int $key
     *
     * @return bool
     */
    public function isEmpty($key): bool
    {
        $this->validateKey($key);
        $result = empty($this->scope[$key]);
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
        $result = $default;
        if (is_null($key)) {
            $result = $this->scope;
        } elseif (array_key_exists($key, $this->scope)) {
            $result = $this->scope[$key];
        } elseif (is_null($default) && $throw) {
            $message = sprintf("Missing key '%s'", $key);
            if (is_string($throw)) {
                throw new $throw($message);
            } else {
                trigger_error($message, $throw);
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
        unset($this->scope[$key]);
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
        $result = new static($this->get(
            $key,
            is_null($options) ? $this->options : $options
        ));

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed[] $scope
     *
     * @return void
     */
    public function override(array $scope): void
    {
        $this->scope = $scope;
    }
}
