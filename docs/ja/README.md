# Acl Extras ドキュメント

Acl Extras は、DbAcl のレコードの管理を助けるコンソールシェルを提供します。主な目的は、
すべてのコントローラーとアクションの ACO ノードを生成することです。壊れた ACO ツリーや
ARO ツリーの検証と修復もできます。
[pieceofcake2/cakephp](https://github.com/pieceofcake2/cakephp) の上で動作します。

- [インストール](#installation)
- [シェルの要件](#requirements-for-the-shell)
- [コマンド](#commands)
- [ACO ツリー](#the-aco-tree)
- [コントローラーの設定](#setting-up-the-controller)
- [コードから ACO を更新する](#updating-acos-from-your-code)
- [クレジット](#credits)

<a id="installation"></a>

## インストール

[Composer](https://getcomposer.org/) でプラグインをインストールします。

```bash
composer require pieceofcake2/acl_extras
```

プラグインのタイプは `cakephp-plugin` なので、`composer/installers` によってプラグインディレクトリに
インストールされます。正しい場所にインストールされるように、アプリケーションの `composer.json` で
`installer-paths` を設定してください。

| レイアウト | `installer-paths` |
| --- | --- |
| モダン (`config/`, `src/`, `templates/`, `plugins/`) | `"plugins/{$name}/": ["type:cakephp-plugin"]` |
| レガシー (`app/Config/`, `app/Plugin/`) | `"app/Plugin/{$name}/": ["type:cakephp-plugin"]` |

`config/bootstrap.php` (レガシーレイアウトでは `app/Config/bootstrap.php`) でプラグインを読み込みます。
すでに `CakePlugin::loadAll()` を使っている場合は不要です。

```php
CakePlugin::load('AclExtras');
```

<a id="requirements-for-the-shell"></a>

## シェルの要件

シェルを使うには、`config/core.php` (レガシーレイアウトでは `app/Config/core.php`) で
DbAcl が設定されていること

```php
Configure::write('Acl.classname', 'DbAcl');
```

と、ACL のテーブルが存在することが必要です。

```bash
bin/cake schema create DbAcl
```

アプリケーションがレガシーのディレクトリレイアウトの場合は、`bin/cake` の代わりに
`app/Console/cake` を使ってください。

<a id="commands"></a>

## コマンド

コマンドの一覧は次のように表示します。

```bash
bin/cake AclExtras.AclExtras -h
```

```bash
# Add new ACOs for new controllers and actions. Does not remove nodes from the ACO table.
bin/cake AclExtras.AclExtras aco_update

# Perform a full sync on the ACO table. Also removes orphaned ACOs that no longer have a
# matching controller or action.
bin/cake AclExtras.AclExtras aco_sync

# Update or sync only one plugin
bin/cake AclExtras.AclExtras aco_sync --plugin MyPlugin

# Verify or recover the tree structure of the ACO or ARO tree
bin/cake AclExtras.AclExtras verify aco
bin/cake AclExtras.AclExtras recover aro
```

| コマンド | 説明 |
| --- | --- |
| `aco_update` | 新しいコントローラーとアクションの ACO を追加します。既存のノードはそのまま残ります。 |
| `aco_sync` | 新しいコントローラーとアクションの ACO を追加し、存在しなくなったコントローラーとアクションの ACO を削除します。 |
| `verify aco\|aro` | ACO ツリーまたは ARO ツリーのツリー構造を検証します。 |
| `recover aco\|aro` | 壊れた ACO ツリーまたは ARO ツリーを修復します。 |

`aco_update` と `aco_sync` では、`--plugin` (`-p`) で一つのプラグインだけを処理できます。

コマンドが失敗した場合、たとえば存在しないプラグインを指定した場合やツリーが不正な場合、
シェルはエラーコードで終了します。

<a id="the-aco-tree"></a>

## ACO ツリー

`aco_update` と `aco_sync` は、`controllers` という名前のルートノードの下に、public な
アクションごとに一つの ACO を作成します。

```
controllers
├── Posts
│   ├── index
│   ├── view
│   └── ...
└── ContactManager          (a plugin)
    └── Contacts
        ├── index
        └── ...
```

`AppController` と `<Plugin>AppController` には、それ自身のノードは作成されません。基底クラス
`Controller` のメソッド、名前が `_` で始まるメソッド、コントローラーのイベントコールバックは
スキップされます。コントローラーが `AppController` から継承した public メソッドは、
各コントローラーに追加されます。

<a id="setting-up-the-controller"></a>

## コントローラーの設定

シェルが作成した ACO がリクエストごとにチェックされるように、`AuthComponent` で `Actions`
認可を使うように設定します。`src/Controller/AppController.php`
(レガシーレイアウトでは `app/Controller/AppController.php`) で次のようにします。

```php
class AppController extends Controller
{
    public $components = [
        'Auth' => [
            'authorize' => [
                'Actions' => ['actionPath' => 'controllers/'],
            ],
        ],
    ];
}
```

<a id="updating-acos-from-your-code"></a>

## コードから ACO を更新する

シェルはプラグインの `AclExtras` クラスを使っています。このクラスは、たとえば管理用の
コントローラーなど、独自のコードからも使えます。

```php
App::uses('AclExtras', 'AclExtras.Lib');

$aclExtras = new AclExtras();
$aclExtras->startup($this);
$aclExtras->aco_sync();
```

コントローラーに `Session` コンポーネントがある場合、メッセージはフラッシュメッセージとして
設定されるので、コントローラーには `Flash` コンポーネントも必要です。

<a id="credits"></a>

## クレジット

Acl Extras は、もともと [Mark Story](https://github.com/markstory/acl_extras) によって作成されました。

<a id="origin"></a>

## 由来

このパッケージは [markstory/acl_extras](https://github.com/markstory/acl_extras)
(Packagist では `markstory/acl_extras`、アップストリームの最終バージョンは 1.2.1) の
メンテナンスされたフォークで、元の作者は Mark Story です。
[pieceofcake2](https://github.com/pieceofcake2) が、モダンな PHP で動く CakePHP 2 向けに
メンテナンスしています。オリジナルからの変更点は
[フォーク以降のコミット履歴](https://github.com/pieceofcake2/acl_extras/compare/1174758...2.x)
で確認できます。
