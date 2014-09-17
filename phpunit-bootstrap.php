<?php

require 'vendor/autoload.php';

Brick\Geo\Geometry::setService(
    new Brick\Geo\Service\DatabaseService(
        new PDO($_ENV['PDO_DSN'], $_ENV['PDO_USERNAME'], $_ENV['PDO_PASSWORD'])
    )
);
