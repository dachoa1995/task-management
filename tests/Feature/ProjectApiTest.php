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
    private $header;
    private $projectAPI = '/api/projects';
    private $assignProjectAPI = '/api/assign_project';
    private $accessProjectAPI = '';

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

        $this->accessProjectAPI = $this->projectAPI . '/' . $this->project->id;

        $this->header = [
            'Authorization' => 'Bearer ' . $this->user->api_token,
            'Accept' => 'application/json',
        ];
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
        $response = $this->json('POST', $this->projectAPI, $new_project, []);
        $response->assertStatus(401);

        //新しいプロジェクトを作成というリクエストを送る
        $response = $this->json('POST', $this->projectAPI, $new_project, $this->header);

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
        //api_tokenがHeaderに含まらないとエラー
        $response = $this->json('GET', $this->accessProjectAPI, [], []);
        $response->assertStatus(401);

        //自分のプロジェクトを取得できるか
        $response = $this->json('GET', $this->accessProjectAPI, [], $this->header);

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
        $response = $this->json('GET', $this->projectAPI . '/300', [], $this->header);
        $response->assertStatus(404);
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
            'name' => 'new name',
            'description' => 'new description',
        ];

        //api_tokenがHeaderに含まらないとエラー
        $response = $this->json('PUT', $this->accessProjectAPI, $query, []);
        $response->assertStatus(401);

        //自分のプロジェクトを編集できるか
        $response = $this->json('PUT', $this->accessProjectAPI, $query, $this->header);

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
        $response = $this->json('PUT', $this->projectAPI . '/300', $query, $this->header);
        $response->assertStatus(404);
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

    public function testIfAssignToProject()
    {
        //他のユーザーテスト作成
        $another_user = factory(User::class)->create();

        //api_tokenがHeaderに含まらないとエラー出るか
        $query = [
            'email' => $another_user->email,
        ];
        $response = $this->json('POST', $this->assignProjectAPI . '/' . $this->project->id, $query, []);
        $response->assertStatus(401);

        //プロジェクトに担当者アサインする
        $response = $this->json('POST', $this->assignProjectAPI . '/' . $this->project->id, $query, $this->header);

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
        $response = $this->json('GET', $this->accessProjectAPI, $query, $header);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => $this->project->name,
                    'description' => $this->project->description
                ]
            ]);

        //アサインされた担当者はプロジェクトを編集できるか
        $query['name'] = 'new name';
        $query['description'] = 'new description';
        $response = $this->json('PUT', $this->accessProjectAPI, $query, $header);

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

        //存在していないプロジェクトに担当者アサインできるか
        $response = $this->json('POST', $this->assignProjectAPI . '/300', $query, $this->header);
        $response->assertStatus(404);
    }

    /*
     * プロジェクト削除の周りのテストです
     * api_tokenがHeaderに含まらないとエラー出るか
     * 存在していないプロジェクトを削除できるか
     * 自分のプロジェクトを削除できるか
     * サーパーから返したレスポンスの形式があっているか
     * プロジェクトはデーターベースで削除されたか
    */
    public function testIfDeleteProject()
    {

        //api_tokenがHeaderに含まらないとエラー出るか
        $response = $this->json('DELETE', $this->accessProjectAPI, [], []);
        $response->assertStatus(401);

        //存在していないプロジェクトを削除できるか
        $response = $this->json('DELETE', $this->projectAPI . '/300', [], $this->header);
        $response->assertStatus(404);

        // 自分のプロジェクトを削除できるか。
        $response = $this->json('DELETE', $this->accessProjectAPI, [], $this->header);

        //サーパーから返したレスポンスの形式があっているか
        $response->assertStatus(204);

        //プロジェクトはデーターベースで削除されたか
        $project = Project::first();
        $this->assertEquals(is_null($project), true);

        $projects_users = ProjectsUsers::first();
        $this->assertEquals(is_null($projects_users), true);
    }

    /*
     * プロジェクト一覧を取得の周りのテストです。
     * api_tokenがHeaderに含まらないとエラー出るか
     * 自分のプロジェクト一覧取得
     */
    public function testIfGetListProject()
    {
        //他のユーザーのプロジェクト作成
        // テストユーザー作成
        $another_user = factory(User::class)->create();

        // テストプロジェクトを作成
        $project = factory(Project::class)->create();

        //プロジェクトとユーザーの関係を作成
        $project_user = new ProjectsUsers();
        $project_user->user_id = $another_user->id;
        $project_user->project_id = $project->id;
        $project_user->save();


        // 自分のテストプロジェクトを作成
        $project2 = factory(Project::class)->create();

        //プロジェクトとユーザーの関係を作成
        $project_user = new ProjectsUsers();
        $project_user->user_id = $this->user->id;
        $project_user->project_id = $project2->id;
        $project_user->save();

        // api_tokenがHeaderに含まらないとエラー出るか
        $response = $this->json('GET', $this->projectAPI, [], []);
        $response->assertStatus(401);

        // 自分のプロジェクト一覧取得
        $response = $this->json('GET', $this->projectAPI, [], $this->header);
        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'id' => $this->project->id,
                        'name' => $this->project->name,
                        'description' => $this->project->description
                    ],
                    [
                        'id' => $project2->id,
                        'name' => $project2->name,
                        'description' => $project2->description
                    ]
                ],
                'links' => [
                    'first' => 'http://localhost/api/projects?page=1',
                    'last' => 'http://localhost/api/projects?page=1',
                    'prev' => null,
                    'next' => null
                ],
                'meta' => [
                    'current_page' => 1,
                    'from' => 1,
                    'last_page' => 1,
                    'path' => 'http://localhost/api/projects',
                    'per_page' => 10
                ]
            ]);
    }

    /*
     * 権限がないアクセスの周りのテストです。
     * 他の人のプロジェクトを取得すれば、エラー出るか
     * 他の人のプロジェクトを編集すれば、エラー出るか
     * 他の人のプロジェクトを削除すれば、エラー出るか
     * 他の人のプロジェクトを担当者アサインすれば、エラー出るか
     */
    public function testIfAccessWithoutAuth()
    {
        //他の人のプロジェクトを取得すれば、エラー出るか
        $another_user = factory(User::class)->create();
        $header = [
            'Authorization' => 'Bearer ' . $another_user->api_token,
            'Accept' => 'application/json',
        ];

        $response = $this->json('GET', $this->accessProjectAPI, [], $header);
        $response->assertStatus(403);

        //他の人のプロジェクトを削除すれば、エラー出るか
        $response = $this->json('DELETE', $this->accessProjectAPI, [], $header);
        $response->assertStatus(403);

        //他の人のプロジェクトを編集すれば、エラー出るか
        $query = [
            'name' => 'new name',
            'description' => 'new description'
        ];
        $response = $this->json('PUT', $this->accessProjectAPI, $query, $header);
        $response->assertStatus(403);

        //他の人のプロジェクトを担当者アサインすれば、エラー出るか
        $query['user_id'] = $this->user->id;
        $response = $this->json('POST', $this->assignProjectAPI . '/' . $this->project->id, $query, $header);
        $response->assertStatus(403);
    }
}