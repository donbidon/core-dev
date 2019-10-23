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
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function set(string $key, $value): void
    {
        $this->scope[$key] = $value;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $key
     *
     * @return bool
     */
    public function exists(string $key): bool
    {
        $result = \array_key_exists($key, $this->scope);

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $key
     *
     * @return bool
     */
    public function isEmpty(string $key): bool
    {
        $result = empty($this->scope[$key]);

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @param string          $key      If not passed, whole scope will be returned
     * @param mixed           $default
     * @param string|int|null $throw    Throw exception or trigger error if no default value passed and
     *                                  key doesn't exist
     *
     * @return mixed
     */
    public function get(?string $key = null, $default = null, $throw = RuntimeException::class)
    {
        $result = $default;
        if (\is_null($key)) {
            $result = $this->scope;
        } elseif (\array_key_exists($key, $this->scope)) {
            $result = $this->scope[$key];
        } elseif (\is_null($default) && $throw) {
            $message = \sprintf("Missing key '%s'", $key);
            if (\is_string($throw)) {
                throw new $throw($message);
            } else {
                \trigger_error($message, $throw);
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @para string $key
     *
     * @return void
     */
    public function delete(string $key): void
    {
        unset($this->scope[$key]);
    }

    /**
     * {@inheritdoc}
     *
     * Replaces all references by its values.
     *
     * @param string  $key
     * @param mixed[] $options
     *
     * @return static
     */
    public function getBranch(string $key, ?array $options = null)
    {
        $result = new static($this->get(
            $key,
            \is_null($options) ? $this->options : $options
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
