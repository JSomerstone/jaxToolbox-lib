<?php

namespace jaxToolbox\lib;

class StringPermutator
{
    private $characterPool;
    private $reverceCharacterPool;
    private $stringLength;
    private $iteration = 0;
    private $previousString;
    private $lastString;
    private $hasNext = true;

    public function __construct($characterPool, $stringLength)
    {
        $this->characterPool = str_split($characterPool);
        $this->reverceCharacterPool = array_flip($this->characterPool);

        $this->stringLength = $stringLength;
        $this->previousString = str_repeat($this->characterPool[0], $this->stringLength);
        $this->lastString = str_repeat($characterPool[strlen($characterPool)-1], $stringLength);
    }

    public function setStartPoint($stringToStartFrom)
    {
        $this->previousString = $stringToStartFrom;
    }

    public function hasNext()
    {
        return $this->previousString !== $this->lastString;
    }

    public function getIteration()
    {
       return $this->iteration;
    }

    public function getNext()
    {
        if ($this->iteration == 0)
        {
            $newString = $this->previousString;
        }
        else
        {
            $newString = $this->formString($this->previousString);
        }
        $this->previousString = $newString;

        $this->iteration++;
        return $newString;
    }

    private function formString($previousString)
    {
        $splitted = str_split($previousString);
        $range = count($this->characterPool)-1;

        $arrayPresentation = array();
        foreach ($splitted as $charNro => $character)
        {
            $arrayPresentation[$charNro] = $this->reverceCharacterPool[$character];
        }

        foreach ($arrayPresentation as $i => &$n)
        {
            if ($n + 1 > $range)
            {
                $n = 0;
                if (!isset($arrayPresentation[$i+1]))
                    break;
            }
            else
            {
                $n++;
                break;
            }
        }

        $newString = array();
        foreach ($arrayPresentation as $charNro => $charIndex)
        {
            $newString[$charNro] = $this->characterPool[$charIndex];
        }

        return implode('', $newString);
    }

}

class StringPermutatorException extends \Exception {}