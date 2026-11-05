<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Seeder;
use RuntimeException;

class InterviewPostSeeder extends Seeder
{
    /**
     * The author the posts are attributed to. When null, an administrator
     * (or the first user) is used.
     */
    public ?User $author = null;

    /**
     * Seed the "Senior backend interview" blog series.
     */
    public function run(): void
    {
        $author = $this->author ?? $this->resolveAuthor();

        $posts = $this->posts();
        $total = count($posts);

        foreach ($posts as $index => $data) {
            $tags = $data['tags'];
            unset($data['tags']);

            $post = Post::query()->updateOrCreate(
                ['slug' => $data['slug']],
                [...$data, 'user_id' => $author->getKey()],
            );

            $post->forceFill([
                'created_at' => now()->subDays($total - $index),
            ])->saveQuietly();

            $post->tags()->sync(
                collect($tags)
                    ->map(fn (string $name): int => Tag::findOrCreateByName($name)->getKey())
                    ->all(),
            );
        }
    }

    /**
     * Resolve the fallback author for the posts.
     */
    private function resolveAuthor(): User
    {
        $author = User::query()
            ->whereHas('roles', fn (Builder $query): Builder => $query->where('name', 'administrator'))
            ->orderBy('id')
            ->first()
            ?? User::query()->orderBy('id')->first();

        if (! $author) {
            throw new RuntimeException('No user exists to attribute the interview posts to. Create a user first.');
        }

        return $author;
    }

