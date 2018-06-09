<?php

abstract class Repository{
    public abstract static function getBy(array $criterions);

    public abstract static function add($entity);

    public abstract static function delete($entity);

    protected static function isValid($id){
        return (isset($id) && (is_int($id) || ctype_digit($id)));
    }
}