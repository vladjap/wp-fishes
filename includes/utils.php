<?php

function diedump($content) {
    echo "<pre>";
    var_dump($content);
    die();
}