    /**
     * @return array<int, array{title: string, slug: string, cover_path: string, excerpt: string, tags: array<int, string>, body: string}>
     */
    private function posts(): array
    {
        return [
            [
                'title' => 'Senior Backend Interview: OOP and PHP — Answers to Have on the Tip of Your Tongue',
                'slug' => 'senior-backend-interview-oop-and-php',
                'cover_path' => 'posts/covers/senior-backend-interview-oop-and-php.png',
                'excerpt' => 'Public, protected, private, abstract vs interface, SOLID and dependency injection — short answers for a technical interview, no hesitation.',
                'tags' => ['PHP', 'OOP', 'Interview'],
                'body' => <<<'MD'
A senior backend interview is rarely a test of knowledge — it is a test of how quickly you can put it into words. These are the answers I have used in practice for years, boiled down to sentences you can say in fifteen seconds.

## Public, protected and private

`public` is accessible from anywhere. `protected` inside the class and in classes that extend it. `private` only inside the class that declares it — not even a subclass can see it.

> Public is open to everyone. Protected to the class and its descendants. Private only to that class — a subclass can't see it. In practice: private is the default, protected when I know the class will be extended, public only for what is genuinely the public interface.

### When protected instead of private?

When I build a base class that someone will extend and I want the child to have access. Otherwise I go with `private` — it is easier to loosen later than to tighten.

### The catch: private across instances

A `private` member is accessible across different instances of the same class. `$this->secret` and `$otherObject->secret` both work as long as both objects are of the same class.

## Abstract class vs interface

An interface is a contract — method signatures only, and a class can implement many of them. An abstract class is a partial implementation — it can hold state and concrete methods, but you can extend only one. Interface when I define **what can be done**, abstract class when I share **how it is done**.

## What final means

It prevents a class from being extended or a method from being overridden. I use it when I don't want anyone changing behaviour I rely on.

## static — when yes, when no

`static` belongs to the class, not to an instance. Useful for factories and stateless helpers. I avoid it for logic because it can't be swapped out in a test — static is hidden global state.

## Traits

A way to share implementation without inheritance — PHP has no multiple inheritance, and traits partly cover that. I use them sparingly because they hide where a method comes from.

## == vs ===

Double equals compares values with type juggling, triple compares both value and type. I always use triple unless I specifically need the conversion.

## Dependency injection

Instead of a class creating what it needs, it receives it from the outside. Dependencies become visible in the constructor and can be replaced in a test. The opposite is `new` inside a method — then you can't test without a real database.

```php
public function __construct(private readonly OrderRepository $orders) {}
```

## SOLID in one sentence per letter

- **S** — one class, one reason to change.
- **O** — open for extension, closed for modification.
- **L** — a subclass must be able to replace its parent without breaking anything.
- **I** — many small interfaces instead of one large one.
- **D** — depend on abstractions, not on concrete implementations.

If they ask which letter I use most: **S and D**. One responsibility per class and dependencies through interfaces — those two have the biggest impact on how testable and changeable the code is later.
MD,
            ],
            [
                'title' => 'Senior Backend Interview: Databases — Normalization, Indexes and Transactions',
                'slug' => 'senior-backend-interview-databases',
                'cover_path' => 'posts/covers/senior-backend-interview-databases.png',
                'excerpt' => '1NF, 2NF, 3NF, composite indexes, ACID, isolation levels, N+1 and deadlocks — the answers every backend interview asks for.',
                'tags' => ['Database', 'SQL', 'Interview'],
                'body' => <<<'MD'
Databases are the topic where it shows fastest whether someone runs production systems or has just read a tutorial. These are the answers I keep ready.

## Normalization

1NF — every cell holds a single value, no repeating groups and no lists in a column. 2NF — 1NF plus no non-key column depends on only part of a composite primary key. 3NF — 2NF plus no non-key column depends on another non-key column.

In practice it means every piece of data lives in one place. In production I deliberately denormalize where reads become too expensive — but that is a decision, not an oversight.

### An example of breaking 3NF

An orders table that stores `customer_city` next to `customer_id`. The city doesn't depend on the order but on the customer — that is a transitive dependency. If the customer moves, the old orders lie.

### BCNF

A stricter version of 3NF — every determinant must be a candidate key. It rarely comes into play in practice.

## Indexes

An index is a structure that speeds up lookups and slows down writes, because it has to be maintained. I put one on the columns I filter, sort and join by. I don't put one on low-cardinality columns — an index on a boolean is usually worthless.

### Composite indexes and the leftmost prefix rule

In a composite index the column order matters. An index on `(a, b)` helps a query on `a` and on `a AND b`, but not a query on `b` alone.

### Why an index isn't used

Most often because the column is wrapped in a function — `WHERE DATE(created_at) = ...` kills the index. Or the selectivity is so poor that the optimizer decides a full scan is cheaper.

## ACID

- **Atomicity** — a transaction succeeds completely or not at all.
- **Consistency** — the database moves from one valid state to another.
- **Isolation** — concurrent transactions don't interfere with each other.
- **Durability** — once committed, it survives a server crash.

## Isolation levels

MySQL defaults to `REPEATABLE READ`. `READ COMMITTED` sees other transactions' committed changes mid-transaction, `REPEATABLE READ` doesn't. `SERIALIZABLE` is the strictest and the most expensive. I rarely change them in practice, but with reports and long transactions it can matter.

## JOINs

`INNER` returns only matching rows. `LEFT` returns everything from the left table plus the matches from the right, the rest is `NULL`. `RIGHT` is the reverse. `FULL OUTER` returns everything from both sides — MySQL doesn't have it, you do it with a `UNION`.

## The N+1 problem

When you fetch a list and then run a new query for every row in a loop — one query for the list and N for the details. The fix is eager loading, `with()` in Laravel, or a `JOIN` or a single `IN` query.

```php
$posts = Post::with('author', 'tags')->latest()->paginate();
```

## Transactions vs locking

A transaction groups operations. Locking prevents someone from changing the data while you are working with it. I use `SELECT ... FOR UPDATE` when I have to read and then write based on what I read — otherwise you get a lost update.

## Deadlock

Two transactions wait for each other. The database detects it and kills one of them. You avoid it by always locking resources in the same order and keeping transactions short.
MD,
            ],
            [
                'title' => 'Senior Backend Interview: REST and APIs — Resources, Methods and Status Codes',
                'slug' => 'senior-backend-interview-rest-and-api',
                'cover_path' => 'posts/covers/senior-backend-interview-rest-and-api.png',
                'excerpt' => 'What makes an API RESTful, idempotency, 401 vs 403, JWT vs sessions and versioning — all in sentences that sound like real experience.',
                'tags' => ['REST', 'API', 'Interview'],
                'body' => <<<'MD'
REST questions aren't hard, but it is easy to start listing things and lose the point. The goal is to show in two sentences that you understand the model, not that you memorized a definition.

## What makes an API RESTful

Resources are addressed by URI and they are nouns, not verbs — `/reservations/12`, not `/getReservation`. The HTTP method carries the meaning of the operation. Communication is stateless — the server remembers nothing between requests, every request carries everything it needs. The response is a representation of the resource, usually JSON, with the correct status code.

## HTTP methods

- `GET` — reads; it is safe and idempotent.
- `POST` — creates; not idempotent, calling it twice creates two records.
- `PUT` — replaces the whole resource; it is idempotent.
- `PATCH` — changes part of it.
- `DELETE` — deletes; idempotent, a second call doesn't change the state.

### What idempotent means

That the same request sent several times leaves the system in the same state as if it had been sent once. It matters for retries after an error — otherwise a retry creates a duplicate reservation.

### HATEOAS

The level at which a response also carries links to the next possible actions. In practice almost nobody does it — most APIs sit at level two of the Richardson Maturity Model, and that is usually perfectly fine.

## Status codes

- `200` — OK
- `201` — created
- `204` — OK, no content
- `400` — bad request
- `401` — you are not signed in
- `403` — you are signed in, but not allowed
- `404` — doesn't exist
- `409` — conflict
- `422` — validation failed
- `429` — too many requests
- `500` — server error
- `503` — service unavailable

### The difference between 401 and 403

`401` means I don't know who you are, identify yourself. `403` means I know who you are, but you can't do this.

## Authentication vs authorization

Authentication is who you are, authorization is what you are allowed to do. One happens first, then the other.

## JWT vs sessions

A session keeps state on the server, the client only carries an ID. A JWT carries the data in the token itself, signed — the server doesn't have to remember anything, which is good for scaling. The downside of a JWT is that it is hard to revoke before it expires; you handle that with a short lifetime and a refresh token.

## API versioning

Most often in the path, `/v1/`. It can also go in a header, but the path is more visible and easier to debug. I bump the version when I break compatibility, not for every change.
MD,
            ],
            [
                'title' => 'Senior Backend Interview: Architecture — Monoliths, Message Queues and Caching',
                'slug' => 'senior-backend-interview-architecture',
                'cover_path' => 'posts/covers/senior-backend-interview-architecture.png',
                'excerpt' => 'When a monolith, when microservices, sync vs async, message queue semantics and caching strategies — no hype and no dogma.',
                'tags' => ['Architecture', 'Interview'],
                'body' => <<<'MD'
Architecture questions are a test of maturity. They don't expect you to know every pattern, but to know when a given problem actually exists.

## Monolith vs microservices

A monolith is one deploy and one database — simpler until the team and the system have grown. Microservices give you independent scaling and deploys, but you pay with the network, with observability, and with consistency becoming your problem. I wouldn't split until it hurts.

## Synchronous vs asynchronous

Synchronously, the user waits for the response. Asynchronously, the work goes onto a queue and the user gets a confirmation right away. I push onto the queue everything that doesn't have to happen before the response — emails, reports, syncs with external systems.

## Message queue semantics

- **At-most-once** — a message can be lost.
- **At-least-once** — a message can be repeated, so jobs must be idempotent.
- **Exactly-once** is hard in practice and usually comes down to at-least-once plus deduplication by key.

## Caching strategies

Most often cache-aside: check the cache, if it's not there read from the database and store it. The key question is invalidation — I clear on an event, not on hope. On top of that, a TTL as a safety net in case something slips through.

```php
$post = Cache::remember("post:{$slug}", 3600, fn () => Post::where('slug', $slug)->firstOrFail());
```

## How to prevent duplicate processing

An idempotency key — the client sends a unique key, the server remembers it and the second time returns the same result instead of creating a new record. With money and reservations it isn't optional, it is a must.
MD,
            ],
            [
                'title' => 'Senior Backend Interview: Security — SQL Injection, XSS, CSRF and Passwords',
                'slug' => 'senior-backend-interview-security',
                'cover_path' => 'posts/covers/senior-backend-interview-security.png',
                'excerpt' => 'Four attacks that come up in every interview, and answers that show you know where the defence actually belongs.',
                'tags' => ['Security', 'Interview'],
                'body' => <<<'MD'
Security is the topic where it is easiest to say the right name and the wrong mechanism. These answers focus on **where** the defence belongs.

## SQL injection

It happens when user input ends up in a query as code. You prevent it with prepared statements and parameters — the ORM does it for you, but as soon as you write raw SQL you have to be careful. Filtering input is not a substitute for parameters.

```php
// wrong
DB::select("select * from users where email = '{$email}'");

// right
DB::select('select * from users where email = ?', [$email]);
```

## XSS

When user content ends up on the page as HTML or JavaScript. You prevent it by encoding on output, not on input — in Blade `{{ }}` encodes, `{!! !!}` doesn't.

## CSRF

Someone else's page makes the browser send a request with your cookie. You prevent it with a token the attacker can't read, plus `SameSite` cookies.

## How passwords are stored

They are hashed with a slow, salted algorithm — bcrypt or Argon2. Never MD5 or SHA, because they are too fast. In PHP it is `password_hash()` and `password_verify()`, and the salt is generated for you.

```php
$hash = password_hash($password, PASSWORD_ARGON2ID);
$ok = password_verify($password, $hash);
```

## Secrets management

Secrets don't go into the repository. They come through environment variables and infrastructure-level secrets, with different values per environment and rotation. `.env` is in `.gitignore`, and an `.env.example` without values serves as documentation.
MD,
            ],
            [
                'title' => 'Senior Backend Interview: Laravel — the Container, Middleware, Eloquent and the Service Layer',
                'slug' => 'senior-backend-interview-laravel',
                'cover_path' => 'posts/covers/senior-backend-interview-laravel.png',
                'excerpt' => 'The service container, middleware, Eloquent relationships, why a service layer, and migrations — Laravel answers without reciting the docs.',
                'tags' => ['Laravel', 'PHP', 'Interview'],
                'body' => <<<'MD'
Laravel is the framework where it is easiest to hide behind "the framework does it". A senior answer shows you know **why** it does it and when you would work around it.

## The service container

It resolves dependencies — if a constructor asks for an interface, the container knows which implementation to inject. I register bindings in a service provider, so swapping an implementation is a single line.

```php
$this->app->bind(OrderRepository::class, EloquentOrderRepository::class);
```

## Middleware

The layer a request passes through before and after the controller — authentication, rate limiting, logging. Everything that concerns all routes but isn't business logic.

## Eloquent relationships

`hasOne` and `hasMany` when the foreign key sits on the other side, `belongsTo` when it is on my side, `belongsToMany` through a pivot table, `morphTo` for polymorphic relations.

## Why a service layer

So that business logic doesn't live in the controller. The controller takes the request and returns the response, the service does the work. That way the logic can be tested without HTTP and called from the console or from a queue.

```php
final class CreateReservation
{
    public function __construct(private ReservationRepository $reservations) {}

    public function handle(array $data): Reservation
    {
        return $this->reservations->create($data);
    }
}
```

## Migrations

A versioned database schema in code. Every change is a file with `up` and `down`, so the database state can be reproduced in any environment. I never change a migration that has already run — I write a new one.
MD,
            ],
            [
                'title' => 'Senior Backend Interview: What to Say When You Don\'t Know the Answer',
                'slug' => 'senior-backend-interview-when-you-dont-know-the-answer',
                'cover_path' => 'posts/covers/senior-backend-interview-when-you-dont-know-the-answer.png',
                'excerpt' => 'Interviewers don\'t reject a candidate for not knowing something, but for fumbling, guessing or pretending. Here is how to avoid that.',
                'tags' => ['Interview', 'Career'],
                'body' => <<<'MD'
This matters more than any single question. Interviewers don't reject a candidate for not knowing something. They reject them for fumbling, guessing or pretending.

## Bad

A long silence, then a guess, then backing down when they say it isn't right.

## Good

> I wouldn't want to guess. I rarely use that term — how do you define it, so we can check we're talking about the same thing?

Or, when you know the thing but not the name:

> The name escapes me right now, but if you mean [description] — this is how I do it: [specifics].

That is a legitimate answer from a senior. What isn't legitimate is offering a wrong answer and then defending it.

## Why this works

An interview isn't an exam with an answer key. What is being assessed is how you think under pressure and how you communicate uncertainty. Calmly admitting the limit of your knowledge, together with a precise description of what you do know, leaves a better impression than listing terms you can't defend.
MD,
            ],
            [
                'title' => 'Senior Backend Interview: Trick Questions — HTTP Methods, the Request and Visibility',
                'slug' => 'senior-backend-interview-trick-questions',
                'cover_path' => 'posts/covers/senior-backend-interview-trick-questions.png',
                'excerpt' => 'POST vs PUT vs PATCH, GET vs POST, HEAD and OPTIONS, the Laravel Request, readonly, property hooks and invokable controllers.',
                'tags' => ['PHP', 'HTTP', 'Laravel', 'Interview'],
                'body' => <<<'MD'
The second part of preparation is for questions that look simple but have a catch. This is where most time gets lost, because you start explaining from the very beginning.

## POST vs PUT vs PATCH

- `POST` — creates a new resource on a collection: `POST /reservations` → `201 Created` + a `Location` header. Sent twice, it creates two records. It is also used for actions that aren't CRUD: `POST /reservations/12/cancellation`.
- `PUT` — replaces the whole resource at a known URI: `PUT /reservations/12` with the complete object. A field you don't send is lost or reset to its default. Idempotent.
- `PATCH` — changes only the fields sent: `PATCH /reservations/12` with `{"status": "confirmed"}`. By definition it isn't necessarily idempotent (e.g. "increment by 1"), but in practice it usually is.

> POST creates, PUT replaces the whole resource, PATCH changes part of it. PUT is idempotent, POST isn't. In practice I use PATCH for edits because the client sends only what changed.

### Can PUT create a resource?

It can — if the client knows the URI, e.g. `PUT /users/42/settings`. Then it is an upsert: if it exists, replace it; if not, create it.

### In Laravel

`Route::apiResource()` binds the `update` method to both `PUT` and `PATCH`. HTML forms only know `GET` and `POST`, so `PUT`, `PATCH` and `DELETE` are sent as `POST` with a hidden `_method` field — `@method('PUT')` in Blade. This is called method spoofing.

## GET vs POST

`GET` has no body, parameters go in the URL, it can be cached and it stays in the browser history and in server logs — so never put passwords or tokens in a `GET`. `POST` carries data in the body and isn't cached.

## HEAD and OPTIONS

`HEAD` is a `GET` without a body — headers only. `OPTIONS` returns the allowed methods; the browser sends it as a CORS preflight before non-simple cross-origin requests.

## The Request — where each value is read from (Laravel)

- `$request->input('x')` reads both the query string and the body.
- `$request->query('x')` reads only the query string.
- `$request->route('id')` reads a route parameter.
- `$request->validated()` returns only the fields that passed validation — that is what I pass to the model, never `$request->all()`.

## Visibility — more trick questions

### Can a subclass change a method's visibility?

It can only widen it — `protected` to `public`. It can't narrow it, because the subclass would then break the parent's contract. The same goes for interfaces: interface methods are always `public`.

### Can a trait see a class's private members?

Yes — at compile time the trait is effectively copied into the class, so its methods have access to that class's `private` properties too.

## readonly and promotion

Since PHP 8.1 a property can be `readonly` — set once, inside the class, and never changed again. With constructor property promotion it is a single line:

```php
public function __construct(private readonly OrderRepository $orders) {}
```

## PHP 8.4 — asymmetric visibility

`public private(set) string $status` — anyone can read it, but only the class can write it. PHP 8.4 also brought property hooks — get/set logic directly on the property.

## Invokable classes and controllers

A class with an `__invoke()` method can be called like a function: `$action($order)`.

A single action controller is a controller with just one action — `php artisan make:controller PayOrder --invokable`. The route takes only the class name.
MD,
            ],
        ];
    }
}
