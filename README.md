# rr-dumpserver

A debugger / dumpserver for Roadrunner.

When I started working with Roadrunner for PHP, I found the debugging experience somewhat frustrating.
I've grown used to Laravel's `dump` and `dd` functions and wanted something similar for Roadrunner development.

This is an attempat at that. It works as a plugin / Service for Roadrunner that you enable in your dev environment.
It serves a UI on a given port where you can see the results of your dump calls.

Those calls are made using the global `rrdump` helper function.

## Sample

Note while the gif mentions gRPC, it's not the case that this is limited to gRPC.

![debug-gif](https://raw.githubusercontent.com/khepin/rr-dumpserver/main/debugger.gif)

## Setup

### Appserver

In your `main.go`:

```go
package main

import (
    // ...
	"github.com/spiral/roadrunner/service/rpc"
	dumpserver "github.com/khepin/rr-dumpserver"
)

func main() {
    // Other service registration
    // ...
	rr.Container.Register(rpc.ID, &rpc.Service{}) // rpc is required
	rr.Container.Register(dumpserver.ID, &dumpserver.Service{})

	rr.Execute()
}

```

In your `.rr.yaml`

```yaml
rpc:
  enable: true
  listen: tcp://127.0.0.1:6001

dumpserver:
  enable: true
  HistorySize: 2000 # How many dumps to keep in memory
  address: :8089
```

### PHP Code

Install the package via composer: `composer require khepin/rr-dumpserver`

Initialize the dumper with your RPC parameters:

```php
use Khepin\RRDumpServer\RRDumper;
use Spiral\Goridge\RPC;
use Spiral\Goridge\SocketRelay;

$relay = new SocketRelay("127.0.0.1", 6001);
$rpc = new RPC($relay);

RRDumper::setupInstance($rpc);
```

## Usage

From anywhere in your code, just call `rrdump($var)`. Then navigate to `localhost:8089` or whichever address / port you've made the dumpserver available at
and review the dumped data in your browser.
