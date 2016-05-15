# conjurer
Conjurer - CREY Framework Service Management Component

### Description

Conjurer is the service container of CREY, a PHP 7 component-based Framework.

### Metrics of master branch

![Package Metrics](https://cdn.rawgit.com/crey-framework/conjurer/master/package-metrics.svg)

### License

The provided source code is licensed under the terms of the [MIT license](LICENSE).

### Usage

This brief introduction demonstrates how to use Conjurer.

##### Registration of a service object using a factory implementation

Conjurer knows 2 different types of services: `Service`-Instances that
allows a complete configuration of the service and `Factory`-Instances
that allows pre-initializing services on registration and a `factorize()`-method
to be used as a dependency parameter resource.

```php
<?php

use Crey\Conjurer\{
    Conjurer,
    Service
}

$container = new Conjurer();
$container->register(new class(DateTime::class) extends Factory {
        
    function initialize()
    {
        $timezone = (new Service(DateTimeZone::class))
            ->withParameter('timezone', 'europe/berlin')
            ->singleton()
        ;

        $this->register($timezone)->withParameter('time', 'now');
    }

    function factorize(DateTimeZone $timezone)
    {
        return ['object' => $timezone];
    }
    
});

$dateTime = $container->make(DateTime::class);
```

##### Registration of a services ( linking )

```php
<?php

use Crey\Conjurer\{
    Conjurer,
    Service
}

$container = new Conjurer();

$container->register(new class(DateTime::class) extends Factory {
        
    function initialize()
    {
        $timezone = (new Service(DateTimeZone::class))
            ->withParameter('timezone', 'europe/berlin')
            ->singleton()
        ;

        $this->register($timezone)->withParameter('time', 'now');
    }

    function factorize(DateTimeZone $timezone)
    {
        return ['object' => $timezone];
    }
    
});

$container->bind(DateTimeInterface::class, DateTime::class);

$dateTime = $container->make(DateTimeInterface::class);
```

#### Using notifiers

Notification Callbacks ( notifiers ) could be used for various tasks ( logging ).
Since Conjurer does not depend on or implement a PSR-Log interface, the notifiers
should be used to achieve logging.

A basic logging handler to connect monolog as a default logger to the container
is planned for the near future.

```php
<?php

use Crey\Conjurer\{
    Conjurer,
    ServiceContract
}

$container = new Conjurer();
$notifier = $container->getNotifierRepository();

$notifer->setBuildFailCallback(function(Throwable $exception, ServiceContract $service) {
    echo sprintf(
        'Something went wrong while building an instance of %s. The process terminated with the message `%s`',
        $service->getInterface(),
        $exception->getMessage()
    );
});
```

#### Maintainer and state of this package

The inventor and maintainer of this package is Matthias Kaschubowski.
This package is currently in alpha mode.

#### Meaning of staging modes

- [x] Alpha - No out-sourced documentation, no or incomplete tests
- [ ] Beta - out-sourced documentation, near to complete tests, CI
- [ ] Universe - production and development ready state with CI

#### Composer integration

This package will be available at packagist in beta mode. Until then
you have to manually link this package repository as a data resource
to your dependencies.
