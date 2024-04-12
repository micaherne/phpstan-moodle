<?php

namespace PhpstanMoodle\Type;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

class GetPluginGeneratorTypeSpecifyingExtension implements DynamicMethodReturnTypeExtension
{

    public function getClass(): string
    {
        return 'testing_data_generator';
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'get_plugin_generator';
    }

    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope
    ): ?Type {
        if (count($methodCall->getArgs()) < 1) {
            return null;
        }
        $arg1 = $methodCall->getArgs()[0]->value;
        if (!$arg1 instanceof String_) {
            return null;
        }

        $component = $arg1->value;

        if (class_exists('core_component')) {
            [$type, $plugin] = \core_component::normalize_component($component);
            $component = $type . '_' . $plugin;
        }

        return new ObjectType('\\' . $component . '_generator');
    }
}