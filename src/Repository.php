<?php

namespace App;

/**
 * Description of Repository
 * Стартует новую сессию, либо возобновляет существующую
 * @author User
 */
class Repository {
    
    /** 
     * Стартует новую сессию, либо возобновляет существующую.     
     */
    public function __construct() {
        session_start();
    }

    /** 
     * Выбирает все значения массива. Массив хранит в себе значения, 
     * которые я ввожу в форму создания нового курса на протяжении сессии.     
     */
    public function all() {
        return array_values($_SESSION);
    }

    /** 
     * Ищет в массиве сессии id по заданному значению? (в моём приложении не используется)      
     */
    public function find(int $id) {
        return $_SESSION[$id];
    }

    /** 
     * Сохраняет данные о новом курсе в массив текущей сессии.    
     */
    public function save(array $item) {
        if (empty($item['title']) || $item['paid'] == '') {
            $json = json_encode($item);
            throw new \Exception("Wrong data: {$json}");
        }
        // Cоздаёт и присваивает уникальный id новому курсу. 
        $item['id'] = uniqid();
        // Сохраняет в массиве сессии новый курс под ключём его же уникального id.
        $_SESSION[$item['id']] = $item;
    }
    
    /**
     * Сохраняет данные о новом курсе в файл.
     * @param type array
     */
    public function saveToFile(array $item) {
        $filename = './templates/coursesList.csv';
        $dataCourse = "Название: " . $item['title'] .
                      ", Стоимость: " . $item['paid'] . 
                      ";" . PHP_EOL;
        file_put_contents($filename, $dataCourse, FILE_APPEND);
    }
        
}
