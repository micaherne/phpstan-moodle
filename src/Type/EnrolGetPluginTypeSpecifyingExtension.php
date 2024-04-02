<?php

namespace PhpstanMoodle\Type;

use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\ObjectType;

class EnrolGetPluginTypeSpecifyingExtension implements DynamicFunctionReturnTypeExtension
{

    public function isFunctionSupported(FunctionReflection $functionReflection): bool
    {
        return $functionReflection->getName() === 'enrol_get_plugin';
    }

    public function getTypeFromFunctionCall(
        FunctionReflection $functionReflection,
        FuncCall $functionCall,
        Scope $scope
    ): ?\PHPStan\Type\Type {
        if (count($functionCall->getArgs()) === 0) {
            return null;
        }
        $arg1 = $functionCall->getArgs()[0]->value;
        if (!$arg1 instanceof String_) {
            return null;
        }

        return new ObjectType('enrol_' . $arg1->value . '_plugin');
    }
}