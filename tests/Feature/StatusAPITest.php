<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Project;
use App\User;
use App\ProjectsUsers;
use App\Status;

class StatusAPITest extends TestCase
{
    use RefreshDatabase;
    private $user;
    private $project;
    private $status;

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

        //ワークフローを作成
        $this->status = new Status();
        $this->status->project_id = $this->project->id;
        $this->status->name = 'TODO';
        $this->status->order = 1;
        $this->status->save();
    }

    /*
     * ワークフローを作成というAPIの周りのテストです。
     * api_tokenがHeaderに含まらないとエラー
     * 作成したワークフローを保存されたかチェック
     * サーパーから返したレスポンスの形式があっているか
     * 存在していないプロジェクトでワークフロー作成すれば、エラー
     */
    public function testIfCreateStatus() {

        //api_tokenがHeaderに含まらないとエラー
        $header = [];
        $response = $this->json('POST', '/api/status', [], $header);
        $response->assertStatus(401);

        $header = [
            'Authorization' => 'Bearer ' . $this->user->api_token,
            'Accept' => 'application/json',
        ];

        //存在していないプロジェクトでワークフロー作成すれば、エラー
        $status_data = [
            'project_id' => '300',
            'name' => '開発中',
            'order' => 2,
        ];
        $response = $this->json('POST', '/api/status', $status_data, $header);
        $response->assertStatus(403);

        // テストプロジェクトワークフローを作成
        $status_data['project_id'] = $this->project->id;

        //ワークフローを作成いうリクエストを送る
        $response = $this->json('POST', '/api/status', $status_data, $header);

        //作成したワークフローを保存されたかチェック
        $status = Status::all()->last();
        $this->assertEquals($status_data['project_id'], $status->project_id);
        $this->assertEquals($status_data['name'], $status->name);
        $this->assertEquals($status_data['order'], $status->order);

        //サーパーから返したレスポンスの形式があっているか
        $response
            ->assertStatus(201)
            ->assertJson([
                'data' => [
                    'project_id' => $status->project_id,
                    'name' => $status->name,
                    'order' => $status->order
                ],
                'version' => '1.0.0',
                'author_url' => 'https://github.com/dachoa1995'
            ]);
    }

    /*
     * プロジェクトのワークフローを取得の周りのテストです。
     * api_tokenがHeaderに含まらないとエラー
     * 自分のプロジェクトのワークフローを取得できるか
     * サーパーから返したレスポンスの形式があっているか
     * 存在していないプロジェクトでワークフローを取得すれば、エラー出るか
     * 存在していないワークフローを取得すれば、エラー出るか
    */
    public function testIfAccessStatus() {
        $query = [
            'project_id' => $this->project->id,
            'status_id' => $this->status->id,
        ];
        //api_tokenがHeaderに含まらないとエラー
        $header = [];
        $response = $this->json('GET', '/api/status', $query, $header);
        $response->assertStatus(401);

        //自分のプロジェクトのワークフローを取得できるか
        $header = [
            'Authorization' => 'Bearer ' . $this->user->api_token,
            'Accept' => 'application/json',
        ];
        $response = $this->json('GET', '/api/status', $query, $header);

        //サーパーから返したレスポンスの形式があっているか
        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'project_id' => $this->project->id,
                    'name' => $this->status->name,
                    'order' => $this->status->order
                ],
                'version' => '1.0.0',
                'author_url' => 'https://github.com/dachoa1995'
            ]);

        //存在していないプロジェクトでワークフローを取得すれば、エラー出るか
        $query['project_id'] = 300;
        $response = $this->json('GET', '/api/status', $query, $header);
        $response->assertStatus(403);

        //存在していないワークフローを取得すれば、エラー出るか
        $query['project_id'] = $this->project->id;
        $query['status_id'] = 300;
        $response = $this->json('GET', '/api/status', $query, $header);
        $response->assertStatus(404);
    }

    /*
     * プロジェクトでのワークフローを修正の周りのテストです。
     * api_tokenがHeaderに含まらないとエラー
     * 自分のプロジェクトでのワークフローを編集できるか
     * サーパーから返したレスポンスの形式があっているか
     * プロジェクトでのワークフローは編集して、保存されたか
     * 存在していないプロジェクトでのワークフローを編集すれば、エラー出るか
     * プロジェクトで存在していないワークフローを編集すれば、エラー出るか
    */
    public function testIfChangeStatus() {
        $query = [
            'project_id' => $this->project->id,
            'status_id' => $this->status->id,
            'name' => 'new name',
            'order' => 'new order'
        ];
        //api_tokenがHeaderに含まらないとエラー
        $header = [];
        $response = $this->json('PUT', '/api/status', $query, $header);
        $response->assertStatus(401);

        //自分のプロジェクトでのワークフローを編集できるか
        $header = [
            'Authorization' => 'Bearer ' . $this->user->api_token,
            'Accept' => 'application/json',
        ];
        $response = $this->json('PUT', '/api/status', $query, $header);

        //サーパーから返したレスポンスの形式があっているか
        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'project_id' => $this->project->id,
                    'name' => $query['name'],
                    'order' => $query['order']
                ],
                'version' => '1.0.0',
                'author_url' => 'https://github.com/dachoa1995'
            ]);

        //プロジェクトでのワークフローは編集して、保存されたか
        $status = Status::all()->last();
        $this->assertEquals($query['project_id'], $status->project_id);
        $this->assertEquals($query['name'], $status->name);
        $this->assertEquals($query['order'], $status->order);

        //存在していないプロジェクトでのワークフローを編集すれば、エラー出るか
        $query['project_id'] = 300;
        $response = $this->json('PUT', '/api/status', $query, $header);
        $response->assertStatus(403);

        //プロジェクトで存在していないワークフローを編集すれば、エラー出るか
        $query['project_id'] = $this->project->id;
        $query['status_id'] = 300;
        $response = $this->json('PUT', '/api/status', $query, $header);
        $response->assertStatus(404);
    }

    /*
     * プロジェクト削除の周りのテストです
     * api_tokenがHeaderに含まらないとエラー出るか
     * 存在していないプロジェクトでのワークフローを削除すれば、エラー出るか
     * プロジェクトで存在していないワークフローを削除すれば、エラー出るか
     * 自分のプロジェクトを削除できるか
     * サーパーから返したレスポンスの形式があっているか
     * プロジェクトはデーターベースで削除されたか
    */
    public function testIfDeleteStatus() {

        //api_tokenがHeaderに含まらないとエラー出るか
        $header = [];
        $response = $this->json('DELETE', '/api/status', [], $header);
        $response->assertStatus(401);

        //存在していないプロジェクトでのワークフローを削除すれば、エラー出るか
        $header = [
            'Authorization' => 'Bearer ' . $this->user->api_token,
            'Accept' => 'application/json',
        ];
        $query = [
            'project_id' => 300,
            'status_id' => $this->status->id,
        ];
        $response = $this->json('DELETE', '/api/status', $query, $header);
        $response->assertStatus(403);

        //プロジェクトで存在していないワークフローを削除すれば、エラー出るか
        $query['project_id'] = $this->project->id;
        $query['status_id'] = 300;
        $response = $this->json('DELETE', '/api/status', $query, $header);
        $response->assertStatus(404);

        //自分のプロジェクトを削除できるか
        $query['status_id'] = $this->status->id;

        $response = $this->json('DELETE', '/api/status', $query, $header);

        //サーパーから返したレスポンスの形式があっているか
        $response->assertStatus(204);

        //プロジェクトはデーターベースで削除されたか
        $status = Status::first();
        $this->assertEquals(is_null($status), true);
    }

    /*
     * プロジェクトでのワークフロー一覧を取得の周りのテストです。
     * api_tokenがHeaderに含まらないとエラー出るか
     * 存在していないプロジェクトでのワークフロー一覧を取得すれば、エラー出るか
     * 自分のプロジェクトでのワークフロー一覧を取得できるか
     */
    public function testIfGetListStatus() {
        //api_tokenがHeaderに含まらないとエラー出るか
        $header = [];
        $response = $this->json('GET', '/api/status_list', [], $header);
        $response->assertStatus(401);

        //存在していないプロジェクトでのワークフロー一覧を取得すれば、エラー出るか
        $query = [
            'project_id' => 300,
        ];
        $header = [
            'Authorization' => 'Bearer ' . $this->user->api_token,
            'Accept' => 'application/json',
        ];
        $response = $this->json('GET', '/api/status_list', $query, $header);
        $response->assertStatus(403);

        //もう一つのワークフローを作成
        $status2 = new Status();
        $status2->project_id = $this->project->id;
        $status2->name = '開発中';
        $status2->order = 2;
        $status2->save();

        //自分のプロジェクトでのワークフロー一覧を取得できるか
        $query['project_id'] = $this->project->id;

        $response = $this->json('GET', '/api/status_list', $query, $header);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'id' => $this->status->id,
                        'project_id' => $this->project->id,
                        'name' => $this->status->name,
                        'order' => $this->status->order
                    ],
                    [
                        'id' => $status2->id,
                        'project_id' => $this->project->id,
                        'name' => $status2->name,
                        'order' => $status2->order
                    ]
                ],
                'links' => [
                    'first' => 'http://localhost/api/status_list?page=1',
                    'last' => 'http://localhost/api/status_list?page=1',
                    'prev' => null,
                    'next' => null
                ],
                'meta' => [
                    'current_page' => 1,
                    'from' => 1,
                    'last_page' => 1,
                    'path' => 'http://localhost/api/status_list',
                    'per_page' => 10
                ]
            ]);
    }

    /*
     * 権限がないアクセスの周りのテストです。
     * 他の人のプロジェクトでワークフローを作成できるか
     * 他の人のプロジェクトのワークフローを取得できるか
     * 他の人のプロジェクトのワークフローを編集できるか
     */
    public function testIfAccessWithoutAuth()
    {
        //他の人のプロジェクトでワークフローを作成できるか
        $another_user = factory(User::class)->create();
        $header = [
            'Authorization' => 'Bearer ' . $another_user->api_token,
            'Accept' => 'application/json',
        ];
        $status_data = [
            'project_id' => $this->project->id,
            'name' => '開発中',
            'order' => 2,
        ];
        $response = $this->json('POST', '/api/status', $status_data, $header);
        $response->assertStatus(403);

        //他の人のプロジェクトのワークフローを取得できるか
        $query = [
            'project_id' => $this->project->id,
            'status_id' => $this->status->id,
        ];
        $response = $this->json('GET', '/api/status', $query, $header);
        $response->assertStatus(403);

        //他の人のプロジェクトのワークフローを編集できるか
        $query['name'] = 'new name';
        $query['order'] = 'new order';
        $response = $this->json('PUT', '/api/status', $query, $header);
        $response->assertStatus(403);

        //他の人のプロジェクトのワークフローを削除できるか
        $response = $this->json('DELETE', '/api/status', $query, $header);
        $response->assertStatus(403);
    }
}
