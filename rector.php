<?php

use Rector\Config\RectorConfig;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php80\Rector\Switch_\ChangeSwitchToMatchRector;
use Rector\Php81\Rector\ClassMethod\NewInInitializerRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Php82\Rector\Class_\ReadOnlyClassRector;
use Rector\Php84\Rector\MethodCall\NewMethodCallWithoutParenthesesRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPhpVersion(PhpVersion::PHP_84)
    ->withRules([
        TypedPropertyFromStrictConstructorRector::class,
        NewMethodCallWithoutParenthesesRector::class,
        JsonThrowOnErrorRector::class,
        ReadOnlyPropertyRector::class,
        NewInInitializerRector::class,
        ReturnNeverTypeRector::class,
        ChangeSwitchToMatchRector::class,
        ClassPropertyAssignToConstructorPromotionRector::class,
        ReadOnlyClassRector::class,
    ])
    // here we can define, what prepared sets of rules will be applied
    ->withPreparedSets(
        codeQuality: true
    )
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/examples',
        __DIR__ . '/tests',
    ]);