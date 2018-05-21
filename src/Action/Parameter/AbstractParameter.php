<?php
/**
 * This file is part of the Objective PHP project
 *
 * More info about Objective PHP on www.objective-php.org
 *
 * @license http://opensource.org/licenses/GPL-3.0 GNU GPL License 3.0
 */

namespace ObjectivePHP\Cli\Action\Parameter;


abstract class AbstractParameter implements ParameterInterface
{
    protected $options = 0;

    protected $shortName = '';

    protected $longName = '';

    protected $value;

    protected $description;

    public function __construct($name, $description = '', $options = 0)
    {
        $this->setName($name);
        $this->setDescription($description);
        $this->setOptions($options);
    }


    /**
     * @return int
     */
    public function getOptions(): int
    {
        return $this->options;
    }

    /**
     * @param int $options
     *
     * @return $this
     */
    public function setOptions(int $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return string
     */
    public function getShortName(): string
    {
        return $this->shortName;
    }

    /**
     * @param string|array $name
     *
     * @return $this
     */
    public function setName($name)
    {
        if (is_array($name)) {
            reset($name);
            $shortName = key($name);
            $longName = current($name);

            if (strlen($shortName) !== 1) {
                throw new ParameterException('Short parameters name has to be exactly one character long');
            }

            $this->shortName = $shortName;
            $this->longName = $longName;
        } else if (strlen($name) == 1) {
            $this->shortName = $name;
        } else {
            $this->longName = $name;
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLongName(): string
    {
        return $this->longName;
    }


    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }
}
