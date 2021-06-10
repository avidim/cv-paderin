<?php

$request = [
    'name' => filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
    'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
    'phone' => filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_NUMBER_INT),
];

require_once('./Main.php');

$api = new Main;
$api->checkToken()->addLead($request);