<?php


namespace App;

/**
 * Description of UserValidator
 * проверяет заполненны ли поля формы, 
 * форма воспринимается как ассоц.массив. И возвращает массив $errors. 
 * Либо пустой - если ошибок нет, либо со значениями - если ошибки есть. 
 * @author User
 */
class UserValidator implements ValidatorInterface {
    public function validate(array $user) {
        // BEGIN (write your solution here)
        $errors = [];
        if (empty($user['id'])) {
            $errors['id'] = "Can't be blank";
        }
        if (empty($user['firstName'])) {
            $errors['firstName'] = "Can't be blank";
        }
        if (empty($user['lastName'])) {
            $errors['lastName'] = "Can't be blank";
        }
        if (empty($user['email'])) {
            $errors['email'] = "Can't be blank";
        }
 
        return $errors;
        // END
    }
}
