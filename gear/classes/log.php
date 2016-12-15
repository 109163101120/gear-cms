<?php

class log {

    public static function set($entry_type, $entry_id, $action = 'add') {

        $now = new DateTime('now');

        $values = [
            'log_entry_type' => $entry_type,
            'log_entry_id' => $entry_id,
            'log_user_id' => user::current()->id,
            'log_action' => $action,
            'log_datetime' => $now->format('Y-m-d H:i:s')
        ];

        return sql::run()->insertInto('logs')->values($values)->execute();

    }

    public static function del($entry_type, $entry_id) {

        if($entry_type && $entry_id) {

            $where = [
                'log_entry_type' => $entry_type,
                'log_entry_id' => $entry_id
            ];

            return sql::run()->deleteFrom('logs')->where($where)->execute();

        }

        return false;

    }

    public static function getAll($limit = 10, $action = false) {

        if(!$action) {
            return sql::run()->from('logs')->orderBy('log_id DESC')->limit($limit)->fetchAll();
        } else {
            return sql::run()->from('logs')->where('log_action', $action)->orderBy('log_id DESC')->limit($limit)->fetchAll();
        }

    }

}

?>
