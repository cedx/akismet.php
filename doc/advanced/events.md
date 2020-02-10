path: blob/master
source: lib/Http/Client.php

# Events
The `Akismet\Http\Client` class, used to query the Akismet service, is a [`League\Event\Emitter`](https://event.thephpleague.com/2.0/emitter/basic-usage) that triggers some events during its life cycle.

### The `Client::eventRequest` event
Emitted every time a request is made to the remote service:

```php
<?php
use Akismet\{Blog};
use Akismet\Http\{Client, RequestEvent};
use GuzzleHttp\Psr7\{Uri};

function main(): void {
  $client = new Client('123YourAPIKey', new Blog(new Uri('https://www.yourblog.com')));
  $client->addListener(Client::eventRequest, fn(RequestEvent $event) =>
    print("Client request: {$event->getRequest()->getUri()}")
  );
}
```

### The `Client::eventResponse` event
Emitted every time a response is received from the remote service:

```php
<?php
use Akismet\{Blog};
use Akismet\Http\{Client, ResponseEvent};
use GuzzleHttp\Psr7\{Uri};

function main(): void {
  $client = new Client('123YourAPIKey', new Blog(new Uri('https://www.yourblog.com')));
  $client->addListener(Client::eventResponse, fn(ResponseEvent $event) =>
    print("Server response: {$event->getResponse()->getStatusCode()}")
  );
}
```
