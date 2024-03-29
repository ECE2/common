<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Traits;

use Ece2\Common\Model\Rpc\Model\Attachment;
use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Relations\MorphToMany;

/**
 * @property Collection<Attachment> $attachments
 */
trait HasAttachments
{
    use HasRelationshipsForRpc;

    public function attachments(): MorphToMany
    {
        return $this
            ->rpcMorphToMany(Attachment::class, 'object', 'model_has_attachments')
            ->withPivot('type');
    }
}
