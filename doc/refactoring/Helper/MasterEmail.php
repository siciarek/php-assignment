<?php

namespace App\Helper;

class MasterEmail
{
    const UNKNOWN_EMAIL = "unknown";

    /**
     * @param array $request
     * @return string
     */
    public static function email(array $params): string
    {
        if (!$params) {
            return self::UNKNOWN_EMAIL;
        }

        foreach (["email", "masterEmail"] as $key) {
            if (!array_key_exists($key, $params)) {
                continue;
            }
            $email = $params[$key];
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $email;
            }
        }
        return self::UNKNOWN_EMAIL;
    }
}
