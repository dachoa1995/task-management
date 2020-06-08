# Laravel＋Vue.jsでタスク管理システムの開発を行う、   
基本的な機能は  
- プロジェクトを作成して、チームを招待。（招待されたユーザーしかアクセスできない）  
- リストでワークフローに適切なステップを作成。  
- 完了すべきタスクをカードとして作成。  
- チームがタスクのカードを開いて期限、コメントなどの詳細を追加。  
- リスト間を移動して進捗を明示。「To Do」から「完了済み」へ瞬時に変更！  
- タスクで担当者をアサインする  
- プロジェクト一覧管理  
- メール通知「招待された・アサインされた場合」  

# 開発環境構築の手順
構築の手順は[Wiki](https://github.com/dachoa1995/task-management/wiki/%E9%96%8B%E7%99%BA%E7%92%B0%E5%A2%83%E6%A7%8B%E7%AF%89%E3%81%AE%E6%89%8B%E9%A0%86)で書いてありますので、参考してください。

# Google+ APIのCredentialsを作成の手順
作成の手順は[wiki](https://github.com/dachoa1995/task-management/wiki/Google--API-Credential)で書いてありますので、参考してください。

# テスト実行
以下のコマンドを実行
```
./vendor/bin/phpunit --testdox
```
