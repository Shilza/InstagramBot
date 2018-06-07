<?php

abstract class Repository{
    protected abstract static function select(array $criterions);

    protected abstract static function update($id, array $values);

    protected abstract static function insert(array $values);
}