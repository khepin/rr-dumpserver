<?php declare(strict_types=1);

namespace Khepin\RRDumpServer;

use Spiral\Goridge\RPC;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

class RRDumper
{
    protected static RRDumper $instance;

    protected RPC $rpc;

    protected VarCloner $cloner;

    protected HtmlDumper $dumper;

    public static function i() : RRDumper
    {
        return self::$instance;
    }

    public static function setupInstance(RPC $rpc)
    {
        self::$instance = new self;
        self::$instance->rpc = $rpc;
        self::$instance->cloner = new VarCloner();
        self::$instance->dumper = new HtmlDumper();
    }

    public function dump($variable) : string
    {
        // Get the backtrace and the information we care about from it.
        $bt = debug_backtrace();
        // We expect not to be called directly but to have been called from the `rrdump` function
        // if we were called directly, this `1` would be `0` etc...
        $file = $bt[1]['file'];
        $line = $bt[1]['line'];
        $funcOrMethod = $bt[2]['function'];
        $args = $bt[2]['args'];

        // our response data containing all the elements we care about
        $result = json_encode([
            'stacktrace' => $this->getDumpString($bt),
            'file' => $file,
            'line' => $line,
            'func' => $funcOrMethod,
            'args' => $this->getDumpString($args),
            'dump' => $this->getDumpString($variable),
            'epochms' => (int) (microtime(true) *1000)
        ]);

        // Sending our info to the roadrunner service
        return $this->rpc->call("dumpserver.SendDebugInfo", $result);
    }

    public function getDumpString($variable) : string
    {
        $output = '';

        $this->dumper->dump(
            $this->cloner->cloneVar($variable),
            function ($line, $depth) use (&$output) {
                // A negative depth means "end of dump"
                if ($depth >= 0) {
                    // Adds a two spaces indentation to the line
                    $output .= str_repeat('  ', $depth).$line."\n";
                }
            }
        );

        return $output;
    }
}

