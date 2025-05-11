<?php

namespace App\Models;

class User extends Model {
    protected static string $table = 'users';
    
    protected array $fillable = [
        'id',
        'username',
        'email',
        'password',
        'role',
        'last_login'
    ];
    
    protected array $relationships = [
        'staff' => Staff::class
    ];

    public function getUsername(): string {
        return $this->getAttribute('username');
    }

    public function setUsername(string $username): void {
        $this->setAttribute('username', $username);
        $this->save();
    }

    public function getEmail(): string {
        return $this->getAttribute('email');
    }

    public function setEmail(string $email): void {
        $this->setAttribute('email', $email);
        $this->save();
    }

    public function getPassword(): string {
        return $this->getAttribute('password');
    }

    public function setPassword(string $password): void {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $this->setAttribute('password', $hashedPassword);
        $this->save();
    }

    public function getRole(): string {
        return $this->getAttribute('role');
    }

    public function setRole(string $role): void {
        $this->setAttribute('role', $role);
        $this->save();
    }

    public function getLastLogin(): ?string {
        return $this->getAttribute('last_login');
    }

    public function setLastLogin(string $lastLogin): void {
        $this->setAttribute('last_login', $lastLogin);
        $this->save();
    }

    public function isAdmin(): bool {
        return $this->getAttribute('role') === 'ADMIN';
    }

    public function isStaff(): bool {
        return $this->getAttribute('role') === 'STAFF';
    }

    public function isVisitor(): bool {
        return $this->getAttribute('role') === 'VISITOR';
    }

    public function getStaff(): ?Staff {
        return Staff::where('user_id', '=', $this->getAttribute('id'))->first();
    }

    public function verifyPassword(string $password): bool {
        return password_verify($password, $this->getAttribute('password'));
    }

    public static function findByUsername(string $username): ?self {
        return self::where('username', '=', $username)->first();
    }

    public static function findByEmail(string $email): ?self {
        return self::where('email', '=', $email)->first();
    }

    public static function findByRole(string $role): array {
        return self::where('role', '=', $role)->get();
    }
} 