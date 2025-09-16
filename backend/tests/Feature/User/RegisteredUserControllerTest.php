<?php

namespace Tests\Feature\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegisteredUserControllerTest extends TestCase
{
    /**
     * 一般ユーザーアカウント登録機能のバリデーションテスト
     */
    public function test_register_名前が未入力の場合、指定したバリデーションエラーメッセージが返される(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonFragment([
                'name' => ['お名前を入力してください'],
            ]);
    }

    /**
     * 一般ユーザーアカウント登録機能のバリデーションテスト
     */
    public function test_register_メールアドレスが未入力の場合、指定したバリデーションエラーメッセージが返される(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'test',
            'email' => '',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email'])
            ->assertJsonFragment([
                'email' => ['メールアドレスを入力してください'],
            ]);
    }

    /**
     * 一般ユーザーアカウント登録機能のバリデーションテスト
     */
    public function test_register_パスワードが8文字未満の場合、指定したバリデーションエラーメッセージが返される(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => '1234567',
            'password_confirmation' => '1234567',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password'])
            ->assertJsonFragment([
                'password' => ['パスワードは8文字以上で入力してください'],
            ]);
    }

    /**
     * 一般ユーザーアカウント登録機能のバリデーションテスト
     */
    public function test_register_パスワードが一致しない場合、指定したバリデーションエラーメッセージが返される(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password'])
            ->assertJsonFragment([
                'password' => ['パスワードと一致しません'],
            ]);
    }

    /**
     * 一般ユーザーアカウント登録機能のバリデーションテスト
     */
    public function test_register_パスワードが未入力の場合、指定したバリデーションエラーメッセージが返される(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password'])
            ->assertJsonFragment([
                'password' => ['パスワードを入力してください'],
            ]);
    }

    /**
     * 一般ユーザーアカウント登録機能のテスト
     */
    public function test_register_フォームに内容が正しく入力されていた場合、データが正常に保存される(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'name' => 'test',
            'email' => 'test@example.com',
        ]);

        $this->dumpdb();
    }
}
