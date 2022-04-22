<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Traits;

use Ece2\Common\Model\Rpc\Model\Attachment;
use Hyperf\Database\Model\Relations\MorphToMany;

trait HasAttachments
{
    use HasRelationshipsForRpc;

    public function attachments(): MorphToMany
    {
        return $this
            ->rpcMorphToMany(Attachment::class, 'subject', 'model_has_attachments') // model_has_attachments 在每个项目内自己保存关系
            ->withPivot('type');
    }
}
