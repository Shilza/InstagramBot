<?php

namespace Repository;

interface Updatable
{
    /**
     * @param $entity
     * @return mixed
     */
    static function update(&$entity);
}