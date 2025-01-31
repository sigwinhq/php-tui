<?php

namespace PhpTui\Tui\Model;

interface Backend
{
    public function size(): Area;

    public function draw(BufferUpdates $updates): void;

    public function flush(): void;

    public function clearRegion(ClearType $type): void;
}
