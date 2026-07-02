<?php

namespace App\Actions\Fortify;

trait PasswordValidationRules
{
    /**
     * パスワードのバリデーションルールを返す
     *
     * @return array<int,string>
     */
    protected function passwordRules(): array
    {
        return ['required', 'string', 'min:8', 'confirmed'];
    }
}
