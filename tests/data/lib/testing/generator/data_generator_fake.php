<?php

class component_generator_base {}

class testing_data_generator {

    /**
     * Return generator for given plugin or component.
     * @param string $component the component name, e.g. 'mod_forum' or 'core_question'.
     * @return component_generator_base or rather an instance of the appropriate subclass.
     */
    public function get_plugin_generator($component) {

    }

}