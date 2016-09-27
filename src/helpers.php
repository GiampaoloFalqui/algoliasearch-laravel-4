<?php

if (! function_exists('is_multidimensional'))
{
    function is_multidimensional(Array $array)
    {
        foreach ($array as $element) {
            if (is_array($element)) {
                return true;
            } else {
                return false;
            }
        }
    }
}