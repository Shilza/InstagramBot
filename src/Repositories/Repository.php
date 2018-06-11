<?php

abstract class Repository{

    /**
     * @param array $criterions
     * @return mixed
     */
    public abstract static function getBy(array $criterions);

    /**
     * @param $entity
     * @return mixed
     */
    public abstract static function add($entity);

    /**
     * @param $entity
     * @return mixed
     */
    public abstract static function delete($entity);

    /**
     * @param $id
     * @return bool
     */
    protected static function isValid($id){
        return (isset($id) && (is_int($id) || ctype_digit($id)));
    }
}