<?php

namespace App;

/**
 * Description of Validator
 * проверяет заполненны ли поля формы, 
 * форма воспринимается как ассоц.массив. И возвращает массив $errors. 
 * Либо пустой - если ошибок нет, либо со значениями - если ошибки есть. 
 * @author User
 */
class CourseValidator implements ValidatorInterface {

    public function validate(array $course) {
        // BEGIN (write your solution here)
        $errors = [];
        if (empty($course['title'])) {
            $errors['title'] = "Can't be blank";
        }
        if (empty($course['paid'])) {
            $errors['paid'] = "Can't be blank";
        }
        return $errors;
        // END
    }

}
