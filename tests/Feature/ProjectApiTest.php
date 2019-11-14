<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Project;
use App\User;

class ProjectApiTest extends TestCase
{
    use RefreshDatabase;
    //protected $user;
    protected function setUp(): void {
        parent::setUp();

        // テストユーザー作成
        $this->user = factory(User::class)->create();
    }

    /*
     * 新しいプロジェクトが作成できるか
     */
    public function testIfCreateNewProject() {
        //新しいプロジェクトを作成
        $new_project = [
            'user_id' => $this->user->id,
            'name' => 'タスク管理システム',
            'description' => 'laravel + vue.jsで開発を行なっている',
        ];
        $response = $this->json('POST', route('CreateProject'), $new_project);

        //作成したプロジェクトを保存されたかチェック
        $project = Project::first();
        $this->assertEquals($new_project['name'], $project->name);
        $this->assertEquals($new_project['description'], $project->description);

        //サーパーから返したレスポンスの形式があっているか
        $response
            ->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => $project->name,
                    'description' => $project->description
                ],
                'version' => '1.0.0',
                'author_url' => 'https://github.com/dachoa1995'
            ]);
    }
}
