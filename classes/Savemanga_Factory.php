<?php

/**
 * Este fichero forma parte de la librería Savemanga
 * @category   Savemanga
 * @package    Savemanga_Factory
 * @author     Rubén Monge <rubenmonge@gmail.com>
 * @copyright  Copyright (c) 2011-2012 Rubén Monge. (http://www.rubenmonge.es/)
 */
class Savemanga_Factory
{

    static public function getInstanceOf($url)
    {
        if (strpos($url, "mangareader") !== false) {

            $object = new Savemanga_Mangareader();
        }

        if (is_object($object)) {
            return $object;
        }
        return false;
    }

}