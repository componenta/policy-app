<?php

declare(strict_types=1);

namespace Componenta\Policy\App\Compile;

use Componenta\Policy\Attribute\AllOf;
use Componenta\Policy\Attribute\OneOf;
use Componenta\Policy\Attribute\Policy;
use Componenta\Policy\Policies\AllOf as AllOfPolicy;
use Componenta\Policy\Policies\OneOf as OneOfPolicy;
use Componenta\Policy\PolicyInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;

/**
 * Compiles policy attributes into serialisable descriptors.
 */
final class PolicyMapCompiler
{
    /**
     * @param iterable<class-string> $classes
     * @return array<string, array<string, mixed>>
     */
    public function compile(iterable $classes): array
    {
        $map = [];

        foreach ($classes as $class) {
            if (!is_string($class) || !class_exists($class)) {
                continue;
            }

            try {
                $reflection = new ReflectionClass($class);
            } catch (\ReflectionException) {
                continue;
            }

            $classPolicy = $this->compileClass($reflection);
            if ($classPolicy !== null) {
                $map[$reflection->getName()] = $classPolicy;
            }

            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                $methodPolicy = $this->compileReflector($method);
                if ($methodPolicy !== null) {
                    $map[$reflection->getName() . '::' . $method->getName()] = $methodPolicy;
                }
            }
        }

        return $map;
    }

    /**
     * Mirrors AttributePolicyProvider::discoverFromClass(): child class
     * policies precede inherited parent policies and combine with AND.
     *
     * @return array<string, mixed>|null
     */
    private function compileClass(ReflectionClass $reflection): ?array
    {
        $policies = [];

        do {
            $descriptors = $this->extractPolicyDescriptors($reflection);
            if ($descriptors === null) {
                return null;
            }

            $policies = [...$policies, ...$descriptors];
            $reflection = $reflection->getParentClass();
        } while ($reflection !== false);

        return $this->combine($policies);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function compileReflector(ReflectionClass|ReflectionMethod $reflector): ?array
    {
        $policies = $this->extractPolicyDescriptors($reflector);

        return $policies === null ? null : $this->combine($policies);
    }

    /**
     * @return list<array<string, mixed>>|null
     */
    private function extractPolicyDescriptors(ReflectionClass|ReflectionMethod $reflector): ?array
    {
        $policies = [];

        foreach ($reflector->getAttributes(PolicyInterface::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            $descriptor = $this->directAttribute($attribute);
            if ($descriptor === null) {
                return null;
            }

            $policies[] = $descriptor;
        }

        foreach ($reflector->getAttributes(Policy::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            try {
                $policy = $attribute->newInstance();
            } catch (\Throwable) {
                return null;
            }

            $policies[] = $this->factoryPolicy($policy);
        }

        foreach ($reflector->getAttributes(AllOf::class) as $attribute) {
            $descriptor = $this->compositeAttribute($attribute, all: true);
            if ($descriptor === null) {
                return null;
            }

            $policies[] = $descriptor;
        }

        foreach ($reflector->getAttributes(OneOf::class) as $attribute) {
            $descriptor = $this->compositeAttribute($attribute, all: false);
            if ($descriptor === null) {
                return null;
            }

            $policies[] = $descriptor;
        }

        return $policies;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function directAttribute(ReflectionAttribute $attribute): ?array
    {
        $class = $attribute->getName();

        return is_a($class, PolicyInterface::class, true)
            ? [
                'kind' => 'direct',
                'class' => $class,
                'arguments' => $attribute->getArguments(),
            ]
            : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function compositeAttribute(ReflectionAttribute $attribute, bool $all): ?array
    {
        try {
            $composite = $attribute->newInstance();
        } catch (\Throwable) {
            return null;
        }

        $children = [];

        foreach ($composite->policies as $policy) {
            $descriptor = $this->policyObject($policy);
            if ($descriptor === null) {
                return null;
            }

            $children[] = $descriptor;
        }

        return [
            'kind' => $all ? 'all_of' : 'one_of',
            'policies' => $children,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function policyObject(Policy|PolicyInterface $policy): ?array
    {
        if ($policy instanceof Policy) {
            return $this->factoryPolicy($policy);
        }

        if ($policy instanceof AllOfPolicy) {
            return null;
        }

        if ($policy instanceof OneOfPolicy) {
            return null;
        }

        $arguments = $this->constructorArguments($policy);

        return $arguments === null
            ? null
            : [
                'kind' => 'direct',
                'class' => $policy::class,
                'arguments' => $arguments,
            ];
    }

    /**
     * @return array<string, mixed>
     */
    private function factoryPolicy(Policy $policy): array
    {
        return [
            'kind' => 'factory',
            'policy' => $policy->policy,
            'arguments' => $policy->arguments,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function constructorArguments(PolicyInterface $policy): ?array
    {
        $reflection = new ReflectionClass($policy);
        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return [];
        }

        $arguments = [];

        foreach ($constructor->getParameters() as $parameter) {
            $name = $parameter->getName();

            if (!$reflection->hasProperty($name)) {
                return $parameter->isDefaultValueAvailable() ? $arguments : null;
            }

            $property = $reflection->getProperty($name);
            $arguments[$name] = $property->getValue($policy);
        }

        return $arguments;
    }

    /**
     * @param list<array<string, mixed>> $policies
     * @return array<string, mixed>|null
     */
    private function combine(array $policies): ?array
    {
        if ($policies === []) {
            return null;
        }

        if (count($policies) === 1) {
            return $policies[0];
        }

        return [
            'kind' => 'all_of',
            'policies' => $policies,
        ];
    }
}
