<?php

namespace Pageflow\Core\modules\authorization\model;


use Pageflow\Core\core\model\Entity;

class User extends Entity {

    private string $username;
    private string $emailAddress;
    private string $firstName;
    private string $prefix;
    private string $lastName;
    private ?string $password = null;
    private string $uuid;

    public static function constructFromRecord(array $row): User {
        $user = new User();
        $user->initFromDb($row);
        return $user;
    }

    protected function initFromDb(array $row): void {
        $this->setUsername($row['username']);
        $this->setEmailAddress($row['email_address']);
        $this->setFirstName($row['first_name']);
        $this->setLastName($row['last_name']);
        $this->setPrefix($row['prefix']);
        $this->setUuid($row['uuid']);
        parent::initFromDb($row);
    }

    public function getPassword(): ?string {
        return $this->password;
    }

    public function setPassword(string $password): void {
        $this->password = $password;
    }

    public function getEmailAddress(): string {
        return $this->emailAddress;
    }

    public function setEmailAddress(string $emailAddress): void {
        $this->emailAddress = $emailAddress;
    }

    public function getUuid(): string {
        return $this->uuid;
    }

    public function setUuid(string $uuid): void {
        $this->uuid = $uuid;
    }

    public function getFullName(): string {
        $fullname = $this->getFirstName();
        if ($this->getPrefix()) {
            $fullname = $fullname . ' ' . $this->getPrefix();
        }
        return $fullname . ' ' . $this->getLastName();
    }

    public function getFirstName(): string {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void {
        $this->firstName = $firstName;
    }

    public function getPrefix(): string {
        return $this->prefix;
    }

    public function setPrefix(string $prefix): void {
        $this->prefix = $prefix;
    }

    public function getLastName(): string {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void {
        $this->lastName = $lastName;
    }

    public function isLoggedInUser(): bool {
        return $this->getUsername() == $_SESSION["username"];
    }

    public function getUsername(): string {
        return $this->username;
    }

    public function setUsername(string $username): void {
        $this->username = $username;
    }
}