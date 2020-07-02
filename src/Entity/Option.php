<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Option.
 *
 * @ORM\Table(name="options")
 * @ORM\Entity()
 */
class Option
{
    public const TYPE_STRING = 'string';
    public const TYPE_NUMBER = 'number';
    public const TYPE_BOOL = 'boolean';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="option_key", type="string", unique=true, length=100)
     */
    private $key;

    /**
     * @var string
     *
     * @ORM\Column(name="option_value", type="string", length=100)
     */
    private $value;

    /**
     * @var string
     *
     * @ORM\Column(name="option_value_type", type="string", length=10)
     */
    private $type = self::TYPE_STRING;

    /**
     * @return string|null
     */
    public function getKey(): ?string
    {
        return $this->key;
    }

    /**
     * @param string $key
     *
     * @return $this
     */
    public function setKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return float|int|mixed|string
     */
    public function getTypedValue()
    {
        switch ($this->type) {
            case self::TYPE_NUMBER:
                $intValue = (int) $this->value;
                $floatValue = (float) $this->value;
                $result = $intValue;
                if (\is_numeric($this->value) && $intValue != $floatValue) {
                    $result = $floatValue;
                }
                break;
            case self::TYPE_BOOL:
                $result = \filter_var($this->value, FILTER_VALIDATE_BOOLEAN);
                break;
            default:
                $result = $this->value;
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getTypedValueString(): string
    {
        $result = $this->getTypedValue();
        if (\is_bool($result)) {
            $result = true === $result ? 'true' : 'false';
        }

        return (string) $result;
    }

    /**
     * @return array|string[]
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_STRING => self::TYPE_STRING,
            self::TYPE_BOOL => self::TYPE_BOOL,
            self::TYPE_NUMBER => self::TYPE_NUMBER,
        ];
    }
}
