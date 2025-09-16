<?php

namespace Tests\Feature\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class AuthenticatedSessionControllerTest extends TestCase
{
    /**
     * 一般ユーザーログイン機能のバリデーションテスト
     */
    public function test_authenticated_メールアドレスが未入力の場合、指定したバリデーションエラーメッセージが返される(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email'])
            ->assertJsonFragment([
                'email' => ['メールアドレスを入力してください'],
            ]);
    }

    /**
     * 一般ユーザーログイン機能のバリデーションテスト
     */
    public function test_authenticated_パスワードが未入力の場合、指定したバリデーションエラーメッセージが返される(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password'])
            ->assertJsonFragment([
                'password' => ['パスワードを入力してください'],
            ]);
    }

    /**
     * 一般ユーザーログイン機能のバリデーションテスト
     */
    public function test_authenticated_登録内容と一致しない場合、指定したバリデーションエラーメッセージが返される(): void
    {
        // 一般ユーザーアカウント作成
        User::factory()->create([
            'name' => 'test',
            'email' => 'test@example.com',
        ]);
        $this->assertDatabaseHas('users', [
            'name' => 'test',
            'email' => 'test@example.com',
        ]);

        // 登録内容と一致しない場合
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email'])
            ->assertJsonFragment([
                'email' => ['ログイン情報が登録されていません。'],
            ]);
    }
}
