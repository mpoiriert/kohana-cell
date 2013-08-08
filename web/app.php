<?php

require_once("../vendor/autoload.php");

include __DIR__ . '/../demo/ApplicationKernel.php';

ApplicationKernel::createInstance()->handleRequest();
