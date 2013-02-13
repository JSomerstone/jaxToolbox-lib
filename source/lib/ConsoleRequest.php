<?php
/******************
 * jaxToolbox\lib\ConsoleRequest
 *
 * @author Joona Somerkivi / 2013
 ******************/

namespace jaxToolbox\lib;

class ConsoleRequest
{
    protected $arguments;

    public function __construct( $initialInput = array())
    {
        $this->setInput($initialInput);
    }

    /**
    * Convert console-parameters into associative PHP-array
    *
    * Converts parameters from console to PHP associative array thus removing the need to input
    * parameters in specific order. Function searches from flags indicated with hyphen "-" as the
    * first character (for example "-f"). Function stores flag (without hyphen) as a key in array.
    * If only one value is found it is stored as the value of that key. If more than one values
    * are found, their stored into array and this array is set as value of the key. If no values
    * are found after flag, TRUE is stored as the value of that key.
    *
    * Examples:
    *    parameters:           resulting array:
    *    -p -l foo             array('p' => TRUE, 'l' => 'foo')
    *    -pl foo               array('p' => TRUE, 'l' => 'foo')
    *    -v one two three      array('v' => array('one', 'two', 'three') )
    *    --help                array('help' => TRUE)
    *
    * $parameters from console are in format:
    * array(
    *      0 => nameOfScript.php,
    *      1 => first argument,
    *      2 => second
    *      ...
    *      n => last one
    * )
    *
    * @param array $arguments   The arguments given to script
    * @return array $commands   Associative array presentation of given $arguments
    */
    public function setInput($arguments)
    {
        $commands = array();

        foreach ($arguments as $i => $arg)
        {
            //Skip values, only take the flags
            if( ! self::isFlag($arg))
                continue;

            $key = self::getFlag($arg);

            if (is_array($key))
            {
                foreach ($key as $subkey)
                {
                    $commands[$subkey] = array();
                }
                $key = $subkey;
            }

            $commands[$key] = array(); //Set key
            //Go trough arguments again, this time skip all arguments before $i
            //and break on first flag after $i
            foreach ($arguments as $j => $arg2)
            {
                if($j <= $i)
                    continue;

                if(self::isFlag($arg2))
                    break;

                $commands[$key][] = $arg2;
            }
        }

        foreach ($commands as $aCommand => &$value)
        {
            //If only one value is found set it as the value for this key
            if(count($value) == 1)
                $value = $value[0];
            //If no values were found set TRUE as the value for this key
            else if(count($value) == 0)
                $value = true;
        }
        $this->arguments = $commands;
        return $commands;
    }

    /**
     * Given string is flag if it starts with '-'
     * @param string $argument
     */
    private static function isFlag($argument)
    {
        return preg_match('/^-(-)?[a-z0-9]+$/i', $argument);
        substr($argument, 0, 1) == '-';
    }

    /**
     * Turn given "flag" into "name of argument(s)"
     *
     * Will convert "-a" argument into "a", "--test" into "test" and
     * "-abc" into array(a,b,c)
     *
     * @param string $argument
     * @return string|array Name of argument or list of names
     */
    private static function getFlag($argument)
    {
        // --help -> "help"
        if (preg_match('/--[a-z0-9]+/i', $argument))
            return substr($argument, 2);

        // -abcd -> array(a,b,c,d)
        if (preg_match('/-[a-z0-9]{2,}/i', $argument))
            return str_split (substr ($argument, 1));

        // -a -> "a"
        if (preg_match('/-[a-z0-9]/i', $argument))
            return substr($argument, 1);
    }

    /**
     * Check if given argument were given with setInput()
     * @return bool True if even one of given arguments was given
     */
    public function hasArgument($argument)
    {
        return isset($this->arguments[$argument]);
    }

    /**
     * Check if all of given arguments were given with setInput()
     * @return boolean
     */
    public function hasArguments()
    {
        $arguments = func_get_args();

        foreach ($arguments as $argumentName)
        {
            //missing an argument
            if ( ! isset($this->arguments[$argumentName]))
               return false;
        }
        //Got all
        return true;
    }

    /**
     * Get value of given argument
     *
     * Will return NULL if argument was not given
     *
     * @param misc|null $argumentName
     */
    public function get($argumentName)
    {
        return $this->hasArgument($argumentName)
            ? $this->arguments[$argumentName]
            : null;
    }

    /**
     * Return all arguments of the ConsoleRequest
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }
}