namespace App\Validators;

class {{$name}}Validator extends ModelValidator
{
    protected $rules = [
@foreach($rules as $field => $rule)
        '{{$field}}' => [
@foreach($rule as $item)
            '{!! $item !!}',
@endforeach
        ],
@endforeach
    ];

    protected $messages = [];

    protected $customerAttributes = [];
}
