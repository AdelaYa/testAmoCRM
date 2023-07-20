<?php

namespace Classes;


class LeadForm
{
    private array $errors = [];

    public function validate($data): bool
    {
        if (empty($data['user_name']) || empty($data['user_phone']) || empty($data['user_email']) || empty($data['lead_price'])) {
            $this->errors[] = "Пожалуйста, заполните все поля!";
            return false;
        }
        $regexp = '/^\s?(\+\s?7|8)([- ()]*\d){10}$/';
        if (!preg_match($regexp, $data['user_phone'])) {
            $this->errors[] = "Введите корректный номер телефона!";
            return false;
        }
        if (!is_numeric($data['lead_price'])) {
            $this->errors[] = "Введите корректную цену!";
            return false;
        }
        return true;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

}