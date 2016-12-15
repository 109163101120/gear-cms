<?php

class option {

    public static function has($name) {

        $option = sql::run()->from('options')->where('option_key', $name)->fetch();

        if($option && !is_null($option->option_value)) {
            return true;
        }

        return false;

    }

    public static function get($name, $default = null) {

        $option = sql::run()->from('options')->where('option_key', $name)->fetch();

        if($option && $option->option_value) {
            return $option->option_value;
        }

        return $default;

    }

    public static function set($name, $value) {

        if(is_null($value)) {
            return self::del($name);
        }

        if(self::has($name)) {

            $values = [
                'option_value' => $value
            ];

            return sql::run()->update('options')->set($values)->where('option_key', $name)->execute();

        } else {

            $values = [
                'option_key' => $name,
                'option_value' => $value
            ];

            return sql::run()->insertInto('options')->values($values)->execute();

        }

    }

    public static function del($name) {

        if(self::has($name)) {
            return sql::run()->deleteFrom('options')->where('option_key', $name)->execute();
        }

        return false;

    }

}

?>
