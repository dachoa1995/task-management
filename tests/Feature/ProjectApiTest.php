<?php

namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Project;
use App\User;
use App\Projects_users;

class ProjectApiTest extends TestCase
{
    use RefreshDatabase;
    private $user;
    private $header;
    private $new_project;
    private $create_project_response;

    protected function setUp(): void {
        parent::setUp();

        // テストユーザー作成
        $this->user = factory(User::class)->create();

        $this->header = [
            'user_id' => $this->user->id,
        ];

        // テストプロジェクトを作成
        $this->new_project = [
            'name' => 'タスク管理システム',
            'description' => 'laravel + vue.jsで開発を行なっている',
        ];
        $this->create_project_response = $this->json('POST', route('CreateProject'), $this->new_project, $this->header);
    }

    /*
     * 新しいプロジェクトが作成できるか
     */
    public function testIfCreateNewProject() {
        //作成したプロジェクトを保存されたかチェック
        $project = Project::first();
        $this->assertEquals($this->new_project['name'], $project->name);
        $this->assertEquals($this->new_project['description'], $project->description);

        //プロジェクトとユーザーの関係を作成されたか。
        $projects_users = Projects_users::first();
        $this->assertEquals($projects_users['user_id'], $this->user->id);
        $this->assertEquals($projects_users['project_id'], $project->id);

        //サーパーから返したレスポンスの形式があっているか
        $this->create_project_response
            ->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => $project->name,
                    'description' => $project->description
                ],
                'version' => '1.0.0',
                'author_url' => 'https://github.com/dachoa1995'
            ]);

        //ユーザーIDがHeaderに含まらないとエラー
        $response = $this->json('POST', route('CreateProject'), $this->new_project, []);
        $response->assertStatus(401);
    }

    /*
     * 自分のプロジェクトを取得できるか
     * またはプロジェクトを読み込む権限がないと、取得できるのか
     */
    public function testIfAccessProject() {
        //作成したプロジェクトを保存されたかチェック
        $project = Project::first();

        //プロジェクトを取得
        $response = $this->json('GET', 'project/' . $project->id, [], $this->header);

        //サーパーから返したレスポンスの形式があっているか
        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => $project->name,
                    'description' => $project->description
                ],
                'version' => '1.0.0',
                'author_url' => 'https://github.com/dachoa1995'
            ]);

        //存在していないプロジェクトを取得すれば、
        $response = $this->json('GET', 'project/300', [], $this->header);
        $response->assertStatus(404);

        //他の人のプロジェクトを取得すれば、
        $another_user = factory(User::class)->create();
        $header = [
            'user_id' => $another_user->id,
        ];
        $response = $this->json('GET', 'project/' . $project->id, [], $header);
        $response->assertStatus(403);

        //ユーザーIDがHeaderに含まらないとエラー
        $response = $this->json('GET', 'project/' . $project->id, [], []);
        $response->assertStatus(401);
    }
}
