<?php

$proxyDir       = __DIR__ . '/src/Proxy/';
$proxyTemplate  = __DIR__ . '/proxy-template.php';
$classFiles     = __DIR__ . '/src/*.php';
$classNamespace = 'Brick\Geo';

use Brick\Geo\CoordinateSystem;
use Brick\Reflection\ReflectionTools;

require __DIR__ . '/vendor/autoload.php';

$classes = [];

foreach (glob($proxyDir . '*.php') as $file) {
    if (basename($file) != 'ProxyInterface.php') {
        unlink($file);
    }
}

foreach (glob($classFiles) as $file) {
    $classes[] = pathinfo($file, PATHINFO_FILENAME);
}

$proxyTemplate = file_get_contents($proxyTemplate);
$proxyTemplate = preg_replace('|/\* (.+?) \*/|', '$1', $proxyTemplate);

preg_match('|// BEGIN METHOD TEMPLATE(.+)// END METHOD TEMPLATE|s', $proxyTemplate, $matches);
$methodTemplate = $matches[1];

$proxyTemplate = str_replace($matches[0], '// METHODS', $proxyTemplate);

$reflectionTools = new ReflectionTools();

foreach ($classes as $class) {
    $class = new ReflectionClass($classNamespace . '\\' .  $class);

    if ($class->getName() == CoordinateSystem::class) {
        continue;
    }

    $methods = '';

    foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        if ($method->isConstructor() || $method->isStatic()) {
            continue;
        }

        if (strpos($method->getDocComment(), '@noproxy') !== false) {
            continue;
        }

        $methodCode = $methodTemplate;
        $methodCode = str_replace('function _TEMPLATE_()', $reflectionTools->exportFunction($method, \ReflectionMethod::IS_ABSTRACT), $methodCode);

        $parameterCode = $method->getShortName() . '(';

        foreach ($method->getParameters() as $key => $parameter) {
            if ($key !== 0) {
                $parameterCode .= ', ';
            }

            $parameterCode .= '$' . $parameter->getName();
        }

        $parameterCode .= ')';

        $methodCode = str_replace('_METHOD_()', $parameterCode, $methodCode);

        $methods .= $methodCode;
    }

    $proxyCode = $proxyTemplate;
    $proxyCode = str_replace('_FQCN_', '\\' . $class->getName(), $proxyCode);
    $proxyCode = str_replace('_CLASSNAME_', $class->getShortName(), $proxyCode);
    $proxyCode = str_replace('// METHODS', $methods, $proxyCode);

    file_put_contents($proxyDir . $class->getShortName() . 'Proxy.php', $proxyCode);

    echo 'Generated proxy for ' . $class->getShortName() . PHP_EOL;
}
