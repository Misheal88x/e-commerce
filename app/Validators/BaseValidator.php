<?php

declare(strict_types=1);

namespace App\Validators;

use App\Exceptions\ValidationException;
use App\Helpers\SanitizerHelper;

abstract class BaseValidator
{
    protected array $errors = [];

    abstract protected function rules(): array;

    public function validate(array $data): array
    {
        $this->errors = [];

        foreach ($this->rules() as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            $value = $data[$field] ?? null;

            foreach ($rules as $rule) {
                [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);
                $this->applyRule($field, $value, $name, $param, $data);
            }
        }

        if (!empty($this->errors)) {
            throw new ValidationException($this->errors);
        }

        return $this->sanitize($data);
    }

    private function applyRule(string $field, mixed $value, string $rule, ?string $param, array $data): void
    {
        match($rule) {
            'required'       => $this->validateRequired($field, $value),
            'required_if'    => $this->validateRequiredIf($field, $value, $param, $data),
            'email'          => $this->validateEmail($field, $value),
            'min'            => $this->validateMin($field, $value, (int) $param),
            'max'            => $this->validateMax($field, $value, (int) $param),
            'min_val'        => $this->validateMinVal($field, $value, (float) $param),
            'max_val'        => $this->validateMaxVal($field, $value, (float) $param),
            'in'             => $this->validateIn($field, $value, explode(',', $param ?? '')),
            'numeric'        => $this->validateNumeric($field, $value),
            'integer'        => $this->validateInteger($field, $value),
            'boolean'        => $this->validateBoolean($field, $value),
            'date'           => $this->validateDate($field, $value),
            'url'            => $this->validateUrl($field, $value),
            'confirmed'      => $this->validateConfirmed($field, $value, $data),
            'nullable'       => null,
            default          => null,
        };
    }

    private function validateRequired(string $field, mixed $value): void
    {
        if ($value === null || $value === '') {
            $this->addError($field, "The {$field} field is required.");
        }
    }

    private function validateRequiredIf(string $field, mixed $value, ?string $param, array $data): void
    {
        if ($param === null) return;
        [$otherField, $otherValue] = explode(',', $param, 2);
        if (($data[$otherField] ?? null) == $otherValue && ($value === null || $value === '')) {
            $this->addError($field, "The {$field} field is required.");
        }
    }

    private function validateEmail(string $field, mixed $value): void
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, "The {$field} must be a valid email address.");
        }
    }

    private function validateMin(string $field, mixed $value, int $min): void
    {
        if ($value !== null && $value !== '' && mb_strlen((string) $value) < $min) {
            $this->addError($field, "The {$field} must be at least {$min} characters.");
        }
    }

    private function validateMax(string $field, mixed $value, int $max): void
    {
        if ($value !== null && mb_strlen((string) $value) > $max) {
            $this->addError($field, "The {$field} must not exceed {$max} characters.");
        }
    }

    private function validateMinVal(string $field, mixed $value, float $min): void
    {
        if ($value !== null && $value !== '' && (float) $value < $min) {
            $this->addError($field, "The {$field} must be at least {$min}.");
        }
    }

    private function validateMaxVal(string $field, mixed $value, float $max): void
    {
        if ($value !== null && $value !== '' && (float) $value > $max) {
            $this->addError($field, "The {$field} must not exceed {$max}.");
        }
    }

    private function validateIn(string $field, mixed $value, array $allowed): void
    {
        if ($value !== null && $value !== '' && !in_array($value, $allowed, true)) {
            $this->addError($field, "The {$field} must be one of: " . implode(', ', $allowed) . '.');
        }
    }

    private function validateNumeric(string $field, mixed $value): void
    {
        if ($value !== null && $value !== '' && !is_numeric($value)) {
            $this->addError($field, "The {$field} must be a number.");
        }
    }

    private function validateInteger(string $field, mixed $value): void
    {
        if ($value !== null && $value !== '' && filter_var($value, FILTER_VALIDATE_INT) === false) {
            $this->addError($field, "The {$field} must be an integer.");
        }
    }

    private function validateBoolean(string $field, mixed $value): void
    {
        if ($value !== null && $value !== '' && filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === null) {
            $this->addError($field, "The {$field} must be a boolean.");
        }
    }

    private function validateDate(string $field, mixed $value): void
    {
        if ($value !== null && $value !== '' && !\DateTime::createFromFormat('Y-m-d', (string) $value)) {
            $this->addError($field, "The {$field} must be a valid date (YYYY-MM-DD).");
        }
    }

    private function validateUrl(string $field, mixed $value): void
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($field, "The {$field} must be a valid URL.");
        }
    }

    private function validateConfirmed(string $field, mixed $value, array $data): void
    {
        if ($value !== ($data[$field . '_confirmation'] ?? null)) {
            $this->addError($field, "The {$field} confirmation does not match.");
        }
    }

    protected function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    protected function sanitize(array $data): array
    {
        return SanitizerHelper::array($data);
    }
}
