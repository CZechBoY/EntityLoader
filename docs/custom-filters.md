Custom filters
====

To use objects of a certain class as parameters you will need two services. One implementing `Arachne\EntityLoader\FilterInInterface` with `arachne.entityLoader.filterIn` tag and one implementing `Arachne\EntityLoader\FilterOutInterface` with `arachne.entityLoader.filterOut` tag. For both services pass the classes handled by these services as the tag attribute.

```yml
services:
    dateTimeFilterIn:
        class: DateTimeFilterIn
        tags:
            arachne.entityLoader.filterIn: DateTime
    dateTimeFilterOut:
        class: DateTimeFilterOut
        tags:
            arachne.entityLoader.filterOut: DateTime
```

```php
class DateTimeFilterIn implements \Arachne\EntityLoader\FilterInInterface
{
    public function filterIn($value)
    {
        if (is_numeric($value)) { // timestamp
            return new \DateTime(date('Y-m-d H:i:s', $value));
        } else { // textual
            return new \DateTime($value);
        }
    }
}

class DateTimeFilterOut implements \Arachne\EntityLoader\FilterInInterface
{
    public function filterOut($entity)
    {
        // $entity instanceof DateTime
        return $entity->getTimestamp();
    }
}
```

You could argue that filterIn method should only handle timestamps and not other formats because filterOut always returns a timestamp. The point is that filterOut should always return an identifier from which the object can be recreated. Regardless how you wish to represent the parameter in the URL. You can change the URL representation in router (see the example below).


Presenter
----

Finally we can use DateTime parameters in presenter:

```php
class FooPresenter extends \Nette\Application\UI\Presenter
{
    public function renderDefault(DateTime $date)
    {
        $this->template->date = $date;
    }
}
```

Persistent parameters are also supported. Use the @var annotation to specify the class.


Routers
----

Now what if we want to use different format than timestamp in URL? For example imagine that you don't need the time part in one case when you use a DateTime parameter and want to reptesent the date simply as 'Y-m-d' in the URL. This is supported and it can even be specific to the route in question. The solution uses `Route::FILTER_OUT` option and an Envelope object.

To use Envelopes you need to change your routing a little. Instead of `Nette\Application\Routers\Route` use `Arachne\EntityLoader\Routing\Route`. Then you can enable envelopes first by passing an optional third parameter to `Arachne\EntityLoader\Routing\RouterWrapper`.

```php
return new RouterWrapper($router, $this->entityUnloader, true);
```

The envelopes are a simple objects implementing the __toString method. Thanks to that and some magic in the `Arachne\EntityLoader\Routing\Route` class they won't have any effect on your application other than that you can use them to get the underlying object in `Route::FILTER_OUT` callbacks:

```php
$router[] = new Route('/<date>', [
    'presenter' => 'Foo',
    'date' => [
        Route::FILTER_OUT => function (\Arachne\EntityLoader\Application\Envelope $value) {
            return $value->getEntity()->format('Y-m-d');
        },
    ],
]);
```