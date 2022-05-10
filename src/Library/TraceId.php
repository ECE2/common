<?php

declare(strict_types=1);

namespace Ece2\Common\Library;

use Hyperf\Utils\Context;
use OpenTracing\Span;
use Zipkin\Propagation\TraceContext;
use ZipkinOpenTracing\SpanContext;
use function Zipkin\Propagation\Id\generateNextId;

class TraceId
{
    public static function get()
    {
        // hyperf/tracer 的 trace root id
        if (($tracerRoot = Context::get('tracer.root')) && $tracerRoot instanceof Span
            && ($spanContext = $tracerRoot->getContext()) && $spanContext instanceof SpanContext
            && ($traceContext = $spanContext->getContext()) && $traceContext instanceof TraceContext) {
            return $traceContext->getTraceId();
        }

        // 如果没有 hyperf/tracer 或者空的话, 就自己生成
        return Context::getOrSet('tracerId', generateNextId());
    }
}
