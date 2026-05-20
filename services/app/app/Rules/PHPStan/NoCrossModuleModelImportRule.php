<?php

declare(strict_types=1);

namespace App\Rules\PHPStan;

use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Use_>
 */
class NoCrossModuleModelImportRule implements Rule
{
    public function getNodeType(): string
    {
        return Use_::class;
    }

    /**
     * @param  Use_  $node
     * @return RuleError[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $currentNamespace = $scope->getNamespace() ?? '';

        if (! str_starts_with($currentNamespace, 'App\\Modules\\')) {
            return [];
        }

        if (str_contains($currentNamespace, '\\Database\\Factories')) {
            return [];
        }

        $parts = explode('\\', $currentNamespace);
        $currentModule = $parts[2] ?? '';

        $errors = [];

        foreach ($node->uses as $use) {
            $importedName = $use->name->toString();

            if (preg_match('/^App\\\\Modules\\\\([^\\\\]+)\\\\Models/', $importedName, $matches) !== 1) {
                continue;
            }

            $importedModule = $matches[1];

            if ($importedModule === $currentModule) {
                continue;
            }

            $errors[] = RuleErrorBuilder::message(
                sprintf(
                    'Cross-module model access forbidden: "%s" imported from module "%s". Use %s\\PublicApi\\%sServiceInterface instead.',
                    $importedName,
                    $importedModule,
                    $importedModule,
                    $importedModule,
                )
            )->build();
        }

        return $errors;
    }
}
