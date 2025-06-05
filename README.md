# 勤怠管理アプリ
## 環境構築
1. git clone git@github.com:ghshzk/attendance-management-app.git
2. Dockerを起動する

## 使用技術
- Laravel 8
- PHP 7.4.9
- MySQL 8.0.26
- Nginx 1.21.1

## テーブル仕様
#### usersテーブル
| カラム名           | 型         | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY |
| :---------------: | :---------------: | :---: | :---: | :---: | :---: |
| id                | BIGINT UNSIGNED   | ○ |   | ○ |  |
| name              | VARCHAR(255)      |   |   | ○ |  |
| email             | VARCHAR(255)      |   | ○ | ○ |  |
| email_verified_at | TIMESTAMP         |   |   | ○ |  |
| password          | VARCHAR(255)      |   |   | ○ |  |
| role              | ENUM(user, admin) |   |   | ○ |  |
| remember_token    | VARCHAR(100)      |   |   |   |  |
| created_at        | TIMESTAMP         |   |   |   |  |
| updated_at        | TIMESTAMP         |   |   |   |  |

## ER図
![ER図](ER.png)

## テストアカウント
### 管理者ユーザー
name: 管理者 ユーザー\
email: admin@example.com\
password: adminpass

### 一般ユーザー
name: 山田 太郎\
email: user1@example.com\
password: password

## URL
- 開発環境: http://localhost/
- phpMyAdmin: http://localhost:8080/