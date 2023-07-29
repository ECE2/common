<?php

declare(strict_types=1);

namespace Ece2\Common\Listener;

use Ece2\Common\Model\Scopes\CompanyIsolateScope;
use Hyperf\Database\Model\Events\Creating;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;

#[Listener]
class CreatingListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            Creating::class
        ];
    }

    public function process(object $event): void
    {
        if ($event instanceof Creating) {
            $model = $event->getModel();

            // 公司隔离的模型, 自动设置 company_id
            if (method_exists($model, 'setCompanyId')) {
                $model->setCompanyId(CompanyIsolateScope::getCompanyId());
            }
        }
    }
}
