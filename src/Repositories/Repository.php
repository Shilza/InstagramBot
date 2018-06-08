<?php

abstract class Repository{
    public abstract static function getBy(array $criterions);

    public abstract static function add($entity);

    public abstract static function delete($entity);
}