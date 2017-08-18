<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 17.08.17
 * Time: 12:15
 */

namespace Model;


interface ModelInterface
{

    /**
     * @return string
     */
    public function __toString();

    /**
     * @return array
     */
    public function __toArray();

}