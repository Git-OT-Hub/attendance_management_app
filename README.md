# 勤怠管理アプリ

## ご報告
時間が足りず、テストコードの実装は途中までとなっております。  
申し訳ございません。  
また、フロントエンド、バックエンドを分離させて実装しているため、分離している状態でも確認できる内容をバックエンド側でテストしております。

## 環境構築(上から順番にお願いします)
### リモートリポジトリからソースコードを取得
```
git clone git@github.com:Git-OT-Hub/attendance_management_app.git
```

### Laravel環境構築(その1)
1. `backend/.env` を作成し、環境変数を設定（下記をコピーして貼り付けてください）
``` text
APP_NAME=Attendance-Management-App
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

SESSION_DOMAIN=localhost
SANCTUM_STATEFUL_DOMAINS=localhost:3000

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="attendance-management-app@example.com"
MAIL_FROM_NAME="${APP_NAME}"

REDIRECT_URL_AFTER_EMAIL_AUTHENTICATION=http://localhost:3000/attendance

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_APP_NAME="${APP_NAME}"
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

### Dockerビルド
```
docker compose up -d --build
```

※ MySQL, phpMyAdmin, mailhog は、OSによって起動しない場合があるため、それぞれのPCに合わせて docker-compose.yml ファイルを編集してください。

> *MacのM1・M2チップのPCの場合、エラーメッセージが表示されビルドができないことがあります。
エラーが発生する場合は、docker-compose.ymlファイルの「mysql」「phpmyadmin」「mailhog」内に「platform」の項目を追加で記載してください*
```
例）
mysql:
    image: "mysql:8.0"
    platform: linux/amd64
```

### Laravel環境構築(その2)
1. backendコンテナに入る
```
(docker-compose.yml と同じ階層で実行し backendコンテナに入る)
docker compose exec backend bash
```
2. 依存関係のライブラリをインストール
```
composer install
```
3. アプリケーションキーの作成
```
php artisan key:generate
```
4. マイグレーションの実行
```
php artisan migrate
```
5. シーディングファイルの編集  
勤怠データ作成にあたり、勤怠の期間を変更したい場合は、下記ファイルを編集してください。
現在、「2025/7/1 〜 2025/9/15」までの勤怠データを作成する設定にしています。
変更方法は、下記ファイル内に記載しております。
```
backend/database/seeders/AttendancesTableSeeder.php
```
6. シーディングの実行
```
php artisan db:seed
```
7. backendコンテナから抜ける

### Laravelテスト環境構築
1. テスト用のDB作成
```
1. docker-compose.yml と同じ階層で実行し mysqlコンテナに入る
docker compose exec mysql bash

2. ログインする
mysql -u root -p
パスワード：root

3. テスト用のDB作成
CREATE DATABASE laravel_test;

4. DBが作成されたか確認
SHOW DATABASES;
laravel_test があることを確認

5. mysqlコンテナから出る
```
2. テスト用の .envファイル作成  
`backend/.env.testing` を作成し、環境変数を設定（下記をコピーして貼り付けてください）
```
APP_NAME=Attendance-Management-App
APP_ENV=testing
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_test
DB_USERNAME=root
DB_PASSWORD=root

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

SESSION_DOMAIN=localhost
SANCTUM_STATEFUL_DOMAINS=localhost:3000

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="attendance-management-app@example.com"
MAIL_FROM_NAME="${APP_NAME}"

REDIRECT_URL_AFTER_EMAIL_AUTHENTICATION=http://localhost:3000/attendance

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_APP_NAME="${APP_NAME}"
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```
3. backendコンテナに入る
```
(docker-compose.yml と同じ階層で実行し backendコンテナに入る)
docker compose exec backend bash
```
4. テスト用のアプリケーションキーの作成
```
php artisan key:generate --env=testing
```
5. 設定ファイルのキャッシュをクリア
```
php artisan config:clear
```
6. マイグレーションの実行
```
php artisan migrate --env=testing

backendコンテナから出る
```
7. テスト用のDBにテーブルが作成されているか確認
```
1. docker-compose.yml と同じ階層で実行し mysqlコンテナに入る
docker compose exec mysql bash

2. ログインする
mysql -u root -p
パスワード：root

3. テスト用のDBを選択
USE laravel_test;

4. laravel_test 内のテーブル確認
SHOW tables;
下記テーブルがあることを確認
admins
attendance_corrections
attendances
breaking_corrections
breakings
users

5. mysqlコンテナから出る
```
8. backendコンテナに入る
```
(docker-compose.yml と同じ階層で実行し backendコンテナに入る)
docker compose exec backend bash
```
9. テストの実行
```
1. 全てをテストする場合
php artisan test

2. 「一般ユーザー側だけの機能」を全てテストする場合
php artisan test tests/Feature/User

