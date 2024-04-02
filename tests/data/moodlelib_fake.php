<?php

class auth_plugin_base {}
class auth_plugin_db extends auth_plugin_base {}

/**
 * Returns an authentication plugin instance.
 *
 * @param string $auth name of authentication plugin
 * @return auth_plugin_base An instance of the required authentication plugin.
 */
function get_auth_plugin(string $name) {
    $class = "auth_plugin_$name";
    return new $class;
}