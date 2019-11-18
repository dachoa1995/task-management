<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Project;
use App\User;
use App\ProjectsUsers;

class ProjectApiTest extends TestCase
{
    use RefreshDatabase;
    private $user;
    private $project;

    protected function setUp(): void
    {
        parent::setUp();

        // テストユーザー作成
        $this->user = factory(User::class)->create();

        // テストプロジェクトを作成
        $this->project = factory(Project::class)->create();

        //プロジェクトとユーザーの関係を作成
        $project_user = new ProjectsUsers();
        $project_user->user_id = $this->user->id;
        $project_user->project_id = $this->project->id;
        $project_user->save();
    }

    /*
     * 新しいプロジェクトを作成の周りのテストです。
     * api_tokenがHeaderに含まらないとエラー
     * 作成したプロジェクトを保存されたかチェック
     * プロジェクトとユーザーの関係を作成されたか。
     * サーパーから返したレスポンスの形式があっているか
     */
    public function testIfCreateNewProject()
    {
        // テストプロジェクトを作成
        $new_project = [
            'name' => 'タスク管理システム',
            'description' => 'laravel + vue.jsで開発を行なっている',
        ];

        //api_tokenがHeaderに含まらないとエラー
        $header = [];
        $response = $this->json('POST', '/api/project', $new_project, $header);
        $response->assertStatus(401);

        //新しいプロジェクトを作成というリクエストを送る
        $header = [
            'Authorization' => 'Bearer ' . $this->user->api_token,
            'Accept' => 'application/json',
        ];

        $response = $this->json('POST', '/api/project', $new_project, $header);

        //作成したプロジェクトを保存されたかチェック
        $project = Project::all()->last();
        $this->assertEquals($new_project['name'], $project->name);
        $this->assertEquals($new_project['description'], $project->description);

        //プロジェクトとユーザーの関係を作成されたか。
        $projects_users = ProjectsUsers::all()->last();
        $this->assertEquals($projects_users['user_id'], $this->user->id);
        $this->assertEquals($projects_users['project_id'], $project->id);

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

    /*
     * プロジェクトを取得の周りのテストです。
     * api_tokenがHeaderに含まらないとエラー
     * 自分のプロジェクトを取得できるか
     * サーパーから返したレスポンスの形式があっているか
     * 存在していないプロジェクトを取得すれば、エラー出るか
    */
    public function testIfAccessProject()
    {
        $header = [];
        //api_tokenがHeaderに含まらないとエラー
        $response = $this->json('GET', '/api/project/' . $this->project->id, [], $header);
        $response->assertStatus(401);

        //自分のプロジェクトを取得できるか
        $header = [
            'Authorization' => 'Bearer ' . $this->user->api_token,
            'Accept' => 'application/json',
        ];
        $response = $this->json('GET', '/api/project/' . $this->project->id, [], $header);

        //サーパーから返したレスポンスの形式があっているか
        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => $this->project->name,
                    'description' => $this->project->description
                ],
                'version' => '1.0.0',
                'author_url' => 'https://github.com/dachoa1995'
            ]);

        //存在していないプロジェクトを取得すれば、エラー出るか
        $response = $this->json('GET', '/api/project/300', [], $header);
        $response->assertStatus(403);
    }

    /*
     * 権限がないアクセスの周りのテストです。
     * 他の人のプロジェクトを取得すれば、エラー出るか
     * 他の人のプロジェクトを編集すれば、エラー出るか
     * 他の人のプロジェクトを削除すれば、エラー出るか
     * 他の人のプロジェクトを担当者アサインすれば、エラー出るか
     */
    public function testIfAccessWithoutAuth() {
        //他の人のプロジェクトを取得すれば、エラー出るか
        $another_user = factory(User::class)->create();
        $header = [
            'Authorization' => 'Bearer ' . $another_user->api_token,
            'Accept' => 'application/json',
        ];
        $response = $this->json('GET', '/api/project/' . $this->project->id, [], $header);
        $response->assertStatus(403);

        //他の人のプロジェクトを編集すれば、エラー出るか
        $query = [
            'project_id' => $this->project->id,
            'name' => 'new name',
            'description' => 'new description',
        ];
        $response = $this->json('PUT', '/api/project', $query, $header);
        $response->assertStatus(403);

        //他の人のプロジェクトを削除すれば、エラー出るか
        $response = $this->json('DELETE', '/api/project/' . $this->project->id, [], $header);
        $response->assertStatus(403);

        /*
         * 他の人のプロジェクトを担当者アサインすれば、エラー出るか
         */
        $query = [
            'user_id' => $this->user->id,
            'project_id' => $this->project->id
        ];
        $response = $this->json('POST', '/api/assign_project', $query, $header);
        $response->assertStatus(403);
    }

    /*
     * プロジェクトを修正の周りのテストです。
     * api_tokenがHeaderに含まらないとエラー
     * 自分のプロジェクトを編集できるか
     * サーパーから返したレスポンスの形式があっているか
     * プロジェクトは編集して、保存されたか
     * 存在していないプロジェクトを編集すれば、エラー出るか
    */
    public function testIfChangeProject()
    {
        $query = [
            'project_id' => $this->project->id,
            'name' => 'new name',
            'description' => 'new description',
        ];

        //api_tokenがHeaderに含まらないとエラー
        $response = $this->json('PUT', '/api/project', $query, []);
        $response->assertStatus(401);

        //自分のプロジェクトを編集できるか
        $header = [
            'Authorization' => 'Bearer ' . $this->user->api_token,
            'Accept' => 'application/json',
        ];
        $response = $this->json('PUT', '/api/project', $query, $header);

        //サーパーから返したレスポンスの形式があっているか
        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => $query['name'],
                    'description' => $query['description']
                ],
                'version' => '1.0.0',
                'author_url' => 'https://github.com/dachoa1995'
            ]);

        //プロジェクトは編集して、保存されたか
        $project = Project::first();
        $this->assertEquals($query['name'], $project->name);
        $this->assertEquals($query['description'], $project->description);

        //存在していないプロジェクトを編集すれば、エラー出るか
        $strange_query = [
            'project_id' => 300,
            'name' => 'new name',
            'description' => 'new description',
        ];

        $response = $this->json('PUT', '/api/project', $strange_query, $header);
        $response->assertStatus(403);
    }

    /*
     * 自分のプロジェクトに担当者アサインの周りのテストです
     * api_tokenがHeaderに含まらないとエラー出るか
     * サーパーから返したレスポンスの形式があっているか
     * プロジェクトとユーザーの関係を作成されたか
     * アサインされた担当者はプロジェクトをアクセスできるか
     * アサインされた担当者はプロジェクトを編集できるか
     * 存在していないプロジェクトに担当者アサインできるか
     */

    public function testIfAssignToProject() {
        //他のユーザーテスト作成
        $another_user = factory(User::class)->create();

        //api_tokenがHeaderに含まらないとエラー出るか
        $header = [];
        $query = [
            'user_id' => $another_user->id,
            'project_id' => $this->project->id
        ];
        $response = $this->json('POST', '/api/assign_project', $query, $header);
        $response->assertStatus(401);

        //プロジェクトに担当者アサインする
        $header = [
            'Authorization' => 'Bearer ' . $this->user->api_token,
            'Accept' => 'application/json',
        ];
        $response = $this->json('POST', '/api/assign_project', $query, $header);

        //サーパーから返したレスポンスの形式があっているか
        $response->assertStatus(204);

        //プロジェクトとユーザーの関係を作成されたか
        $projects_users = ProjectsUsers::all()->last();
        $this->assertEquals($projects_users['user_id'], $another_user->id);
        $this->assertEquals($projects_users['project_id'], $this->project->id);

        //アサインされた担当者はプロジェクトを取得できるか
        $header = [
            'Authorization' => 'Bearer ' . $another_user->api_token,
            'Accept' => 'application/json',
        ];
        $response = $this->json('GET', '/api/project/' . $this->project->id, [], $header);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => $this->project->name,
                    'description' => $this->project->description
                ],
                'version' => '1.0.0',
                'author_url' => 'https://github.com/dachoa1995'
            ]);

        //アサインされた担当者はプロジェクトを編集できるか
        $query = [
            'project_id' => $this->project->id,
            'name' => 'new name',
            'description' => 'new description',
        ];
        $response = $this->json('PUT', '/api/project', $query, $header);

        $response
            ->assertStatus(200)
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
    }

    /*
     * プロジェクト削除の周りのテストです
     * 存在していないプロジェクトを削除できるか
     * 自分のプロジェクトを削除できるか
     * サーパーから返したレスポンスの形式があっているか
     * プロジェクトはデーターベースで削除されたか
    */
    public function testIfDeleteProject() {
        $header = [
            'Authorization' => 'Bearer ' . $this->user->api_token,
            'Accept' => 'application/json',
        ];

        //存在していないプロジェクトを削除できるか
        $response = $this->json('DELETE', '/api/project/300', [], $header);
        $response->assertStatus(403);

        // 自分のプロジェクトを削除できるか。
        $response = $this->json('DELETE', '/api/project/' . $this->project->id, [], $header);

        //サーパーから返したレスポンスの形式があっているか
        $response->assertStatus(204);

        //プロジェクトはデーターベースで削除されたか
        $project = Project::first();
        $this->assertEquals(is_null($project), true);

        $projects_users = ProjectsUsers::first();
        $this->assertEquals(is_null($projects_users), true);
    }
}