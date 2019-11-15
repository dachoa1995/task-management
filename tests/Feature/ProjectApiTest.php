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

    protected function setUp(): void
    {
        parent::setUp();

        // テストユーザー作成
        $this->user = factory(User::class)->create();

        $this->header = [
            'user_id' => $this->user->google_id,
        ];

        // テストプロジェクトを作成
        $this->new_project = [
            'name' => 'タスク管理システム',
            'description' => 'laravel + vue.jsで開発を行なっている',
        ];
        $this->create_project_response = $this->json('POST', route('CreateProject'), $this->new_project, $this->header);
    }

    /*
     * 新しいプロジェクトを作成の周りのテストです。
     * 作成したプロジェクトを保存されたかチェック
     * プロジェクトとユーザーの関係を作成されたか。
     * サーパーから返したレスポンスの形式があっているか
     * ユーザーIDがHeaderに含まらないとエラー出るか
     */
    public function testIfCreateNewProject()
    {
        //作成したプロジェクトを保存されたかチェック
        $project = Project::first();
        $this->assertEquals($this->new_project['name'], $project->name);
        $this->assertEquals($this->new_project['description'], $project->description);

        //プロジェクトとユーザーの関係を作成されたか。
        $projects_users = Projects_users::first();
        $this->assertEquals($projects_users['user_id'], $this->user->google_id);
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
     * プロジェクトを取得の周りのテストです。
     * 自分のプロジェクトを取得できるか
     * サーパーから返したレスポンスの形式があっているか
     * 存在していないプロジェクトを取得すれば、エラー出るか
     * 他の人のプロジェクトを取得すれば、エラー出るか
     * ユーザーIDがHeaderに含まらないとエラー出るか
     */
    public function testIfAccessProject()
    {
        $project = Project::first();

        //自分のプロジェクトを取得できるか
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

        //存在していないプロジェクトを取得すれば、エラー出るか
        $response = $this->json('GET', 'project/300', [], $this->header);
        $response->assertStatus(404);

        //他の人のプロジェクトを取得すれば、エラー出るか
        $another_user = factory(User::class)->create();
        $header = [
            'user_id' => $another_user->google_id,
        ];
        $response = $this->json('GET', 'project/' . $project->id, [], $header);
        $response->assertStatus(403);

        //ユーザーIDがHeaderに含まらないとエラー出るか
        $response = $this->json('GET', 'project/' . $project->id, [], []);
        $response->assertStatus(401);
    }

    /*
     * プロジェクトを修正の周りのテストです。
     * 自分のプロジェクトを編集できるか
     * サーパーから返したレスポンスの形式があっているか
     * プロジェクトは編集されたか
     * 存在していないプロジェクトを編集すれば、エラー出るか
     * 他の人のプロジェクトを編集すれば、エラー出るか
     * ユーザーIDがHeaderに含まらないとエラー出るか
     */
    public function testIfChangeProject()
    {
        $project = Project::first();

        //自分のプロジェクトを編集できるか
        $query = [
            'project_id' => $project->id,
            'name' => 'new name',
            'description' => 'new description',
        ];
        $response = $this->json('PUT', 'project', [], $this->header);

        //サーパーから返したレスポンスの形式があっているか
        $response
            ->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => $query['name'],
                    'description' => $query['description']
                ],
                'version' => '1.0.0',
                'author_url' => 'https://github.com/dachoa1995'
            ]);

        //プロジェクトは編集されたか
        $project = Project::first();
        $this->assertEquals($query['name'], $project->name);
        $this->assertEquals($query['description'], $project->description);

        //存在していないプロジェクトを編集すれば、エラー出るか
        $strange_query = [
            'project_id' => '300',
            'name' => 'new name',
            'description' => 'new description',
        ];

        $response = $this->json('PUT', 'project', $strange_query, $this->header);
        $response->assertStatus(404);

        //他の人のプロジェクトを編集すれば、エラー出るか
        $another_user = factory(User::class)->create();
        $header = [
            'user_id' => $another_user->google_id,
        ];
        $response = $this->json('PUT', 'project', $query, $header);
        $response->assertStatus(403);

        //ユーザーIDがHeaderに含まらないとエラー出るか
        $response = $this->json('PUT', 'project', $query, []);
        $response->assertStatus(401);
    }

    /*
     * 自分のプロジェクトに担当者アサインの周りのテストです
     * サーパーから返したレスポンスの形式があっているか
     * プロジェクトとユーザーの関係を作成されたか
     * アサインされた担当者はプロジェクトをアクセスできるか
     * アサインされた担当者はプロジェクトを編集できるか
     * ユーザーIDがHeaderに含まらないとエラー出るか
     * 存在していないプロジェクトに担当者アサインできるか
     */
    public function testIfAssignToProject() {
        $project = Project::first();
        //他のユーザーテスト作成
        $another_user = factory(User::class)->create();

        //プロジェクトに担当者アサインする
        $header = [
            'user_id' => $this->user->google_id,
            'assign_to_user_id' => $another_user->google_id
        ];
        $response = $this->json('GET', 'assign_project/' . $project->id, [], $header);

        //サーパーから返したレスポンスの形式があっているか
        $response
            ->assertStatus(201)
            ->assertJson([
                'data' => [],
                'version' => '1.0.0',
                'author_url' => 'https://github.com/dachoa1995'
            ]);

        //存在していないプロジェクトに担当者アサインできるか
        $response = $this->json('GET', 'assign_project/300', [], $header);
        $response->assertStatus(404);

        //プロジェクトとユーザーの関係を作成されたか
        $projects_users = Projects_users::first();
        $this->assertEquals($projects_users['user_id'], $another_user->google_id);
        $this->assertEquals($projects_users['project_id'], $project->id);

        //アサインされた担当者はプロジェクトを取得できるか
        $header = [
            'user_id' => $another_user->google_id,
        ];
        $response = $this->json('GET', 'project/' . $project->id, [], $header);

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

        //アサインされた担当者はプロジェクトを編集できるか
        $query = [
            'project_id' => $project->id,
            'name' => 'new name',
            'description' => 'new description',
        ];
        $response = $this->json('PUT', 'project', $query, $header);

        $response
            ->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => $query['name'],
                    'description' => $query['description']
                ],
                'version' => '1.0.0',
                'author_url' => 'https://github.com/dachoa1995'
            ]);

        $project = Project::first();
        $this->assertEquals($query['name'], $project->name);
        $this->assertEquals($query['description'], $project->description);

        //ユーザーIDがHeaderに含まらないとエラー出るか
        $response = $this->json('GET', 'assign_project/' . $project->id, [], []);
        $response->assertStatus(401);
    }

    /*
     * プロジェクト削除の周りのテストです
     * 権限がないユーザーが削除できるか
     * 存在していないプロジェクトを削除できるか
     * 自分のプロジェクトを削除できるか
     * サーパーから返したレスポンスの形式があっているか
     * プロジェクトはデーターベースで削除されたか
     */
    public function testIfDeleteProject() {
        $project = Project::first();
        //他のユーザーテスト作成
        $another_user = factory(User::class)->create();

        //権限がないユーザーが削除できるか
        $header = [
            'user_id' => $another_user->google_id,
        ];
        $response = $this->json('DELETE', 'project/' . $project->id, [], $header);
        $response->assertStatus(403);

        //存在していないプロジェクトを削除できるか
        $response = $this->json('DELETE', 'project/300', [], $this->header);
        $response->assertStatus(404);

        // 自分のプロジェクトを削除できるか。
        $response = $this->json('DELETE', 'project/' . $project->id, [], $this->header);

        //サーパーから返したレスポンスの形式があっているか
        $response
            ->assertStatus(201)
            ->assertJson([
                'data' => [],
                'version' => '1.0.0',
                'author_url' => 'https://github.com/dachoa1995'
            ]);

        //プロジェクトはデーターベースで削除されたか
        $project = Project::first();
        $this->assertEquals($project, null);

        $projects_users = Projects_users::first();
        $this->assertEquals($projects_users, null);
    }
}