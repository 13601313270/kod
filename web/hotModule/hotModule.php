<?php
include_once dirname(__FILE__) . '/fileMtime.php';
restApi::get()->run(function () {
    return dir_size(webDIR);
});
