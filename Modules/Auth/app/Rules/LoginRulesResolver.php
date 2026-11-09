<?php

namespace Modules\Auth\Rules;

use Modules\Auth\Contracts\LoginRules;
use Modules\Auth\Contracts\LoginStrategy;

/**
 * Selects the validation rules that match the active login strategy.
 */
class LoginRulesResolver
{
    /**
     * @var array<string, LoginRules>
     */
    private array $ruleSets = [];

    /**
     * @param  iterable<LoginRules>  $ruleSets
     */
    public function __construct(
        private readonly LoginStrategy $strategy,
        iterable $ruleSets,
    ) {
        foreach ($ruleSets as $ruleSet) {
            $this->ruleSets[$ruleSet->method()->value] = $ruleSet;
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $method = $this->strategy->method();
        $ruleSet = $this->ruleSets[$method->value] ?? null;

        return $ruleSet?->rules() ?? [];
    }
}
