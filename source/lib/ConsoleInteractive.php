<?php
/******************
 * jaxToolbox\lib\ConsoleRequest
 *
 * @author Joona Somerkivi / 2013
 ******************/

namespace jaxToolbox\lib;

class ConsoleInteractive
{
    private $lastInput;

    public function askUserFor($messageToUser, $regexpToValidateWith = null)
    {
        echo $messageToUser, "\n>\t";

        $answer = $this->getUserInput();

        if ( !is_null($regexpToValidateWith))
        {
            while ( !preg_match($regexpToValidateWith, $answer))
            {
                echo 'Please try again', "\n", $messageToUser, "\n>\t";
                $answer = $this->getUserInput();
            }
        }
        return $answer;
    }

    public function askUserForNumber($message)
    {
        return $this->askUserFor($message, '/[0-9]+([.][0-9]+)?/');
    }

    public function askUserForNumberBetween($message, $min, $max)
    {
        $answer = $this->askUserForNumber($message);
        while ( $answer < $min || $answer > $max )
        {
            $answer = $this->askUserForNumber(
                $message . " - between $min and $max"
            );
        }
        return $answer;
    }

    public function getUserInput()
    {
        $input = trim(fgets(STDIN));
        $this->lastInput = $input;
        self::userExits($input);
        return $input;
    }

    /**
     * Presents the user given message and ask "Yes or No?"
     * Returns TRUE if "Yes" and FALSE if "No"
     *
     * @param string $message
     * @return bool
     */
    public function yesOrNo($message)
    {
        $answer = $this->askUserFor(
            $message . " (Y)es or (N)o?",
            '/^(yes|y|no|n)$/i'
        );

        return preg_match('/^[yY]/', $answer);
    }

    private static function userExits($answer)
    {
        if ($answer == 'exit')
            exit();
    }
}