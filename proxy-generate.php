<?php

$proxyDir          = __DIR__ . '/src/Proxy/';
$proxyTemplate     = __DIR__ . '/proxy-template.php';
$classFiles        = __DIR__ . '/src/*.php';
$classNamespace    = 'Brick\Geo';

define('EOL', "\n");
define('TAB', '    ');

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

$reflectionTools = new Brick\Reflection\ReflectionTools();

foreach ($classes as $class) {
    $fqcn = $classNamespace . '\\' .  $class;
    $class = new ReflectionClass($fqcn);

    $methods = '';
    $use = [];

    foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        if ($method->isStatic()) {
            continue;
        }

        if (strpos($method->getDocComment(), '@noproxy') !== false) {
            continue;
        }

        $methods .= TAB . '/**' . EOL;
        $methods .= TAB . ' * {@inheritdoc}' . EOL;
        $methods .= TAB . ' */' . EOL;

        $methods .= TAB . $reflectionTools->exportFunction($method, \ReflectionMethod::IS_ABSTRACT) . EOL;
        $methods .= TAB . '{' . EOL;

        $methods .= TAB . TAB . 'if ($this->geometry === null) {' . EOL;
        $methods .= TAB . TAB . TAB . '$this->load();' . EOL;
        $methods .= TAB . TAB . '}' . EOL;
        $methods .= EOL;
        $methods .= TAB . TAB . 'return $this->geometry->' . $method->getShortName() . '(';

        foreach ($method->getParameters() as $key => $parameter) {
            if ($key !== 0) {
                $methods .= ', ';
            }

            $methods .= '$' . $parameter->getName();
        }

        $methods .= ');' . EOL;

        $methods .= TAB . '}' . EOL;
        $methods .= EOL;
    }

    $proxyCode = $proxyTemplate;
    $proxyCode = str_replace(Brick\Geo\Geometry::class, $fqcn, $proxyCode);

    $proxyCode = str_replace('/* {CLASSNAME} */', $class->getShortName(), $proxyCode);
    $proxyCode = str_replace('/* {EXTENDS} */', 'extends ' . '\\' . $class->getName(), $proxyCode);
    $proxyCode = str_replace('/* {METHODS} */', $methods, $proxyCode);

    file_put_contents($proxyDir . $class->getShortName() . 'Proxy.php', $proxyCode);

    echo 'Generated proxy for ' . $class->getShortName() . PHP_EOL;
}
