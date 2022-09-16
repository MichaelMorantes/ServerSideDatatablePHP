<?php

class alert
{
    const F_KEY = 'alert';
    const ERROR = 'danger';
    const SUCCESS = 'success';
    const WARNING = 'warning';

    private static $flash = null;

    private function __construct(){}

    public static function add($type, $message) {
        self::init();

        self::$flash[] = [
            'type' => $type,
            'message' => $message,
        ];
    }

    public static function get() {
        self::init();

        $messages = self::$flash;

        self::$flash = [];

        return $messages;
    }
    
    private static function init() {
        if (!isset($_SESSION["user"])) {
            $_SESSION["user"] = "";
        }

        if (self::$flash !== null) {
            return;
        }

        if (!array_key_exists(self::F_KEY, $_SESSION)) {
            $_SESSION[ self::F_KEY ] = [];
        }

        self::$flash = &$_SESSION[ self::F_KEY ];
    }
}