3. 「管理ユーザー側だけの機能」を全てテストする場合
php artisan test tests/Feature/Admin
```

### Next.js環境構築(不要)
※ 上記でDockerをビルドした際、frontendコンテナ側で自動的に下記コマンドを実行しているため、`環境構築は不要`です。念の為、記載しております。
```
npm install
npm run dev
```
※ frontendコンテナ内に入る場合は、下記コマンドを実行してください。
```
docker compose exec frontend bash
```

### ログインに関して
seederファイルを流すと自動で一般ユーザーアカウント、管理ユーザーアカウントが作成されます。
#### 一般ユーザー
seederファイルで作成した一般ユーザーアカウントでログインする場合は、下記の通りになります。
1. メールアドレスの取得
  - http://localhost:8080 にアクセスして laravel_db/usersテーブルにある任意の email を取得
2. パスワードの取得  
どの一般ユーザーアカウントも同じパスワードです。
```
パスワード：password
```
※ 一般ユーザーアカウント登録で、自分で作成してログインしても問題ございません。  
※ 勤怠管理アプリのURLは下記の URL > 開発環境 になります。
#### 管理ユーザー
メールアドレス、パスワード共に固定です。
```
メールアドレス：admin@example.com
パスワード：password
```

## URL
- 開発環境：
  - 一般ユーザー画面：http://localhost:3000/login
  - 管理ユーザー画面：http://localhost:3000/admin/login
- phpMyAdmin：http://localhost:8080
- mailhog：http://localhost:8025

## 勤怠管理アプリの仕様について
### スプレッドシートの「画面設計」について
- フロントエンド側でも管理ユーザー、一般ユーザーによって各画面へのアクセス制御を実装しており、その関係で、スプレッドシートの「画面設計」で指定されたパスとは異なるパスで実装している箇所があります。
- 実際のパスはスプレッドシートの「基本設計書」の方に記載しております。
### DBに登録する日時について
- `DBの登録する日時は、秒数を 0 にして、分単位で管理しています。`
### 勤怠登録画面（一般ユーザー）
- `出勤、退勤、休憩開始、休憩終了に関して、同じ日時でDBに登録されないようバリデーションを実装しています（それぞれボタンを押す際は、１分間開けないと押せない仕様にしています）。`
- 例えば、出勤時間が「2025-09-06 09:00:00」の時、退勤時間を同じ日時の「2025-09-06 09:00:00」では登録できないようにしています。同様に、出勤時間が「2025-09-06 09:00:00」の時、休憩開始時間を同じ日時の「2025-09-06 09:00:00」では登録できないようにしています。
- 日付をまたいだ勤務を想定して実装しています。
- 例えば、出勤時間が「2025-09-06 21:00:00」、退勤時間が「2025-09-07 06:00:00」で勤怠登録可能です。
### 勤怠一覧画面（一般ユーザー/管理ユーザー）
- 修正申請されている勤怠は、修正申請された内容を表示させてます。
### 勤怠詳細画面（一般ユーザー）
- 修正申請されている勤怠は、修正申請された内容を表示させてます。
- 日付をまたいだ勤務を想定して実装しています。
- 例えば、出勤時間が「2025-09-06 21:00:00」、退勤時間が「2025-09-07 06:00:00」で修正申請可能です。
- 勤務していない日付も修正申請可能にするため、勤怠詳細画面のパスは２通りあります（スプレッドシートの「基本設計書」を参照）。
- 修正申請で、休憩欄の日付を削除して、空白で修正申請した場合、休憩を削除できる仕様にしています（出勤・退勤は不可）。
### 承認済み申請一覧から勤怠詳細画面へのアクセスについて（一般ユーザー/管理ユーザー）
- これまでの承認履歴を確認できるようにするため、通常の勤怠詳細画面のパスとは別のパスで実装しています（スプレッドシートの「基本設計書」を参照）。
### 勤怠詳細画面（管理ユーザー）
- 修正申請されている勤怠は、修正申請された内容を表示させてます。
- 日付をまたいだ勤務を想定して実装しています。
- 例えば、出勤時間が「2025-09-06 21:00:00」、退勤時間が「2025-09-07 06:00:00」で修正可能です。
- 勤務していない日付も修正可能にするため、勤怠詳細画面のパスは２通りあります（スプレッドシートの「基本設計書」を参照）。
- 修正で、休憩欄の日付を削除して、空白で修正した場合、休憩を削除できる仕様にしています（出勤・退勤は不可）。
- `修正申請されている勤怠の場合、「修正ボタン」ではなく「承認」ボタンが表示され、承認処理ができるように実装しています。`
### 修正申請承認画面（管理ユーザー）
- `勤怠詳細画面において、修正申請されている勤怠の場合、「修正ボタン」ではなく「承認」ボタンが表示され、承認処理ができるように実装しています。`
- `そのため、修正申請承認画面は実装しておりません。`
- `承認待ち申請一覧の「詳細ボタン」を押すと、勤怠詳細画面に遷移します。`
### 当該ユーザーの選択した月で勤怠一覧情報がCSVでダウンロードできる機能について
- ダウンロードしたCSVファイルの文字化けに関して、windows,mac でそれぞれ文字化けしないように実装しておりますが、windows環境では確認できておりません。
- mac の場合、ExcelでCSVファイルを開く場合、文字コードを「65001:Unicode(UTF-8)」、「932:日本語(シフト JIS)」、「50220:日本語(JIS)」等で選択して開くと、文字化けせずに、問題なく開くことができました。
- CSV出力の際は、修正申請していて、かつ承認されていないデータは「修正申請中」の文字を表示させています。

## ER図
[![Image from Gyazo](https://i.gyazo.com/91c5feb3fc652acb8fdb3d1564ab0c96.png)](https://gyazo.com/91c5feb3fc652acb8fdb3d1564ab0c96)

## 使用技術
### フロントエンド
- React 19.1.0
- Next.js 15.4.4
- TypeScript 5.8.3
### バックエンド
- PHP 8.2
- Laravel 10.48.29
### DB
- MySQL 8.0

## URL
- 開発環境：
  - 一般ユーザー画面：http://localhost:3000/login
  - 管理ユーザー画面：http://localhost:3000/admin/login
- phpMyAdmin：http://localhost:8080
- mailhog：http://localhost:8025