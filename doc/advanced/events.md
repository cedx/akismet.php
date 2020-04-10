path: blob/master
source: src/Client.php

# Events
The `Akismet\Client` class, used to query the Akismet service, is an [EventDispatcher](https://symfony.com/doc/current/components/event_dispatcher.html) that triggers some events during its life cycle.

### The `Client::eventRequest` event
Emitted every time a request is made to the remote service:

```php
<?php
use Akismet\{Blog, Client, RequestEvent};
use Nyholm\Psr7\{Uri};

function main(): void {
  $client = new Client('123YourAPIKey', new Blog(new Uri('https://www.yourblog.com')));
  $client->addListener(Client::eventRequest, function(RequestEvent $event) {
    echo 'Client request: ', $event->getRequest()->getUri();
  });
}
```

### The `Client::eventResponse` event
Emitted every time a response is received from the remote service:

```php
<?php
use Akismet\{Blog, Client, ResponseEvent};
use Nyholm\Psr7\{Uri};

function main(): void {
  $client = new Client('123YourAPIKey', new Blog(new Uri('https://www.yourblog.com')));
  $client->addListener(Client::eventResponse, function(ResponseEvent $event) {
    echo 'Server response: ', $event->getResponse()->getStatusCode();
  });
}
```
