<?php

class ajax {

    static $return = [];

    public static function is() {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    }

    public static function addReturn($text) {
        self::$return[] = $text;
    }

    public static function getReturn() {
        return implode('<br>', self::$return);
    }

    public static function messages() {

        if(type::post('method', 'string', '') == 'getMessages') {

            $messages = type::session('messages');

            if(is_array($messages) && count($messages)) {
                foreach($messages as $index => $val) {
                    $return = [
                        'html' => message::getMessage($val['message'], $val['class']),
                        'index' => $index
                    ];
                    self::addReturn(json_encode($return));
                    break;
                }
            }

        }

        if(type::post('method', 'string', '') == 'deleteMessage') {

            $messages = type::session('messages');
            $index = type::post('index');

            unset($messages[$index]);

            type::addSession('messages', $messages);

        }

    }

    public static function setMenu() {

        if(type::post('method', 'string', '') == 'setMenu') {

            $url = type::post('url', 'string', '');
            $active = type::post('active', 'int', 0);

            $user = user::current();
            $menu = ($user->openMenu) ? unserialize($user->openMenu) : [];

            if($active) {
                $menu[$url] = true;
            } else {
                if(isset($menu[$url])) {
                    unset($menu[$url]);
                }
            }

            $menu = (is_array($menu) && count($menu)) ? serialize($menu) : null;

            $user->save([
                'openMenu' => $menu
            ]);

        }

    }

}

?>
