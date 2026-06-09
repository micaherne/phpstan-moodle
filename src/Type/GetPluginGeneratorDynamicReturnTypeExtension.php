<?php

namespace PhpstanMoodle\Type;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

class GetPluginGeneratorDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{

    /** @return class-string */
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
        $args = $methodCall->getArgs();
        if ($args === []) {
            return null;
        }

        $types = [];
        foreach ($scope->getType($args[0]->value)->getConstantStrings() as $constantString) {
            $component = $constantString->getValue();

            if (class_exists('core_component')) {
                [$type, $plugin] = \core_component::normalize_component($component);
                $component = $type . '_' . $plugin;
            }

            $types[] = new ObjectType('\\' . $component . '_generator');
        }
        if ($types === []) {
            return null;
        }

        return TypeCombinator::union(...$types);
    }
}
