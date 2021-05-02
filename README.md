# laravel-model-validator

This is a **simple** validator.

The validator can be created by command.

The validator has all common table column constraint, eg: string or numeric, max length or min length, etc.

## register service

`config/app.php` - `providers`

```
GordenSong\ModelValidatorHelperServiceProvider::class,
```

## create model validator

```
make:gs-model-validator {model*}


make:gs-model-validator Models/Book
```

## create table validator (TODO)

## how to use validator

validator 

### rule
- all table field 
- validation type : string, numeric, etc.
- string(length:50): `max:50`
- auto increment: `min:1`   
- `nullable`
- not `required`
- self define

### method

#### `with(array $data) : self`

data to deal with.

---  

#### `required(...$fields) : self`

fields in params list add `required`

--- 

#### `only(...$fields) : self:

only fields can be validated.

--- 

#### `exclude(...$field) : self`

$fields will not be validated.

--- 

#### `passes() : bool`
#### `fails() : bool`
#### `validate() : array | throw ValidationException`
#### `validated() : array |  throw ValidationException`

#### BooksValidator

##### migration
```
Schema::create('books', function (Blueprint $table) {
    $table->id();
    $table->string('title', 50)->nullable();
    $table->integer('price')->nullable()->default(0);
});
```
##### validator
```
class BooksValidator extends ModelValidator
{
    protected $rules = [
        'id' => [
            'numeric',
            'min:1',
        ],
        'title' => [
            'string',
            'max:50',
            'nullable',
        ],
        'price' => [
            'numeric',
            'min:0',
            'nullable',
        ],
    ];

    protected $customerAttributes = [

    ];
}
```
### controller
```
class BookController extends Controller
{
    public function store(Request $request, BooksValidator $validator)
    {
        $data = $validator
            ->with($request->all())
            ->validated();

        $book = Books::query()->create($data);

        return $book->toArray();
    }

    public function storeRequireTitlePrice(Request $request, BooksValidator $validator)
    {
        $data = $validator
            ->with($request->all())
            ->required('title', 'price')
            ->validated();

        $book = Books::query()->create($data);

        return $book->toArray();
    }

    public function updateOnlyPrice(Request $request, BooksValidator $validator)
    {
        $data = $validator
            ->with($request->all())
            ->only('price')
            ->validated();

        $book = Books::query()->findOrFail($request->id);
        $book->update($data);

        return $book->toArray();
    }

    public function updateExcludePrice(Request $request, BooksValidator $validator)
    {
        $data = $validator
            ->with($request->all())
            ->exclude('price')// 排除
            ->validated();

        $book = Books::query()->findOrFail($request->id);
        $book->update($data);

        return $book->toArray();
    }
}
```
##### test
```
class BookControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function testStoreValidate()
    {
        $data = ['title' => Str::random(60)];

        $response = $this->postJson(route('book.store'), $data);
        $response->assertStatus(422);

        $data = ['price' => -1];

        $response = $this->postJson(route('book.store'), $data);
        $response->assertStatus(422);

        $data = [];

        $this->withoutExceptionHandling();
        try {
            $response = $this->postJson(route('book.store'), $data);
            self::fail('InvalidArgumentException');
        } catch (\Exception $e) {
            self::assertInstanceOf(\InvalidArgumentException::class, $e);
        }
    }

    public function testStoreValidate_argument_too_many()
    {
        $data = [
            'title' => $title = 'old title',
            'author' => Str::random(30),
            'hehe' => 'haha',
        ];

        $response = $this->postJson(route('book.store'), $data);
        $response->assertOk();
        $response->assertJsonStructure(['title', 'id']);
        $response->assertJsonFragment(['title' => $title]);
    }

    public function testStoreRequireTitlePrice()
    {
        $data = [
            'title' => 'aaa',
        ];
        $response = $this->postJson(route('book.store.require-title-price'), $data);
        $response->assertStatus(422);

        $data = [
            'title' => 'aaa',
            'price' => 111,
        ];
        $response = $this->postJson(route('book.store.require-title-price'), $data);
        $response->assertOk();
    }

    public function testUpdateOnlyPrice()
    {
        $book = factory(Books::class)->create([
            'price' => $oldPrice = 100,
            'title' => $oldTitle = 'old title',
        ]);

        $data = [
            'id' => $book->id,
            'title' => $newTitle = 'new title',
            'price' => $newPrice = 200
        ];
        $response = $this->postJson(route('book.update.only-price'), $data);
        $response->assertOk();

        $book->refresh();

        self::assertEquals($newPrice, $book->price);
        self::assertEquals($oldTitle, $book->title);
    }

    public function test_updateExcludePrice()
    {
        /** @var Books $book */
        $book = factory(Books::class)->create([
            'price' => $oldPrice = 100,
            'title' => $oldTitle = 'old title',
        ]);

        $data = [
            'id' => $book->id,
            'title' => $newTitle = 'new title',
            'price' => $newPrice = 200
        ];
        $response = $this->postJson(route('book.update.exclude-price'), $data);
        $response->assertOk();

        $book->refresh();

        self::assertEquals($newTitle, $book->title);
        self::assertEquals($oldPrice, $book->price);
    }
}
```
