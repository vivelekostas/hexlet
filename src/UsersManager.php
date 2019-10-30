<?php

namespace App;

/**
 * Description of UsersManager
 * Исполняет всякие манипуляции с users.json 
 * для работы обработчиков и шаблонов
 * @author User
 */
class UsersManager {

    use \Tightenco\Collect\Support\Traits\EnumeratesValues;

    /**
     * Путь к файлу с юзерами
     */
    const PATH_TO_USER = __DIR__ . '/../data/users.json';

    /**
     * Читает содержимое файла в строку
     * @return type str
     */
    protected function readsTheContents() {
        $file = self::PATH_TO_USER;
        return file_get_contents($file);
    }

    /**
     * Декодирует строку JSON в массив
     * @return type array
     */
    protected function turnIntoAnArray($strFromFile) {
        return json_decode($strFromFile, true);
    }

    /**
     * Возвращает массив с юзерами из json файла.
     * @return type array
     */
    public function getUsers() {
        $strFromFile = $this->readsTheContents();
        return $this->turnIntoAnArray($strFromFile);
    }

    /**
     * Ищет необходимого юзера по id в users.json путём создания объекта с 
     * методом firstWhere. Создание объекта не обременяет мой класс излишним 
     * функционалом в отличие от подключения трейта непосредственно.
     * @param int $id
     * @return array
     */
    public function find(int $id): array {
        $usersArray = $this->getUsers();
        $collection = new \Tightenco\Collect\Support\Collection($usersArray);
        return $collection->firstWhere('id', $id);
    }
    
    /**
     * Превращает массив в json строку.
     * @param array $user
     * @return type str
     */
    protected function convertToJson(array $user) {
        return json_encode($user);
    }
    
    /**
     * Записывает данные о новом юзере в файл.
     * Путём многочисленных манипуляций))
     * @param array $user
     */
    public function save(array $user) {
        $usersArray = $this->getUsers(); // Извлекает старые данные.
        $id = $user['id'];  // Извлекает значение id нового или обновлённого юзера.
        // И ищет его в массиве с юзерами. И если находит - то перезаписывает, 
        // а если нет - то создаёт нового юзера с таким id.
        $usersArray[$id] = $user; 
        $json = $this->convertToJson($usersArray); // Обновлённый массив конвертирует в json.
        file_put_contents(self::PATH_TO_USER, $json); // Записывает обновленные данные в файл.
    }
    
    /**
     * Удаляет юзера из файла.
     * @param type $id
     */
    public function destroy($id) {
        $usersArray = $this->getUsers(); // Извлекает старые данные.
        unset($usersArray[$id]); //удаляет по заданному id массив.
        $json = $this->convertToJson($usersArray); // Обновлённый массив конвертирует в json.
        file_put_contents(self::PATH_TO_USER, $json); // Записывает обновленные данные в файл.
    }

}
