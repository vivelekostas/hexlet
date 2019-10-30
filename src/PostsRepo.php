<?php

namespace App;

/**
 * Description of PostsRepo
 *
 * @author User
 */
class PostsRepo {

    private $posts;

    public function __construct() {
        $this->posts = PostsGenerator::generate(100);
    }

    public function all() {
        return $this->posts;
    }

    public function find(string $id) {
        return collect($this->posts)->firstWhere('id', $id);
    }

}
