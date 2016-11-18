<?php

define('LANGEDITOR_BASE', basename(dirname(__FILE__)));

Config::inst()->update('LeftAndMain', 'extra_requirements_javascript', [
    LANGEDITOR_BASE.'/javascript/lang_editor.js' => [],
    LANGEDITOR_BASE.'/css/lang_editor.css'       => [],
]);