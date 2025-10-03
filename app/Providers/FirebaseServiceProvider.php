<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;

class FirebaseServiceProvider
{
    protected $database;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(config('firebase.credentials.file'))
            ->withDatabaseUri(config('firebase.database_url'));

        $this->database = $factory->createDatabase();
    }

    public function getDatabase(): Database
    {
        return $this->database;
    }

    // Métodos auxiliares opcionales
    public function getReference(string $path)
    {
        return $this->database->getReference($path);
    }

    public function set(string $path, $value)
    {
        return $this->database->getReference($path)->set($value);
    }

    public function get(string $path)
    {
        return $this->database->getReference($path)->getValue();
    }
}